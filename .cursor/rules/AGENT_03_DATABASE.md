# AGENT 03 — Database + Activator + Deactivator
## Education Resources Manager

---

## 🎯 Misión de Este Agente

Crear la tabla personalizada de tracking, el sistema de CRUD para esa tabla, y las clases de activación/desactivación del plugin.

**Depende de:** Agent 01 (constantes disponibles)

---

## 📦 Archivos a Generar

1. `includes/class-erm-activator.php` — Activación del plugin
2. `includes/class-erm-deactivator.php` — Desactivación del plugin
3. `includes/class-erm-database.php` — CRUD y queries para la tabla custom

---

## 📋 Instrucciones Detalladas

### Contexto Heredado
```
Tabla: {prefix}_erm_tracking
Constantes: ERM_VERSION, ERM_DB_VERSION, ERM_PLUGIN_DIR
```

---

### Archivo 1: `includes/class-erm-activator.php`

**Clase:** `ERM_Activator`

Todos los métodos son `public static`.

#### `activate()`
Llama en orden:
1. `self::check_requirements()`
2. `self::create_tables()`
3. `self::set_default_options()`
4. `flush_rewrite_rules()`

#### `check_requirements()`
```php
global $wp_version;
if (version_compare(PHP_VERSION, '7.4', '<')) {
    wp_die(
        esc_html__('Education Resources Manager requiere PHP 7.4 o superior.', 'education-resources-manager'),
        esc_html__('Error de activación', 'education-resources-manager'),
        ['back_link' => true]
    );
}
if (version_compare($wp_version, '6.0', '<')) {
    wp_die(
        esc_html__('Education Resources Manager requiere WordPress 6.0 o superior.', 'education-resources-manager'),
        esc_html__('Error de activación', 'education-resources-manager'),
        ['back_link' => true]
    );
}
```

#### `create_tables()`
Usar `dbDelta()` (no `CREATE TABLE IF NOT EXISTS` directo). Requiere `require_once(ABSPATH . 'wp-admin/includes/upgrade.php')`.

**SQL de la tabla `{prefix}_erm_tracking`:**
```sql
CREATE TABLE {table_name} (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    resource_id BIGINT(20) UNSIGNED NOT NULL,
    user_id BIGINT(20) UNSIGNED DEFAULT NULL,
    action_type VARCHAR(20) NOT NULL DEFAULT 'view',
    action_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    PRIMARY KEY  (id),
    KEY resource_id (resource_id),
    KEY user_id (user_id),
    KEY action_date (action_date),
    KEY action_type (action_type)
) {charset_collate};
```

⚠️ **Nota crítica para `dbDelta()`:** El SQL debe tener exactamente 2 espacios antes de `PRIMARY KEY`, y las columnas separadas por `\n`. Respetar esto o `dbDelta` no funcionará.

#### `set_default_options()`
```php
add_option('erm_version', ERM_VERSION);
add_option('erm_db_version', ERM_DB_VERSION);
```
Usar `add_option` (no `update_option`) para no sobreescribir si ya existe.

---

### Archivo 2: `includes/class-erm-deactivator.php`

**Clase:** `ERM_Deactivator`

#### `deactivate()` (public static)
Solo hace:
```php
flush_rewrite_rules();
// Limpiar transients del plugin
delete_transient('erm_top_resources');
delete_transient('erm_stats_summary');
```

**NO** eliminar la tabla ni las opciones (eso es para uninstall.php, que ya hizo Agent 01).

---

### Archivo 3: `includes/class-erm-database.php`

**Clase:** `ERM_Database`

Propiedad privada: `$table_name` — se define en el constructor como `$wpdb->prefix . 'erm_tracking'`.

#### Constructor `__construct()`
```php
global $wpdb;
$this->table_name = $wpdb->prefix . 'erm_tracking';
```

---

#### Método `insert_tracking($resource_id, $action_type = 'view')`

**Propósito:** Registrar una visualización/descarga de un recurso.

Validaciones:
- `$resource_id` debe ser un entero positivo (`absint()`)
- `$action_type` debe estar en `['view', 'download', 'complete']`
- Si el post con ese ID no existe o no es `education_resource`, retornar `false`

Datos a insertar:
```php
$data = [
    'resource_id' => absint($resource_id),
    'user_id'     => get_current_user_id() ?: null,
    'action_type' => sanitize_text_field($action_type),
    'ip_address'  => $this->get_client_ip(),
    'user_agent'  => isset($_SERVER['HTTP_USER_AGENT']) 
                     ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) 
                     : '',
];
$format = ['%d', '%d', '%s', '%s', '%s'];

$result = $wpdb->insert($this->table_name, $data, $format);
return $result !== false ? $wpdb->insert_id : false;
```

#### Método privado `get_client_ip()`

Detecta la IP real del cliente considerando proxies:
- Revisar `HTTP_X_FORWARDED_FOR`, `HTTP_CLIENT_IP`, `REMOTE_ADDR`
- Validar con `filter_var($ip, FILTER_VALIDATE_IP)`
- Retornar string sanitizado

---

#### Método `get_resource_views($resource_id)`

```php
global $wpdb;
return (int) $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM {$this->table_name} WHERE resource_id = %d AND action_type = %s",
    absint($resource_id),
    'view'
));
```

---

#### Método `get_top_resources($limit = 5, $action_type = 'view')`

Retorna top N recursos más vistos. Usa JOIN con `$wpdb->posts`.

```php
global $wpdb;

// Usar transient para cache
$cache_key = 'erm_top_resources_' . $action_type . '_' . $limit;
$cached = get_transient($cache_key);
if ($cached !== false) {
    return $cached;
}

$results = $wpdb->get_results($wpdb->prepare(
    "SELECT 
        t.resource_id,
        p.post_title,
        COUNT(t.id) as action_count
    FROM {$this->table_name} t
    INNER JOIN {$wpdb->posts} p ON t.resource_id = p.ID
    WHERE 
        t.action_type = %s
        AND p.post_type = %s
        AND p.post_status = %s
    GROUP BY t.resource_id, p.post_title
    ORDER BY action_count DESC
    LIMIT %d",
    $action_type,
    'education_resource',
    'publish',
    absint($limit)
));

set_transient($cache_key, $results, HOUR_IN_SECONDS);
return $results;
```

---

#### Método `get_monthly_stats($months = 6)`

Estadísticas de recursos creados por mes (últimos N meses). Consulta `$wpdb->posts`.

```php
global $wpdb;

return $wpdb->get_results($wpdb->prepare(
    "SELECT 
        DATE_FORMAT(post_date, '%%Y-%%m') as month,
        COUNT(*) as total
    FROM {$wpdb->posts}
    WHERE 
        post_type = %s
        AND post_status = %s
        AND post_date >= DATE_SUB(NOW(), INTERVAL %d MONTH)
    GROUP BY month
    ORDER BY month ASC",
    'education_resource',
    'publish',
    absint($months)
));
```

---

#### Método `get_stats_summary()`

Retorna un array con estadísticas generales. Usa transient para cache (1 hora).

```php
// Usar transient
$cache_key = 'erm_stats_summary';
$cached = get_transient($cache_key);
if ($cached !== false) {
    return $cached;
}

global $wpdb;

// Total por tipo (via postmeta)
$by_type = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT pm.meta_value as type, COUNT(*) as total
        FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
        WHERE p.post_type = %s
        AND p.post_status = %s
        AND pm.meta_key = %s
        GROUP BY pm.meta_value",
        'education_resource', 'publish', '_erm_resource_type'
    )
);

// Total vistas y descargas
$totals = $wpdb->get_row(
    $wpdb->prepare(
        "SELECT
            SUM(CASE WHEN action_type = %s THEN 1 ELSE 0 END) as total_views,
            SUM(CASE WHEN action_type = %s THEN 1 ELSE 0 END) as total_downloads,
            COUNT(DISTINCT user_id) as unique_users
        FROM {$this->table_name}",
        'view',
        'download'
    )
);

$stats = [
    'total_resources' => wp_count_posts('education_resource')->publish ?? 0,
    'by_type'         => $by_type,
    'total_views'     => $totals->total_views ?? 0,
    'total_downloads' => $totals->total_downloads ?? 0,
    'unique_users'    => $totals->unique_users ?? 0,
];

set_transient($cache_key, $stats, HOUR_IN_SECONDS);
return $stats;
```

#### Método `get_resource_tracking_count($resource_id, $action_type = null)`

Retorna el conteo de tracking para un recurso específico, opcionalmente filtrado por tipo.

#### Método `invalidate_cache()`

Borra los transients del plugin:
```php
delete_transient('erm_stats_summary');
delete_transient('erm_top_resources_view_5');
delete_transient('erm_top_resources_download_5');
```

Llamar este método después de cualquier `insert_tracking()`.

---

## ✅ Criterios de Aceptación

- [ ] La tabla se crea correctamente al activar el plugin (verificar con phpMyAdmin o Query Monitor)
- [ ] `dbDelta()` no genera errores
- [ ] `insert_tracking()` inserta un registro correctamente
- [ ] `get_top_resources()` retorna resultados ordenados por conteo
- [ ] `get_monthly_stats()` retorna datos agrupados por mes
- [ ] Los prepared statements usan `$wpdb->prepare()` en TODAS las queries directas
- [ ] Los transients se usan para cachear queries costosas
- [ ] La IP del cliente se obtiene y valida correctamente
- [ ] Al desactivar el plugin, solo se limpian los transients (no la tabla)

---

## 🔗 Output para Agentes Posteriores

Agent 04 (REST API) y Agent 06 (Admin) usarán:
- `ERM_Database::insert_tracking($resource_id, $action_type)`
- `ERM_Database::get_top_resources($limit)`
- `ERM_Database::get_stats_summary()`
- `ERM_Database::get_monthly_stats($months)`
- Nombre de tabla: `$wpdb->prefix . 'erm_tracking'`
