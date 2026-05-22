# AGENT 02 — Custom Post Type + Taxonomías
## Education Resources Manager

---

## 🎯 Misión de Este Agente

Crear las clases `ERM_Post_Type` y `ERM_Taxonomy` que registran el Custom Post Type `education_resource` con todos sus campos personalizados (post meta), y las dos taxonomías: `resource_category` (jerárquica) y `skill_tag` (no jerárquica).

**Depende de:** Agent 01 (constantes disponibles: `ERM_PLUGIN_DIR`, `ERM_PLUGIN_URL`)

---

## 📦 Archivos a Generar

1. `includes/class-erm-post-type.php` — CPT + meta boxes + save meta
2. `includes/class-erm-taxonomy.php` — Taxonomías personalizadas

---

## 📋 Instrucciones Detalladas

### Contexto Heredado
```php
// Constantes disponibles
ERM_VERSION       // '1.0.0'
ERM_PLUGIN_DIR    // plugin_dir_path(__FILE__)
ERM_PLUGIN_URL    // plugin_dir_url(__FILE__)

// Prefijo de la tabla
global $wpdb;
$table = $wpdb->prefix . 'erm_tracking';

// CPT slug
'education_resource'

// Meta keys
'_erm_resource_type'       // course | tutorial | ebook | video
'_erm_difficulty_level'    // beginner | intermediate | advanced
'_erm_duration_minutes'    // integer positivo
'_erm_resource_url'        // URL válida
'_erm_instructor'          // string libre
'_erm_price'               // float >= 0 (0 = gratuito)
```

---

### Archivo 1: `includes/class-erm-post-type.php`

**Clase:** `ERM_Post_Type`

#### Método `register()` — hook: `init`

Llama a `register_post_type('education_resource', $args)` con:

**Labels (en español):**
- name: Recursos Educativos
- singular_name: Recurso Educativo
- add_new: Añadir Nuevo
- add_new_item: Añadir Nuevo Recurso
- edit_item: Editar Recurso
- new_item: Nuevo Recurso
- view_item: Ver Recurso
- search_items: Buscar Recursos
- not_found: No se encontraron recursos
- not_found_in_trash: No hay recursos en la papelera
- menu_name: Recursos Edu.

**Args del CPT:**
```php
'public'             => true,
'publicly_queryable' => true,
'show_ui'            => true,
'show_in_menu'       => true,
'show_in_nav_menus'  => true,
'show_in_admin_bar'  => true,
'show_in_rest'       => true,        // Expuesto a REST API
'menu_position'      => 20,
'menu_icon'          => 'dashicons-welcome-learn-more',
'capability_type'    => 'post',
'hierarchical'       => false,
'supports'           => ['title', 'editor', 'thumbnail', 'excerpt', 'author', 'custom-fields'],
'taxonomies'         => ['resource_category', 'skill_tag'],
'has_archive'        => true,
'rewrite'            => ['slug' => 'recursos', 'with_front' => false],
'query_var'          => true,
```

#### Método `add_meta_boxes()` — hook: `add_meta_boxes`

Registra UN meta box llamado "Detalles del Recurso" que aparece en el CPT. El meta box llama a `render_meta_box()`.

#### Método `render_meta_box($post)`

Genera el HTML del formulario de meta box con campos:
1. **Tipo de recurso** — `<select>` con: course, tutorial, ebook, video
2. **Nivel de dificultad** — `<select>` con: beginner, intermediate, advanced
3. **Duración (minutos)** — `<input type="number" min="0">`
4. **URL del recurso** — `<input type="url">`
5. **Instructor/Autor** — `<input type="text">`
6. **Precio** — `<input type="number" min="0" step="0.01">` + texto "(0 = gratuito)"

Incluir nonce con `wp_nonce_field('erm_save_meta', 'erm_meta_nonce')`.

Cada campo hace `esc_attr(get_post_meta($post->ID, '_erm_*', true))` para prellenar valores.

#### Método `save_meta($post_id)` — hook: `save_post_education_resource`

Validaciones antes de guardar:
1. Verificar nonce: `wp_verify_nonce($_POST['erm_meta_nonce'], 'erm_save_meta')`
2. Verificar `current_user_can('edit_post', $post_id)`
3. Verificar que NO es autosave: `if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return`

Guardar cada campo con la función apropiada:
- `_erm_resource_type`: `sanitize_text_field()` + validar que esté en `['course','tutorial','ebook','video']`
- `_erm_difficulty_level`: `sanitize_text_field()` + validar en `['beginner','intermediate','advanced']`
- `_erm_duration_minutes`: `absint()`
- `_erm_resource_url`: `esc_url_raw()`
- `_erm_instructor`: `sanitize_text_field()`
- `_erm_price`: `floatval()` + `max(0, $price)`

Usar `update_post_meta($post_id, '_erm_*', $value)` para cada uno.

#### Método `add_custom_columns($columns)` — hook: `manage_education_resource_posts_columns`

Añade columnas personalizadas al listado del admin:
- Tipo
- Nivel
- Duración
- Precio
- Visualizaciones

#### Método `render_custom_columns($column, $post_id)` — hook: `manage_education_resource_posts_custom_column`

Renderiza el valor de cada columna personalizada usando `get_post_meta()`.

---

### Archivo 2: `includes/class-erm-taxonomy.php`

**Clase:** `ERM_Taxonomy`

#### Método `register()` — hook: `init`

**Taxonomía 1: `resource_category`** (jerárquica — como categorías)

```php
register_taxonomy('resource_category', 'education_resource', [
    'labels' => [
        'name'              => 'Categorías de Recursos',
        'singular_name'     => 'Categoría',
        'search_items'      => 'Buscar Categorías',
        'all_items'         => 'Todas las Categorías',
        'parent_item'       => 'Categoría padre',
        'parent_item_colon' => 'Categoría padre:',
        'edit_item'         => 'Editar Categoría',
        'update_item'       => 'Actualizar Categoría',
        'add_new_item'      => 'Añadir Nueva Categoría',
        'new_item_name'     => 'Nueva Categoría',
        'menu_name'         => 'Categorías',
    ],
    'hierarchical'      => true,
    'show_ui'           => true,
    'show_admin_column' => true,
    'show_in_rest'      => true,
    'query_var'         => true,
    'rewrite'           => ['slug' => 'categoria-recurso'],
]);
```

**Taxonomía 2: `skill_tag`** (no jerárquica — como tags)

```php
register_taxonomy('skill_tag', 'education_resource', [
    'labels' => [
        'name'                       => 'Etiquetas de Habilidades',
        'singular_name'              => 'Habilidad',
        'search_items'               => 'Buscar Habilidades',
        'popular_items'              => 'Habilidades Populares',
        'all_items'                  => 'Todas las Habilidades',
        'edit_item'                  => 'Editar Habilidad',
        'update_item'                => 'Actualizar Habilidad',
        'add_new_item'               => 'Añadir Nueva Habilidad',
        'new_item_name'              => 'Nueva Habilidad',
        'separate_items_with_commas' => 'Separar habilidades con comas',
        'add_or_remove_items'        => 'Añadir o quitar habilidades',
        'choose_from_most_used'      => 'Elegir de las más usadas',
        'menu_name'                  => 'Habilidades',
    ],
    'hierarchical'      => false,
    'show_ui'           => true,
    'show_admin_column' => true,
    'show_in_rest'      => true,
    'query_var'         => true,
    'rewrite'           => ['slug' => 'habilidad'],
]);
```

---

## ✅ Criterios de Aceptación

- [ ] CPT `education_resource` aparece en el menú admin
- [ ] El ícono del menú es `dashicons-welcome-learn-more`
- [ ] Las taxonomías aparecen como submenús del CPT
- [ ] El meta box de "Detalles del Recurso" aparece en el editor
- [ ] Los datos del meta box se guardan correctamente
- [ ] El nonce se verifica antes de guardar
- [ ] Los inputs están sanitizados
- [ ] Las columnas personalizadas aparecen en el listado
- [ ] El CPT está expuesto a la REST API (`show_in_rest: true`)
- [ ] Las taxonomías están expuestas a la REST API

---

## 🔗 Output para Agentes Posteriores

Los agentes 04 (REST API) y 06 (Admin) necesitan saber:
- CPT slug: `education_resource`
- Taxonomía 1: `resource_category` (jerárquica)
- Taxonomía 2: `skill_tag` (no jerárquica)
- Meta keys disponibles con sus valores válidos (documentados arriba)
