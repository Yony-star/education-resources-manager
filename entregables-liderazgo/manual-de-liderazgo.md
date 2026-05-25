# Manual de Liderazgo ⭐

> Este es el **entregable central** del ejercicio asíncrono de liderazgo.
>
> Rellena cada sección con **tu** forma de liderar. No hay respuestas correctas. Buscamos consistencia, profundidad y humanidad — no frameworks copiados de un libro.
>
> Si para alguna sección no tienes experiencia previa, **dilo explícitamente** y explica cómo lo abordarías con la lógica que aplicarías. Es 100% legítimo. Lo que no es legítimo es inventar experiencia.

---

## 0. Sobre ti como líder (≤ 200 palabras)

Antes de las secciones operativas, cuéntanos en primera persona:

- ¿Qué tipo de líder eres? (no etiquetas, descríbete con verbos)
- ¿Qué cosa te enorgullece haber construido en un equipo, y por qué?
- ¿Qué cosa lamentas haber hecho como líder y qué aprendiste?

> Soy un líder técnico al que le gusta estar cerca de los problemas reales del equipo, no solo de la estrategia. Me gusta ayudar a que las personas entiendan por qué estamos construyendo algo y convertir eso en decisiones técnicas concretas sin perder foco en entregar valor.
> 
> Me enorgullece haber fundado Machina Games Studios y sostener un equipo creativo durante varios años en una industria bastante caótica. Llegamos a crecer hasta 17 personas y lanzamos cientos de juegos, pero lo que más valoro es que aprendimos a operar bajo mucha presión sin destruir al equipo en el proceso. Más adelante, en Mareigua, participé liderando una migración grande de datos financieros hacia Snowflake que nos obligó a mejorar muchísimo la calidad y estabilidad operativa.
> 
> También he cometido errores importantes. En uno de mis primeros roles de liderazgo demoré demasiado en enfrentar un problema de desempeño porque la persona técnicamente rendía mucho. Perdí a un junior muy bueno por no actuar antes. Desde ahí aprendí que postergar conversaciones difíciles normalmente termina costando más al equipo.

---

## 1. Onboarding del primer mes

Diseña el onboarding de un dev WordPress senior que entra a tu equipo. Tu equipo es 6 personas: tú, 2 seniors, 2 mid-level, 1 junior.

Estructura tu respuesta así:

### Día 1
- Qué hace, con quién, qué entrega al final del día.
- Métrica de éxito del día 1.

### Semana 1
- Hitos por día. Quién es su buddy. A qué reuniones va y a cuáles no.
- Métrica de éxito de semana 1.

### Mes 1
- Primer PR mergeado: ¿cuándo, de qué tipo, quién lo revisa?
- Primer 1:1 contigo: cuándo y agenda.
- Métrica de éxito de mes 1.

### Mes 3
- Qué esperas que esté haciendo solo. Qué responsabilidades tiene asignadas.
- Métrica de éxito de mes 3.

> ### Día 1
> - Bienvenida personal, acceso completo al entorno y pairing de varias horas con uno de los seniors.
> - Intento que haga su primer commit el mismo día, aunque sea pequeño.
> - Al final del día espero que ya pueda correr el proyecto localmente y entender el flujo básico.
> - **Métrica de éxito:** La persona sabe cómo pedir ayuda y siente que no está sola entrando al equipo.
> 
> ### Semana 1
> - Los primeros días hace pairing frecuente con su buddy principal, que normalmente sería uno de los seniors.
> - Participa en dailies y reuniones técnicas internas, pero no lo meto todavía en conversaciones pesadas con stakeholders.
> - Antes del viernes debería haber abierto su primer PR pequeño.
> - Hago un 1:1 corto para entender cómo se está sintiendo y qué dudas tiene.
> - **Métrica de éxito:** Ya entiende el flujo básico del proyecto y puede mergear cambios simples con acompañamiento.
> 
> ### Mes 1
> - Entre la semana 2 y 3 espero un primer PR más relevante, normalmente una mejora o feature pequeña revisada por mí y otro senior.
> - El primer 1:1 formal conmigo sería en la semana 3. Hablaríamos de onboarding, expectativas, dudas técnicas y cómo se siente con el equipo.
> - **Métrica de éxito:** Ya entrega trabajo con menos acompañamiento y entiende mejor el contexto del negocio y del sistema.
> 
> ### Mes 3
> - Espero que ya pueda tomar features completas de forma autónoma, participar activamente en code reviews y tener ownership de algún módulo o área pequeña.
> - **Métrica de éxito:** El equipo ya puede confiar en sus entregas sin necesidad de supervisión constante.

---

## 2. Plan de mentoring para un junior

Llega al equipo un dev junior con 8 meses de experiencia profesional. Sabe lo básico de WordPress pero nunca ha trabajado en un equipo con code review.

Diseña su plan:

- **Pairing**: cadencia (¿diaria? ¿2x semana?), duración, quién hace pair (¿tú? ¿un senior?), qué se trabaja en cada sesión.
- **Micro-objetivos 30 / 60 / 90 días**: 3 objetivos concretos por hito, no genéricos.
- **Técnicas que usarás**: Socratic questioning, deliberate practice, rubber duck, lecture-and-test, otras. Da un ejemplo concreto de aplicación.
- **Cómo decides que ya no necesita pairing**: criterios objetivos.
- **Qué NO harás**: 3 cosas que evitarás conscientemente.

> - **Pairing:** Haría 3 sesiones por semana durante las primeras semanas. La mayoría con un senior y algunas conmigo. Normalmente sesiones de 60-90 minutos enfocadas en entender el flujo del proyecto, debugging y code reviews.
> - **Micro-objetivos 30 / 60 / 90 días:**
>   - **30 días:** Mergear PRs pequeños, aprender el flujo de revisión y explicar sus decisiones técnicas básicas.
>   - **60 días:** Resolver un ticket completo end-to-end y empezar a revisar PRs sencillos de otros.
>   - **90 días:** Participar activamente en un postmortem y proponer alguna mejora concreta.
> - **Técnicas:** Uso mucho preguntas abiertas y rubber duck. Por ejemplo: “¿Qué crees que pasaría si esta query empieza a recibir mucho más tráfico?”.
> - **Cómo decido que ya no necesita pairing:** Cuando empieza a entregar de forma consistente, hace mejores preguntas y sus PRs requieren menos correcciones importantes.
> - **Qué NO harás:** Resolverle todo, evitarle cualquier frustración o defenderlo automáticamente de feedback duro.

---

## 4. 1:1 template

Comparte:

### 4.1 Estructura del 1:1
- Duración, cadencia (¿semanal? ¿bisemanal?), formato (videollamada, en persona, paseo).
- Agenda fija que usas. Quién la lleva (tú o la persona).
- Cómo tomas notas. Dónde las guardas.
- Cómo das seguimiento entre 1:1s.

### 4.2 Qué SÍ y qué NO discutir
- 5 temas que SÍ van al 1:1.
- 3 temas que NO van al 1:1 (y dónde van entonces).

### 4.3 Ejemplo redactado de un 1:1 ficticio (50-100 líneas)
Inventa un personaje (ej. *"Marta, mid-level, 11 meses en el equipo"*) y redacta cómo iría un 1:1 contigo en formato:
```
Tú: ...
Marta: ...
Tú: ...
```
Que se vea natural. Incluye un momento donde Marta saca un tema incómodo (lo que tú quieras: conflicto con otro dev, queja sobre carga de trabajo, dudas sobre su futuro). Muestra cómo lo manejas en vivo.

> ### 4.1 Estructura del 1:1
> - Bisemanal.
> - Entre 45 y 60 minutos.
> - Normalmente videollamada o presencial si estamos en oficina.
> - La agenda la trae principalmente la persona y yo agrego temas al final si hace falta.
> - Tomo notas cortas en Notion privado y revisamos acuerdos en el siguiente 1:1.
> - Entre sesiones hago seguimiento informal por Slack o conversaciones rápidas.
> 
> ### 4.2 Qué SÍ y qué NO discutir
> **Sí:**
> - Crecimiento profesional.
> - Bloqueos.
> - Carga de trabajo.
> - Feedback mutuo.
> - Motivación y expectativas.
> 
> **No:**
> - Chismes.
> - Discutir personas que no están presentes.
> - Debates técnicos demasiado profundos (eso lo movemos a sesiones técnicas o al canal correspondiente).
> 
> ### 4.3 Ejemplo redactado de un 1:1 ficticio
> ```
> Tú: ¿Cómo te has sentido esta semana realmente?
> Marta: Cansada. Siento que estoy trabajando más que antes y el review de Pedro me pegó duro.
> Tú: Cuéntame qué pasó.
> Marta: Sentí que me habló como si yo no entendiera nada.
> Tú: Entiendo por qué te afectó. Pedro suele ponerse muy directo cuando está bajo presión, pero igual necesitamos cuidar el tono. ¿Quieres que hablemos con él juntos o prefieres intentarlo primero tú?
> Marta: Prefiero hablarlo yo primero.
> Tú: Perfecto. Si quieres, pensamos juntos cómo abordarlo y además revisemos la carga que tienes esta semana.
> Marta: También me preocupa que últimamente siento que solo hago tickets pequeños y no estoy creciendo mucho.
> Tú: Gracias por decirlo. Prefiero enterarme ahora y no cuando ya estés frustrada hace meses. Revisemos qué tipo de tareas podemos empezar a darte para que tomes más ownership.
> ```

---

## 5. Feedback framework

### 5.1 Feedback positivo
- ¿Cómo lo das para que no sea genérico? (evita "buen trabajo", "great job")
- 2 ejemplos concretos: cómo felicitarías a un dev por un buen review y a otro por una refactorización limpia.

### 5.2 Feedback formativo (sobre algo a mejorar, no urgente)
- Tu estructura (SBI, COIN, otro modelo, propio).
- Cuándo lo das (¿en 1:1? ¿inmediato? ¿esperas?).
- 1 ejemplo concreto: feedback a alguien que interrumpe en standups.

### 5.3 Feedback duro (algo que está afectando al equipo)
- Cómo te preparas tú antes de darlo.
- Cómo lo abres. Frase concreta.
- Cómo cierras la conversación con un compromiso.
- 1 ejemplo concreto: feedback a un senior que da reviews condescendientes a juniors.

### 5.4 Feedback vs. coaching
Explica con tus palabras la diferencia. ¿Cuándo usas uno y cuándo el otro?

> ### 5.1 Feedback positivo
> Intento que el feedback positivo sea específico y conectado con algo real que ayudó al equipo.
> 
> Ejemplo 1:
> “Me gustó cómo manejaste el review de Camila. Explicaste el porqué del cambio sin hacerla sentir mal y ella aplicó el ajuste rápido.”
> 
> Ejemplo 2:
> “El cambio que hiciste en los pipelines evitó varios errores manuales y nos ahorró bastante tiempo operativo.”
> 
> ### 5.2 Feedback formativo (sobre algo a mejorar, no urgente)
> Normalmente lo doy en privado y usando ejemplos concretos.
> 
> Ejemplo:
> “En el standup interrumpiste varias veces a Juan y al final dejó de explicar el problema completo. Quiero que trabajemos eso porque afecta la dinámica del equipo.”
> 
> ### 5.3 Feedback duro (algo que está afectando al equipo)
> Antes de estas conversaciones intento llegar con hechos observables y no solo percepciones.
> 
> Suelo abrir con algo como:
> “Necesito hablar contigo de algo que ya está afectando al equipo.”
> 
> Ejemplo:
> “Tus reviews están siendo técnicamente correctos, pero el tono está haciendo que algunos juniors eviten pedir ayuda.”
> 
> Intento cerrar siempre con acuerdos concretos y seguimiento posterior.
> 
> ### 5.4 Feedback vs. coaching
> Para mí el feedback tiene más que ver con comportamiento presente o pasado. Coaching tiene más que ver con ayudar a que la persona piense mejor sus próximos pasos.
> 
> Uso feedback cuando hay algo que necesita corregirse. Uso coaching cuando quiero que la persona llegue por sí misma a una solución o gane más criterio.

---

## 6. Manejo de bajo desempeño

Un dev mid-level entrega tarde 3 sprints seguidos. Las estimaciones eran razonables y consensuadas. No ha pedido ayuda.

### 6.1 Árbol de decisión
Dibújalo en texto. ¿Qué decisiones tomas y en qué orden? ¿Cuándo escalas a RR.HH.? ¿Cuándo inicias un plan de mejora de desempeño (PIP) formal?

### 6.2 Scripts de conversación

**Escenario A** — primera conversación con el dev que entrega tarde:
- Frase de apertura.
- 3 preguntas que harás.
- Cómo cierras.

**Escenario B** — el dev que no respeta el code review (mergea sin esperar aprobación, ignora comentarios):
- Frase de apertura.
- 3 preguntas que harás.
- Cómo cierras.

> ### 6.1 Árbol de decisión
> 1. Primero intento entender qué está pasando realmente.
> 2. Luego acordamos objetivos concretos y visibles por algunas semanas.
> 3. Si no mejora, involucro a RR.HH. y formalizamos un plan.
> 4. Si incluso así no hay cambio, tomamos una decisión de salida.
> 
> Aprendí hace años que dejar esto “a ver si mejora solo” normalmente empeora todo.
> 
> ### 6.2 Scripts de conversación
> **Escenario A**
> Apertura:
> “He notado que las últimas entregas llegaron tarde y quiero entender qué está pasando desde tu lado.”
> 
> Preguntas:
> - “¿Dónde te estás bloqueando?”
> - “¿Qué crees que está faltando?”
> - “¿Cómo puedo ayudarte?”
> 
> Cierre:
> “Definamos acciones concretas para las próximas semanas y revisemos cómo va.”
> 
> **Escenario B**
> Apertura:
> “He visto varios merges sin esperar aprobación completa del review.”
> 
> Preguntas:
> - “¿Qué te llevó a hacerlo así?”
> - “¿Qué rol crees que cumplen los reviews?”
> - “¿Qué necesitamos ajustar?”
> 
> Cierre:
> “Necesitamos volver a respetar el proceso porque si no empezamos a generar riesgo para todo el equipo.”

---

## 7. Manejo de conflictos

Dos seniors discrepan sobre arquitectura. Empezó técnico, ahora se ignoran en standups y los demás están incómodos.

- **Tu rol**: ¿mediador? ¿árbitro? ¿coach? ¿depende?
- **Protocolo**: paso a paso de qué haces en las primeras 48 horas.
- **Si no se resuelve**: ¿cuándo decides tú? ¿cuándo escalas?
- **Prevención**: ¿qué normas del equipo previenen que esto pase de nuevo?

> - Primero hablo individualmente con cada persona para entender el problema completo.
> - Intento actuar inicialmente como mediador y coach, pero si el conflicto ya está afectando al equipo tomo una posición más directa.
> - En las primeras 48 horas hago conversaciones separadas y luego una conversación conjunta.
> - Si no logran resolverlo, tomo una decisión técnica final y explico claramente el razonamiento.
> - Intento prevenir esto dejando claras desde temprano las reglas de cómo discrepamos y cómo damos feedback.

---

## 8. Definition of Done sociocultural

Define qué esperas del equipo *como humanos*, no como código. 5-8 puntos del estilo:

- Comunicación en standups.
- Ayuda mutua y peticiones de ayuda.
- Tono en code reviews.
- Comportamiento en turno de guardia.
- Cómo se discrepa.
- Cómo se reconoce el trabajo de otros.
- Manejo del tiempo personal (horarios, vacaciones).

Cada punto debe tener una formulación concreta (qué SE HACE) y un ejemplo de qué viola el principio.

> - En standups hablamos de blockers reales, no de “todo bien”. Viola el principio esconder problemas hasta el último momento.
> - Ayudar al equipo también es parte del trabajo. Viola el principio ignorar mensajes de ayuda constantemente.
> - En reviews criticamos código, nunca a la persona. Viola el principio usar frases despectivas o burlonas.
> - Si alguien discrepa, espero argumentos y respeto. Viola el principio imponer decisiones solo por seniority.
> - Dar crédito público importa. Viola el principio apropiarse del trabajo de otros.
> - Las guardias se toman con responsabilidad y buen handoff. Viola el principio desaparecer después de dejar un problema a medias.
> - Respeto horarios y vacaciones salvo incidentes reales. Viola el principio normalizar disponibilidad permanente.

---

## 9. Rotación de guardia justa

Diseña la rotación de guardia de tu equipo de 6 personas:

- **Compensación**: ¿pagas? ¿das tiempo libre? ¿depende del contrato?
- **Salida voluntaria**: ¿quién puede salirse y por qué? (parentalidad, salud, etc.)
- **Juniors**: ¿están en rotación? ¿acompañados? ¿desde cuándo?
- **Post-incidente**: si alguien estuvo 6h despierto resolviendo un SEV1 un sábado, ¿qué pasa el lunes?
- **Carga**: cómo te aseguras de que la rotación sea justa cuando hay vacaciones y bajas.

> - Intento compensar guardias con tiempo libre y revisar qué permite también el contrato.
> - Permitiría salir temporalmente por temas de salud, burnout o situaciones familiares importantes.
> - Los juniors entrarían acompañados y no demasiado temprano.
> - Si alguien estuvo horas resolviendo un incidente grave un fin de semana, el lunes no espero que opere al 100% normal.
> - Reviso periódicamente la distribución para evitar que siempre caiga la carga en las mismas personas.

---

## 10. Construcción de una cultura sin señalar culpables

Llegas a liderar un equipo donde **la cultura es de echar culpas**: cuando hay incidente, se busca culpable, se grita en Slack, los devs ocultan errores.

Diseña tu plan de 90 días para cambiar la cultura:

- **Días 1-15**: qué observas, qué NO haces todavía.
- **Días 16-45**: primeras intervenciones. ¿Cómo conduces el primer postmortem? ¿Qué dices la primera vez que alguien busca culpable en una reunión?
- **Días 46-90**: cambios estructurales. Procesos, normas escritas, símbolos visibles.
- **Métricas de éxito**: ¿cómo sabes que está funcionando? (no "encuestas de clima")

> ### Días 1-15
> Escucho mucho y observo patrones antes de intentar cambiar cosas rápido.
> 
> ### Días 16-45
> En el primer postmortem corto cualquier intento de buscar culpables y trato de llevar la conversación hacia sistemas y procesos.
> 
> ### Días 16-45 (Intervención en caliente)
> La primera vez que alguien busca un culpable en una reunión, intervengo inmediatamente reorientando el foco: "No estamos aquí para apuntar a una persona, sino para entender qué fallo en nuestro proceso o herramientas que permitió que este error ocurriera y cómo evitarlo juntos".
> 
> ### Días 46-90
> Empiezo a formalizar reglas y celebro cuando alguien reconoce un error temprano.
> 
> ### Métricas de éxito
> - Qué tan rápido se reportan problemas.
> - Cuánto aprendemos después de incidentes.
> - Si la gente empieza a pedir ayuda antes y no después de explotar el problema.

---

## 11. Retención

### 11.1 Señales tempranas
Lista 5 señales tempranas de que alguien está pensando en irse. Que no sean obvias ("pide más vacaciones").

### 11.2 Cuándo aún no lo ha anunciado
Detectaste las señales. ¿Qué haces? ¿Lo abordas directamente? ¿Esperas? ¿Cambias algo del entorno?

### 11.3 Cuándo ya lo anunció
Te dijo en 1:1 que tiene oferta de otra empresa. Es clave para el equipo.

- Tu reacción **en ese momento** (frase concreta).
- Próximos 7 días: qué haces.
- Si decide irse de todas formas: cómo manejas la transición (knowledge transfer, comunicación al equipo, despedida).

> ### 11.1 Señales tempranas
> - Participa menos.
> - Responde más corto.
> - Se vuelve más defensivo en PRs.
> - Deja de proponer mejoras.
> - Empieza a desconectarse socialmente del equipo.
> 
> ### 11.2 Cuándo aún no lo ha anunciado
> Intento hablar temprano y de forma directa.
> 
> Algo como:
> “Te noto más callado últimamente. ¿Cómo estás realmente?”
> 
> Antes me costaba abrir estas conversaciones tan temprano porque sentía que podía sonar invasivo. Después entendí que muchas veces cuando el líder pregunta tarde, la persona ya tomó la decisión de irse.
> 
> ### 11.3 Cuándo ya lo anunció
> Primero agradecería honestidad.
> 
> Algo como:
> “Gracias por decírmelo directamente. Entiendo que no es una conversación fácil.”
> 
> Durante los siguientes días intentaría entender las razones reales y si todavía hay algo que podamos corregir.
> 
> Si decide irse, organizo una transición ordenada para no dejar al equipo dependiendo de una sola persona y trato de que la salida sea respetuosa y transparente.

---

## 12. Cómo dices "no"

Da una respuesta concreta (frase exacta + razonamiento) para cada situación:

### 12.1 PM que pide push de feature crítica un viernes 18:00
Tu equipo está cansado, hay riesgo. El PM dice "es para un cliente VIP".

### 12.2 Ingeniero que quiere usar tecnología nueva sin justificación
Un senior te dice: "deberíamos reescribir esto en Rust, es 10x más rápido".

### 12.3 CEO que quiere recortar QA
Dice: "QA es un cuello de botella, los devs deberían hacer su propio testing".

> ### 12.1 PM que pide push de feature crítica un viernes 18:00
> “Entiendo la presión y lo importante que es para el cliente. Pero salir hoy sin suficiente validación nos puede generar un problema más grande después. Prefiero moverlo a mañana temprano y hacerlo con más tranquilidad.”
> 
> ### 12.2 Ingeniero que quiere usar tecnología nueva sin justificación
> “Entiendo por qué te interesa, pero hoy el costo de reescribir sería alto. Prefiero primero validar el problema con algo pequeño antes de mover todo.”
> 
> ### 12.3 CEO que quiere recortar QA
> “Si quitamos demasiado QA probablemente ganemos velocidad unas semanas, pero más adelante lo terminamos pagando en incidentes y desgaste.”

---

## 13. Cómo presentas deuda técnica a negocio

Necesitas 8 semanas del equipo para resolver deuda técnica acumulada en el sistema de pagos. Causa 2-3 bugs en producción al mes.

- **Tu framework** para traducir esto a lenguaje de negocio. ¿Hablas de revenue? ¿De riesgo? ¿De velocidad? ¿De los tres?
- **Datos que recoges antes** de la presentación.
- **Estructura de la presentación**: 5 bullets de cómo la armarías.
- **Anticipación de objeciones**: 3 objeciones que esperas + tu respuesta.

> Intento traducir deuda técnica a cosas que negocio sí siente: retrasos, incidentes repetitivos, desgaste del equipo y pérdida de velocidad.
> 
> Antes de la presentación recogería:
> - cantidad de incidentes,
> - tiempo perdido,
> - impacto operativo,
> - frecuencia de bugs,
> - y cuánto esfuerzo se va en apagar incendios.
> 
> Estructura:
> 1. Qué problema existe.
> 2. Cómo impacta hoy al negocio.
> 3. Qué riesgo hay si no se corrige.
> 4. Qué opciones tenemos.
> 5. Qué necesitamos del negocio para resolverlo.
> 
> Objeciones que esperaría:
> - “¿Por qué no se hizo antes?”
> - “¿Y las features?”
> - “¿Realmente necesitamos tanto tiempo?”
> 
> Mi respuesta sería intentar mostrar costo acumulado, trade-offs y por qué seguir postergándolo probablemente sale más caro.

---

## Cierre — Lo que NO está en este manual

Al final, lista 3 cosas importantes sobre liderazgo de equipos que esta plantilla **no te preguntó** y que tú consideras críticas. Explica brevemente cada una.

> 1. La importancia de seguir conectado técnicamente al trabajo real. Todavía me gusta revisar código y entender detalles del sistema.
> 
> 2. Cómo manejar equipos distribuidos y diferencias culturales. Me tocó aprender eso trabajando con personas de distintos países y horarios.
> 
> 3. Que todavía sigo ajustando cosas de mi liderazgo. En proyectos recientes me di cuenta de que cuando la presión por entregar y mantener calidad sube mucho, tiendo a involucrarme demasiado en detalles técnicos y caer un poco en micro management. Estoy trabajando en confiar más temprano y crear mejores mecanismos de seguimiento sin sentir que tengo que revisar todo personalmente.