# Copia de `.cursor/rules/`

Snapshots de los prompts usados en **Cursor** para implementar el plugin.

| Archivo | Rol |
|---------|-----|
| `00_MASTER_ORCHESTRATOR.md` | Plan, orden de fases, checklist de entrega, contexto entre agentes |
| `AGENT_01_SCAFFOLDING.md` … `AGENT_07_DOCS_SQL.md` | Prompts de implementación por fase |
| `PRUEBA_TECNICA.mdc` | Regla Cursor: leer `docs/requerimientos/` (enunciado local, gitignored) |

**Fuente de verdad en desarrollo:** `.cursor/rules/` en la raíz del plugin.

**Sincronizar antes de entregar:**

```bash
cp .cursor/rules/*.md .cursor/rules/*.mdc PROMPTS/cursor-rules/
```
