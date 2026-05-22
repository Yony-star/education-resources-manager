# Por qué agentes y cómo usarlos — explicación de Claude Code

> **Instrucción:** la sección **“Texto de Claude Code”** debe ser la explicación **literal** (o exportada) que te dio Claude al proponer el plan y los agentes.  
> La sección **“Resumen de referencia”** resume lo que ya quedó en el orquestador por si el evaluador lee solo este repo.

---

## Texto de Claude Code (entregables y arranque)

Claude resumió la entrega así (detalle en [`SALIDA-CLAUDE-CODE.md`](SALIDA-CLAUDE-CODE.md)):

- **10 archivos listos para Cursor:** 7 prompts de agente (`AGENT_01` … `AGENT_07`) + 3 de soporte (`00_MASTER_ORCHESTRATOR.md`, `CURSORRULES.md`, `CURSOR_SKILLS_GUIDE.md`).
- **Orden de ejecución:** `01` → `02`+`03` (paralelo) → `04` → `05`+`06` (paralelo) → `07`.
- **Uso en Cursor:** por cada agente, pegar el `.md` en Chat o Composer y generar archivos; copiar `CURSORRULES.md` a `.cursorrules` en la raíz del plugin.

La guía `CURSOR_SKILLS_GUIDE.md` del paquete original no está versionada en este repo; en la **Fase 2** se usaron los **skills de WordPress** en `.cursor/skills/` (ver [README del plugin](../../README.md#skills-de-wordpress-cursor)).

---

## Resumen de referencia (derivado del orquestador en el repo)

*Esto no sustituye tu pegado anterior; sirve como índice si el evaluador no abre el chat de Claude.*

### Objetivo del enfoque multi-agente

- La prueba pide **muchas capas** (CPT, BD custom, REST, frontend AJAX, admin, documentación). Un solo prompt enorme mezcla contexto y suele omitir seguridad o docs.
- **Un agente = un alcance acotado** con archivos concretos, checklist al final y bloque de contexto para el siguiente paso.
- El **Master Orchestrator** fija el orden: no implementar REST sin CPT y tabla de tracking; no documentar antes de tener código estable.

### Por qué siete agentes (01–07)

| Agente | Motivo de existir separado |
|--------|----------------------------|
| **01 Scaffolding** | Base común: constantes, hooks de activación, loader; todo lo demás depende de esto. |
| **02 CPT + taxonomías** | Dominio WordPress (posts, meta, terms); mucho detalle de labels y meta box. |
| **03 Database** | `dbDelta`, `$wpdb->prepare`, transients; riesgo alto si se mezcla con UI. |
| **04 REST API** | Contrato HTTP estable; requiere posts y tracking ya definidos. |
| **05 Shortcode + JS** | Consume REST; assets y plantilla PHP aparte del admin. |
| **06 Admin** | Menús, vistas, gráfico Canvas; permisos `manage_options`. |
| **07 Docs + SQL** | Entregables explícitos de la prueba (`docs/`, `schema.sql`) tras el código. |

### Instrucciones que lleva cada agente

Cada `AGENT_XX_*.md` en `PROMPTS/cursor-rules/` incluye típicamente:

- Contexto del proyecto (prefijos `erm_`, CPT, taxonomías, endpoints).
- Lista de archivos a crear o modificar.
- Fragmentos de código o pseudocódigo alineados con WordPress Coding Standards.
- Checklist de verificación antes de pasar al siguiente agente.

### Cómo se ejecutó en Cursor

1. Abrir chat o Composer con el contenido del agente correspondiente (o reglas en `.cursor/rules/`).
2. Revisar diff, activar plugin en local, probar el mínimo de esa fase.
3. Commit atómico (ej. `feat: register education_resource CPT and taxonomies`).
4. Repetir con el siguiente agente del orquestador.

### Prioridades si el tiempo apremia

Alineado con `INSTRUCTIONS_FOR_CANDIDATE.md`: primero CPT + shortcode básico + tabla + ≥2 endpoints REST + arquitectura; luego filtros AJAX, admin/stats y API doc completa.

---

## Enlace al plan materializado

- Orquestador: [`../cursor-rules/00_MASTER_ORCHESTRATOR.md`](../cursor-rules/00_MASTER_ORCHESTRATOR.md)
- Agentes 01–07: [`../cursor-rules/`](../cursor-rules/)
