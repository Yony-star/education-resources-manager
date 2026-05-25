# Dilemas de Liderazgo

> 8 situaciones reales sin respuesta única. Cada dilema tiene **al menos dos caminos defendibles**.
>
> **Formato**: 200-400 palabras por dilema. Estructura sugerida:
> 1. Cómo lees la situación (qué identificas como núcleo del problema, no la superficie).
> 2. Qué haces y en qué orden.
> 3. Qué riesgo asumes con tu camino y cómo lo mitigas.
> 4. Qué señales te harían cambiar de camino.
>
> **No queremos**: respuestas calcadas de libros, soluciones que evitan la conversación dura, ni caminos donde tú no asumes ningún costo.
>

---

## Dilema 1 — El aumento salarial

Un senior con caída clara de desempeño los últimos 3 meses te pide un aumento en su 1:1. Trae una oferta externa con +25% sobre su salario actual. Tu presupuesto sí da para igualarla o superarla.

Tu lectura interna: hace 3 meses entregaba más, ahora entrega menos. Sus reviews son superficiales. Su asistencia a reuniones bajó. Tú no has tenido aún la conversación sobre el bajón.

¿Qué haces?

> Primero tendría la conversación que debí haber tenido antes. Para mí el problema real no es la oferta externa sino que llevo semanas viendo señales claras de bajón y no las he abordado directamente. Igualar el salario sin hablar de eso mandaría un mensaje equivocado al equipo.
>
> En el 1:1 reconocería su valor histórico y la oferta, pero también sería transparente con lo que estoy viendo: menos profundidad en reviews, menos presencia y caída en entregas. Intentaría entender primero si hay burnout, desmotivación o algo personal detrás.
>
> Si veo intención real de recuperarse, propondría un plan corto de 4-6 semanas con objetivos claros antes de tomar la decisión salarial final. El riesgo es que se vaya. Lo asumo, porque ya aprendí que tomar decisiones por miedo a perder gente termina afectando la credibilidad del liderazgo.
>
> Cambiaría mi postura si descubro una situación personal fuerte o si noto que el problema viene más de algo estructural del equipo que de él individualmente.

---

## Dilema 2 — El brillante tóxico

El dev más productivo del equipo (entrega 2x sobre el promedio, conoce sistemas críticos) es **condescendiente** en code reviews: comentarios como *"esto es de primer año"*, *"¿realmente probaste esto?"*, *"otra vez lo mismo"*.

Dos juniors te dijeron en 1:1, por separado, que **no quieren trabajar con él**. Uno está empezando a evitarlo asignándose tickets que él no toque.

El dev en cuestión **no lo percibe**. Si le preguntas, te dirá que él "solo está siendo riguroso". Tu manager le tiene aprecio por su productividad.

¿Qué haces?

> Lo primero que veo es que el problema no es técnico sino cultural. Cuando la gente empieza a evitar trabajar con alguien, el daño ya está pasando aunque la productividad individual siga siendo alta.
>
> Hablaría primero con los juniors por separado para validar patrones concretos. Después tendría una conversación directa con el senior usando ejemplos específicos de comentarios y el efecto que están generando. No intentaría suavizar demasiado el mensaje.
>
> Hace tiempo tuve un junior que repetía constantemente “soy una máquina” cada vez que resolvía algo, incluso tareas pequeñas. Al principio parecía entusiasmo, pero terminó construyendo una percepción de héroe dentro del equipo. Cuando se fue por una mejor oferta, otros devs también empezaron a buscar afuera. Desde ahí me volví mucho más cuidadoso con los comportamientos que erosionan silenciosamente la cultura.
>
> El riesgo acá es perder velocidad técnica o incluso a la persona. Lo mitigaría distribuyendo conocimiento crítico desde temprano. Cambiaría mi postura si veo autocrítica genuina y cambios sostenidos en cómo interactúa con el equipo.

---

## Dilema 3 — El leak de información

Un viernes por la tarde descubres que un miembro del equipo, hace 6 días, hizo push de código del producto a un repositorio personal público en GitHub. Lo descubres tú por casualidad. Ya lo borró cuando se dio cuenta, pero la caché de GitHub mostró el código durante ~30 horas. No hay credenciales en el código, sí lógica de negocio sensible.

La persona aún no te lo ha dicho. Crees que se dio cuenta y prefirió que se enterara nadie.

¿Comunicas? ¿A quién? ¿Cuándo? ¿Qué le dices a la persona?

> Sí comunicaría, pero primero validaría el alcance real del leak antes de reaccionar emocionalmente. El problema para mí no es solo el error técnico sino que la persona aparentemente decidió no reportarlo.
>
> Hablaría con ella ese mismo día en privado y de forma directa: “Encontré el repositorio público y necesito entender exactamente qué pasó”. Quiero escuchar primero si hubo miedo, desconocimiento o una decisión consciente de ocultarlo.
>
> Después escalaría a seguridad y a mi jefe con la información concreta y el impacto estimado. No esperaría hasta el lunes porque prefiero controlar la narrativa temprano antes de que alguien más lo descubra.
>
> El riesgo es que la persona entre en modo defensivo o sienta que ya está condenada. Intentaría separar claramente el error de la identidad de la persona, enfocándome más en responsabilidad y aprendizaje que en culpa pública.
>
> Cambiaría parcialmente el camino si descubro que el impacto real fue mínimo y la persona mostró transparencia inmediata antes de que yo hablara con ella.

---

## Dilema 4 — El push del viernes

Viernes 17:00. El PM entra a Slack: *"Necesitamos deployar la feature X esta tarde, el cliente VIP la pidió y firma el contrato anual el lunes a primera hora."*

Tu equipo está cansado (sprint duro). La feature tuvo code review pero no QA completo. Tú estimas riesgo medio-alto de bug. Tu monitoring de fin de semana es limitado.

El PM cuando expresas dudas dice: *"Si no la subimos, perdemos 200k de revenue."* Y escala a tu jefe.

Tu jefe te escribe por DM: *"Tú decides, tú lideras el equipo. Pero ten en cuenta el revenue."*

¿Qué haces?

> Mi lectura es que el problema real no es el deploy sino la presión por tomar una decisión cansados y con visibilidad parcial del riesgo. He visto incidentes ocurrir más por fatiga y urgencia que por falta de capacidad técnica.
>
> Respondería con datos concretos del riesgo y propondría una alternativa: deploy controlado el sábado temprano con rollback plan claro y equipo mínimo necesario. Si aun así quieren salir el viernes, dejaría documentado mi razonamiento y pediría que el riesgo quede explícitamente asumido.
>
> En una compañía extranjera donde trabajé estaba prácticamente prohibido desplegar los viernes. La lógica era proteger el balance vida-trabajo y evitar incidentes donde todo el equipo terminara conectado el fin de semana. En ese momento me pareció extremo, pero honestamente casi nunca vi que un despliegue terminara en crisis.
>
> Después estuve en otro entorno donde la mentalidad era distinta: si ya hubo revisión y QA, no debería existir miedo al deploy. Y también entendí el valor de esa confianza técnica. Hoy creo que ambas visiones tienen algo de razón. Pero también creo que, por más checklists y procesos que existan, siempre hay una probabilidad real de falla en producción.
>
> El riesgo es quedar como alguien poco flexible frente al revenue. Lo mitigaría llegando con opciones y no solamente con un “no”.
>
> Cambiaría mi decisión si aparece evidencia fuerte de que el contrato realmente depende de tener la feature activa esa misma noche.

---

## Dilema 5 — El dev fantasma remoto

Tienes un dev mid-level en una zona horaria con 7h de diferencia. Cumple sus entregas. Pero hace 3 meses:

- Apaga la cámara en todas las videollamadas.
- Sus mensajes en Slack tardan 4-6h en responderse, incluso en su horario.
- No participa en discusiones técnicas en el canal del equipo.
- En el último 1:1 estuvo monosilábico. "Todo bien." Nada más.

El equipo empieza a sentir que "no está realmente con ellos". Un senior te preguntó si está bien o si va a renunciar.

¿Qué haces?

> No asumiría inmediatamente mala actitud. Muchas veces detrás de ese comportamiento hay burnout, desconexión o algo personal que el equipo no está viendo. Pero tampoco ignoraría las señales porque ya están afectando la percepción del resto.
>
> Tendría un 1:1 dedicado únicamente a entender qué está pasando, sin entrar acusando. Le preguntaría directamente cómo se siente con el trabajo remoto, la carga y el equipo.
>
> Hace un tiempo tuve un científico de datos al que conscientemente evité supervisar demasiado porque no quería caer en micro-management. Veía pocos avances reales, pero asumí que necesitaba más espacio. Después entendí que gran parte de su energía estaba puesta en construir un caso de estudio para irse a Rappi. Eso me frustró bastante y me dejó un aprendizaje incómodo: dar autonomía no significa desconectarse del seguimiento real.
>
> El riesgo aquí es pasarme al otro extremo y volverme invasivo. Intento ser consciente de eso porque cuando la presión sube tiendo naturalmente a involucrarme demasiado para asegurar calidad y tiempos.
>
> Cambiaría el camino si noto señales claras de desgaste emocional serio o si descubro que realmente ya está desconectado del proyecto.

---

## Dilema 6 — El despido necesario

Llevas 3 meses con un miembro del equipo en un proceso formal de mejora. Has dado feedback estructurado, has documentado, le has puesto soporte (pairing, mentor, objetivos micro). No hay mejora suficiente.

RR.HH. te dice: *"Tienes la documentación. Procede con la salida. Tienes esta semana."*

Es una persona que aprecias humanamente. Tiene hipoteca, dos hijos pequeños. Te ha dicho varias veces que valora trabajar contigo.

¿Cómo conduces la conversación final? ¿Qué dices? ¿Qué NO dices? ¿Cómo lo comunicas al resto del equipo después?

> Intentaría ser muy directo y humano al mismo tiempo. Abriría la conversación diciendo algo como: “Esta conversación es difícil para mí también, pero después de todo el proceso que hicimos no vimos la mejora que el rol necesita”.
>
> Explicaría claramente la decisión y los siguientes pasos sin esconderme detrás de lenguaje corporativo. No intentaría llenar silencios incómodos ni dar falsas esperanzas. Aprendí hace años que suavizar demasiado estas conversaciones termina generando más confusión.
>
> Tampoco diría frases como “esto es lo mejor para todos” o “no es personal”, porque sí tiene un impacto profundamente personal para la persona.
>
> El riesgo acá es cargar emocionalmente con la decisión y que el equipo perciba inseguridad en el liderazgo. Lo mitigaría siendo transparente, respetuoso y consistente con todo el proceso previo.
>
> Al equipo comunicaría algo simple y profesional, sin detalles privados: que después de un proceso largo la persona deja la compañía y agradecemos sus aportes.
>
> Cambiaría parcialmente el camino si en los últimos días apareciera una mejora muy clara y sostenida que justificara extender el proceso.

---

## Dilema 7 — Cambios en el stack tecnológico

La compañía necesita adoptar nuevas tecnologías: uso de IA en la codificación, adopción de nuevos lenguajes de desarrollo y te están presionando por aumentar la velocidad en los desarrollos y la eficiencia del tiempo.

¿Qué planteamiento harías para lograr la adopción de estás tecnologías, aunque algunas de ellas puedan ser disruptivas para el equipo?

> No intentaría imponer cambios de golpe porque normalmente eso genera más resistencia y ansiedad que adopción real. Haría primero pruebas pequeñas con gente interesada para medir si realmente mejoran productividad, calidad o velocidad.
>
> En proyectos recientes vimos que algunas herramientas de IA aceleraban muchísimo ciertas tareas y otras solo agregaban ruido. Por eso intentaría separar hype de valor real antes de convertir algo en estándar del equipo.
>
> También hablaría abiertamente de algo que a veces management subestima: aprender nuevas tecnologías mientras sigues entregando genera desgaste real. Si no se reconoce eso, la gente empieza a sentir que nunca alcanza.
>
> El riesgo es avanzar demasiado lento frente a la presión del negocio o perder competitividad. Lo mitigaría mostrando resultados concretos de los pilotos y compartiendo quick wins temprano.
>
> Cambiaría mi enfoque si noto que la tecnología realmente mejora métricas importantes sin aumentar demasiado la carga cognitiva del equipo.

---