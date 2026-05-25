# Caso práctico 01 — Feedback a un PR de Junior

> **Lo que evaluamos**: tono, balance, jerarquía de issues, pedagogía, tacto.
> **Lo que NO evaluamos**: tu capacidad técnica para encontrar bugs. Aquí los issues ya te los damos.

---

## 🎬 Contexto

Estás en una empresa de tamaño medio. Tu equipo tiene 6 personas. Una de ellas es **Camila**:

- 4 meses en el equipo.
- Su primer trabajo "de verdad" como desarrolladora (antes hizo prácticas y proyectos personales).
- Hace 2 días entregó **su primera feature solo, sin pairing**.
- Está visiblemente orgullosa: lo comentó en standup, le mandó el link del PR a su mentor anterior, posteó algo en su LinkedIn (sin dar detalles, solo *"shipping my first feature"*).
- Su confianza venía remontando después de un mes 2 difícil donde sintió que "no estaba dando la talla".

El PR es para el plugin de gestión de recursos educativos (el de la prueba técnica). Es la implementación del shortcode `[recursos_educativos]` con filtros AJAX.

---

## 🔍 Los 12 issues que tú ya detectaste en el PR

> Estos issues están plantados deliberadamente. No tienes que validarlos técnicamente. Asume que están allí.

1. **🔴 SQL Injection sutil**: una de las consultas concatena un `$_GET['category']` sin pasar por `$wpdb->prepare()`. Está dentro de un método interno que parece "controlado" pero el input viene del frontend.

2. **🔴 Falta de nonce**: el endpoint AJAX para registrar visualizaciones no verifica nonce. Cualquier visitante puede inflar el contador de vistas con un script trivial.

3. **🔴 N+1 query**: para mostrar 12 recursos en una página, hace 1 query principal + 12 queries para obtener el `instructor` de cada uno desde `wp_postmeta`. Cuando el listado crezca, esto colapsa.

4. **🟡 Race condition leve**: la tabla `erm_tracking` se inserta sin transacción cuando se actualiza también un contador en `wp_options`. Posible inconsistencia en alta concurrencia.

5. **🟡 Naming inconsistente**: en el mismo archivo conviven `get_resources()`, `getResource()` y `obtenerCategorias()`. Mezcla snake_case, camelCase y español.

6. **🟡 Magic numbers**: el límite de paginación es `12` hardcoded en 4 lugares distintos del código.

7. **🟡 Dead code**: hay una función `legacy_filter_resources()` que no se usa en ningún sitio, con un comentario `// TODO: borrar después de la migración`.

8. **🟢 `console.log` olvidado**: hay un `console.log('aaaaa', response)` en el JS del shortcode.

9. **🟢 Cero tests**: el PR no incluye ningún test. El equipo no tiene una política estricta de coverage pero los seniors siempre añaden al menos un happy-path test.

10. **🟢 Accesibilidad rota**: los botones de filtro son `<div onclick="...">` sin role ni keyboard support. No se pueden usar con tab.

11. **🟢 Sin i18n**: las cadenas mostradas al usuario están hardcoded en español, sin pasar por `__()` o `_e()`. El proyecto usa i18n en todas las features anteriores.

12. **🟢 Commit message inútil**: el PR tiene un único commit con mensaje `"finished feature"`. Cero contexto, cero descripción de cambios.

**Leyenda**: 🔴 Blocking · 🟡 Importante · 🟢 Sugerencia / educativo

---

## 📝 Tu entregable

Produce dos artefactos:

### Artefacto A — Comentario(s) de review en el PR

> Como lo pondrías literalmente en GitHub/GitLab. Incluye el comentario general del PR y los comentarios inline si los hubiera.

Reglas:
- **Tono apropiado** para Camila (4 meses, primera feature solo).
- **Jerarquía clara**: qué bloquea merge, qué es importante para esta iteración, qué es educativo para próximas.
- **Educativo**: para los 🔴 y 🟡, no solo digas qué está mal — explica brevemente *por qué* y enlaza recursos cuando aplique.
- **Reconocer lo bueno**: el PR tiene cosas bien hechas (asume que sí). Nómbralas. Que no sea cumplido vacío al inicio para "endulzar".
- **No abrumes**: 12 issues de golpe pueden hundir a un junior. Decide qué va al review escrito y qué prefieres hablar en 1:1.

Formato sugerido (puedes adaptarlo):

```markdown
## Review general del PR

Hola Camila,

Primero: buen trabajo sacando tu primera feature completa sola. Se nota que le metiste bastante tiempo y ganas. El shortcode `[recursos_educativos]` ya tiene bastante lógica integrada y me gustó especialmente cómo organizaste la parte del frontend y los filtros AJAX. También valoro que fueras mostrando avances en el standup y que te sintieras orgullosa de compartirlo. Eso es positivo.

Antes de mergear sí necesitamos corregir varios puntos importantes para dejar esto en estándar de producción. Los separé por prioridad para que trabajemos primero lo que realmente genera riesgo.

## Comentarios bloqueantes (🔴)

### Comentario 1 — shortcode.php (~línea 45)

Acá estás concatenando directamente `$_GET['category']` sin sanitizar. Aunque el valor venga controlado desde frontend, cualquier input del cliente debe asumirse como inseguro.

→ Acción requerida: usar `$wpdb->prepare()` antes de ejecutar la query.

Te dejo esta referencia porque explica bastante bien el riesgo y las prácticas recomendadas:
https://developer.wordpress.org/plugins/security/

### Comentario 2 — ajax-handler.php

El endpoint que registra visualizaciones no valida nonce. Hoy cualquiera podría pegarle manualmente y alterar contadores.

→ Acción requerida: agregar validación de nonce antes de procesar la petición.

### Comentario 3 — resources-list.php

Por cada recurso estás haciendo una query extra para obtener el instructor. Funciona con pocos datos, pero en producción esto escala mal rápido.

→ Acción requerida: evaluar JOIN o alguna estrategia de caché para evitar queries repetidas.

## Comentarios importantes (🟡)

### Comentario 4 — Consistencia de naming

En el mismo flujo aparecen `get_resources()`, `getResource()` y `obtenerCategorias()`.

→ Recomiendo alinearlo a snake_case como el resto del proyecto para mantener consistencia y legibilidad.

### Comentario 5 — Magic numbers

El límite de paginación (`12`) aparece repetido en distintos lugares.

→ Mejor moverlo a una constante para evitar inconsistencias futuras.

### Comentario 6 — Tracking / concurrencia

La actualización del contador en `erm_tracking` puede quedar inconsistente bajo carga concurrente.

→ No es bloqueante inmediato, pero sí algo que conviene corregir antes de que el tráfico crezca.

## Para próximas iteraciones (🟢)

- Remover `console.log` olvidado.
- Agregar al menos un test básico de happy path.
- Revisar accesibilidad de los filtros para navegación por teclado.
- Agregar soporte i18n usando `__()` y `_e()`.
- Mejorar el commit message con más contexto funcional.
- Revisar si `legacy_filter_resources()` todavía tiene sentido mantenerla.

El PR va bien y ya resolviste varias cosas complejas para ser tu primera feature completa. Ahora toca llevarlo al nivel de producción que necesitamos como equipo.

Cuando cierres los bloqueantes me avisas y lo revisamos juntos otra vez.
```

### Artefacto B — Plan del 1:1 con Camila

Después del review escrito, vas a tener un 1:1 con ella (15-20 min, ad hoc, no el quincenal regular).

Entrega:

- **Por qué** decidiste tener el 1:1 además del review escrito (¿qué cosas NO pusiste en el PR y prefieres hablar en persona?).
- **Cómo lo abres**: la primera frase que dices al sentarte con ella.
- **Qué temas tocas y en qué orden** (lista corta).
- **Qué temas explícitamente NO tocas en este 1:1** (los dejas para después o nunca).
- **Cómo lo cierras**: la frase con la que la mandas de vuelta a su escritorio.

---

## ⚠️ Anti-patrones que estaremos buscando

- Listar los 12 issues sin priorizarlos como si todos pesaran igual.
- Tono condescendiente disfrazado de paciencia (*"tranquila, todos cometemos errores"*).
- Falsa empatía (*"sé que es difícil"*) sin contenido específico.
- Sándwich de feedback estructural (positivo - negativo - positivo) que diluye el mensaje.
- Pasar todos los 🔴 al 1:1 para no "humillarla" en el PR — eso priva al resto del equipo de la trazabilidad del review.
- Ignorar lo del LinkedIn / standup / orgullo, como si fuera información irrelevante.
- Resolver tú los issues (commit directo a su rama) "para no hacerla sentir mal".

---

## 🟢 Señales de un buen entregable

- El review escrito es algo que **Camila podría leer un viernes a las 18:00 y no llegaría a casa destruida**, pero **sí entendiendo claramente que tiene que rehacer cosas críticas**.
- La jerarquía 🔴/🟡/🟢 es coherente con tu criterio (puedes mover alguno de mi clasificación si lo justificas).
- Hay al menos un comentario donde reconoces algo específico y bien hecho.
- El 1:1 toca lo que NO se puede poner por escrito (lo del orgullo, la confianza, el LinkedIn) sin hacerlo incómodo.
- En conjunto, Camila sale del proceso con **claridad técnica** y **confianza intacta o reforzada**.

---

## Artefacto B — Plan del 1:1 con Camila

### ¿Por qué decidí tener este 1:1 además del review escrito?

Porque hay cosas importantes que prefiero conversar en persona y no dejar solamente escritas en un PR.

Por un lado, quiero reforzar algo positivo: Camila mostró iniciativa, orgullo por su trabajo y disposición a exponerse mostrando avances. Eso me parece valioso y no quiero que el volumen de comentarios técnicos opaque completamente eso.

Pero también quiero asegurarme de que entienda bien el peso de algunos temas de seguridad y escalabilidad. No desde el miedo ni desde “lo hiciste mal”, sino entendiendo por qué esos detalles después terminan generando problemas reales en producción.

Además, un review con muchos comentarios puede pegar emocionalmente más fuerte cuando es la primera feature completa que alguien entrega solo. Prefiero validar cómo lo recibió y asegurarme de que salga con claridad y no con inseguridad.

### ¿Cómo lo abro?

> “Primero: buen trabajo sacando tu primera feature completa sola. Se nota que le metiste bastante esfuerzo. Quería revisar contigo cómo te sentiste durante el desarrollo y también cómo recibiste el review.”

### ¿Qué temas toco y en qué orden?

1. Reconocer el esfuerzo y el ownership que mostró sacando la feature completa.
2. Preguntarle cómo recibió el review y escuchar primero su percepción.
3. Explicarle por qué los temas bloqueantes sí son importantes en producción:
   - seguridad,
   - validación de inputs,
   - performance,
   - y escalabilidad.
4. Aclararle que un PR con varios comentarios no significa que haya fracasado.
5. Ofrecer apoyo puntual si se bloquea resolviendo alguno de los puntos críticos.
6. Reforzar que prefiero ver initiative y PRs reales antes que juniors escondiendo dudas por miedo al review.

### ¿Qué temas explícitamente NO toco en este 1:1?

- Los temas menores del PR (`console.log`, i18n, detalles pequeños de estilo).
- Comparaciones con otros juniors o con velocidad de otros devs.
- Conversaciones de performance formal o evaluación.
- Cambios de proceso del equipo o discusiones técnicas profundas que pertenecen a otro espacio.

### ¿Cómo lo cierro?

> “La feature va bien. Solo necesitamos llevar varios puntos a estándar de producción antes de mergearla. Prefiero que te enfrentes a este tipo de feedback ahora y no más adelante cuando los errores cuestan mucho más. Cuando avances con los bloqueantes me buscas y lo revisamos juntos otra vez.”
