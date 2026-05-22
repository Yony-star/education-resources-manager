# AGENT 05 — Shortcode + Frontend (CSS + JS + AJAX)
## Education Resources Manager

---

## 🎯 Misión de Este Agente

Crear el shortcode `[recursos_educativos]` con su plantilla HTML, los estilos CSS responsivos, y el JavaScript que implementa los filtros AJAX usando la REST API del plugin.

**Depende de:** Agent 04 (endpoints REST disponibles)

---

## 📦 Archivos a Generar

1. `includes/class-erm-shortcode.php` — Clase del shortcode
2. `public/views/shortcode-template.php` — Plantilla HTML del listado
3. `public/css/erm-public.css` — Estilos del frontend
4. `public/js/erm-public.js` — Lógica AJAX y filtros

---

## 📋 Contexto Heredado

```
Shortcode: [recursos_educativos]
REST endpoint: GET /wp-json/erm/v1/resources
REST endpoint: POST /wp-json/erm/v1/resources/{id}/track
Nonce: wp_create_nonce('wp_rest') → pasado con wp_localize_script
```

---

## 📋 Instrucciones Detalladas

### Archivo 1: `includes/class-erm-shortcode.php`

**Clase:** `ERM_Shortcode`

#### Método `render($atts)`

Parsea los atributos del shortcode:
```php
$atts = shortcode_atts([
    'type'       => '',          // Filtro inicial por tipo
    'difficulty' => '',          // Filtro inicial por nivel
    'category'   => '',          // Slug de categoría
    'per_page'   => 10,          // Recursos por página
    'orderby'    => 'date',
    'order'      => 'DESC',
    'title'      => __('Recursos Educativos', 'education-resources-manager'),
    'show_filters' => 'yes',     // Mostrar/ocultar filtros
], $atts, 'recursos_educativos');
```

- Sanea cada atributo (`sanitize_text_field`, `absint`, etc.)
- Encola los assets (CSS y JS)
- Llama a `wp_localize_script()` para pasar datos a JS
- Incluye la plantilla con `include()`
- Retorna el output del buffer (`ob_start()` / `ob_get_clean()`)

#### Método `enqueue_assets()`

```php
public function enqueue_assets() {
    wp_enqueue_style(
        'erm-public',
        ERM_PLUGIN_URL . 'public/css/erm-public.css',
        [],
        ERM_VERSION
    );

    wp_enqueue_script(
        'erm-public',
        ERM_PLUGIN_URL . 'public/js/erm-public.js',
        ['jquery'],
        ERM_VERSION,
        true  // En el footer
    );

    wp_localize_script('erm-public', 'ermPublic', [
        'apiUrl'   => esc_url_raw(rest_url('erm/v1')),
        'nonce'    => wp_create_nonce('wp_rest'),
        'i18n'     => [
            'loading'    => __('Cargando recursos...', 'education-resources-manager'),
            'no_results' => __('No se encontraron recursos.', 'education-resources-manager'),
            'error'      => __('Error al cargar los recursos.', 'education-resources-manager'),
            'view'       => __('Ver recurso', 'education-resources-manager'),
            'free'       => __('Gratuito', 'education-resources-manager'),
        ],
        'perPage'  => 10,
        'siteUrl'  => get_site_url(),
    ]);
}
```

---

### Archivo 2: `public/views/shortcode-template.php`

Plantilla PHP con el HTML del shortcode. Usa variables `$atts` disponibles del scope.

**Estructura HTML:**
```html
<div id="erm-resources-container" class="erm-container" 
     data-per-page="<?php echo esc_attr($atts['per_page']); ?>"
     data-type="<?php echo esc_attr($atts['type']); ?>"
     data-difficulty="<?php echo esc_attr($atts['difficulty']); ?>"
     data-category="<?php echo esc_attr($atts['category']); ?>">

  <!-- HEADER -->
  <?php if (!empty($atts['title'])): ?>
  <h2 class="erm-title"><?php echo esc_html($atts['title']); ?></h2>
  <?php endif; ?>

  <!-- SECCIÓN DE FILTROS (si show_filters = yes) -->
  <?php if ($atts['show_filters'] === 'yes'): ?>
  <div class="erm-filters" role="search" aria-label="<?php esc_attr_e('Filtros de recursos', 'education-resources-manager'); ?>">
    
    <!-- Búsqueda por texto -->
    <div class="erm-filter-group">
      <label for="erm-search"><?php esc_html_e('Buscar', 'education-resources-manager'); ?></label>
      <input type="text" id="erm-search" class="erm-filter-input" 
             placeholder="<?php esc_attr_e('Buscar recursos...', 'education-resources-manager'); ?>"
             aria-label="<?php esc_attr_e('Buscar recursos por texto', 'education-resources-manager'); ?>">
    </div>

    <!-- Filtro por tipo -->
    <div class="erm-filter-group">
      <label for="erm-type"><?php esc_html_e('Tipo', 'education-resources-manager'); ?></label>
      <select id="erm-type" class="erm-filter-select" aria-label="<?php esc_attr_e('Filtrar por tipo', 'education-resources-manager'); ?>">
        <option value=""><?php esc_html_e('Todos los tipos', 'education-resources-manager'); ?></option>
        <option value="course"><?php esc_html_e('Curso', 'education-resources-manager'); ?></option>
        <option value="tutorial"><?php esc_html_e('Tutorial', 'education-resources-manager'); ?></option>
        <option value="ebook"><?php esc_html_e('Ebook', 'education-resources-manager'); ?></option>
        <option value="video"><?php esc_html_e('Video', 'education-resources-manager'); ?></option>
      </select>
    </div>

    <!-- Filtro por nivel -->
    <div class="erm-filter-group">
      <label for="erm-difficulty"><?php esc_html_e('Nivel', 'education-resources-manager'); ?></label>
      <select id="erm-difficulty" class="erm-filter-select">
        <option value=""><?php esc_html_e('Todos los niveles', 'education-resources-manager'); ?></option>
        <option value="beginner"><?php esc_html_e('Principiante', 'education-resources-manager'); ?></option>
        <option value="intermediate"><?php esc_html_e('Intermedio', 'education-resources-manager'); ?></option>
        <option value="advanced"><?php esc_html_e('Avanzado', 'education-resources-manager'); ?></option>
      </select>
    </div>

    <!-- Botón limpiar filtros -->
    <button type="button" id="erm-clear-filters" class="erm-btn erm-btn-secondary">
      <?php esc_html_e('Limpiar filtros', 'education-resources-manager'); ?>
    </button>

  </div>
  <?php endif; ?>

  <!-- ESTADO DE CARGA -->
  <div id="erm-loading" class="erm-loading" aria-live="polite" style="display:none;">
    <span class="erm-spinner" aria-hidden="true"></span>
    <span><?php esc_html_e('Cargando recursos...', 'education-resources-manager'); ?></span>
  </div>

  <!-- GRID DE RECURSOS (se llena via JS) -->
  <div id="erm-resources-grid" class="erm-grid" role="list" aria-label="<?php esc_attr_e('Lista de recursos educativos', 'education-resources-manager'); ?>">
    <!-- Las tarjetas se insertan aquí por JavaScript -->
  </div>

  <!-- PAGINACIÓN -->
  <div id="erm-pagination" class="erm-pagination" aria-label="<?php esc_attr_e('Paginación', 'education-resources-manager'); ?>">
    <!-- Los botones de página se insertan por JavaScript -->
  </div>

</div>
```

---

### Archivo 3: `public/css/erm-public.css`

CSS responsivo, limpio, sin frameworks. Variables CSS para fácil personalización.

```css
/* Variables */
:root {
    --erm-primary: #2271b1;
    --erm-primary-hover: #135e96;
    --erm-secondary: #f6f7f7;
    --erm-border: #dcdcde;
    --erm-text: #3c434a;
    --erm-text-light: #646970;
    --erm-radius: 8px;
    --erm-shadow: 0 2px 8px rgba(0,0,0,0.08);
    --erm-gap: 24px;
    --erm-card-min: 280px;
}
```

Incluir estilos para:
- `.erm-container` — contenedor principal con max-width y centrado
- `.erm-title` — título de la sección
- `.erm-filters` — barra de filtros en flex wrap, gap entre items
- `.erm-filter-group` — grupo label + input/select con flex column
- `.erm-filter-input`, `.erm-filter-select` — inputs con borde, radius, padding
- `.erm-btn` — botón base con padding, radius, cursor pointer
- `.erm-btn-primary` — botón primario con color `--erm-primary`
- `.erm-btn-secondary` — botón secundario con borde
- `.erm-grid` — CSS Grid con `repeat(auto-fill, minmax(var(--erm-card-min), 1fr))`
- `.erm-card` — tarjeta con shadow, radius, overflow hidden, hover transform
- `.erm-card__image` — imagen con aspect-ratio 16/9, object-fit cover
- `.erm-card__body` — padding interno
- `.erm-card__title` — título del recurso
- `.erm-card__meta` — badges de tipo y nivel en flex wrap
- `.erm-badge` — badge pequeño con background y border-radius
- `.erm-badge--course`, `--tutorial`, `--ebook`, `--video` — colores diferentes por tipo
- `.erm-badge--beginner`, `--intermediate`, `--advanced` — colores por nivel
- `.erm-card__duration` — duración con ícono de reloj (unicode)
- `.erm-card__price` — precio o "Gratuito"
- `.erm-card__footer` — botón "Ver recurso" a todo el ancho
- `.erm-loading` — estado de carga con spinner CSS (keyframes animation)
- `.erm-spinner` — spinner circular CSS puro (border + border-top de color, border-radius 50%, animation)
- `.erm-pagination` — paginación centrada con botones
- `.erm-pagination__btn` — botón de página con estado active y hover
- `.erm-no-results` — mensaje de sin resultados centrado
- Media queries para mobile: filtros en columna, grid de 1 columna

---

### Archivo 4: `public/js/erm-public.js`

JavaScript modular usando IIFE o módulo ES. **No jQuery para la lógica principal** (solo como dependencia declarada pero usar `fetch()` nativo para las llamadas a la REST API).

**Estructura:**
```javascript
(function() {
    'use strict';

    // =====================
    // ESTADO
    // =====================
    const state = {
        currentPage: 1,
        perPage: 10,
        filters: {
            search: '',
            type: '',
            difficulty: '',
            category: '',
        },
        isLoading: false,
        totalPages: 1,
    };

    // =====================
    // SELECTORES DOM
    // =====================
    const container   = document.getElementById('erm-resources-container');
    const grid        = document.getElementById('erm-resources-grid');
    const pagination  = document.getElementById('erm-pagination');
    const loadingEl   = document.getElementById('erm-loading');
    const searchInput = document.getElementById('erm-search');
    const typeSelect  = document.getElementById('erm-type');
    const diffSelect  = document.getElementById('erm-difficulty');
    const clearBtn    = document.getElementById('erm-clear-filters');

    // =====================
    // API
    // =====================
    async function fetchResources() {
        if (state.isLoading) return;
        state.isLoading = true;
        showLoading(true);

        const params = new URLSearchParams({
            page:       state.currentPage,
            per_page:   state.perPage,
            search:     state.filters.search,
            type:       state.filters.type,
            difficulty: state.filters.difficulty,
            category:   state.filters.category,
        });

        // Remover params vacíos
        for (const [key, val] of params.entries()) {
            if (!val) params.delete(key);
        }

        try {
            const response = await fetch(`${ermPublic.apiUrl}/resources?${params}`, {
                headers: { 'X-WP-Nonce': ermPublic.nonce }
            });

            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            const data = await response.json();

            if (data.success) {
                renderResources(data.data.resources);
                renderPagination(data.data.pagination);
                state.totalPages = data.data.pagination.total_pages;
            } else {
                renderError(ermPublic.i18n.error);
            }
        } catch (err) {
            renderError(ermPublic.i18n.error);
            console.error('[ERM] Error al cargar recursos:', err);
        } finally {
            state.isLoading = false;
            showLoading(false);
        }
    }

    async function trackResource(resourceId, actionType = 'view') {
        try {
            await fetch(`${ermPublic.apiUrl}/resources/${resourceId}/track`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': ermPublic.nonce,
                },
                body: JSON.stringify({ action_type: actionType }),
            });
        } catch (err) {
            // Silencioso — el tracking no debe interrumpir la experiencia
            console.warn('[ERM] Error al registrar tracking:', err);
        }
    }

    // =====================
    // RENDER
    // =====================
    function renderResources(resources) {
        if (!resources.length) {
            grid.innerHTML = `<p class="erm-no-results">${ermPublic.i18n.no_results}</p>`;
            return;
        }

        grid.innerHTML = resources.map(resource => createCardHTML(resource)).join('');

        // Bind click en botones "Ver recurso"
        grid.querySelectorAll('.erm-card__btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const resourceId  = this.dataset.resourceId;
                const resourceUrl = this.dataset.resourceUrl;
                trackResource(resourceId, 'view');
                if (resourceUrl) {
                    window.open(resourceUrl, '_blank', 'noopener noreferrer');
                }
            });
        });
    }

    function createCardHTML(resource) {
        const price = resource.price === 0
            ? `<span class="erm-price erm-price--free">${ermPublic.i18n.free}</span>`
            : `<span class="erm-price">$${parseFloat(resource.price).toFixed(2)}</span>`;

        const duration = resource.duration_minutes
            ? `<span class="erm-card__duration">⏱ ${resource.duration_minutes} min</span>`
            : '';

        const image = resource.featured_image
            ? `<div class="erm-card__image"><img src="${escapeHTML(resource.featured_image)}" alt="${escapeHTML(resource.title)}" loading="lazy"></div>`
            : `<div class="erm-card__image erm-card__image--placeholder" aria-hidden="true"></div>`;

        const typeLabel = {
            course: 'Curso', tutorial: 'Tutorial', ebook: 'Ebook', video: 'Video'
        }[resource.type] || resource.type;

        const diffLabel = {
            beginner: 'Principiante', intermediate: 'Intermedio', advanced: 'Avanzado'
        }[resource.difficulty] || resource.difficulty;

        return `
            <article class="erm-card" role="listitem">
                ${image}
                <div class="erm-card__body">
                    <div class="erm-card__meta">
                        <span class="erm-badge erm-badge--${escapeHTML(resource.type)}">${escapeHTML(typeLabel)}</span>
                        <span class="erm-badge erm-badge--${escapeHTML(resource.difficulty)}">${escapeHTML(diffLabel)}</span>
                    </div>
                    <h3 class="erm-card__title">${escapeHTML(resource.title)}</h3>
                    <p class="erm-card__excerpt">${escapeHTML(resource.excerpt)}</p>
                    <div class="erm-card__info">
                        ${duration}
                        ${price}
                        <span class="erm-card__views">👁 ${resource.views || 0}</span>
                    </div>
                </div>
                <div class="erm-card__footer">
                    <button type="button" class="erm-btn erm-btn-primary erm-card__btn"
                            data-resource-id="${resource.id}"
                            data-resource-url="${escapeHTML(resource.url || resource.permalink)}"
                            aria-label="${escapeHTML(ermPublic.i18n.view + ': ' + resource.title)}">
                        ${ermPublic.i18n.view} →
                    </button>
                </div>
            </article>`;
    }

    function renderPagination(paginationData) {
        if (paginationData.total_pages <= 1) {
            pagination.innerHTML = '';
            return;
        }
        let html = '';
        for (let i = 1; i <= paginationData.total_pages; i++) {
            const active = i === paginationData.current_page ? ' erm-pagination__btn--active' : '';
            html += `<button type="button" class="erm-pagination__btn${active}" data-page="${i}" aria-label="Página ${i}" ${i === paginationData.current_page ? 'aria-current="page"' : ''}>${i}</button>`;
        }
        pagination.innerHTML = html;

        pagination.querySelectorAll('.erm-pagination__btn').forEach(btn => {
            btn.addEventListener('click', function() {
                state.currentPage = parseInt(this.dataset.page, 10);
                fetchResources();
                container.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });
    }

    function renderError(message) {
        grid.innerHTML = `<p class="erm-error" role="alert">${message}</p>`;
    }

    function showLoading(show) {
        if (loadingEl) loadingEl.style.display = show ? 'flex' : 'none';
        if (grid) grid.style.opacity = show ? '0.5' : '1';
    }

    // =====================
    // UTILIDADES
    // =====================
    function escapeHTML(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Debounce para el campo de búsqueda
    function debounce(fn, delay) {
        let timer;
        return function(...args) {
            clearTimeout(timer);
            timer = setTimeout(() => fn.apply(this, args), delay);
        };
    }

    // =====================
    // EVENT LISTENERS
    // =====================
    function initEventListeners() {
        if (!container) return;

        // Leer configuración inicial del container
        state.perPage  = parseInt(container.dataset.perPage, 10) || 10;
        state.filters.type       = container.dataset.type || '';
        state.filters.difficulty = container.dataset.difficulty || '';
        state.filters.category   = container.dataset.category || '';

        // Prellenar selects con filtros iniciales
        if (typeSelect && state.filters.type) typeSelect.value = state.filters.type;
        if (diffSelect && state.filters.difficulty) diffSelect.value = state.filters.difficulty;

        // Búsqueda con debounce de 400ms
        if (searchInput) {
            searchInput.addEventListener('input', debounce(function() {
                state.filters.search = this.value.trim();
                state.currentPage = 1;
                fetchResources();
            }, 400));
        }

        // Filtro de tipo
        if (typeSelect) {
            typeSelect.addEventListener('change', function() {
                state.filters.type = this.value;
                state.currentPage = 1;
                fetchResources();
            });
        }

        // Filtro de dificultad
        if (diffSelect) {
            diffSelect.addEventListener('change', function() {
                state.filters.difficulty = this.value;
                state.currentPage = 1;
                fetchResources();
            });
        }

        // Limpiar filtros
        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                state.filters = { search: '', type: '', difficulty: '', category: '' };
                state.currentPage = 1;
                if (searchInput) searchInput.value = '';
                if (typeSelect) typeSelect.value = '';
                if (diffSelect) diffSelect.value = '';
                fetchResources();
            });
        }
    }

    // =====================
    // INIT
    // =====================
    function init() {
        if (!container) return; // El shortcode no está en esta página
        initEventListeners();
        fetchResources(); // Carga inicial
    }

    // Esperar DOM listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
```

---

## ✅ Criterios de Aceptación

- [ ] El shortcode `[recursos_educativos]` renderiza en el frontend
- [ ] Los recursos se cargan automáticamente al abrir la página
- [ ] Los filtros (tipo, nivel, búsqueda) actualizan el listado sin recargar
- [ ] El debounce en la búsqueda espera 400ms antes de hacer la petición
- [ ] El botón "Ver recurso" registra el tracking y abre la URL en nueva pestaña
- [ ] Los estados de carga (`loading`) son visibles
- [ ] La paginación funciona correctamente
- [ ] El botón "Limpiar filtros" resetea todo
- [ ] El diseño es responsivo (mobile-first)
- [ ] No hay errores en la consola del navegador
- [ ] Los textos están preparados para internacionalización (`__()`)
- [ ] El HTML generado por JS escapa el contenido (XSS prevention)
