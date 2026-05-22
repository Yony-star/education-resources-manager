# AGENT 04 — REST API Endpoints
## Education Resources Manager

---

## 🎯 Misión de Este Agente

Crear la clase `ERM_REST_API` con los 4 endpoints REST documentados en la prueba técnica, incluyendo validación de parámetros, permisos, y respuestas estandarizadas.

**Depende de:** Agent 02 (CPT + meta keys), Agent 03 (ERM_Database métodos)

---

## 📦 Archivos a Generar

1. `includes/class-erm-rest-api.php` — Todos los endpoints REST

---

## 📋 Contexto Heredado

```
Namespace REST: erm/v1
Base URL: /wp-json/erm/v1

CPT: education_resource
Taxonomías: resource_category, skill_tag
Meta keys: _erm_resource_type, _erm_difficulty_level, _erm_duration_minutes,
           _erm_resource_url, _erm_instructor, _erm_price

Tipos válidos: course | tutorial | ebook | video
Niveles válidos: beginner | intermediate | advanced
Acciones válidas: view | download | complete

Clase disponible: ERM_Database (métodos: insert_tracking, get_top_resources, get_stats_summary, get_monthly_stats)
```

---

## 📋 Instrucciones Detalladas

### Clase: `ERM_REST_API`

Propiedades privadas:
```php
private $namespace = 'erm/v1';
private $valid_types = ['course', 'tutorial', 'ebook', 'video'];
private $valid_difficulties = ['beginner', 'intermediate', 'advanced'];
private $valid_actions = ['view', 'download', 'complete'];
```

---

### Método `register_routes()` — hook: `rest_api_init`

Registrar estas 4 rutas:

```php
// GET /wp-json/erm/v1/resources
register_rest_route($this->namespace, '/resources', [
    'methods'             => WP_REST_Server::READABLE,
    'callback'            => [$this, 'get_resources'],
    'permission_callback' => '__return_true',
    'args'                => $this->get_resources_args(),
]);

// GET /wp-json/erm/v1/resources/(?P<id>\d+)
register_rest_route($this->namespace, '/resources/(?P<id>\d+)', [
    'methods'             => WP_REST_Server::READABLE,
    'callback'            => [$this, 'get_resource'],
    'permission_callback' => '__return_true',
    'args'                => [
        'id' => [
            'required'          => true,
            'type'              => 'integer',
            'sanitize_callback' => 'absint',
            'validate_callback' => fn($val) => $val > 0,
        ],
    ],
]);

// POST /wp-json/erm/v1/resources/(?P<id>\d+)/track
register_rest_route($this->namespace, '/resources/(?P<id>\d+)/track', [
    'methods'             => WP_REST_Server::CREATABLE,
    'callback'            => [$this, 'track_resource'],
    'permission_callback' => [$this, 'track_permission_check'],
    'args'                => [
        'id' => [
            'required'          => true,
            'type'              => 'integer',
            'sanitize_callback' => 'absint',
        ],
        'action_type' => [
            'required'          => true,
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'validate_callback' => fn($val) => in_array($val, $this->valid_actions, true),
        ],
    ],
]);

// GET /wp-json/erm/v1/stats
register_rest_route($this->namespace, '/stats', [
    'methods'             => WP_REST_Server::READABLE,
    'callback'            => [$this, 'get_stats'],
    'permission_callback' => [$this, 'admin_permission_check'],
    'args'                => [
        'period' => [
            'required'          => false,
            'type'              => 'string',
            'default'           => 'all',
            'sanitize_callback' => 'sanitize_text_field',
            'validate_callback' => fn($val) => in_array($val, ['all', 'month', 'week'], true),
        ],
    ],
]);
```

---

### Método `get_resources_args()`

Retorna el array de definición de parámetros para el endpoint de listado:

```php
private function get_resources_args() {
    return [
        'page'       => ['type' => 'integer', 'default' => 1, 'minimum' => 1, 'sanitize_callback' => 'absint'],
        'per_page'   => ['type' => 'integer', 'default' => 10, 'minimum' => 1, 'maximum' => 100, 'sanitize_callback' => 'absint'],
        'search'     => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
        'type'       => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field',
                         'validate_callback' => fn($val) => empty($val) || in_array($val, $this->valid_types, true)],
        'difficulty' => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field',
                         'validate_callback' => fn($val) => empty($val) || in_array($val, $this->valid_difficulties, true)],
        'category'   => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
        'skill'      => ['type' => 'string', 'sanitize_callback' => 'sanitize_text_field'],
        'orderby'    => ['type' => 'string', 'default' => 'date',
                         'validate_callback' => fn($val) => in_array($val, ['date', 'title', 'views'], true)],
        'order'      => ['type' => 'string', 'default' => 'DESC',
                         'validate_callback' => fn($val) => in_array(strtoupper($val), ['ASC', 'DESC'], true)],
    ];
}
```

---

### Método `get_resources(WP_REST_Request $request)`

**Endpoint:** `GET /wp-json/erm/v1/resources`

```php
public function get_resources(WP_REST_Request $request) {
    $page     = $request->get_param('page');
    $per_page = $request->get_param('per_page');

    $query_args = [
        'post_type'      => 'education_resource',
        'post_status'    => 'publish',
        'posts_per_page' => $per_page,
        'paged'          => $page,
        'orderby'        => $request->get_param('orderby') !== 'views' ? $request->get_param('orderby') : 'date',
        'order'          => strtoupper($request->get_param('order')),
    ];

    // Búsqueda por texto
    if ($search = $request->get_param('search')) {
        $query_args['s'] = $search;
    }

    // Meta query (tipo y dificultad)
    $meta_query = [];
    if ($type = $request->get_param('type')) {
        $meta_query[] = ['key' => '_erm_resource_type', 'value' => $type, 'compare' => '='];
    }
    if ($difficulty = $request->get_param('difficulty')) {
        $meta_query[] = ['key' => '_erm_difficulty_level', 'value' => $difficulty, 'compare' => '='];
    }
    if (!empty($meta_query)) {
        $query_args['meta_query'] = $meta_query;
    }

    // Tax query (categoría y habilidad)
    $tax_query = [];
    if ($category = $request->get_param('category')) {
        $tax_query[] = ['taxonomy' => 'resource_category', 'field' => 'slug', 'terms' => $category];
    }
    if ($skill = $request->get_param('skill')) {
        $tax_query[] = ['taxonomy' => 'skill_tag', 'field' => 'slug', 'terms' => $skill];
    }
    if (!empty($tax_query)) {
        $query_args['tax_query'] = $tax_query;
    }

    $query = new WP_Query($query_args);

    $resources = [];
    $db = new ERM_Database();

    if ($query->have_posts()) {
        foreach ($query->posts as $post) {
            $resources[] = $this->format_resource($post, $db, false); // false = sin contenido completo
        }
    }

    return rest_ensure_response([
        'success' => true,
        'data'    => [
            'resources'  => $resources,
            'pagination' => [
                'total'        => (int) $query->found_posts,
                'total_pages'  => (int) $query->max_num_pages,
                'current_page' => $page,
                'per_page'     => $per_page,
                'has_more'     => $page < $query->max_num_pages,
            ],
        ],
    ]);
}
```

---

### Método `get_resource(WP_REST_Request $request)`

**Endpoint:** `GET /wp-json/erm/v1/resources/{id}`

```php
public function get_resource(WP_REST_Request $request) {
    $id   = $request->get_param('id');
    $post = get_post($id);

    if (!$post || $post->post_type !== 'education_resource' || $post->post_status !== 'publish') {
        return new WP_Error(
            'resource_not_found',
            __('El recurso solicitado no existe.', 'education-resources-manager'),
            ['status' => 404]
        );
    }

    $db = new ERM_Database();

    return rest_ensure_response([
        'success' => true,
        'data'    => $this->format_resource($post, $db, true), // true = con contenido completo
    ]);
}
```

---

### Método `track_resource(WP_REST_Request $request)`

**Endpoint:** `POST /wp-json/erm/v1/resources/{id}/track`

```php
public function track_resource(WP_REST_Request $request) {
    $resource_id = $request->get_param('id');
    $action_type = $request->get_param('action_type');

    // Verificar que el recurso existe
    $post = get_post($resource_id);
    if (!$post || $post->post_type !== 'education_resource') {
        return new WP_Error('resource_not_found', __('Recurso no encontrado.', 'education-resources-manager'), ['status' => 404]);
    }

    $db = new ERM_Database();
    $tracking_id = $db->insert_tracking($resource_id, $action_type);

    if ($tracking_id === false) {
        return new WP_Error(
            'tracking_failed',
            __('No se pudo registrar la acción.', 'education-resources-manager'),
            ['status' => 500]
        );
    }

    return new WP_REST_Response([
        'success' => true,
        'data'    => [
            'tracking_id' => $tracking_id,
            'resource_id' => $resource_id,
            'action_type' => $action_type,
            'timestamp'   => current_time('mysql'),
        ],
        'message' => __('Acción registrada exitosamente.', 'education-resources-manager'),
    ], 201);
}
```

---

### Método `get_stats(WP_REST_Request $request)`

**Endpoint:** `GET /wp-json/erm/v1/stats`

```php
public function get_stats(WP_REST_Request $request) {
    $period = $request->get_param('period');
    $db     = new ERM_Database();
    $summary  = $db->get_stats_summary();
    $top      = $db->get_top_resources(5);
    $monthly  = $db->get_monthly_stats(6);

    return rest_ensure_response([
        'success' => true,
        'data'    => [
            'summary'        => $summary,
            'top_resources'  => $top,
            'monthly_growth' => $monthly,
        ],
    ]);
}
```

---

### Método `track_permission_check(WP_REST_Request $request)`

```php
public function track_permission_check(WP_REST_Request $request) {
    // Permitir usuarios no logueados (se registra con user_id = null)
    // Solo verificar nonce si viene del frontend
    return true; // El nonce se valida a nivel de WP REST API con X-WP-Nonce header
}
```

### Método `admin_permission_check(WP_REST_Request $request)`

```php
public function admin_permission_check(WP_REST_Request $request) {
    if (!current_user_can('manage_options')) {
        return new WP_Error(
            'forbidden',
            __('No tienes permisos para ver las estadísticas.', 'education-resources-manager'),
            ['status' => 403]
        );
    }
    return true;
}
```

---

### Método privado `format_resource($post, $db, $full = false)`

Formatea un objeto `WP_Post` en el array de respuesta estándar.

```php
private function format_resource($post, ERM_Database $db, bool $full = false) {
    $categories = wp_get_post_terms($post->ID, 'resource_category', ['fields' => 'all']);
    $skills     = wp_get_post_terms($post->ID, 'skill_tag', ['fields' => 'all']);

    $resource = [
        'id'               => $post->ID,
        'title'            => get_the_title($post),
        'excerpt'          => get_the_excerpt($post),
        'type'             => get_post_meta($post->ID, '_erm_resource_type', true),
        'difficulty'       => get_post_meta($post->ID, '_erm_difficulty_level', true),
        'duration_minutes' => (int) get_post_meta($post->ID, '_erm_duration_minutes', true),
        'url'              => esc_url(get_post_meta($post->ID, '_erm_resource_url', true)),
        'instructor'       => get_post_meta($post->ID, '_erm_instructor', true),
        'price'            => (float) get_post_meta($post->ID, '_erm_price', true),
        'categories'       => array_map(fn($t) => ['id' => $t->term_id, 'name' => $t->name, 'slug' => $t->slug, 'parent_id' => $t->parent], $categories ?: []),
        'skills'           => array_map(fn($t) => ['id' => $t->term_id, 'name' => $t->name, 'slug' => $t->slug], $skills ?: []),
        'featured_image'   => get_the_post_thumbnail_url($post, 'medium') ?: null,
        'views'            => $db->get_resource_views($post->ID),
        'permalink'        => get_permalink($post),
        'date_created'     => get_the_date('c', $post),
    ];

    if ($full) {
        $resource['content']       = wp_kses_post(apply_filters('the_content', $post->post_content));
        $resource['downloads']     = $db->get_resource_tracking_count($post->ID, 'download');
        $resource['date_modified'] = get_the_modified_date('c', $post);
    }

    return $resource;
}
```

---

## ✅ Criterios de Aceptación

- [ ] `GET /wp-json/erm/v1/resources` responde 200 con paginación
- [ ] Filtros `type`, `difficulty`, `category`, `skill`, `search` funcionan
- [ ] `GET /wp-json/erm/v1/resources/123` responde 200 con detalle completo
- [ ] `GET /wp-json/erm/v1/resources/999` responde 404 con mensaje
- [ ] `POST /wp-json/erm/v1/resources/123/track` inserta en la BD y responde 201
- [ ] `GET /wp-json/erm/v1/stats` responde 403 para usuarios sin permisos
- [ ] `GET /wp-json/erm/v1/stats` responde 200 para administradores
- [ ] Todos los parámetros son validados y sanitizados
- [ ] Los errores retornan `WP_Error` con código HTTP correcto
- [ ] El formato de respuesta es consistente (`success`, `data`, `message`)

---

## 🧪 Prueba con cURL

```bash
# Listar cursos
curl "http://localhost/wp-json/erm/v1/resources?type=course&per_page=3"

# Ver recurso individual
curl "http://localhost/wp-json/erm/v1/resources/1"

# Track (necesita nonce)
curl -X POST "http://localhost/wp-json/erm/v1/resources/1/track" \
  -H "Content-Type: application/json" \
  -H "X-WP-Nonce: TU_NONCE" \
  -d '{"action_type":"view"}'

# Stats (necesita auth)
curl "http://localhost/wp-json/erm/v1/stats" \
  --user "admin:password_de_aplicacion"
```
