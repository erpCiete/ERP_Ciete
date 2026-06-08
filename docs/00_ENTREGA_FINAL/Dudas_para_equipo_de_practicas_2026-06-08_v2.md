# Dudas para el equipo de prácticas — ERP Ciete

**Fecha:** 2026-06-08
**Contexto:** Ábaco retoma el desarrollo del ERP de Ciete (rama `Desplegadav2`, entrega **v2.2.0** del 2026-06-04). Estas preguntas surgen tras leer `docs/00_ENTREGA_FINAL/`, mapear el código y montar el entorno en local. Están ordenadas por bloques y marcadas con prioridad:

- 🔴 **Bloqueante / alto impacto** para retomar el desarrollo con garantías.
- 🟡 **Importante**, conviene aclararlo pronto.
- ⚪ **Aclaración / contexto**, sin prisa.

> Cada pregunta incluye lo que hemos observado, para que sea fácil reconocer el caso.

---

## A. Entorno, base de datos y reproducibilidad

**A1. 🔴 Motor de base de datos: ¿MariaDB o MySQL?**
El README dice "MySQL 8+ o MariaDB compatible", pero **el proyecto no arranca en MySQL 8 puro**: el dump `database/schema/abaco_ciete_v220_2026-06-04.sql` es un volcado de **MariaDB 10.4.32**, y las migraciones usan claves foráneas compuestas que MySQL 8 rechaza con `error 6125 Missing unique key` (ejemplo: `fk_tipos_trabajo_tipo_doc_contexto` sobre `tipos_documento`). Hemos tenido que montar **MariaDB** para que funcione.

- ¿Confirmáis que el motor objetivo es **MariaDB** (¿vía XAMPP?)? ¿Qué **versión exacta** usáis?
- ¿Hay intención de soportar MySQL 8 (cliente final), o nos estandarizamos todos en MariaDB?

**Respuesta:**
Ahora mismo el motor real del proyecto es MariaDB, en concreto la línea 10.4.x, que es con la que llevo trabajando y con la que el dump funciona bien.

El problema con MySQL 8 no es solo de configuración: viene de cómo están definidas algunas claves foráneas compuestas en las migraciones, sobre todo en tablas maestras como `tipos_trabajo` y `tipos_documento`. MariaDB acepta esa estructura, pero MySQL 8 es más estricto y exige índices únicos concretos para esas FK compuestas.

Si en algún momento queremos dar soporte real a MySQL 8, no bastaría con cambiar el `.env`: habría que tocar migraciones, índices, claves foráneas y regenerar el dump validándolo en MySQL 8. Mi recomendación es que nos quedemos con MariaDB como estándar salvo que Ciete o el servidor final nos obliguen a usar MySQL 8.

**A2. 🟡 ¿Existe un dump compatible o seed con datos demo "ricos"?**
El dump no importa en MySQL 8; en MariaDB sí. Como alternativa, `php artisan migrate --seed` crea esquema + catálogos + usuarios, pero **sin** los trabajos de ejemplo (`DEMO-610001`, etc.).

- ¿El flujo recomendado para un entorno nuevo es **restaurar el dump** (MariaDB) o **migrate + seeders**? ¿Hay seeders que carguen datos demo equivalentes (vimos `database/seeders/private/` y `PrivateSeederGeneratorService`)?

**Respuesta:**
Para levantar un entorno completo con datos demo ricos, lo más fiable es restaurar el dump sobre MariaDB.

`migrate --seed` te deja estructura, catálogos y usuarios base, pero no te monta toda la demo funcional con trabajos, pedidos, facturas y los casos `DEMO-*`. Para tener algo parecido a lo de la entrega, lo suyo es partir del dump documentado o del SQL manual de demo si quieres una muestra más reducida.

Sobre `database/seeders/private/` y `PrivateSeederGeneratorService`, no los trataría como seeders de demo normales: están pensados para generar datos a partir de Excel reales o sensibles del proyecto, así que yo los mantendría como flujo local/controlado y no como mecanismo público de demo. Resumiendo:

- Entorno demo completo: dump MariaDB.
- Entorno mínimo: migrate + seeders públicos.
- Datos reales/sensibles: seeders privados o importadores específicos, siempre con cuidado.

**A3. ⚪ Variables de entorno y servicios externos.**
El `.env.example` trae SMTP de Gmail de ejemplo y `APP_ALLOW_DEMO_USERS=false`.

- ¿Qué valores reales de SMTP/correo usáis en local y en el servidor del cliente?
- ¿Para qué sirve exactamente `APP_ALLOW_DEMO_USERS` y cómo debe quedar en producción?

**Respuesta:**
Los valores reales de SMTP no van en el repo, y eso está bien así. En local podemos seguir trabajando con `log`, `array`, Mailtrap o una cuenta de pruebas; para producción habría que pedirle a Ciete o a quien gestione el servidor los datos SMTP reales.

Sobre `APP_ALLOW_DEMO_USERS`: está declarado en la configuración, pero no tiene ningún uso funcional real en el código actual. Es un flag que quedó preparado pero que ahora mismo no activa ni desactiva nada. En producción lo dejaría en `false`, y de paso decidiría si lo terminamos de implementar o lo quitamos para no liar a quien venga después.

Otra cosa que aprovecho para comentar: el `.env.example` sigue trayendo configuración de `sqlite`, cuando el proyecto real trabaja con MySQL/MariaDB. Lo ajustaría para que cualquiera que clone el repo no arranque con una configuración que no es la real.

---

## B. Flujo de trabajo del repositorio

**B1. 🔴 Estrategia de ramas.**
La entrega vive en `Desplegadav2`, pero la rama por defecto del remoto es `develop` (existen además `main` y `UltimaVersion`).

- ¿Sobre qué rama desarrollamos lo nuevo y cómo se promociona a la versión desplegada? ¿Cuál es la rama "fuente de verdad" hoy?
- ¿Convenciones de commits / PR / revisión que sigáis?

**Respuesta:**
Aquí lo más claro es separar el flujo histórico del equipo y la forma en la que se ha trabajado en esta última fase.

Antes cada persona trabajaba en su rama de tarea semanal. Luego esas ramas se integraban en ramas intermedias de `back-develop` o `front-develop`, después pasaban a `develop` y finalmente a `main` si todo estaba correcto.

En esta última etapa, desde que asumí la continuidad del proyecto, el trabajo real se ha hecho principalmente en local y los despliegues se han ido subiendo a la rama correspondiente cuando tocaba actualizar el servidor temporal de pruebas, entorno "130".

Ahora mismo, la rama que refleja el estado más actual de la entrega es `Desplegadav2`: https://github.com/erpCiete/ERP_Ciete/tree/Desplegadav2. Esa es la referencia que miraría para retomar el proyecto hoy, no `develop` ni `main`.

Si el proyecto vuelve a moverse en equipo, creo que habría que acordar de nuevo un flujo sencillo y claro: una rama estable de entrega, una rama de desarrollo activa y revisión antes de desplegar.

**B2. ⚪ Entorno de despliegue del cliente.**

- ¿Dónde corre actualmente el ERP (servidor, hosting, on-premise en Ciete)? ¿Hay pipeline de despliegue o es manual (`docs/00_ENTREGA_FINAL/14_DESPLIEGUE.md`)?

**Respuesta:**
El despliegue que tenemos documentado es manual: subir el código, instalar dependencias, generar el build, ejecutar los comandos de Laravel, ajustar permisos y comprobar que todo carga bien. No hay ningún pipeline tipo GitHub Actions montado, así que de momento lo trataría simplemente como despliegue manual documentado.

Lo que sí falta cerrar es el entorno real donde corre en casa del cliente: si es servidor propio, hosting, VPS, on-premise, etc. Eso afecta a cosas como:

- la versión de PHP,
- el motor de base de datos,
- los permisos,
- el acceso SSH/SFTP,
- el dominio y el SSL,
- los backups,
- y si podemos o no ejecutar Composer, Node o `artisan` directamente ahí.

Hasta que no tengamos esa información, yo no daría el despliegue de producción por cerrado del todo.

---

## C. Estado funcional, tests y deuda técnica

**C1. 🟡 Los 3 tests que fallan.**
La entrega documenta 270 OK / 3 fallidos: `AdminAccessTest` (admin a `/dashboard` devuelve 403 en vez de 200), `ContextCreationGuardTest` (creación en OTROS exige tarifario), `ImportacionesAccessTest` (falta `resources/js/Pages/Importaciones/Form.jsx` en el manifest de Vite).

- ¿Son fallos reales pendientes de corregir, o "esperados" por estado/entorno? ¿Los abordamos nosotros?

**Respuesta:**
He corregido dos de los tres. Te cuento cómo quedó cada uno:

1. **`AdminAccessTest`** — esto era justo al revés de lo que parecía a primera vista. El test decía que `/dashboard` tenía que devolver `200` para admin, pero hay otro test (`RoleModuleAccessTest`) que ya viene comprobando, para admin, director, ejecución y contable por igual, que `/dashboard` debe devolver `403`. Ese segundo test ya pasaba junto con el resto de la suite, así que el bloqueo de `/dashboard` no es un descuido de última hora: es el comportamiento que el resto de la suite da por bueno para todos los roles por igual. Es verdad que detrás hay un `DashboardController` y una `Dashboard.jsx` completos y bien hechos, pero esa página no está enlazada desde ningún sitio del menú — todo apunta a que se dejó construida pero apagada a propósito, no a un olvido. Lo que hice fue corregir la expectativa equivocada en `AdminAccessTest` para que coincida con el resto de la suite, en lugar de tocar la ruta y romper otros cuatro tests que ya validaban ese bloqueo. Ya pasa.

2. **`ContextCreationGuardTest`** — sigue fallando, y para mí sigue siendo una decisión de negocio más que un bug de código. Aquí entra justo el contexto que te comentaba de OTROS CLIENTES: hoy por hoy ese contexto no va a tener clientes grandes tipo MOEVE o REPSOL, está ahí prácticamente sin uso. Más adelante sí podría servir para dar de alta empresas pequeñas que necesiten el mismo funcionamiento, y entonces sí tocaría decidir si les exigimos tarifario igual que a los grandes o si seguimos una regla distinta para ellos. Como de momento no hay ningún caso real esperando, no lo considero urgente todavía — pero conviene dejar anotado que el test seguirá en rojo hasta que tomemos esa decisión.

3. **`ImportacionesAccessTest`** — este sí era un fallo real, y al revisarlo con detalle era más serio de lo que parecía. No es solo que falte un componente para que pase el test: en producción, si alguien sube un archivo, la aplicación redirige a una página `Importaciones/Preview` que nunca llegó a construirse, y la ruta `/importaciones/subir` apuntaba a un `Importaciones/Form` que tampoco existe. Mientras tanto, `Importaciones/Index.jsx` ya tiene montados, en la misma pantalla, un formulario de subida y una tabla de previsualización — o sea, el diseño real es de página única, y esas dos rutas viejas se quedaron a medio migrar de un diseño anterior multipágina. Lo que hice fue quitar esas dos rutas/métodos huérfanos y hacer que, al subir un archivo, el sistema vuelva a `Importaciones/Index` con los datos de la previsualización ya cargados — que es justo lo que esa pantalla estaba esperando recibir y nunca le llegaba. Ya pasa, y de paso se cierra un bug que también afectaba al uso real en producción, no solo al test.

Resumiendo: dos de los tres ya están arreglados y la suite ha vuelto a verde salvo el de OTROS CLIENTES, que depende de una decisión de negocio que de momento no parece bloqueante porque ese contexto todavía no tiene un cliente real detrás.

**C2. 🟡 Deuda técnica conocida — ¿prioridad?**
Identificadas: `TrabajoController::patchField` (~300 líneas, múltiples responsabilidades), rutas de **pedidos y facturas como closures** en `routes/web/operativa.php` (en vez de controladores dedicados), `ExcelParserService` sin soporte CSV.

- ¿Hay interés en refactorizar esto a corto plazo o se prioriza funcionalidad nueva?

**Respuesta:**
Sí, hay deuda técnica de verdad y es más grande de lo que parecía a primera vista.

`patchField` de `TrabajoController` no es un método pequeño: concentra demasiada lógica y demasiadas responsabilidades. Y las rutas de pedidos y facturas en `operativa.php` tienen closures bastante grandes con mucha lógica metida ahí dentro, lo que complica mantenerlas, testearlas y hacerlas evolucionar.

Mi propuesta sería no meternos en un refactor grande hasta que no tengamos claro el feedback de Ciete sobre la v2.2.0 — podríamos romper cosas que ni sabemos si el cliente va a aceptar tal cual están. Lo que sí haría desde ya:

- documentar bien esta deuda,
- reforzar tests antes de tocar nada,
- ir sacando piezas pequeñas y seguras primero,
- no mezclar refactor con funcionalidad nueva grande.

Por delante de todo esto, pondría los bloqueantes funcionales y de decisión: importaciones, dashboard, lo de OTROS sin tarifario, ARIBA, legalizaciones y el feedback de la v2.2.0.

**C3. ⚪ Importación CSV y correo MOEVE con adjuntos.**
La UI de importación anuncia CSV pero el parser solo soporta `.xlsx`. El modal "Preparar correo Moeve" genera textos y descargas, pero **no envía el correo con adjuntos** automáticamente.

- ¿Son requisitos pendientes priorizados por el cliente o aceptables como están de momento?

**Respuesta:**
Estos dos los trataría como pendientes, no como cosas terminadas.

Con la importación de CSV no es solo que falte un `if`: hay varias piezas distintas conviviendo. Una parte usa PhpSpreadsheet, que en teoría podría leer varios formatos, pero el flujo real de MOEVE/REPSOL usa un lector de `.xlsx` específico que no sirve para CSV. Para soportarlo bien habría que decidir qué motor usamos, validar el formato, añadir tests y documentarlo.

Con el correo de MOEVE, ahora mismo el sistema prepara el texto y los adjuntos para enviarlo a mano, pero no manda nada de forma automática. No hay ninguna integración SMTP montada para eso. Si Ciete lo pide, sería una funcionalidad nueva: configurar correo real, adjuntos, pruebas y manejo de errores.

De momento lo dejaría tal cual está si la entrega era para uso manual/demo, pero no lo presentaría como algo automatizado.

---

## D. Modelo de negocio y decisiones abiertas (reunión cliente 19/05)

> En la transcripción de la reunión del 19/05 (César y Amaya) quedaron varios puntos a medio cerrar. Queremos saber cómo terminaron implementándose.

**D1. 🟡 Tarifario ↔ sociedad facturadora.**
El cliente pidió que el tarifario/contrato no dependa solo del cliente, sino que pueda asociarse a **una o varias sociedades facturadoras**. En el código vemos `contratos` con `tarifarios` colgando del contrato y una tabla pivote `contrato_empresas_facturadoras`.

- ¿Cómo quedó finalmente el modelo? ¿El tarifario se elige por contrato, por sociedad, o ambos? ¿Hay un tarifario "por defecto" al crear el trabajo cuando aún no se conoce la sociedad?

**Respuesta:**
Así quedó montado a nivel técnico:

- El tarifario cuelga del contrato.
- Un contrato puede estar asociado a varias sociedades facturadoras, a través de la tabla pivote `contrato_empresas_facturadoras`.
- La sociedad facturadora se usa de cara a facturación/exportación, pero el tarifario se sigue resolviendo principalmente desde el contrato.

No hay una lógica de "tarifario por defecto" para cuando todavía no se conoce la sociedad — de hecho, al crear un pedido se exige que el trabajo tenga un tarifario válido, que es justo lo que está chocando con el test de `OTROS CLIENTES`.

Y aquí enlazo con lo que comentaba en el punto C1: hoy OTROS CLIENTES no tiene detrás clientes grandes tipo MOEVE o REPSOL, está prácticamente sin uso. Lo que sí veo posible es que más adelante sirva para dar de alta empresas pequeñas con esta misma mecánica, y en ese momento sí habrá que decidir si les aplicamos la misma exigencia de tarifario que a los grandes o si para ellos la regla es distinta. Mientras no haya un caso real esperando, no priorizaría esa decisión hasta que exista un caso real o Ciete lo confirme.

También está implementado que el tarifario se bloquea en cuanto el trabajo ya tiene pedidos, para evitar inconsistencias posteriores.

Para mí el modelo técnico está bastante avanzado, pero todavía falta confirmar con Ciete si esto encaja exactamente con lo que pedían en su día.

**D2. 🟡 Trabajo con varios pedidos.**
Se acordó estándar **1 trabajo – 1 pedido**, con la **excepción** de varios pedidos por trabajo (p. ej. dirección facultativa + coordinación). El modelo `Trabajo hasMany Pedido` lo soporta.

- ¿Está la UI preparada para esa excepción (botón "añadir pedido extra") y validada con el cliente?

**Respuesta:**
Sí, está preparado a nivel técnico. El modelo permite varios pedidos por trabajo y la interfaz permite crear pedidos nuevos desde un trabajo que ya existe.

Eso cubre el caso que se comentó en la reunión: lo normal es un trabajo con un pedido, pero a veces hace falta más de uno (dirección facultativa, coordinación, etc.).

Lo que no puedo decir es que esto esté validado por Ciete sobre la v2.2.0, porque no hay ningún feedback registrado después del 4 de junio. Así que, para que quede claro:

- Implementado técnicamente: sí.
- Coherente con lo que se habló en la reunión del 19/05: sí.
- Validado por el cliente sobre la v2.2.0: no consta.

**D3. ⚪ Panel de cierre.**
En la reunión, el cliente **cuestionó la utilidad** del panel de cierre frente a trabajar con filtros en la pantalla de Trabajos.

- ¿Cuál fue la decisión final: se mantiene como está, se rebaja a herramienta de diagnóstico, o se retira?

**Respuesta:**
No hay una decisión final de cliente registrada sobre este punto, pero el motivo de cómo quedó sí se entiende bastante bien si juntamos las piezas.

En la reunión, Ciete dejó claro que su forma natural de trabajar es la tabla tipo Excel de Trabajos, con sus filtros y vistas operativas — ese es su sitio del día a día. Eso explica por qué tanto el dashboard general como el panel de cierre acabaron perdiendo protagonismo: no es que estén rotos ni que sobren a nivel técnico, es que no encajaron como pantalla principal frente a lo que Ciete realmente usa.

Conviene separar las dos piezas, porque no están en la misma situación:

- El **dashboard general** (`DashboardController` + `Dashboard.jsx`) está construido por completo — controlador, vista y traducciones — pero la ruta `/dashboard` está bloqueada con `403` para todos los roles, incluido admin. Ya he revisado el código a fondo y confirmo que no es un descuido: el bloqueo es coherente y consistente en toda la aplicación. No lo borraría; lo dejaría tal cual está, porque tiene una base técnica sólida y potencial real de cara al futuro.
- El **panel de cierre** sí está montado, conectado y operativo (`/cierre`), pero su utilidad es justo lo que el cliente cuestionó en la reunión anterior frente a trabajar con filtros en Trabajos.

No tocaría ninguno de los dos sin hablarlo antes con Ciete. Mi propuesta sería plantearles si les interesa que cualquiera de estas pantallas tenga un papel complementario — no como sustituto de la tabla de Trabajos, sino como apoyo —, por ejemplo:

- panel de diagnóstico interno,
- resumen ejecutivo para dirección,
- vista de control de cierre,
- pantalla de alertas o incidencias (trabajos bloqueados, pendientes de facturar, legalizaciones pendientes, inconsistencias).

Para mí el estado correcto a día de hoy es **"implementado técnicamente y oculto a propósito"**, no "roto" ni "abandonado". La decisión que falta no es "mantener o borrar sin más", sino decidir si le damos un uso concreto que complemente —sin competir con— la tabla de Trabajos.

Pregunta para Ciete: **como el trabajo diario lo queréis centralizar en la tabla de Trabajos, ¿os interesa que el dashboard y/o el panel de cierre queden como vistas de apoyo (diagnóstico, resumen ejecutivo, alertas o control de cierre), o preferís que sigan fuera de la navegación principal?**

**D4. 🟡 Cobros / importes no asignables.**
Amaya describió el caso real de cobros que no casan con ningún trabajo/pedido ("170 € que no puedo asignar"). La doc dice que hoy Contabilidad lo gestiona fuera del ERP.

- ¿Se espera una pantalla de "pendientes de asignar" en el ERP, o se deja fuera?

**Respuesta:**
Ahora mismo no hay ninguna pantalla operativa para gestionar esos importes que no casan con nada.

Existe parte del modelo de datos relacionado con cobros, pero no hay nada montado para que Contabilidad pueda gestionar esos importes pendientes de asignar desde el ERP.

No me lanzaría a montarlo sin confirmarlo antes, porque puede acabar siendo todo un módulo contable en sí mismo. Lo plantearía como pregunta directa:

- ¿Es algo que pasa a menudo?
- ¿Quieren gestionarlo dentro del ERP o no hace falta?
- ¿Vale con dejarlo fuera, como excepción que sigue gestionando Contabilidad a mano?
- ¿Tendría que afectar a facturas, cierres o estados?

Si pasa pocas veces, se puede dejar fuera tranquilamente. Si pasa a menudo, habría que pensarlo bien antes de montarlo.

**D5. ⚪ Estados e importes.**
Convención observada: importe de pedido **0 = cancelado**, **vacío = pendiente de tarificar**; estados del trabajo derivados automáticamente; cancelados visibles pero al final.

- ¿Es correcta esta interpretación y está cerrada con el cliente?

**Respuesta:**
Los estados derivados sí están hechos y la lógica para recalcular el estado del trabajo según pedidos y facturas está bastante clara.

Lo que no daría por cerrado sin preguntar es la convención exacta:

- importe 0 = cancelado,
- importe vacío = pendiente de tarificar.

No la trataría como una regla cerrada mientras Ciete no la confirme, porque puede tener implicaciones importantes. Un importe a 0 podría ser una cancelación, pero también un error, una cortesía, una regularización o un caso especial que todavía no contemplamos.

Yo lo formalizaría con Ciete y, una vez confirmado, lo dejaría bien documentado para que no haya interpretaciones distintas entre nosotros.

---

## E. Datos maestros pendientes

**E1. 🟡 Tarifarios Repsol.**
En la reunión se apuntó que podrían **faltar Excel de tarifarios de Repsol** (diseño, edificación, obras) por enviar/cargar.

- ¿Se recibieron y cargaron todos? ¿Falta alguno?

**Respuesta:**
Aquí ya tenemos más información de la que recoge solo la reunión, así que actualizo la respuesta con eso.

César envió un correo el 20/05/2026 a las 17:03, con asunto "ERP Ciete - Tarifa Repsol", donde dice literalmente: "La tarifa de Repsol. Es única." En ese mismo correo cuenta que ha estado revisando el programa con Repsol y con Contabilidad, que hay cuestiones que habrá que cambiar pero que la idea general encaja bastante con lo visto, que serán cambios menores y que prefiere que sigamos puliendo la nueva versión desde ahí. Ese correo lo reenvió Andrés Casanueva el 21/05/2026, con el adjunto de tarifa Repsol/ingeniería incluido. Así que no es que no llegara nada de Repsol: sí llegó información concreta y con archivo.

Con esto, el punto cambia de fondo. La duda ya no es "si se recibió algo de Repsol", sino si lo que se recibió es lo definitivo. Y la indicación de César es clara: para Repsol no hace falta manejar varios tarifarios separados por diseño, edificación u obras, sino una tarifa única con muchas líneas/conceptos dentro. Eso simplifica el modelo respecto a lo que se planteaba en la reunión del 19/05.

Con esa premisa, hicimos además una revisión interna documentada el 01/06 (`REVISION_TARIFA_UNICA_REPSOL_CESAR_2026-06-01.md`) para comprobar si el sistema actual encaja con lo que dijo César. La conclusión de esa revisión es que sí: el sistema admite varios tarifarios por contrato a nivel general, pero los datos, el importador (`RepsolExcelImporter`) y la selección automática de tarifario en Trabajos ya funcionan hoy, en la práctica, como tarifa única para Repsol. Lo que esa revisión también dejó anotado es que el sistema no está "blindado" frente a un error humano: si alguien llegara a crear un segundo tarifario Repsol activo por equivocación, hoy no hay ninguna validación que lo impida o avise. Es un riesgo menor y no urgente —no ha pasado y no hay indicios de que vaya a pasar—, pero conviene valorar más adelante si merece la pena añadir esa validación específica para blindarlo técnicamente.

Aun así, no lo cerraría al 100% sin una confirmación más. Quedan dos cosas por verificar con César/Amaya:

- si el Excel adjunto que reenvió Andrés es la versión definitiva y completa, o si todavía hay que contrastarlo con algún otro Excel de los que se mencionaron en la reunión anterior (diseño, edificación, obras),
- y si falta algún dato maestro adicional que ese Excel no recoja.

No lo presentaría como "Repsol cerrado al 100%", porque no consta una validación posterior de Ciete sobre la v2.2.0 que lo confirme — lo que sí puedo decir con seguridad es que la indicación de tarifa única existe, está documentada y el sistema ya es compatible con ella.

Pregunta reformulada para César/Amaya, que creo que encaja mejor que la original: **¿damos por definitiva la tarifa única de Repsol que nos enviasteis por correo, o falta todavía algún Excel o dato maestro adicional que no estuviera en ese adjunto?**

**E2. 🔴 Valores ARIBA reales (contrato 772 MOEVE).**
Los valores ARIBA configurados en demo (OPEX-MOEVE-2026, AC-MOEVE-2026, etc.) son **placeholders**. La exportación MOEVE/ARIBA depende de ellos.

- ¿César ha entregado ya los valores reales? Si no, ¿quién y cuándo los proporciona?

**Respuesta:**
Este lo dejaría como bloqueante para producción real.

La exportación MOEVE/ARIBA puede estar lista a nivel técnico, pero mientras los valores sigan siendo placeholders no podemos decir que esté lista para uso real.

Necesitamos que César, Amaya o quien corresponda nos confirme los valores reales del contrato 772 MOEVE. Hasta entonces:

- podemos probar la exportación a nivel técnico,
- podemos usarla en demo,
- pero yo no la presentaría como una exportación real ya válida.

Pregunta directa: **¿tenemos ya los valores ARIBA reales del contrato 772 MOEVE, o siguen pendientes de que nos los pasen?**

---

## F. Producto y próximos pasos

**F1. 🔴 Feedback del cliente tras la v2.2.0.**

- ¿Qué impresiones/correcciones devolvió Ciete tras la entrega del 4 de junio? ¿Hay una lista priorizada de lo siguiente a desarrollar?

**Respuesta:**
No tengo constancia de que Ciete haya revisado o validado la v2.2.0.

Para mí este es el punto más importante antes de meternos a desarrollar nada nuevo. La v2.2.0 puede estar implementada técnicamente, con sus pruebas (ahora mismo en 277 OK / 1 pendiente de una decisión de negocio, no de un bug), su documentación y su demo — pero nada de eso equivale a que el cliente la haya visto y dado por buena. Implementado, probado por nosotros y documentado es un nivel; validado por el cliente es otro distinto, y en ese segundo nivel seguimos sin nada que enseñar.

El último feedback de cliente que tengo como cierto y registrado sigue siendo el de la reunión anterior (la del 19/05, de la fase v2.1) — no hay nada posterior y formal sobre la v2.2.0 entregada el 4 de junio. Así que antes de priorizar nada nuevo necesitamos saber:

- si Ciete ha visto ya la v2.2.0,
- qué les ha parecido,
- qué fallos han encontrado,
- qué les ha gustado o aceptado,
- qué quieren cambiar,
- qué es lo prioritario ahora para ellos.

Sin eso, el riesgo es que sigamos avanzando sobre cosas que el cliente igual no valida tal y como están.

**F2. ⚪ Roadmap.**

- ¿Qué módulos o mejoras están previstos a continuación (presupuestos, legalizaciones, importación, exportación Repsol, etc.)?

**Respuesta:**
Antes de pensar en un roadmap nuevo, seguiría haciendo limpieza de lo que ya tenemos — y aquí el panorama ha cambiado un poco desde la última vuelta de este documento, porque ya he revisado y corregido parte de lo que estaba pendiente:

1. **Ya resuelto parcialmente — reducir los 3 fallos iniciales de tests a 1 pendiente.** Revisé el código y corregí dos casos: `AdminAccessTest`, que tenía la expectativa al revés porque esperaba `200` en `/dashboard` cuando el comportamiento validado por la suite es `403` para todos los roles; e `ImportacionesAccessTest`, que apuntaba a un fallo real de rutas y vistas (`Form`/`Preview`) que nunca llegaron a construirse y que también habría afectado al uso real. La suite queda ahora en **277 OK / 1 pendiente**, siendo el pendiente `ContextCreationGuardTest`, que depende de una decisión de negocio sobre OTROS CLIENTES y tarifario.
2. **Ya resuelto — Aclarar el tema de importaciones y las rutas viejas.** Quité las rutas y métodos huérfanos (`importaciones.create` / `importaciones.preview`, que apuntaban a `Importaciones/Form.jsx` y `Importaciones/Preview.jsx`, inexistentes) y dejé el flujo real funcionando de principio a fin en una sola pantalla — `Importaciones/Index.jsx`: subida, previsualización y confirmación, sin saltos a páginas que no existen.
3. Confirmar MariaDB como motor oficial.
4. Arreglar el `.env.example` y la documentación que no cuadra.
5. Confirmar los valores ARIBA reales de MOEVE (sigue siendo el bloqueante más claro para producción real).
6. Conseguir feedback de Ciete sobre la v2.2.0 — para mí, el bloqueante más importante de todos ahora mismo (ver F1).
7. Cerrar si `OTROS CLIENTES` necesita tarifario obligatorio o no — esta es la decisión de negocio que hoy mantiene `ContextCreationGuardTest` en rojo; no la trataría como bug técnico, sino como algo que decidir con calma porque ese contexto todavía no tiene un cliente real detrás.
8. Decidir qué papel le damos al dashboard y al panel de cierre (ver D3) — no es "arreglar algo roto", es decidir si les damos un uso de apoyo (diagnóstico, resumen ejecutivo, alertas, control de cierre) o los dejamos como están.
9. Decidir si legalizaciones necesita su propio CRUD o solo tiene que influir en el cierre.
10. Confirmar si la tarifa única de Repsol que nos envió César es definitiva y completa, o si falta algo más, y valorar si conviene blindar técnicamente que no se puedan crear dos tarifarios Repsol activos por error (ver E1).

Una vez resuelto esto, ya miraría módulos nuevos. Como propuesta de roadmap con el estado actual:

- **Primero:** cerrar las decisiones de negocio que quedan abiertas (OTROS CLIENTES, dashboard/panel de cierre, Repsol) y conseguir el feedback de Ciete sobre la v2.2.0 — sin esto, cualquier cosa nueva que montemos corre el riesgo de no encajar con lo que el cliente realmente quiere.
- **Después:** valores ARIBA reales, legalizaciones, cobros sin asignar y exportación a Repsol si Ciete la pide.
- **Más adelante:** refactor de `TrabajoController`, las rutas de pedidos/facturas como closures, soporte CSV en importaciones y automatizar el correo de MOEVE.

---

### Resumen de las críticas (🔴) para no perderlas en la reunión

- **A1** Confirmar MariaDB como motor oficial (y versión).
- **B1** Rama base para desarrollar y flujo de promoción.
- **E2** Valores ARIBA reales del contrato 772.
- **F1** Feedback del cliente y siguiente prioridad.

**Resumen para la reunión:**

- **A1 — MariaDB:** para mí lo lógico es oficializar MariaDB como motor del proyecto, salvo que Ciete nos obligue a usar MySQL 8. Si hay que dar soporte a MySQL 8, habría que revisar y modificar migraciones, regenerar el dump y validarlo a fondo.

- **B1 — Ramas:** el esquema de antes (ramas semanales → back/front-develop → develop → main) ya no es como estamos trabajando ahora. Durante esta última fase he trabajado principalmente en local y he subido los cambios a la rama correspondiente cuando tocaba actualizar el entorno temporal de pruebas 130. La rama actual y la que hay que mirar es `Desplegadav2` (https://github.com/erpCiete/ERP_Ciete/tree/Desplegadav2). Si vamos a retomar esto en equipo, tenemos que sentarnos y fijar entre todos una rama de verdad y un flujo de trabajo sencillo.

- **E2 — ARIBA:** sigue siendo bloqueante. La exportación está lista a nivel técnico, pero necesita los valores reales del contrato 772 MOEVE. Mientras sigan siendo placeholders, no lo podemos dar por producción real.

- **F1 — Feedback v2.2.0:** este es el punto principal de todos. No tenemos constancia de que Ciete haya validado la v2.2.0. Antes de seguir desarrollando, necesitamos saber si la han visto y qué prioridades o correcciones nos han marcado.
