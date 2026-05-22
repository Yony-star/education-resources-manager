# Arquitectura — Education Resources Manager

## 1. Visión general

**Education Resources Manager (ERM)** es un plugin de WordPress para gestionar recursos educativos en una prueba técnica de nivel senior. Permite:

- Crear y clasificar recursos (`education_resource`) con metadatos (tipo, dificultad, precio, etc.).
- Registrar visualizaciones, descargas y completados en una tabla dedicada.
- Exponer los datos vía REST API (`erm/v1`).
- Mostrar un listado filtrable en el frontend con el shortcode `[recursos_educativos]`.
- Consultar estadísticas desde un panel de administración propio.

**Público objetivo:** administradores del sitio (gestión de contenido y métricas) y visitantes (consulta y consumo de recursos).

**Alcance:** plugin autocontenido, sin dependencias Composer/npm en runtime. Prefijos `erm_` / `ERM_`. WordPress 6.0+, PHP 7.4+.

---

## 2. Diagrama de arquitectura

```
┌─────────────────────────────────────────────────────────────────────────┐
│                         WordPress Core                                   │
│  (posts, postmeta, terms, REST infrastructure, hooks, capabilities)   │
└─────────────────────────────────────────────────────────────────────────┘
                                    ▲
                                    │ hooks / $wpdb
┌───────────────────────────────────┴───────────────────────────────────┐
│              education-resources-manager.php (bootstrap)                   │
│  define ERM_* constants → require classes → register_activation_hook    │
│  plugins_loaded → run_erm() → ERM_Loader                                  │
└───────────────────────────────────┬───────────────────────────────────┘
                                    │
        ┌───────────────────────────┼───────────────────────────┐
        │                           │                           │
        ▼                           ▼                           ▼
┌───────────────┐         ┌─────────────────┐         ┌─────────────────┐
│  Admin (WP)   │         │  Frontend       │         │  REST Clients   │
│  ERM_Admin    │         │  ERM_Shortcode  │         │  (fetch / apps) │
│  CPT editor   │         │  erm-public.js  │         │  erm/v1/*       │
└───────┬───────┘         └────────┬────────┘         └────────┬────────┘
        │                          │                             │
        │                          └──────────────┬──────────────┘
        │                                         ▼
        │                              ┌─────────────────────┐
        │                              │   ERM_REST_API      │
        │                              └──────────┬──────────┘
        │                                         │
        ▼                                         ▼
┌───────────────────────────────────────────────────────────────────────┐
│                        Capa de dominio                                 │
│  ERM_Post_Type │ ERM_Taxonomy │ ERM_Database │ ERM_Activator/Deactivator │
└───────────────────────────────────────────────────────────────────────┘
        │                    │                    │
        ▼                    ▼                    ▼
┌──────────────┐    ┌────────────────┐    ┌─────────────────────────┐
│ wp_posts     │    │ wp_terms /     │    │ {prefix}_erm_tracking   │
│ wp_postmeta  │    │ relationships  │    │ (tabla custom)          │
└──────────────┘    └────────────────┘    └─────────────────────────┘
```

---

## 3. Estructura de componentes

### `ERM_Activator`

- Valida PHP ≥ 7.4 y WordPress ≥ 6.0 (`check_requirements()`).
- Crea la tabla `{prefix}_erm_tracking` con `dbDelta()` (`create_tables()`).
- Registra opciones `erm_version` y `erm_db_version` (`set_default_options()`).
- Ejecuta `flush_rewrite_rules()`.

### `ERM_Deactivator`

- `flush_rewrite_rules()`.
- Elimina transients `erm_top_resources` y `erm_stats_summary` (y variantes por límite/tipo vía `ERM_Database::invalidate_cache()`).

### `ERM_Loader`

- Patrón collector: acumula acciones y filtros en arrays y los registra en `run()`.
- Desacopla el bootstrap del registro directo de hooks en el archivo principal.

### `ERM_Post_Type`

- Registra CPT `education_resource` (`init`).
- Meta box «Detalles del Recurso» con nonce `erm_save_meta`.
- Guardado en `save_post_education_resource` con sanitización y listas blancas de tipo/dificultad.
- Columnas admin: Tipo, Nivel, Duración, Precio, Visualizaciones (consulta `ERM_Database::get_resource_views()`).

### `ERM_Taxonomy`

- `resource_category` (jerárquica, slug rewrite `categoria-recurso`).
- `skill_tag` (no jerárquica, slug `habilidad`).
- Ambas con `show_in_rest => true`.

### `ERM_Database`

- Acceso a `{prefix}_erm_tracking`.
- `insert_tracking()`, `get_resource_views()`, `get_top_resources()`, `get_monthly_stats()`, `get_stats_summary()`, `get_resource_tracking_count()`, `invalidate_cache()`.
- Caché con transients (1 hora) en consultas agregadas.
- Todas las consultas SQL usan `$wpdb->prepare()`.

### `ERM_Admin`

- Menú `erm-resources` y submenú `erm-stats` (`manage_options`).
- Listado con filtros GET (`erm_type`, `erm_difficulty`, `s`, paginación).
- Página de estadísticas: tarjetas, tabla por tipo, top 5, gráfico Canvas.
- Assets solo en `toplevel_page_erm-resources` y `erm-resources_page_erm-stats`.

### `ERM_Shortcode`

- Shortcode `[recursos_educativos]` con atributos: `type`, `difficulty`, `category`, `per_page`, `orderby`, `order`, `title`, `show_filters`.
- Plantilla `public/views/shortcode-template.php`.
- Encola `erm-public.css` / `erm-public.js` y localiza `ermPublic` (API URL, nonce REST, i18n).

### `ERM_REST_API`

- Namespace `erm/v1`.
- Rutas: listado, detalle, track (POST), stats (admin).
- Validación de argumentos vía `validate_callback` en cada ruta.
- Respuestas envueltas en `{ success, data [, message] }`.

---

## 4. Flujos de datos

### 4.1 Crear / editar un recurso (admin)

```
Usuario admin → editor CPT education_resource
    → meta box (tipo, dificultad, URL, etc.)
    → save_post_education_resource → update_post_meta()
    → datos en wp_posts + wp_postmeta
```

### 4.2 Ver recursos en frontend (shortcode)

```
Página con [recursos_educativos]
    → ERM_Shortcode::render() → enqueue assets
    → erm-public.js → GET /wp-json/erm/v1/resources?...
    → ERM_REST_API::get_resources() → WP_Query
    → JSON → renderizado de tarjetas en el DOM
```

### 4.3 Filtrar sin recargar (AJAX vía REST)

```
Usuario cambia filtro / búsqueda (debounce 400 ms)
    → fetchResources() actualiza state.filters
    → nueva petición GET /resources con query params
    → re-render grid + paginación
```

### 4.4 Registrar tracking

```
Clic en «Ver recurso» (frontend)
    → POST /resources/{id}/track { action_type: "view" }
    → ERM_Database::insert_tracking()
    → INSERT en erm_tracking + invalidate_cache()
    → abre URL del recurso en nueva pestaña
```

---

## 5. Decisiones técnicas

| Decisión | Justificación |
|----------|----------------|
| **CPT para recursos** | Aprovecha editor, revisiones, REST nativo, taxonomías, permisos y UI de WordPress sin reinventar CRUD. |
| **Tabla custom para tracking** | Alto volumen de eventos append-only; evita inflar `postmeta` y permite índices orientados a analítica. |
| **REST API para filtros frontend** | Contrato estable, cacheable, testeable con cURL; mismo endpoint usable por apps externas. |
| **Canvas nativo en admin** | Sin dependencias JS adicionales; cumple requisito de gráfico ligero en el panel. |
| **Transients en estadísticas** | Reduce carga en `get_stats_summary()` y `get_top_resources()`; se invalidan al insertar tracking. |
| **ERM_Loader** | Mantiene `education-resources-manager.php` legible y facilita extender hooks. |

---

## 6. Seguridad

- **Capabilities:** admin y stats requieren `manage_options`; REST `/stats` igual.
- **Nonces:** meta box `erm_save_meta`; REST con header `X-WP-Nonce` (`wp_rest`).
- **Sanitización:** `sanitize_text_field`, `absint`, `esc_url_raw`, `floatval` en meta y filtros.
- **Escape en vistas:** `esc_html`, `esc_attr`, `esc_url` en PHP; `escapeHTML()` en JS público.
- **SQL:** `$wpdb->prepare()` en todas las queries directas a `erm_tracking`.
- **Tracking POST:** validación de `action_type` en lista blanca; comprobación de que el post es `education_resource`.

---

## 7. Rendimiento

- Transients de 1 hora para agregados (`erm_stats_summary`, `erm_top_resources_{type}_{limit}`).
- `invalidate_cache()` tras cada `insert_tracking()`.
- Assets admin y públicos solo cuando se necesitan (páginas del plugin / shortcode presente).
- `per_page` máximo 100 en REST; listado admin: 20 posts por página.
- Índices en `resource_id`, `action_type`, `action_date` para conteos y tops.

---

## 8. Dependencias

| Requisito | Versión |
|-----------|---------|
| WordPress | 6.0+ |
| PHP | 7.4+ |
| MySQL / MariaDB | 5.7+ / 10.3+ |
| Extensiones PHP | estándar (mysqli, json) |

No requiere Node, Composer ni plugins de terceros en producción.

---

## 9. Estructura de directorios

```
education-resources-manager/
├── education-resources-manager.php
├── uninstall.php
├── includes/          # Clases PHP
├── admin/             # CSS, JS y vistas del panel
├── public/            # Assets y plantilla del shortcode
├── docs/              # Documentación técnica
├── database/          # schema.sql, sample-data.sql
└── languages/         # Traducciones (stub)
```
