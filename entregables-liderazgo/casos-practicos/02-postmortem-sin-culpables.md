# Caso práctico 02 — Postmortem sin señalar culpables

> **Lo que evaluamos**: cultura sin señalar culpables, comunicación con partes interesadas, cómo conduces la conversación 1 a 1 con el responsable directo.
> **Lo que NO evaluamos**: análisis técnico profundo del incidente. La causa ya te la damos; no inventes "root causes" técnicas.

---

## 🎬 Contexto

Eres el líder de un equipo de 5 personas en una empresa de e-learning. Tu plugin sirve recursos educativos a ~40.000 usuarios diarios. Hace **3 días** hubo un outage de **47 minutos** en horario pico.

### Lo que pasó (los hechos)

- **Sábado 11:18** — **Marcos** (senior con 3 años en el equipo) mergeó un PR de migración de tabla. El PR había estado abierto 4 días esperando review. Marcos lo aprobó **a sí mismo** y mergeó porque "ya nadie estaba conectado un sábado y la migración era urgente para la demo del lunes".

- **11:22** — Marcos ejecutó la migración en producción. Lock de tabla más largo de lo esperado por el volumen de datos.

- **11:26** — Empezaron los 5xx. Los chequeos de estado fallaron. PagerDuty alertó a Marcos.

- **11:33** — Marcos detectó el problema, intentó rollback. La caché que había confiado en recuperarse no lo hizo (cache stampede al volver: miles de requests pegando a la BD a la vez).

- **12:05** — Resuelto, después de subir más replicas y forzar warmup del cache.

- **Duración total**: 47 min con error rate > 50%.

- **Impacto**: ~2.300 usuarios afectados en el rango. Una empresa cliente nos escribió quejándose. Soporte recibió 18 tickets. Twitter mencionó la caída 4 veces.

### El estado actual del equipo (3 días después)

- **Está dividido**:
  - 2 personas (incluido un senior) opinan que *"Marcos fue irresponsable, mergeó sin review, esto no se hace, hay que sancionar"*.
  - 2 personas opinan que *"el proceso es lo que está mal: si no hay nadie disponible un sábado y la pieza se necesita, alguien iba a tener que cortar esquinas. Marcos hizo lo que cualquiera de nosotros".*
  - 1 persona no se ha posicionado.

- **Marcos** está mal. Ha estado callado en standup, escribió un mensaje en privado a tu jefe asumiendo "toda la responsabilidad". Te dijo en pasillo *"si quieres puedo coger un día de vacaciones, no me siento bien aquí ahora mismo".*

- **Tu jefe** te pregunta *"¿qué medida vamos a tomar?"*

- **El cliente que se quejó** quiere una explicación escrita en 72h.

- **El equipo** está esperando ver qué hacés. Mañana es lunes. Tienes que actuar.

---

## 📝 Tu entregable

Produce **tres artefactos**, en este orden.

### Artefacto A — Postmortem público interno

Documento en Markdown que vas a publicar en el wiki/Notion/Confluence del equipo, accesible para toda la ingeniería de la empresa.

Estructura sugerida (puedes adaptarla pero cubre todo):

```markdown
# Postmortem: Outage del sábado 11:18 (47 minutos)

## Resumen ejecutivo

El sábado entre las 11:18 y las 12:05 tuvimos una degradación severa del servicio después de una migración de base de datos ejecutada sobre una tabla con más volumen del esperado. El incidente terminó generando locks prolongados y, posteriormente, un cache stampede cuando intentamos recuperar tráfico.

El error rate superó el 50% durante 47 minutos y afectó aproximadamente a 2.300 usuarios. También recibimos tickets de soporte y una queja formal de un cliente.

No hubo una única causa aislada. El incidente apareció por una combinación de decisiones operativas, ausencia de ciertas protecciones en el proceso de deploy y falta de mecanismos preparados para recuperar el sistema bajo esa carga.

## Timeline

- 11:18 — Se mergea el PR de migración.
- 11:22 — Se ejecuta la migración en producción.
- 11:26 — Empiezan a aparecer respuestas 5xx y fallan health checks.
- 11:27 — PagerDuty dispara alerta automática.
- 11:33 — Se identifica lock prolongado sobre la tabla afectada.
- 11:36 — Se intenta rollback parcial.
- 11:40 — Empieza degradación adicional por cache stampede durante recuperación.
- 11:49 — Se decide subir más réplicas para absorber tráfico.
- 11:57 — Se fuerza warmup de caché para estabilizar lecturas.
- 12:05 — El servicio vuelve a niveles normales.

## Impacto

- 47 minutos con error rate superior al 50%.
- Aproximadamente 2.300 usuarios afectados.
- 18 tickets recibidos por soporte.
- 1 cliente empresarial pidió explicación formal.
- 4 menciones públicas en Twitter reportando caída del servicio.
- Parte del equipo tuvo que intervenir durante el fin de semana para estabilizar producción.

## Qué falló

### Factores contribuyentes

- La migración se ejecutó directamente en producción sin review efectivo adicional.
- No existía una política clara para deploys de riesgo durante fines de semana.
- El rollback estaba pensado para revertir la migración, pero no contemplaba el comportamiento de la caché al recuperar tráfico.
- No teníamos alertas suficientemente visibles para detectar locks largos antes de que el impacto escalara.
- El sistema dependía demasiado de que la caché se recuperara de forma natural después del rollback.
- No había un mecanismo formal que bloqueara merges sin review en cambios estructurales.

## Qué funcionó bien

- La alerta automática se disparó rápido y permitió empezar mitigación pocos minutos después.
- Se logró identificar relativamente rápido el lock de tabla como principal factor técnico del incidente.
- La decisión de subir réplicas ayudó a estabilizar el servicio antes de terminar el warmup completo.
- Varias personas del equipo se conectaron durante el incidente para ayudar con mitigación y comunicación.
- Soporte logró centralizar los reportes y reducir ruido mientras infraestructura trabajaba en la recuperación.

## Cómo se detectó

El incidente se detectó inicialmente mediante alertas automáticas de PagerDuty después de la caída de health checks y aumento sostenido de respuestas 5xx.

En paralelo empezaron a entrar tickets de soporte y menciones públicas de usuarios reportando errores.

## Cómo se mitigó

- Se frenó la migración.
- Se intentó rollback para liberar el lock principal.
- Se agregaron réplicas adicionales para distribuir carga.
- Se forzó warmup de caché para evitar seguir golpeando base de datos directamente.

## Cómo se resolvió

El servicio se estabilizó una vez que las nuevas réplicas absorbieron tráfico y el warmup redujo el volumen de requests directas hacia la base de datos.

A partir de ahí el error rate volvió progresivamente a valores normales y se mantuvo estable el resto del día.

## Action items

| Acción | Dueño | Fecha objetivo | Tipo |
|--------|--------|--------|--------|
| Bloquear merges a producción sin al menos un review aprobado | Engineering | 10 mayo | Prevención |
| Definir política formal para deploys de riesgo fuera de horario laboral | Yo | 12 mayo | Prevención |
| Agregar rollback probado en staging para migraciones grandes | Plataforma | 15 mayo | Mitigación |
| Implementar feature flags para cambios estructurales | Backend | 20 mayo | Prevención |
| Mejorar monitoreo y alertas de locks largos y queries lentas | Infraestructura | 11 mayo | Detección |
| Automatizar warmup de caché post-rollback | Infraestructura | 18 mayo | Mitigación |
| Documentar procedimiento operativo para incidentes de cache stampede | Yo | 13 mayo | Mitigación |

## Lecciones aprendidas

Necesitamos asumir que en momentos de presión el equipo va a intentar destrabar entregas importantes. El proceso tiene que estar diseñado para soportar eso sin depender únicamente de criterio individual.

También vimos que nuestros mecanismos de recuperación eran mucho más frágiles de lo que pensábamos una vez que el tráfico volvía a entrar al sistema.

El objetivo de este postmortem no es decidir quién cometió un error “más grande”, sino identificar qué condiciones permitieron que una sola decisión terminara escalando hasta afectar producción de esta forma.

## Apéndice: glosario / enlaces

- Cache stampede: múltiples requests pegando simultáneamente a backend cuando la caché no tiene datos calientes.
- Warmup de caché: precarga controlada de datos para evitar picos de tráfico directo a BD.
- Health checks: verificaciones automáticas de disponibilidad del servicio.
```

### Artefacto B — Comunicación al cliente externo

Email (o post de status page) que vas a enviar al cliente afectado. Reglas:

- Honesto. No minimices.
- Específico. Dile qué pasó en términos que él pueda entender, no "tuvimos un problemita técnico".
- Reparativo. ¿Qué compensación o garantía ofreces? (no necesariamente dinero — puede ser proceso, transparencia, acceso a status page).
- Forward-looking. Qué van a cambiar para que no se repita.
- **No menciones nombres**.

```markdown
Asunto: Explicación del incidente del sábado y acciones tomadas

Hola [Nombre del cliente],

Quería escribirles personalmente para explicarles el incidente que afectó parcialmente el servicio el sábado entre las 11:18 y las 12:05.

Durante ese periodo ejecutamos un cambio de infraestructura relacionado con una migración de base de datos. La migración generó un bloqueo más largo de lo previsto y, cuando intentamos recuperar el sistema, se produjo una sobrecarga adicional en la base de datos debido al volumen de tráfico concurrente.

El resultado fue una degradación importante del servicio durante 47 minutos, incluyendo errores de acceso para parte de los usuarios.

La situación ya fue corregida y desde entonces el sistema ha operado con normalidad.

Además de resolver el incidente, esta semana estamos implementando varios cambios concretos para reducir significativamente la probabilidad de repetir un escenario similar:

- Ningún cambio estructural podrá desplegarse sin review adicional aprobado.
- Vamos a restringir deploys de alto riesgo durante fines de semana.
- Añadiremos monitoreo específico para detectar locks largos antes de que escalen.
- Automatizaremos parte de la recuperación de caché para evitar sobrecarga durante rollback.

Entendemos el impacto que esto tuvo para ustedes y no queremos minimizarlo.

Como compensación, vamos a extender 7 días adicionales en su próximo ciclo de facturación. También podemos compartirles el postmortem técnico resumido una vez quede cerrado internamente si les interesa revisarlo.

Si quieren conversar directamente sobre el incidente o las medidas tomadas, estoy disponible para coordinar una llamada esta semana.

Saludos,

Yony González  
Technical Leader
```

### Artefacto C — Plan del 1:1 con Marcos

Lunes por la mañana vas a tener un 1:1 con Marcos (30 min, agendado por ti, no por él).

Entrega:

- **Cómo preparas el 1:1**: qué te dices a ti mismo antes de entrar. Qué hipótesis tienes sobre cómo Marcos está procesando esto.

- **Cómo abres**: frase inicial concreta. (Pista: no es *"vamos a hablar del incidente"*.)

- **3 preguntas que harás** en la conversación. Que sean reales, no retóricas.

- **Cómo manejas el momento si Marcos**:
  - Se hunde más y se autoflagela.
  - Se defiende agresivamente echando culpa al proceso.
  - Te pide formalmente "una sanción" para sentirse "absuelto".

- **Cómo cierras**: con qué se va Marcos del 1:1.

- **Una decisión** que **NO** discutes con él en ese 1:1, pero que tienes que tomar tú esta semana (¿qué cambia operativamente — quién mergea sin review nunca más, política de fin de semana, etc.?).

```markdown
## Cómo preparo el 1:1

Antes de entrar me recuerdo dos cosas:

La primera es que el incidente sí fue serio. No voy a fingir que no lo fue para proteger emocionalmente a Marcos porque eso termina generando más tensión después.

La segunda es que si una sola persona puede provocar un outage de este tamaño sin barreras reales, entonces hay un problema de sistema además de una mala decisión puntual.

Mi hipótesis es que Marcos está mezclando culpa profesional con vergüenza pública. Probablemente siente que decepcionó al equipo y que ahora todos lo están mirando distinto. También creo que está intentando adelantarse a una posible sanción asumiendo toda la responsabilidad él solo.

## Cómo lo abro

> “Antes de hablar de procesos o decisiones, quiero saber cómo estás tú desde el sábado.”

## 3 preguntas que voy a hacer

1. “¿En qué momento sentiste que esto se te empezó a ir de las manos?”
2. “¿Qué información sentías que tenías cuando decidiste mergear el sábado?”
3. “¿Qué parte de todo esto te está pesando más hoy?”

## Cómo manejo distintos escenarios

### Si Marcos se hunde más y se autoflagela

No le voy a decir “no fue tu culpa” porque no sería cierto ni le ayudaría.

Voy a bajarlo de la idea de que él es “el problema completo”.

Algo como:

> “Tomaste una decisión que salió mal y tuvo impacto real. Eso es cierto. Pero también es cierto que el sistema permitió que una decisión individual llegara tan lejos sin frenos. Las dos cosas pueden ser verdad al mismo tiempo.”

Quiero sacarlo del lugar de “soy un desastre” y llevarlo a “hubo una mala decisión dentro de un sistema frágil”.

### Si se defiende agresivamente culpando solo al proceso

No le voy a discutir a la defensiva.

Le reconocería que varias fallas de proceso son reales, pero sin borrar su parte:

> “Sí, el proceso tenía agujeros claros. Los vamos a cambiar. Pero aprobarte un PR crítico tú solo y ejecutarlo en producción un sábado seguía siendo una decisión de riesgo. Necesitamos poder hablar de las dos cosas.”

La idea es evitar que la conversación se vuelva “o el culpable es Marcos o el culpable es el proceso”.

### Si pide formalmente una sanción

No voy a improvisar castigos para aliviar culpa emocional.

Le diría algo parecido a:

> “No necesito una sanción simbólica para cerrar esto. Necesito que salgamos con cambios concretos que hagan menos probable repetirlo.”

Si insiste mucho, le aclararía que las decisiones operativas o disciplinarias las define liderazgo y que no espero que él cargue con eso personalmente en ese momento.

## Cómo cierro

Quiero que salga entendiendo tres cosas:

- El incidente fue serio.
- Su lugar en el equipo no está en discusión.
- Vamos a cambiar cosas reales después de esto.

Cerraría con algo así:

> “No quiero que pases las próximas semanas intentando compensar esto trabajando más horas o castigándote solo. Lo importante ahora es que hagamos cambios reales y que tú vuelvas a operar normal dentro del equipo.”

## Una decisión que NO discuto con él en ese 1:1

Esta semana voy a cambiar la política de deploys:

- ningún cambio estructural podrá mergearse sin review externo;
- no habrá deploys de riesgo en fines de semana sin aprobación explícita;
- y ciertos cambios pasarán a requerir rollout gradual obligatorio.

Eso no lo voy a debatir con Marcos en el 1:1 porque no quiero descargar sobre él la responsabilidad de definir las consecuencias operativas del incidente.
```

---

## ⚠️ Anti-patrones que estaremos buscando

- Postmortem que parece apegado a la cultura sin señalar culpables, pero todos los action items son "Marcos hará X".
- Email al cliente que minimiza el impacto o usa lenguaje corporativo vacío.
- 1:1 con Marcos donde le "perdonas" la falla — eso no es una cultura sin señalar culpables, es paternalismo.
- 1:1 donde le pides que "haga un análisis profundo de lo que pasó" — eso es delegarle el trabajo emocional del líder.
- Aceptar la oferta de Marcos de coger días sin abordarlo emocionalmente primero.
- Sancionar a Marcos como gesto político para calmar al subgrupo que pide cabeza.
- Ignorar a la otra mitad del equipo que opina que el proceso es el problema.

---

## 🟢 Señales de un buen entregable

- El postmortem se podría compartir con **toda la empresa, Marcos incluido**, y nadie sentiría que está siendo lapidado ni que se está encubriendo.
- Los action items son cambios de **sistema** (proceso, herramienta, regla), no de **persona** ("Marcos será más cuidadoso").
- El email al cliente es algo que **tú firmarías con tu nombre** y al cliente le **subiría** la confianza, no se la bajaría.
- El 1:1 con Marcos termina con él sintiendo:
  - Que el incidente fue serio (no minimización).
  - Que no es el monstruo de la historia.
  - Que hay un cambio concreto en el sistema que él va a ver implementarse.
  - Que su lugar en el equipo no está en cuestión.
- Hay una **decisión operativa** que tomas tú esta semana **sin pedirle a Marcos su opinión**, porque es tu trabajo y no quieres descargar la responsabilidad en él.