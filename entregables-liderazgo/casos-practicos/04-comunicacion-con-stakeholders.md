# Caso práctico 04 — Comunicación con Partes Interesadas

> **Lo que evaluamos**: traducción técnico → negocio, encuadre por impacto, capacidad de decir "no" sin enfrentamiento, anticipación de objeciones.
> **Lo que NO evaluamos**: profundidad técnica de la deuda. La descripción técnica del problema ya te la damos; tu trabajo es traducirla.

---

## 🎬 Contexto

Eres líder del equipo del plugin de gestión de recursos educativos. Tu empresa vende SaaS B2B a universidades y centros de formación. **75 clientes activos**. Tu equipo es 5 personas.

### Lo técnico (te lo damos resumido, no profundices)

Heredaste el plugin con **deuda técnica significativa**. Lo más crítico:

- El sistema de tracking de visualizaciones (tabla `erm_tracking`) no fue diseñado para escala. Cada vista hace un INSERT directo y un UPDATE a un contador en `wp_options`. Bajo carga genera locks.
- La paginación en el listado de recursos hace queries N+1 con `wp_postmeta`. Con clientes que tienen >5.000 recursos, cargar la página tarda 8-14 segundos.
- No hay caché en endpoints REST. Cada request golpea BD.
- El admin de WordPress muere si un cliente carga más de 1.000 recursos (timeout de PHP).

### El impacto que ESO está causando ahora

- **2 clientes grandes** (universidades, ~40% del MRR cada una) reportaron lentitud severa el último mes.
- **18 tickets de soporte al mes** sobre performance. Crecen un 12% mensual.
- **Soporte se está quemando**: el último 1:1 que tuvo el lead de soporte con tu jefa terminó con *"si no se arregla, voy a tener que dejar de cubrirles"*.
- **Una cuenta clave** está renegociando contrato a la baja porque *"el producto no escala con nuestro tamaño"*.
- **2 bugs P1 en producción este trimestre** (cada uno con ~3h de outage parcial). Los tres tuvieron como factor contribuyente la deuda técnica descrita.

### Lo que necesitas

**8 semanas del equipo completo** (40 semanas-persona) para resolver lo crítico:
- Refactor del módulo de tracking con buffering async + batch inserts.
- Rediseño de queries con JOINs apropiados + caché objeto.
- Cache layer para REST API (CDN + memcached).
- Paginación adecuada en admin.

### El terreno político

- **Carla, PM senior** (con quien priorizas el roadmap): piensa que esto es *"perfeccionismo de ingenieros"*. Quiere meter 3 features nuevas este trimestre porque *"ya las prometimos a 4 clientes"*. Llama al refactor *"limpieza"*. No tiene mala intención, no entiende el alcance.

- **Diego, CEO**: técnico de formación pero hace 6 años no toca código. Escuchó la palabra *"refactor"* en su anterior empresa y le costó **8 meses de retraso en una feature crítica**. Desde entonces, la palabra le activa todas las alarmas. Te dijo el mes pasado en pasillo: *"chicos, no me hagan otro refactor por favor".*

- **Tu jefa de ingeniería**: aliada pero "que no me lo escalen". Quiere que tú lo negocies con Carla y Diego. Te apoyará si llegas alineado, no si llegas con conflicto.

- **Soporte**: tu aliado de facto. Tienen los datos.

---

## 📝 Tu entregable

Produce **tres artefactos**, en este orden de audiencia.

---

### Artefacto A — Email a Carla (PM)

Email que vas a mandarle hoy. Ella es tu primera parada porque sin su acuerdo, el roadmap no se mueve.

Reglas:
- **Asunto** que no diga "refactor".
- **Primera línea** que no la ponga a la defensiva.
- **Datos concretos**: tickets, MRR en riesgo, cliente renegociando.
- **Trade-offs explícitos**: si te da 8 semanas, qué pierde ella (no escondas el costo).
- **Alternativas**: 2-3 caminos con sus pros/contras (ej. full 8 sem vs. parcial 4 sem + features paralelas vs. solo 1 cliente piloto vs. nada y aceptamos riesgo).
- **Pregunta de cierre** que invite a reunión, no que pida aprobación por email.
- Longitud: **400-600 palabras**.

#### Respuesta

**Asunto:** Riesgo de estabilidad en cuentas grandes y opciones para este trimestre

Hola Carla,

Quería hablar contigo antes de la próxima revisión de roadmap porque creo que ya estamos entrando en un punto donde algunos problemas de estabilidad empiezan a afectar negocio de forma más visible.

En el último mes tuvimos dos universidades grandes reportando lentitud severa. Entre ambas representan cerca del 40% del MRR. Además, soporte ya está manejando alrededor de 18 tickets mensuales relacionados con performance y el volumen viene creciendo aproximadamente un 12% mes a mes. Una de las cuentas incluso está renegociando contrato a la baja porque sienten que el producto no responde bien con el volumen que manejan hoy.

Mi preocupación no es tanto “calidad técnica” en abstracto sino que ya estamos gastando cada vez más tiempo en mitigaciones, soporte e incidentes que terminan afectando roadmap igual, solo que de forma menos visible y más desordenada.

También veo desgaste real en soporte. En el último 1:1 con ingeniería, el lead comentó que no cree sostenible seguir cubriendo este nivel de presión durante muchos meses más si el comportamiento actual continúa.

Sé que tenemos 3 features comprometidas este trimestre y no quiero esconder ese costo. Si priorizamos estabilización completa, probablemente tengamos que mover al menos 2 de esas iniciativas al próximo trimestre.

Las alternativas que veo hoy son:

1. **Estabilización completa (~8 semanas)**  
El equipo se enfoca en resolver los puntos que hoy generan la mayoría de incidentes y lentitud en clientes grandes.  
**Beneficio:** reducimos bastante el riesgo operacional y la presión sobre soporte.  
**Costo:** mover gran parte del roadmap comprometido este trimestre.

2. **Estabilización parcial (~4-5 semanas)**  
Atacamos solo los problemas que hoy más impactan clientes enterprise y soporte.  
**Beneficio:** podríamos mantener 1 o 2 features comprometidas.  
**Riesgo:** probablemente volvamos a tener esta conversación en algunos meses porque no elimina el problema completo.

3. **Contención mínima**  
Hacemos ajustes puntuales para proteger la cuenta que hoy renegocia contrato y mantenemos casi todo el roadmap.  
**Beneficio:** menor impacto inmediato sobre features.  
**Riesgo:** seguimos acumulando presión operativa y posibilidad de nuevos incidentes.

No lo veo como ingeniería versus roadmap. Entiendo perfectamente la presión comercial detrás de esas features. Mi lectura es que estabilidad ya empezó a convertirse también en un problema de retención y crecimiento.

¿Te parece si lo conversamos hoy o mañana y vemos juntas qué escenario tiene más sentido antes de bajarlo a Diego con una propuesta alineada?

Saludos,  
Yony

---

### Artefacto B — Deck para Diego (CEO) en 5 láminas

Vas a tener una reunión de **20 minutos** con Diego. Crea el deck en **Markdown** (no diseño, solo contenido y estructura). 5 láminas, ni una más.

Lámina 1: **Título + un dato impactante**  
Lámina 2: **Problema** (en lenguaje de negocio, no de stack)  
Lámina 3: **Costo de no actuar** (proyección a 6 meses)  
Lámina 4: **Propuesta** (opciones, no "el refactor")  
Lámina 5: **Lo que necesito de ti**

Reglas no negociables:
- **La palabra "refactor" no puede aparecer ni una sola vez**. Si la necesitas, busca otra forma de decirlo.
- **Cada lámina debe tener un número o dato** específico, no solo prosa.
- **El deck debe poder leerse en 90 segundos** si Diego solo mira las láminas y no escucha tu explicación.
- **Lámina 4** debe ofrecer **al menos 2 caminos**, no uno solo (Diego rechaza opciones únicas).
- **Lámina 5** debe pedir algo **concreto y pequeño** primero, no la aprobación de las 8 semanas.

Formato:

```markdown
## Lámina 1 — [título]
- Bullet 1
- Bullet 2
- (notas para mi narración: ...)

## Lámina 2 — [título]
...
```

#### Respuesta

```markdown
## Lámina 1 — La estabilidad ya está afectando cuentas grandes
- 2 universidades grandes reportaron lentitud severa el último mes
- ~40% del MRR involucrado en cuentas afectadas
- 18 tickets mensuales de performance (+12% mensual)
- 1 cuenta renegociando contrato a la baja

(notas para mi narración: abrir desde impacto en clientes y revenue, no desde tecnología)

## Lámina 2 — El problema ya consume capacidad del negocio
- Soporte absorbe cada vez más incidentes y desgaste operativo
- Ingeniería dedica más tiempo a mitigaciones y menos a roadmap
- Clientes grandes perciben que el producto no escala bien con su crecimiento
- Tuvimos 2 incidentes P1 este trimestre relacionados con carga y estabilidad

(notas para mi narración: explicar que el costo ya existe aunque no se vea como proyecto formal)

## Lámina 3 — Costo de seguir igual (próximos 6 meses)
- Mayor riesgo de churn en cuentas enterprise
- Más presión sobre soporte y tiempos de respuesta
- Más interrupciones parciales en momentos críticos
- Menor velocidad real de entrega por firefighting continuo

(notas para mi narración: mostrar que no decidir también tiene costo)

## Lámina 4 — Opciones posibles

### Opción 1 — Estabilización completa (~8 semanas)
- Reduce significativamente incidentes y presión operativa
- Mejora experiencia de clientes grandes
- Requiere mover parte importante del roadmap actual

### Opción 2 — Estabilización parcial (~4-5 semanas)
- Reduce los problemas más visibles para clientes enterprise
- Permite sostener algunas features comprometidas
- Mantiene parte del riesgo operativo

### Opción 3 — Contención puntual
- Protege la cuenta que hoy renegocia contrato
- Mantiene casi todo el roadmap
- Riesgo alto de repetir incidentes y desgaste continuo

(notas para mi narración: dejar claro trade-offs y evitar presentar una única salida)

## Lámina 5 — Lo que necesito esta semana
- Alinear prioridad entre estabilidad y roadmap comercial
- Definir qué nivel de riesgo estamos dispuestos a aceptar este trimestre
- Validar un primer bloque de 1 semana para medir impacto y ajustar alcance
- Coordinar mensaje común entre Producto, Ingeniería y Soporte

(notas para mi narración: pedir un primer paso pequeño y reversible, no aprobación total inmediata)
```

---

### Artefacto C — FAQ anticipando objeciones

Página de FAQ que **tú** vas a tener a mano (no la entregas) para anticipar objeciones de Carla y Diego.

Estructura: 8-10 objeciones reales + tu respuesta de ≤ 80 palabras cada una.

#### Respuesta

### 1. “¿Por qué no se hizo bien desde el principio?”
Es una pregunta válida. Parte del sistema fue construido cuando el volumen de clientes y uso era mucho menor. Algunas decisiones funcionaban bien en ese momento, pero hoy el producto opera en otra escala. Mi foco ahora es corregir los puntos que ya están impactando clientes y soporte, no buscar culpables.

---

### 2. “Y si dejamos solo el cliente que renegocia, ¿no podemos contener la situación?”
Sí, podemos reducir parcialmente el riesgo inmediato de esa cuenta. El problema es que los otros clientes grandes muestran señales similares y probablemente lleguen al mismo punto. Puede servir como contención temporal, pero no reduce realmente la presión operativa general.

---

### 3. “¿Por qué 8 semanas y no 3 con tu mejor gente?”
Entiendo la preocupación y tampoco quiero convertir esto en un proyecto interminable. El problema afecta varias áreas del producto y el equipo sigue sosteniendo operación diaria al mismo tiempo. Reducir demasiado el alcance probablemente termine moviendo el problema de lugar en vez de resolverlo.

---

### 4. “Tu equipo entrega lento. ¿Cómo me garantizas que no será 16 semanas?”
No puedo garantizar riesgo cero y sería irresponsable decirlo. Lo que sí propongo es trabajar en fases visibles, con revisiones semanales y resultados parciales medibles. Prefiero mostrar progreso concreto cada semana antes que pedir confianza ciega durante dos meses.

---

### 5. “¿Y las 3 features que prometimos a los clientes? ¿Las cancelo yo?”
Tienes razón en que esas promesas importan. Por eso no estoy proponiendo una única alternativa. Los escenarios intermedios permiten reducir parte del riesgo mientras mantenemos algunas entregas comprometidas. Mi intención no es frenar negocio, sino evitar que estabilidad termine afectando esas mismas cuentas.

---

### 6. “¿No podemos comprar una herramienta que resuelva esto?”
Podemos apoyarnos en herramientas para mejorar partes del problema, especialmente cache y monitoreo. Pero hoy también hay limitaciones en cómo el producto procesa carga internamente. Comprar algo puede ayudar, pero no reemplaza ordenar los puntos críticos actuales.

---

### 7. “¿Por qué no contratamos a alguien junior solo para esto?”
Podría ayudar más adelante en tareas puntuales, pero el trabajo más sensible hoy requiere contexto del producto y coordinación con clientes activos. Además, alguien nuevo necesita onboarding antes de generar impacto real.

---

### 8. “Si tan grave es, ¿por qué no me lo dijiste hace 3 meses?”
Es una objeción justa. Hace algunos meses ya veíamos señales, pero todavía no había impacto tan visible en clientes grandes ni soporte. Mi error fue asumir que las mitigaciones actuales nos darían más margen. Hoy ya tenemos suficiente evidencia para saber que no es sostenible seguir igual.

---

### 9. “¿No están exagerando para conseguir tiempo técnico?”
Entiendo la preocupación porque muchas veces estos proyectos se presentan mal desde ingeniería. Por eso intenté enfocarme en datos concretos: tickets creciendo, clientes afectados, renegociación activa y desgaste visible en soporte. Si fuera solo una mejora interna, no propondría mover roadmap.

---

### 10. “¿Qué pasa si decidimos no hacer nada este trimestre?”
Podemos hacerlo, pero necesitamos aceptar conscientemente el costo asociado: más tickets, más presión sobre soporte, mayor probabilidad de incidentes y menor velocidad real del equipo por firefighting continuo. Mi intención no es forzar una decisión, sino hacer visibles los riesgos de cada camino.