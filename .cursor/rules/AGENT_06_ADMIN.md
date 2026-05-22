# AGENT 06 — Panel de Administración
## Education Resources Manager

---

## 🎯 Misión de Este Agente

Crear el panel de administración personalizado con listado de recursos, filtros, y estadísticas. Incluye la clase PHP, las vistas HTML/PHP, y los assets CSS/JS del admin.

**Depende de:** Agent 02 (CPT + meta keys), Agent 03 (ERM_Database métodos)

---

## 📦 Archivos a Generar

1. `includes/class-erm-admin.php` — Clase principal del admin
2. `admin/views/admin-page-main.php` — Vista principal con listado y filtros
3. `admin/views/admin-page-stats.php` — Vista de estadísticas
4. `admin/css/erm-admin.css` — Estilos del admin
5. `admin/js/erm-admin.js` — JavaScript del admin (gráfico + filtros)

---

## 📋 Contexto Heredado

```
CPT: education_resource
Meta keys: _erm_resource_type, _erm_difficulty_level, _erm_price, _erm_instructor
Taxonomías: resource_category, skill_tag
Tabla: {prefix}_erm_tracking
ERM_Database: get_top_resources(), get_stats_summary(), get_monthly_stats()
Constantes: ERM_PLUGIN_DIR, ERM_PLUGIN_URL, ERM_VERSION
```

---

## 📋 Instrucciones Detalladas

### Archivo 1: `includes/class-erm-admin.php`

**Clase:** `ERM_Admin`

#### Método `add_admin_menu()` — hook: `admin_menu`

```php
public function add_admin_menu() {
    // Página principal (tabla de recursos)
    add_menu_page(
        __('Recursos Educativos', 'education-resources-manager'),
        __('Recursos Edu.', 'education-resources-manager'),
        'manage_options',
        'erm-resources',
        [$this, 'render_main_page'],
        'dashicons-welcome-learn-more',
        25
    );

    // Submenú: Todos los recursos (mismo que menú principal)
    add_submenu_page(
        'erm-resources',
        __('Todos los Recursos', 'education-resources-manager'),
        __('Todos los Recursos', 'education-resources-manager'),
        'manage_options',
        'erm-resources',
        [$this, 'render_main_page']
    );

    // Submenú: Estadísticas
    add_submenu_page(
        'erm-resources',
        __('Estadísticas', 'education-resources-manager'),
        __('Estadísticas', 'education-resources-manager'),
        'manage_options',
        'erm-stats',
        [$this, 'render_stats_page']
    );
}
```

#### Método `enqueue_scripts($hook)` — hook: `admin_enqueue_scripts`

Solo encolar en las páginas del plugin:
```php
public function enqueue_scripts($hook) {
    $plugin_pages = ['toplevel_page_erm-resources', 'recursos-edu_page_erm-stats'];
    if (!in_array($hook, $plugin_pages, true)) {
        return;
    }

    wp_enqueue_style(
        'erm-admin',
        ERM_PLUGIN_URL . 'admin/css/erm-admin.css',
        [],
        ERM_VERSION
    );

    wp_enqueue_script(
        'erm-admin',
        ERM_PLUGIN_URL . 'admin/js/erm-admin.js',
        ['jquery'],
        ERM_VERSION,
        true
    );

    wp_localize_script('erm-admin', 'ermAdmin', [
        'apiUrl' => esc_url_raw(rest_url('erm/v1')),
        'nonce'  => wp_create_nonce('wp_rest'),
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'adminNonce' => wp_create_nonce('erm_admin_nonce'),
    ]);
}
```

#### Método `render_main_page()`

Verificar permisos: `if (!current_user_can('manage_options')) wp_die(...)`.

Obtener filtros desde `$_GET` (sanitizados):
```php
$filter_type       = sanitize_text_field($_GET['erm_type'] ?? '');
$filter_difficulty = sanitize_text_field($_GET['erm_difficulty'] ?? '');
$filter_category   = sanitize_text_field($_GET['erm_category'] ?? '');
$search            = sanitize_text_field($_GET['s'] ?? '');
$paged             = absint($_GET['paged'] ?? 1);
```

Construir `WP_Query` con los filtros y pasar los resultados a la vista:
```php
include ERM_PLUGIN_DIR . 'admin/views/admin-page-main.php';
```

#### Método `render_stats_page()`

Verificar permisos. Obtener datos:
```php
$db       = new ERM_Database();
$stats    = $db->get_stats_summary();
$top_5    = $db->get_top_resources(5);
$monthly  = $db->get_monthly_stats(6);

include ERM_PLUGIN_DIR . 'admin/views/admin-page-stats.php';
```

---

### Archivo 2: `admin/views/admin-page-main.php`

Vista principal. Variables disponibles desde la clase: `$query`, `$filter_type`, `$filter_difficulty`, `$filter_category`, `$search`.

**Estructura:**

```html
<div class="wrap erm-admin-wrap">
    <h1 class="wp-heading-inline">
        <?php esc_html_e('Recursos Educativos', 'education-resources-manager'); ?>
    </h1>
    <a href="<?php echo esc_url(admin_url('post-new.php?post_type=education_resource')); ?>" class="page-title-action">
        <?php esc_html_e('Añadir Nuevo', 'education-resources-manager'); ?>
    </a>
    <hr class="wp-header-end">

    <!-- FORMULARIO DE FILTROS -->
    <form method="get" action="" class="erm-admin-filters">
        <input type="hidden" name="page" value="erm-resources">
        
        <!-- Búsqueda -->
        <input type="search" name="s" value="<?php echo esc_attr($search); ?>"
               placeholder="<?php esc_attr_e('Buscar recursos...', 'education-resources-manager'); ?>">
        
        <!-- Filtro tipo -->
        <select name="erm_type">
            <option value=""><?php esc_html_e('Todos los tipos', 'education-resources-manager'); ?></option>
            <?php foreach (['course' => 'Curso', 'tutorial' => 'Tutorial', 'ebook' => 'Ebook', 'video' => 'Video'] as $val => $label): ?>
            <option value="<?php echo esc_attr($val); ?>" <?php selected($filter_type, $val); ?>>
                <?php echo esc_html($label); ?>
            </option>
            <?php endforeach; ?>
        </select>

        <!-- Filtro nivel -->
        <select name="erm_difficulty">
            <option value=""><?php esc_html_e('Todos los niveles', 'education-resources-manager'); ?></option>
            <?php foreach (['beginner' => 'Principiante', 'intermediate' => 'Intermedio', 'advanced' => 'Avanzado'] as $val => $label): ?>
            <option value="<?php echo esc_attr($val); ?>" <?php selected($filter_difficulty, $val); ?>>
                <?php echo esc_html($label); ?>
            </option>
            <?php endforeach; ?>
        </select>

        <?php submit_button(__('Filtrar', 'education-resources-manager'), 'secondary', 'submit', false); ?>
        <a href="<?php echo esc_url(admin_url('admin.php?page=erm-resources')); ?>" class="button">
            <?php esc_html_e('Limpiar', 'education-resources-manager'); ?>
        </a>
    </form>

    <!-- RESUMEN RÁPIDO (3 tarjetas de stats) -->
    <div class="erm-stats-quick">
        <!-- Total recursos | Total vistas | Total descargas -->
        <!-- Datos tomados de ERM_Database::get_stats_summary() -->
    </div>

    <!-- TABLA DE RECURSOS -->
    <?php if ($query->have_posts()): ?>
    <table class="wp-list-table widefat fixed striped erm-resources-table">
        <thead>
            <tr>
                <th><?php esc_html_e('Título', 'education-resources-manager'); ?></th>
                <th><?php esc_html_e('Tipo', 'education-resources-manager'); ?></th>
                <th><?php esc_html_e('Nivel', 'education-resources-manager'); ?></th>
                <th><?php esc_html_e('Instructor', 'education-resources-manager'); ?></th>
                <th><?php esc_html_e('Precio', 'education-resources-manager'); ?></th>
                <th><?php esc_html_e('Visualizaciones', 'education-resources-manager'); ?></th>
                <th><?php esc_html_e('Fecha', 'education-resources-manager'); ?></th>
                <th><?php esc_html_e('Acciones', 'education-resources-manager'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php while ($query->have_posts()): $query->the_post(); ?>
            <tr>
                <td>
                    <strong>
                        <a href="<?php echo esc_url(get_edit_post_link()); ?>">
                            <?php the_title(); ?>
                        </a>
                    </strong>
                </td>
                <td><?php /* badge con tipo */ ?></td>
                <td><?php /* badge con nivel */ ?></td>
                <td><?php echo esc_html(get_post_meta(get_the_ID(), '_erm_instructor', true)); ?></td>
                <td><?php /* precio o "Gratuito" */ ?></td>
                <td><?php /* conteo de visualizaciones */ ?></td>
                <td><?php echo esc_html(get_the_date()); ?></td>
                <td>
                    <a href="<?php echo esc_url(get_edit_post_link()); ?>" class="button button-small">
                        <?php esc_html_e('Editar', 'education-resources-manager'); ?>
                    </a>
                    <a href="<?php echo esc_url(get_permalink()); ?>" class="button button-small" target="_blank">
                        <?php esc_html_e('Ver', 'education-resources-manager'); ?>
                    </a>
                </td>
            </tr>
            <?php endwhile; wp_reset_postdata(); ?>
        </tbody>
    </table>

    <!-- PAGINACIÓN WordPress -->
    <?php
    echo paginate_links([
        'total'   => $query->max_num_pages,
        'current' => $paged,
        'format'  => '?paged=%#%',
        'add_args' => array_filter(['erm_type' => $filter_type, 'erm_difficulty' => $filter_difficulty, 's' => $search]),
    ]);
    ?>

    <?php else: ?>
    <div class="erm-no-resources">
        <p><?php esc_html_e('No se encontraron recursos con los filtros aplicados.', 'education-resources-manager'); ?></p>
    </div>
    <?php endif; ?>
</div>
```

---

### Archivo 3: `admin/views/admin-page-stats.php`

Variables disponibles: `$stats`, `$top_5`, `$monthly`.

**Estructura:**

```html
<div class="wrap erm-admin-wrap">
    <h1><?php esc_html_e('Estadísticas', 'education-resources-manager'); ?></h1>
    <hr class="wp-header-end">

    <!-- TARJETAS DE RESUMEN -->
    <div class="erm-stats-cards">
        <div class="erm-stat-card">
            <span class="erm-stat-card__icon dashicons dashicons-welcome-learn-more"></span>
            <span class="erm-stat-card__number"><?php echo esc_html($stats['total_resources']); ?></span>
            <span class="erm-stat-card__label"><?php esc_html_e('Total Recursos', 'education-resources-manager'); ?></span>
        </div>
        <!-- Total vistas, total descargas, usuarios únicos -->
    </div>

    <!-- RECURSOS POR TIPO (tabla con porcentajes) -->
    <div class="erm-stats-section">
        <h2><?php esc_html_e('Recursos por Tipo', 'education-resources-manager'); ?></h2>
        <table class="widefat">
            <!-- Iterar $stats['by_type'] -->
        </table>
    </div>

    <!-- TOP 5 RECURSOS -->
    <div class="erm-stats-section">
        <h2><?php esc_html_e('Top 5 Recursos Más Vistos', 'education-resources-manager'); ?></h2>
        <ol class="erm-top-list">
            <?php foreach ($top_5 as $item): ?>
            <li>
                <strong><?php echo esc_html($item->post_title); ?></strong>
                <span class="erm-badge"><?php echo esc_html($item->action_count); ?> vistas</span>
            </li>
            <?php endforeach; ?>
        </ol>
    </div>

    <!-- GRÁFICO DE RECURSOS POR MES (canvas HTML5) -->
    <div class="erm-stats-section">
        <h2><?php esc_html_e('Recursos Creados por Mes (últimos 6 meses)', 'education-resources-manager'); ?></h2>
        <canvas id="erm-monthly-chart" width="700" height="300" 
                aria-label="<?php esc_attr_e('Gráfico de recursos por mes', 'education-resources-manager'); ?>"
                role="img">
        </canvas>
        <!-- Datos pasados via data attributes para el JS -->
        <script>
        var ermMonthlyData = <?php echo wp_json_encode($monthly); ?>;
        </script>
    </div>
</div>
```

---

### Archivo 4: `admin/css/erm-admin.css`

Estilos que extienden el admin de WordPress sin romper su apariencia:

```css
/* Variables usando los colores del admin de WP */
.erm-admin-wrap { max-width: 1400px; }

/* Filtros */
.erm-admin-filters { display: flex; gap: 8px; align-items: center; margin: 16px 0; flex-wrap: wrap; }

/* Tarjetas de stats */
.erm-stats-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin: 20px 0; }
.erm-stat-card { background: #fff; border: 1px solid #c3c4c7; border-radius: 4px; padding: 20px; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
.erm-stat-card__number { display: block; font-size: 2rem; font-weight: 700; color: #2271b1; }
.erm-stat-card__label { display: block; font-size: 0.875rem; color: #646970; margin-top: 4px; }
.erm-stat-card__icon { font-size: 1.5rem; color: #2271b1; margin-bottom: 8px; }

/* Badges de tipo y nivel */
.erm-type-badge, .erm-level-badge { display: inline-block; padding: 2px 8px; border-radius: 3px; font-size: 0.75rem; font-weight: 600; }
.erm-type-badge--course { background: #d1f0e0; color: #0a6640; }
.erm-type-badge--tutorial { background: #cce5ff; color: #004085; }
.erm-type-badge--ebook { background: #fff3cd; color: #856404; }
.erm-type-badge--video { background: #f8d7da; color: #721c24; }

/* Top list */
.erm-top-list { counter-reset: top-counter; list-style: none; padding: 0; }
.erm-top-list li { counter-increment: top-counter; display: flex; justify-content: space-between; align-items: center; padding: 10px 16px; border-bottom: 1px solid #f0f0f1; }
.erm-top-list li::before { content: counter(top-counter); font-size: 1.25rem; font-weight: 700; color: #2271b1; min-width: 30px; }

/* Stats sections */
.erm-stats-section { background: #fff; border: 1px solid #c3c4c7; border-radius: 4px; padding: 20px; margin: 20px 0; }
.erm-stats-section h2 { margin-top: 0; border-bottom: 1px solid #f0f0f1; padding-bottom: 10px; }

/* Canvas del gráfico */
#erm-monthly-chart { max-width: 100%; height: auto; }
```

---

### Archivo 5: `admin/js/erm-admin.js`

JavaScript para el gráfico de barras mensual. Usar **Canvas API nativo** (sin Chart.js ni librerías externas).

```javascript
(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        var canvas = document.getElementById('erm-monthly-chart');
        if (!canvas || typeof ermMonthlyData === 'undefined') return;

        var ctx = canvas.getContext('2d');
        var data = ermMonthlyData; // Array de {month, total}

        if (!data || !data.length) {
            ctx.font = '16px Arial';
            ctx.fillStyle = '#646970';
            ctx.textAlign = 'center';
            ctx.fillText('No hay datos disponibles', canvas.width / 2, canvas.height / 2);
            return;
        }

        // Configuración del gráfico
        var padding = { top: 30, right: 20, bottom: 50, left: 50 };
        var chartW   = canvas.width - padding.left - padding.right;
        var chartH   = canvas.height - padding.top - padding.bottom;
        var maxVal   = Math.max.apply(null, data.map(function(d) { return parseInt(d.total, 10); })) || 1;
        var barW     = (chartW / data.length) * 0.7;
        var barGap   = (chartW / data.length) * 0.3;

        // Fondo
        ctx.fillStyle = '#fff';
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        // Líneas de guía
        ctx.strokeStyle = '#f0f0f1';
        ctx.lineWidth = 1;
        var steps = 5;
        for (var i = 0; i <= steps; i++) {
            var y = padding.top + chartH - (chartH / steps * i);
            ctx.beginPath();
            ctx.moveTo(padding.left, y);
            ctx.lineTo(padding.left + chartW, y);
            ctx.stroke();
            // Etiqueta eje Y
            ctx.fillStyle = '#646970';
            ctx.font = '12px Arial';
            ctx.textAlign = 'right';
            ctx.fillText(Math.round(maxVal / steps * i), padding.left - 8, y + 4);
        }

        // Barras y etiquetas eje X
        data.forEach(function(d, i) {
            var val  = parseInt(d.total, 10);
            var barH = (val / maxVal) * chartH;
            var x    = padding.left + i * (barW + barGap) + barGap / 2;
            var y    = padding.top + chartH - barH;

            // Barra
            ctx.fillStyle = '#2271b1';
            ctx.fillRect(x, y, barW, barH);

            // Valor encima de la barra
            ctx.fillStyle = '#3c434a';
            ctx.font = 'bold 13px Arial';
            ctx.textAlign = 'center';
            ctx.fillText(val, x + barW / 2, y - 6);

            // Etiqueta mes en eje X
            ctx.fillStyle = '#646970';
            ctx.font = '11px Arial';
            ctx.fillText(d.month, x + barW / 2, padding.top + chartH + 20);
        });

        // Eje X
        ctx.strokeStyle = '#c3c4c7';
        ctx.lineWidth = 2;
        ctx.beginPath();
        ctx.moveTo(padding.left, padding.top + chartH);
        ctx.lineTo(padding.left + chartW, padding.top + chartH);
        ctx.stroke();
    });

})();
```

---

## ✅ Criterios de Aceptación

- [ ] El menú "Recursos Edu." aparece en el sidebar del admin de WordPress
- [ ] Los submenús "Todos los Recursos" y "Estadísticas" funcionan
- [ ] La tabla de recursos muestra título, tipo, nivel, instructor, precio y vistas
- [ ] Los filtros (tipo, nivel, búsqueda) filtran correctamente la tabla
- [ ] La paginación funciona con los filtros aplicados
- [ ] Los assets CSS/JS solo se cargan en las páginas del plugin
- [ ] La página de estadísticas muestra las tarjetas de resumen
- [ ] El Top 5 de recursos más vistos se muestra correctamente
- [ ] El gráfico de barras mensual se renderiza sin librerías externas
- [ ] No hay errores PHP en WP_DEBUG
- [ ] Los permisos se verifican (`manage_options`)
