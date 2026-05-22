# Plan multi-agente (Claude Code)

Aquí va el material **anterior** a la implementación en Cursor: lo que pediste a **Claude Code** para diseñar el plan y los agentes, y la explicación de por qué esa estructura.

## Archivos

| Archivo | Uso |
|---------|-----|
| `PROMPT-INICIAL-PLAN.md` | Prompt literal enviado a Claude Code |
| `SALIDA-CLAUDE-CODE.md` | **10 archivos** entregados por Claude (7 agentes + 3 soporte) y cómo arrancar |
| `CURSORRULES.md` | Copia de las reglas globales para `.cursorrules` en la raíz del plugin |
| `EXPLICACION-CLAUDE-AGENTES.md` | Resumen del *por qué* del enfoque multi-agente |

## Flujo recomendado

```
Claude Code  →  plan + AGENT_XX + orquestador  →  copiar a .cursor/rules/
                                                      ↓
Cursor       →  ejecutar agente por agente     →  código en includes/, admin/, public/, docs/
```

El resultado del paso de Claude quedó materializado en:

- `PROMPTS/cursor-rules/00_MASTER_ORCHESTRATOR.md`
- `PROMPTS/cursor-rules/AGENT_01_SCAFFOLDING.md` … `AGENT_07_DOCS_SQL.md`

Esta subcarpeta documenta el **origen** y el **razonamiento**; `cursor-rules/` documenta los **prompts de implementación** tal como se usaron en Cursor.
