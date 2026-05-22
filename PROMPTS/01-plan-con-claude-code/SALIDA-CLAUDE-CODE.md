# Salida de Claude Code (Fase 1)

Resumen de lo que **Claude Code** entregó tras el [prompt inicial](PROMPT-INICIAL-PLAN.md) y los documentos de la prueba técnica.

---

## 10 archivos listos para Cursor

### Los 7 agentes especializados

| Agente | Archivo | Genera |
|--------|---------|--------|
| **Agent 01 — Scaffolding** | `AGENT_01_SCAFFOLDING.md` | Estructura completa, plugin principal, loader, uninstall, `.gitignore`, README |
| **Agent 02 — CPT + Taxonomías** | `AGENT_02_CPT_TAXONOMY.md` | Custom Post Type, meta boxes, `save_meta`, taxonomías jerárquica y plana |
| **Agent 03 — Database** | `AGENT_03_DATABASE.md` | Tabla tracking, CRUD, transients, activator, deactivator |
| **Agent 04 — REST API** | `AGENT_04_REST_API.md` | 4 endpoints con validación, permisos y formato de respuesta estándar |
| **Agent 05 — Frontend** | `AGENT_05_SHORTCODE_FRONTEND.md` | Shortcode, CSS responsivo con variables, JS con fetch + debounce + tracking |
| **Agent 06 — Admin Panel** | `AGENT_06_ADMIN.md` | Menú admin, tabla con filtros, página de stats, gráfico Canvas nativo |
| **Agent 07 — Docs + SQL** | `AGENT_07_DOCS_SQL.md` | `ARCHITECTURE.md`, `DATABASE.md`, `API.md`, `schema.sql`, `sample-data.sql` |

Copia versionada en el repo: [`../cursor-rules/`](../cursor-rules/) y copia de trabajo en [`.cursor/rules/`](../../.cursor/rules/).

### Los 3 archivos de soporte

| Archivo | Rol |
|---------|-----|
| **`00_MASTER_ORCHESTRATOR.md`** | El “cerebro” que coordina el orden, dependencias y checklist de entrega |
| **`CURSORRULES.md`** | Copiarlo como `.cursorrules` en la raíz del plugin; aplica reglas WP a todos los agentes. En este repo: [`.cursorrules/CURSORRULES.md`](../../.cursorrules/CURSORRULES.md) y copia en [`CURSORRULES.md`](CURSORRULES.md) |
| **`CURSOR_SKILLS_GUIDE.md`** | Qué `@codebase`, `@docs`, `@web`, Composer y Agent Mode usar en cada fase, más snippets reutilizables. *No se versionó el archivo original*; el uso de skills quedó documentado en el [README del plugin](../../README.md#skills-de-wordpress-cursor) y en la práctica con `.cursor/skills/` |

---

## Cómo arrancar (instrucciones de Claude)

1. Descargar / copiar todos los archivos generados.
2. Crear **`.cursorrules`** en la raíz del plugin copiando el contenido de **`CURSORRULES.md`** (ver rutas arriba).
3. **Ejecutar los agentes en orden:**
   - `01`
   - `02` + `03` *(en paralelo si se desea)*
   - `04`
   - `05` + `06` *(en paralelo si se desea)*
   - `07`
4. Para cada agente: abrir **Cursor Chat** o **Composer**, pegar el contenido del `.md` correspondiente y dejar que genere los archivos.

### Cómo se ejecutó en este proyecto

- **Fase 1:** Claude generó el plan y los `.md` anteriores.
- **Fase 2:** Cursor implementó cada agente con **skills de WordPress** activos (ver README del plugin).
- Regla adicional en Cursor: `PRUEBA_TECNICA.mdc` (auditoría contra `docs/requerimientos/`, local).

---

## Diagrama de orden

```
01 Scaffolding
      │
      ├── 02 CPT + Taxonomías ──┐
      └── 03 Database ──────────┤ (paralelo posible)
                                ▼
                           04 REST API
                                │
              ┌─────────────────┴─────────────────┐
              ▼                                   ▼
        05 Shortcode + Frontend            06 Admin Panel
              │                                   │
              └─────────────────┬─────────────────┘
                                ▼
                           07 Docs + SQL
```
