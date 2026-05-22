# 🧠 MASTER ORCHESTRATOR — Education Resources Manager Plugin
## Plan de Trabajo Multi-Agente para Cursor

---

## Contexto del Proyecto

Desarrollar el plugin WordPress **`education-resources-manager`** (ERM) completo y funcional para una prueba técnica Senior. El plugin gestiona recursos educativos con CPT, taxonomías, REST API, tracking en tabla personalizada, shortcode con AJAX, y panel de administración con estadísticas.

---

## 🗺️ Arquitectura de Agentes

```
┌─────────────────────────────────────────────────────────┐
│               MASTER ORCHESTRATOR (tú)                  │
│         Coordina el orden y contexto entre agentes      │
└────────────────────────┬────────────────────────────────┘
                         │
        ┌────────────────┼────────────────┐
        │                │                │
   FASE 1             FASE 2           FASE 3
   (Base)            (Core)          (Frontend)
        │                │                │
   Agent-01          Agent-02         Agent-04
   Scaffolding       CPT+Taxonomy     Shortcode+JS
        │                │                │
   Agent-01b         Agent-03         Agent-05
   DB+Activator      REST API         Admin Panel
                                          │
                                      Agent-06
                                     Docs+SQL
```

---

## 📋 Tabla de Agentes

| ID | Agente | Archivo | Responsabilidad | Depende de |
|----|--------|---------|-----------------|------------|
| 01 | Scaffolding Agent | `AGENT_01_SCAFFOLDING.md` | Estructura de archivos, plugin principal, constantes | — |
| 02 | CPT + Taxonomy Agent | `AGENT_02_CPT_TAXONOMY.md` | Custom Post Type, taxonomías, post meta | Agent 01 |
| 03 | Database + Activator Agent | `AGENT_03_DATABASE.md` | Tabla custom, CRUD tracking, activator/deactivator | Agent 01 |
| 04 | REST API Agent | `AGENT_04_REST_API.md` | 4 endpoints REST, validación, auth | Agent 02, 03 |
| 05 | Shortcode + Frontend Agent | `AGENT_05_SHORTCODE_FRONTEND.md` | Shortcode, CSS, JS con AJAX | Agent 04 |
| 06 | Admin Panel Agent | `AGENT_06_ADMIN.md` | Página admin, stats, filtros, meta boxes | Agent 02, 03 |
| 07 | Docs + SQL Agent | `AGENT_07_DOCS_SQL.md` | ARCHITECTURE.md, DATABASE.md, API.md, schema.sql | Todos |

---

## 🚀 Orden de Ejecución (Secuencial)

```
FASE 1 — Fundación
  └── Agent 01: Scaffolding + Plugin Principal

FASE 2 — Backend Core
  ├── Agent 02: CPT + Taxonomías (paralelo posible con Agent 03)
  └── Agent 03: Database + Activator

FASE 3 — Capa de API
  └── Agent 04: REST API (espera Agent 02 + 03)

FASE 4 — Interfaces
  ├── Agent 05: Shortcode + Frontend (espera Agent 04)
  └── Agent 06: Admin Panel (espera Agent 02 + 03)

FASE 5 — Documentación
  └── Agent 07: Docs + SQL (espera todos)
```

---

## 📁 Estructura Final Esperada

```
education-resources-manager/
├── education-resources-manager.php       ← Agent 01
├── uninstall.php                          ← Agent 01
├── includes/
│   ├── class-erm-activator.php           ← Agent 03
│   ├── class-erm-deactivator.php         ← Agent 03
│   ├── class-erm-loader.php              ← Agent 01
│   ├── class-erm-post-type.php           ← Agent 02
│   ├── class-erm-taxonomy.php            ← Agent 02
│   ├── class-erm-database.php            ← Agent 03
│   ├── class-erm-admin.php               ← Agent 06
│   ├── class-erm-rest-api.php            ← Agent 04
│   └── class-erm-shortcode.php           ← Agent 05
├── admin/
│   ├── css/erm-admin.css                 ← Agent 06
│   ├── js/erm-admin.js                   ← Agent 06
│   └── views/
│       ├── admin-page-main.php           ← Agent 06
│       └── admin-page-stats.php          ← Agent 06
├── public/
│   ├── css/erm-public.css               ← Agent 05
│   ├── js/erm-public.js                  ← Agent 05
│   └── views/shortcode-template.php     ← Agent 05
├── docs/
│   ├── ARCHITECTURE.md                   ← Agent 07
│   ├── DATABASE.md                       ← Agent 07
│   └── API.md                            ← Agent 07
├── database/
│   ├── schema.sql                        ← Agent 07
│   └── sample-data.sql                   ← Agent 07
└── languages/                            ← Agent 01 (stub)
```

---

## ⚙️ Instrucciones para Usar los Agentes en Cursor

### Método 1: Cursor Chat (por agente)
1. Abre Cursor Chat (`Cmd/Ctrl + L`)
2. Copia el contenido completo del archivo MD del agente
3. Pega como primer mensaje
4. El agente responderá generando los archivos
5. Revisa → acepta → abre el siguiente agente

### Método 2: Cursor Composer (multi-archivo)
1. Abre Composer (`Cmd/Ctrl + I`)
2. Pega el prompt del agente
3. Cursor generará múltiples archivos simultáneamente
4. Ideal para los agentes 02, 03, y 06

### Método 3: Cursor Agent Mode (autónomo)
1. Activa Agent Mode en el chat
2. Pega el prompt con instrucción de crear archivos
3. El agente navegará el filesystem y creará todo solo
4. Úsalo con Agent 01 (scaffolding) y Agent 07 (docs)

---

## 🔧 Skills de Cursor Recomendadas para Este Proyecto

### Skills Nativas de Cursor

| Skill | Cuándo Usarla |
|-------|--------------|
| **`@codebase`** | Cuando un agente necesite ver el código ya generado por otro agente previo |
| **`@file`** | Para referenciar un archivo específico al prompt (ej: `@class-erm-database.php`) |
| **`@docs`** | Para que Cursor consulte docs de WordPress.org automáticamente |
| **`@web`** | Para buscar ejemplos actuales de WP REST API o `dbDelta` |
| **`.cursorrules`** | Reglas globales del proyecto (ver sección abajo) |
| **Cursor Tab** | Autocompletado mientras escribes el código del plugin |

### Custom `.cursorrules` para Este Proyecto

Crea el archivo `.cursorrules` en la raíz del plugin con el contenido del archivo `CURSORRULES.md` de este paquete. Esto hace que **todos** los agentes hereden las reglas del proyecto automáticamente.

---

## 🛡️ Reglas Globales del Proyecto (para todos los agentes)

Todos los agentes deben respetar:

1. **Prefijo:** Todas las funciones, clases, constantes usan `erm_` o `ERM_`
2. **WordPress Coding Standards:** indentación con tabs, no espacios
3. **Seguridad siempre:** `sanitize_*`, `esc_*`, nonces, prepared statements
4. **PHP 7.4+:** No usar sintaxis de PHP 8 exclusiva
5. **WordPress 6.0+:** Usar REST API moderna
6. **No plugins de terceros** para funcionalidad core
7. **OOP estricto:** una clase por archivo, namespace opcional pero consistente
8. **`$wpdb->prepare()`** en TODAS las queries directas a la BD
9. **Hooks sobre código directo:** usar `add_action` / `add_filter`
10. **`wp_die()`** para errores críticos en activación

---

## ✅ Checklist de Entrega

Tras ejecutar todos los agentes, verifica:

- [ ] Plugin se activa sin errores PHP
- [ ] CPT `education_resource` aparece en el admin
- [ ] Taxonomías `resource_category` y `skill_tag` funcionan
- [ ] Tabla `wp_erm_tracking` se crea al activar
- [ ] `GET /wp-json/erm/v1/resources` responde 200
- [ ] `GET /wp-json/erm/v1/resources/{id}` responde 200 o 404
- [ ] `POST /wp-json/erm/v1/resources/{id}/track` registra en BD
- [ ] `GET /wp-json/erm/v1/stats` responde (solo admin)
- [ ] `[recursos_educativos]` renderiza listado en frontend
- [ ] Filtros AJAX funcionan sin recargar página
- [ ] Panel admin muestra estadísticas
- [ ] `/docs/ARCHITECTURE.md`, `DATABASE.md`, `API.md` completos
- [ ] `/database/schema.sql` válido y ejecutable
- [ ] `.gitignore` incluido

---

## 📌 Notas de Contexto para Pasar entre Agentes

Cuando inicies un nuevo agente, incluye este bloque al inicio:

```
CONTEXTO DEL PROYECTO:
- Plugin: education-resources-manager
- Prefijo: erm_ / ERM_
- CPT: education_resource
- Taxonomías: resource_category (jerárquica), skill_tag (no jerárquica)
- Tabla custom: {prefix}_erm_tracking
- Meta keys: _erm_resource_type, _erm_difficulty_level, _erm_duration_minutes, 
             _erm_resource_url, _erm_instructor, _erm_price
- Endpoints: /wp-json/erm/v1/resources, /resources/{id}, /resources/{id}/track, /stats
- WP mínimo: 6.0 | PHP mínimo: 7.4
```
