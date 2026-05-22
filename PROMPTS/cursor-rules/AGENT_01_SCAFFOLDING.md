# AGENT 01 — Scaffolding + Plugin Principal
## Education Resources Manager

---

## 🎯 Misión de Este Agente

Crear la **estructura completa de archivos y carpetas** del plugin, el archivo principal `education-resources-manager.php`, el loader de hooks, el `.gitignore`, y el `uninstall.php`. Este es el primer agente y no tiene dependencias.

---

## 📦 Archivos a Generar

1. `education-resources-manager.php` — Archivo principal del plugin
2. `includes/class-erm-loader.php` — Gestor de hooks
3. `uninstall.php` — Script de desinstalación limpia
4. `.gitignore` — Ignora node_modules, vendor, etc.
5. `README.md` — Readme del plugin (instalación, uso del shortcode)
6. Todas las **carpetas vacías** con `.gitkeep` donde corresponda

---

## 📋 Instrucciones Detalladas

### Contexto del Proyecto
- Plugin: `education-resources-manager`
- Prefijo: `erm_` para funciones, `ERM_` para clases y constantes
- WordPress mínimo: 6.0 | PHP mínimo: 7.4
- Sigue WordPress Coding Standards (tabs, no espacios)

---

### Archivo 1: `education-resources-manager.php`

Debe incluir:

```
/**
 * Plugin Name: Education Resources Manager
 * Plugin URI: https://example.com/erm
 * Description: Sistema de gestión de recursos educativos para WordPress
 * Version: 1.0.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: [Nombre del candidato]
 * License: GPL-2.0+
 * Text Domain: education-resources-manager
 * Domain Path: /languages
 */
```

Debe:
- Definir las constantes: `ERM_VERSION`, `ERM_PLUGIN_DIR`, `ERM_PLUGIN_URL`, `ERM_PLUGIN_BASENAME`, `ERM_DB_VERSION`
- Registrar `register_activation_hook` → llama a `ERM_Activator::activate()`
- Registrar `register_deactivation_hook` → llama a `ERM_Deactivator::deactivate()`
- Tener una función `run_erm()` que:
  - Instancia todas las clases principales
  - Usa `ERM_Loader` para registrar todos los hooks
  - Se ejecuta en `plugins_loaded`
- Hacer `require_once` de todos los archivos en `includes/`
- Incluir el guard: `if ( ! defined( 'WPINC' ) ) { die; }`

Las clases que debe instanciar y hookear:
- `ERM_Post_Type` → `add_action('init', [$post_type, 'register'])`
- `ERM_Taxonomy` → `add_action('init', [$taxonomy, 'register'])`
- `ERM_Database` → instanciada (sin hook en init, solo disponible)
- `ERM_Admin` → `add_action('admin_menu', [$admin, 'add_admin_menu'])` y `add_action('admin_enqueue_scripts', [$admin, 'enqueue_scripts'])`
- `ERM_Shortcode` → `add_shortcode('recursos_educativos', [$shortcode, 'render'])`
- `ERM_REST_API` → `add_action('rest_api_init', [$rest_api, 'register_routes'])`

---

### Archivo 2: `includes/class-erm-loader.php`

Clase `ERM_Loader` que:
- Mantiene arrays `$actions` y `$filters`
- Tiene métodos `add_action()` y `add_filter()`
- Tiene método `run()` que registra todos los hooks via `add_action()` y `add_filter()` de WordPress

---

### Archivo 3: `uninstall.php`

Guard con `if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) { exit; }`

Debe limpiar:
- Opción `erm_version` y `erm_db_version` vía `delete_option()`
- Tabla `{prefix}_erm_tracking` con `DROP TABLE IF EXISTS`
- Transients con prefijo `erm_` vía `delete_transient()`
- **NO** eliminar los posts del CPT (son contenido del usuario)

---

### Archivo 4: `.gitignore`

Incluir:
```
/vendor/
/node_modules/
*.log
.DS_Store
Thumbs.db
wp-config.php
/tests/coverage/
*.cache
.env
```

---

### Archivo 5: `README.md`

Debe tener secciones:
1. **Descripción** del plugin
2. **Requisitos** (WP 6.0+, PHP 7.4+)
3. **Instalación** (manual vía ZIP y vía FTP)
4. **Uso del Shortcode**: `[recursos_educativos]` con todos sus atributos posibles:
   - `type` — course | tutorial | ebook | video
   - `difficulty` — beginner | intermediate | advanced
   - `category` — slug de categoría
   - `per_page` — número (default: 10)
5. **REST API** — mención de los endpoints disponibles
6. **Desinstalación**
7. **Changelog**

---

### Carpetas y `.gitkeep`

Crea la estructura de carpetas. Para carpetas que estarán vacías inicialmente, añade un archivo `.gitkeep`:

```
admin/css/.gitkeep
admin/js/.gitkeep
admin/views/.gitkeep
admin/partials/.gitkeep
public/css/.gitkeep
public/js/.gitkeep
public/views/.gitkeep
assets/images/.gitkeep
assets/screenshots/.gitkeep
languages/.gitkeep
```

---

## ✅ Criterios de Aceptación

- [ ] El plugin puede activarse en WordPress sin errores
- [ ] Todas las constantes están definidas correctamente
- [ ] Los hooks de activación y desactivación están registrados
- [ ] `run_erm()` se ejecuta en `plugins_loaded`
- [ ] `uninstall.php` limpia BD sin borrar contenido
- [ ] `.gitignore` es apropiado para un proyecto WordPress/PHP
- [ ] `README.md` explica cómo instalar y usar el shortcode

---

## 🔗 Output para el Siguiente Agente

Tras completar este agente, el contexto disponible para Agent 02 y Agent 03 es:
- Constantes disponibles: `ERM_VERSION`, `ERM_PLUGIN_DIR`, `ERM_PLUGIN_URL`
- Namespace/prefijo: `ERM_` para clases, `erm_` para funciones
- Loader disponible: `ERM_Loader`
- Todos los archivos `includes/class-erm-*.php` serán cargados via `require_once` en el archivo principal
