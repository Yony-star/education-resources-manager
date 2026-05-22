# .cursorrules — Education Resources Manager
# Reglas globales del proyecto para todos los agentes de Cursor

## Identidad del Proyecto
- Plugin Name: Education Resources Manager
- Prefix: erm_ (funciones), ERM_ (clases y constantes)
- CPT: education_resource
- Taxonomías: resource_category (jerárquica), skill_tag (no jerárquica)
- Tabla custom: {prefix}_erm_tracking
- WP mínimo: 6.0 | PHP mínimo: 7.4

## Reglas de Código PHP

1. SIEMPRE usa tabs para indentación, nunca espacios (WordPress Coding Standards)
2. SIEMPRE incluye el guard `if (!defined('WPINC')) { die; }` en el archivo principal
3. NUNCA uses `$_GET`, `$_POST`, `$_REQUEST` sin sanitizar con `sanitize_text_field()`, `absint()`, o equivalente
4. SIEMPRE usa `$wpdb->prepare()` para queries directas a la base de datos
5. SIEMPRE escapa outputs con `esc_html()`, `esc_attr()`, `esc_url()`, `wp_kses_post()` según contexto
6. SIEMPRE verifica nonces en acciones POST: `wp_verify_nonce($_POST['nonce'], 'action_name')`
7. SIEMPRE verifica capabilities: `current_user_can('manage_options')` para páginas admin
8. NUNCA uses `echo` directamente en plantillas sin escapar el valor
9. NUNCA uses `-1` en `posts_per_page` en endpoints públicos (máximo 100)
10. SIEMPRE usa `add_option()` (no `update_option()`) para configurar defaults en activación

## Convenciones de Nomenclatura

- Clases: `ERM_Post_Type`, `ERM_REST_API`, `ERM_Database` (PascalCase con prefijo ERM_)
- Métodos: `register_routes()`, `get_resources()` (snake_case)
- Funciones standalone: `erm_get_resource()`, `erm_run()` (snake_case con prefijo erm_)
- Constantes: `ERM_VERSION`, `ERM_PLUGIN_DIR` (SCREAMING_SNAKE_CASE)
- Meta keys: `_erm_resource_type`, `_erm_price` (snake_case con guión bajo al inicio)
- Hooks: `erm_before_resource_render`, `erm_resource_saved` (snake_case con prefijo erm_)
- CSS classes: `erm-container`, `erm-card__title` (BEM con prefijo erm-)
- JS variables: `ermPublic`, `ermAdmin` (camelCase con prefijo erm)

## Estructura de Archivos

- Una clase por archivo
- Archivos en includes/ nombrados como: `class-erm-{nombre}.php`
- Vistas en admin/views/ o public/views/ (sin lógica de negocio)
- Assets en admin/css/, admin/js/, public/css/, public/js/

## Seguridad

- Nonces para TODOS los formularios admin: `wp_nonce_field('erm_action', 'erm_nonce')`
- Nonces REST vía header: `X-WP-Nonce: wp_create_nonce('wp_rest')`
- Prepared statements: `$wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id)`
- Sanitización al guardar: `sanitize_text_field()`, `absint()`, `esc_url_raw()`, `floatval()`
- Escaping al mostrar: `esc_html()`, `esc_attr()`, `esc_url()`, `wp_kses_post()`

## REST API

- Namespace: `erm/v1`
- Siempre usar `sanitize_callback` y `validate_callback` en `args`
- Siempre retornar `WP_Error` con código HTTP correcto en errores
- Siempre usar `rest_ensure_response()` para respuestas exitosas
- Formato de respuesta estándar: `{'success': true/false, 'data': {}, 'message': ''}`

## Base de Datos

- Tabla: `$wpdb->prefix . 'erm_tracking'`
- Usar `dbDelta()` para creación/actualización de tablas (nunca CREATE TABLE directo)
- Usar transients para cachear queries costosas: `set_transient('erm_key', $data, HOUR_IN_SECONDS)`
- Invalidar transients tras insert/update en la tabla de tracking

## JavaScript

- Usar `fetch()` nativo (no $.ajax) para llamadas REST
- Escapar HTML antes de insertar en DOM (función `escapeHTML()` propia)
- Debounce de 400ms en campos de búsqueda
- Estado de loading visible durante peticiones
- Tracking silencioso (errores en tracking no interrumpen UX)

## Internacionalización

- Text domain: `education-resources-manager`
- SIEMPRE envolver strings en `__()`, `_e()`, `esc_html__()`, o `esc_attr__()`
- No concatenar strings i18n: usar `printf()` o `sprintf()` con placeholders

## Lo que NO debes hacer

- NO usar plugins de terceros para funcionalidad core
- NO usar jQuery para la lógica principal del frontend (solo como dependencia)
- NO usar `wp_send_json()` en endpoints REST (usar WP_REST_Response)
- NO usar funciones deprecated de WordPress
- NO hardcodear URLs: usar `ERM_PLUGIN_URL`, `get_permalink()`, `rest_url()`
- NO omitir el textdomain en strings de usuario
- NO usar sintaxis PHP 8.0+ exclusiva (named arguments, enums, fibers)
- NO usar `error_reporting(0)` ni suprimir errores con @
