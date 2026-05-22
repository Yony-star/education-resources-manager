# Prompt inicial a Claude Code

Documentos adjuntos en la misma conversación: enunciado y plantillas de la prueba técnica (`README.md`, `INSTRUCTIONS_FOR_CANDIDATE.md`, `ARCHITECTURE_TEMPLATE.md`, `API_TEMPLATE.md`, `DATABASE_TEMPLATE.md`, `STRUCTURE_EXAMPLE.md`, etc.).

---

## Prompt principal (texto literal)

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

## Salida obtenida (10 archivos)

Ver detalle en [`SALIDA-CLAUDE-CODE.md`](SALIDA-CLAUDE-CODE.md):

- **7 agentes:** `AGENT_01_SCAFFOLDING.md` … `AGENT_07_DOCS_SQL.md`
- **3 soporte:** `00_MASTER_ORCHESTRATOR.md`, `CURSORRULES.md`, `CURSOR_SKILLS_GUIDE.md`

Copiados a `.cursor/rules/` y `PROMPTS/cursor-rules/`; `CURSORRULES.md` también en esta carpeta y en `.cursorrules/`.

---

## Ajustes o mensajes de seguimiento

_(Opcional: añadir aquí refinamientos posteriores a la misma conversación con Claude.)_

---

## Notas

- **Herramienta:** Claude Code.
- **Siguiente paso:** implementación en Cursor con skills de WordPress (ver [README del plugin](../../README.md#desarrollo-asistido-por-ia-entrega)).
