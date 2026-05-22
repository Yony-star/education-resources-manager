# Education Resources Manager

Plugin de WordPress para gestionar recursos educativos: Custom Post Type, taxonomías, seguimiento de vistas y descargas, REST API, shortcode con filtros y panel de administración con estadísticas.

## Requisitos

- WordPress 6.0 o superior
- PHP 7.4 o superior
- MySQL 5.7+ o MariaDB 10.3+

## Instalación

### Vía ZIP (recomendado)

1. Descarga o genera un ZIP de la carpeta `education-resources-manager`.
2. En el escritorio de WordPress, ve a **Plugins → Añadir nuevo → Subir plugin**.
3. Selecciona el archivo ZIP e instala.
4. Activa el plugin **Education Resources Manager**.

### Vía FTP / SFTP

1. Sube la carpeta `education-resources-manager` a `wp-content/plugins/`.
2. En **Plugins**, activa **Education Resources Manager**.

Tras la activación, el plugin registra sus hooks, crea la tabla de tracking y expone CPT, REST API, shortcode y panel de administración.

## Uso del shortcode

Inserta en cualquier entrada, página o plantilla:

```
[recursos_educativos]
```

### Atributos

| Atributo     | Valores                                      | Descripción                          |
|-------------|-----------------------------------------------|--------------------------------------|
| `type`      | `course`, `tutorial`, `ebook`, `video`        | Filtra por tipo de recurso           |
| `difficulty`| `beginner`, `intermediate`, `advanced`        | Filtra por nivel de dificultad       |
| `category`  | slug de `resource_category`                   | Filtra por categoría                 |
| `per_page`  | número entero (por defecto: `10`)             | Recursos por página                  |

### Ejemplos

```
[recursos_educativos type="course" difficulty="beginner"]
[recursos_educativos category="programacion" per_page="6"]
[recursos_educativos type="video" difficulty="advanced" per_page="12"]
```

## REST API

Namespace: `erm/v1` — URL base: `/wp-json/erm/v1`

| Método | Ruta                         | Descripción                    |
|--------|------------------------------|--------------------------------|
| GET    | `/resources`                 | Lista recursos con filtros     |
| GET    | `/resources/{id}`            | Detalle de un recurso          |
| POST   | `/resources/{id}/track`      | Registra vista, descarga, etc. |
| GET    | `/stats`                     | Estadísticas agregadas         |

Consulta `docs/API.md` para parámetros, respuestas y ejemplos completos. Ver también `docs/ARCHITECTURE.md` y `docs/DATABASE.md`.

## Desarrollo asistido por IA (entrega)

La prueba técnica permite y valora el uso de agentes de IA. Este proyecto usó **dos herramientas de forma complementaria**: primero **Claude** para planificar y redactar los agentes que luego ejecutaría **Cursor**, apoyado en **skills de WordPress** y revisión contra el enunciado.

### Por qué Claude para el plan y Cursor para el código

Tras probar ambos entornos con el mismo enunciado, **Claude (Claude Code) dio mejores resultados en la fase de planificación**: arquitectura por fases, tabla de dependencias entre agentes, prompts largos y detallados (`AGENT_01` … `AGENT_07`) y el archivo orquestador (`00_MASTER_ORCHESTRATOR.md`). Esa salida se copió a [`.cursor/rules/`](.cursor/rules/) como **instrucciones listas para Cursor**.

**Cursor** se usó después para **implementar** cada agente en el repositorio (PHP, JS, CSS, vistas, `docs/`, SQL), con **Agent/Composer**, las reglas generadas por Claude y los **skills de WordPress** activos en el proyecto. En código y refactors finos, Cursor + skills encajaron mejor con el flujo diario del plugin (hooks, REST, `$wpdb`, assets).

En resumen: **Claude diseñó el “qué” y el “en qué orden”; Cursor construyó el “cómo” en archivos**, con skills WP como guía de calidad.

### Flujo en dos fases

```
Enunciado de la prueba
        │
        ▼
┌───────────────────────┐
│  Fase 1 — Claude Code │  Plan maestro, orquestador, AGENT_01…07
│  (mejor en planificación)│  → PROMPTS/01-plan-con-claude-code/
└───────────┬───────────┘  → .cursor/rules/ + PROMPTS/cursor-rules/
            │
            ▼
┌───────────────────────┐
│  Fase 2 — Cursor      │  Ejecutar cada agente → código del plugin
│  + skills WordPress   │  Auditorías, ajustes, docs, pruebas en Local
└───────────────────────┘
```

### Herramientas

| Herramienta | Rol en este proyecto |
|-------------|----------------------|
| **Claude Code (Claude)** | Crear el **plan multi-agente** y los **prompts** (`AGENT_XX`, orquestador, checklists). Material en [`PROMPTS/01-plan-con-claude-code/`](PROMPTS/01-plan-con-claude-code/). |
| **Cursor** | **Ejecutar** esos agentes fase a fase, generar y revisar código; reglas en `.cursor/rules/` (entrega en [`PROMPTS/cursor-rules/`](PROMPTS/cursor-rules/)). |
| **Skills WordPress (Cursor)** | Durante la Fase 2: REST API, plugin lifecycle, seguridad, rendimiento, etc. (ver tabla más abajo). |
| **Local (Flywheel)** | WordPress 6.x + PHP 7.4+ para activar el plugin y validar REST, shortcode y admin. |

### Carpeta `PROMPTS/`

Incluye lo pedido en las instrucciones de entrega (*prompts utilizados*):

- Copia versionada de los prompts de implementación (`.cursor/rules` → `PROMPTS/cursor-rules/`).
- Prompt inicial, **salida de Claude (10 archivos)** y `CURSORRULES.md` en `PROMPTS/01-plan-con-claude-code/` (ver `SALIDA-CLAUDE-CODE.md`).

Índice completo: [`PROMPTS/README.md`](PROMPTS/README.md).

### Skills de WordPress (Cursor)

En el repositorio local se instalaron **skills de WordPress** bajo `.cursor/skills/` (no se versionan en Git; ver `.gitignore`). Cursor los aplica automáticamente cuando el contexto encaja, para no improvisar APIs obsoletas ni saltarse seguridad o estándares del Plugin Handbook.

| Skill | Cómo ayudó a la calidad del resultado |
|-------|----------------------------------------|
| **`wordpress-router`** | Clasificar el repo como plugin WP y elegir el flujo correcto (REST, BD, admin, docs) antes de codificar. |
| **`wp-plugin-development`** | Estructura del plugin, hooks de activación/desactivación/uninstall, Settings API, sanitización, nonces y ciclo de vida. |
| **`wp-rest-api`** | Diseño de rutas `erm/v1`, `permission_callback`, validación de argumentos, envelope JSON y documentación en `docs/API.md`. |
| **`wp-project-triage`** | Inspección determinística del proyecto (tipo, carpetas, convenciones) para no desviar la entrega. |
| **`wp-performance`** | Criterios para queries con `$wpdb->prepare()`, índices en `erm_tracking`, transients en estadísticas y carga condicional de assets. |
| **`wp-wpcli-and-ops`** | Referencia para pruebas operativas (activar plugin, flush, cron) en entorno local cuando aplica. |
| **`wp-phpstan`** | Orientación de tipado y anotaciones PHP en código orientado a WordPress (cuando se revisó calidad estática). |
| **`wp-plugin-directory-guidelines`** | Checklist de seguridad, GPL y patrones aceptables si se evaluara publicación en el directorio. |

Skills del mismo paquete disponibles pero **poco o no usados** en este plugin (sin bloques Gutenberg ni theme.json): `wp-block-development`, `wp-block-themes`, `wp-interactivity-api`, `wp-playground`, `wp-abilities-api`, `wpds`, `blueprint`.

### Reglas y contexto del enunciado

- **Agentes 01–07** — Alcance acotado por fase, checklist y dependencias (menos omisiones entre CPT, BD, REST y frontend).
- **`PRUEBA_TECNICA.mdc`** — Regla Cursor que apunta a `docs/requerimientos/` (enunciado local, gitignored) para comparar implementación vs requisitos antes de cerrar la entrega.

### Qué aportó esto al resultado final

- **Plan coherente** (Claude): menos saltos de fase y prompts reutilizables en Cursor.
- **Implementación alineada con WordPress** (Cursor + skills): WPCS, prepared statements, `sanitize_*` / `esc_*`, capabilities, REST `erm/v1` coherente con el shortcode AJAX.
- **Documentación y SQL** como entregables de la prueba (`docs/`, `database/schema.sql`).
- **Trazabilidad** para el evaluador: origen del plan en Claude, prompts en `PROMPTS/`, ejecución y skills descritos en este README.

## Desinstalación

1. Desactiva el plugin desde **Plugins**.
2. Pulsa **Eliminar**.

El archivo `uninstall.php` elimina las opciones `erm_version` y `erm_db_version`, la tabla `{prefix}_erm_tracking` y los transients con prefijo `erm_`. **No** borra los posts del tipo `education_resource` (contenido del sitio).

## Changelog

### 1.0.0

- CPT `education_resource`, taxonomías, meta boxes y tabla `erm_tracking`.
- REST API `erm/v1`, shortcode `[recursos_educativos]` con filtros AJAX y panel admin con estadísticas.
- Documentación en `docs/` y scripts SQL en `database/`.
- Plan y agentes creados con Claude Code; implementación en Cursor con skills WordPress (`PROMPTS/` + sección IA en README).
