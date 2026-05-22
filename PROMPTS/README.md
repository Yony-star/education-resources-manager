# PROMPTS — Education Resources Manager

Entregable de la prueba técnica: **prompts y planificación** usados para generar el plugin (ver `docs/requerimientos/INSTRUCTIONS_FOR_CANDIDATE.md`, sección 5).

## Contenido de esta carpeta

| Ruta | Qué es |
|------|--------|
| [`01-plan-con-claude-code/`](01-plan-con-claude-code/) | Prompt inicial, **salida de Claude** (10 archivos), `CURSORRULES.md` |
| [`cursor-rules/`](cursor-rules/) | Copia de los prompts de implementación en `.cursor/rules/` (Agent 01–07 + orquestador) |

## Relación con `.cursor/rules/`

- **Trabajo diario:** Cursor lee `.cursor/rules/` (reglas del proyecto).
- **Entrega al evaluador:** `PROMPTS/cursor-rules/` es una **copia versionada** de esos mismos archivos en el momento de la entrega.

Si actualizas un agente en `.cursor/rules/`, vuelve a copiarlo aquí antes del commit final:

```bash
cp .cursor/rules/*.md .cursor/rules/*.mdc PROMPTS/cursor-rules/
```

## Cómo se usó en la práctica

**Fase 1 — Claude Code:** se usó **Claude** para crear el plan y los archivos de agentes para Cursor. En las pruebas del candidato, **el modelo de Claude rindió mejor en planificación** (orquestador, dependencias, prompts largos por fase) que en la misma tarea solo con Cursor.

**Fase 2 — Cursor:** con esos `.md` en `.cursor/rules/`, se **ejecutó cada agente** en Cursor (chat/Composer/Agent), apoyado en los **skills de WordPress** del proyecto.

1. **Claude** → plan + `AGENT_XX` + orquestador → `01-plan-con-claude-code/` y copia a `cursor-rules/`.
2. **Cursor** → implementación del plugin siguiendo cada agente.
3. **Regla local** — `PRUEBA_TECNICA.mdc` → `docs/requerimientos/` (gitignored) para auditar el enunciado.
4. **Skills WordPress** — `.cursor/skills/` (local). Detalle en el [README del plugin](../README.md#desarrollo-asistido-por-ia-entrega).

## Prompt inicial a Claude Code (Fase 1)

Texto literal enviado a **Claude Code** junto con los documentos de la prueba (`README.md`, plantillas de arquitectura/API/BD, `INSTRUCTIONS_FOR_CANDIDATE.md`, etc.). Copia extendida en [`01-plan-con-claude-code/PROMPT-INICIAL-PLAN.md`](01-plan-con-claude-code/PROMPT-INICIAL-PLAN.md).

---

Actúa como un Arquitecto de Software y Especialista en Sistemas Multi-Agente. Voy a proporcionarte documentos de contexto y requerimientos de un proyecto complejo que desarrollaré en Cursor utilizando metodologías de desarrollo basadas en agentes.

Con base en esta información, necesito que generes un plan de trabajo detallado y estructurado para una arquitectura multi-agente. Además, debes crear un prompt optimizado para Cursor que permita coordinar el trabajo entre múltiples agentes especializados durante el desarrollo del proyecto.

También necesito que definas los agentes requeridos y generes la documentación de cada uno en archivos Markdown independientes (.md).

Debes especificar claramente:

- Qué agentes especializados se necesitan para el proyecto.
- Las responsabilidades, alcance y objetivos de cada agente.
- Cómo deben colaborar e interactuar entre sí.
- Qué skills, herramientas o capacidades de Cursor son recomendables para este entorno multi-agente.
- Buenas prácticas de arquitectura, coordinación, trazabilidad y ejecución para asegurar escalabilidad y eficiencia en el desarrollo.

El objetivo es construir una estructura profesional, mantenible y escalable que permita ejecutar el proyecto de forma organizada y eficiente dentro de Cursor.

---

## Salida de Claude Code (10 archivos)

Documentación completa: [`01-plan-con-claude-code/SALIDA-CLAUDE-CODE.md`](01-plan-con-claude-code/SALIDA-CLAUDE-CODE.md).

### Los 7 agentes especializados

| Agente | Archivo | Genera |
|--------|---------|--------|
| Agent 01 — Scaffolding | `AGENT_01_SCAFFOLDING.md` | Estructura, plugin principal, loader, uninstall, `.gitignore`, README |
| Agent 02 — CPT + Taxonomías | `AGENT_02_CPT_TAXONOMY.md` | CPT, meta boxes, `save_meta`, taxonomías |
| Agent 03 — Database | `AGENT_03_DATABASE.md` | Tabla tracking, CRUD, transients, activator/deactivator |
| Agent 04 — REST API | `AGENT_04_REST_API.md` | 4 endpoints, validación, permisos, respuesta estándar |
| Agent 05 — Frontend | `AGENT_05_SHORTCODE_FRONTEND.md` | Shortcode, CSS, JS (fetch, debounce, tracking) |
| Agent 06 — Admin Panel | `AGENT_06_ADMIN.md` | Menú admin, filtros, stats, gráfico Canvas |
| Agent 07 — Docs + SQL | `AGENT_07_DOCS_SQL.md` | `ARCHITECTURE.md`, `DATABASE.md`, `API.md`, SQL |

### Los 3 archivos de soporte

| Archivo | Rol en el proyecto |
|---------|-------------------|
| `00_MASTER_ORCHESTRATOR.md` | Orden, dependencias y checklist de entrega |
| `CURSORRULES.md` | Reglas globales WP → [`.cursorrules/CURSORRULES.md`](../.cursorrules/CURSORRULES.md) y copia en `01-plan-con-claude-code/` |
| `CURSOR_SKILLS_GUIDE.md` | Guía de `@codebase`, Composer, Agent Mode (original no versionado; ver [README del plugin](../README.md#skills-de-wordpress-cursor)) |

### Cómo arrancar (según Claude)

1. Copiar los archivos generados a `.cursor/rules/` (y `PROMPTS/cursor-rules/` para entrega).
2. Crear `.cursorrules` en la raíz del plugin desde `CURSORRULES.md`.
3. Ejecutar en orden: **01** → **02+03** (paralelo) → **04** → **05+06** (paralelo) → **07**.
4. Por cada agente: Cursor Chat o Composer + pegar el `.md` del agente.

En este repo la **Fase 2** se hizo en Cursor con **skills de WordPress** además de las reglas anteriores.

## Índice de agentes (`cursor-rules/`)

| Archivo | Fase | Entregable principal |
|---------|------|----------------------|
| `00_MASTER_ORCHESTRATOR.md` | Coordinación | Orden de ejecución, checklist, contexto entre agentes |
| `AGENT_01_SCAFFOLDING.md` | 1 | Plugin principal, loader, uninstall |
| `AGENT_02_CPT_TAXONOMY.md` | 2 | CPT, taxonomías, meta box |
| `AGENT_03_DATABASE.md` | 2 | Tabla `erm_tracking`, activator |
| `AGENT_04_REST_API.md` | 3 | REST `erm/v1` |
| `AGENT_05_SHORTCODE_FRONTEND.md` | 4 | Shortcode + AJAX |
| `AGENT_06_ADMIN.md` | 4 | Panel admin + estadísticas |
| `AGENT_07_DOCS_SQL.md` | 5 | `docs/*`, `database/*.sql` |
| `PRUEBA_TECNICA.mdc` | Transversal | Contexto del enunciado (carpeta local) |

## Documentación relacionada

- [`01-plan-con-claude-code/PROMPT-INICIAL-PLAN.md`](01-plan-con-claude-code/PROMPT-INICIAL-PLAN.md) — prompt enviado a Claude.
- [`01-plan-con-claude-code/SALIDA-CLAUDE-CODE.md`](01-plan-con-claude-code/SALIDA-CLAUDE-CODE.md) — entregables y orden de ejecución.
- [`01-plan-con-claude-code/EXPLICACION-CLAUDE-AGENTES.md`](01-plan-con-claude-code/EXPLICACION-CLAUDE-AGENTES.md) — resumen del *por qué* multi-agente (complemento).
