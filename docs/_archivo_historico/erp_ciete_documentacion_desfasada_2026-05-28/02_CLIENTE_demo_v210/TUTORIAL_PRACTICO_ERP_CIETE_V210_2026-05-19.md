# Tutorial práctico de uso

## ERP CIETE v2.1.0

**Guía operativa para revisión funcional y demostración del flujo diario de trabajo.**

---

> Documento de apoyo para la revisión funcional del sistema.  
> Entorno de demostración: **http://130.110.232.84/**  
> Fecha: 19 de mayo de 2026  
> Versión del sistema: ERP CIETE v2.1.0

---

## Índice

1. [Portada y datos del sistema](#1-portada-y-datos-del-sistema)
2. [Objetivo de la guía](#2-objetivo-de-la-guía)
3. [Qué se revisó respecto a la reunión anterior](#3-qué-se-revisó-respecto-a-la-reunión-anterior)
4. [Conceptos básicos del ERP](#4-conceptos-básicos-del-erp)
5. [Acceso al sistema](#5-acceso-al-sistema)
6. [Roles y uso diario](#6-roles-y-uso-diario)
7. [Flujo diario recomendado](#7-flujo-diario-recomendado)
8. [Uso de Ciete Excel](#8-uso-de-ciete-excel)
9. [Uso de Ciete Moderno](#9-uso-de-ciete-moderno)
10. [Trabajos](#10-trabajos)
11. [Pedidos e ítems](#11-pedidos-e-ítems)
12. [Facturas](#12-facturas)
13. [Maestros](#13-maestros)
14. [Panel de cierre / Dirección](#14-panel-de-cierre--dirección)
15. [Administración técnica](#15-administración-técnica)
16. [Mensajes de inicio](#16-mensajes-de-inicio)
17. [Estado del sistema](#17-estado-del-sistema)
18. [Ejemplo de recorrido de demostración](#18-ejemplo-de-recorrido-de-demostración)
19. [Buenas prácticas de uso](#19-buenas-prácticas-de-uso)
20. [Preguntas frecuentes](#20-preguntas-frecuentes)
21. [Cierre del documento](#21-cierre-del-documento)
22. [Capturas recomendadas para añadir después](#22-capturas-recomendadas-para-añadir-después)

---

## 1. Portada y datos del sistema

| Dato                    | Valor                               |
| ----------------------- | ----------------------------------- |
| Sistema                 | ERP CIETE v2.1.0                    |
| Entorno de demostración | http://130.110.232.84/              |
| Fecha del documento     | 19 de mayo de 2026                  |
| Uso                     | Guía práctica de revisión funcional |

> Esta guía es un documento de apoyo para revisar el funcionamiento del ERP CIETE v2.1.0 de forma práctica. No contiene información reservada ni datos de acceso privados.

---

## 2. Objetivo de la guía

Esta guía sirve para recorrer el ERP CIETE v2.1.0 desde el punto de vista del trabajo diario. Cubre los siguientes bloques:

- Entrada al sistema e inicio de sesión.
- Selección de contexto de trabajo (MOEVE, REPSOL, OTROS CLIENTES).
- Consulta y gestión de trabajos.
- Pedidos asociados e ítems de facturación.
- Facturas y control de sociedad/CIF.
- Panel de cierre y revisión por Dirección.
- Administración técnica del sistema.
- Mensajes internos y avisos del sistema.

La guía está pensada tanto para un recorrido guiado durante una presentación como para que el equipo pueda consultarla de forma autónoma.

---

## 3. Qué se revisó respecto a la reunión anterior

En la reunión de trabajo con CIETE se identificaron las prioridades que guían el desarrollo actual del ERP. A continuación se resume cómo cada punto se traduce en el sistema:

### El trabajo como eje principal

El ERP gira en torno al **trabajo**. Cualquier operación económica —pedido, ítem, factura— está ligada a un trabajo previo. El sistema no permite facturar lo que no está registrado como trabajo.

### Separación por cliente y contexto

Cada trabajo pertenece a un contexto: **MOEVE**, **REPSOL** u **OTROS CLIENTES**. Los datos de cada contexto están completamente separados. No es posible mezclar accidentalmente trabajos de MOEVE con los de REPSOL.

### Pedidos asociados a trabajos

Un pedido puede llegar antes, durante o después de ejecutar el trabajo. El ERP lo gestiona en los tres casos, siempre vinculando el pedido al trabajo correspondiente.

### Facturas basadas en ítems

La facturación se basa en **ítems** (líneas tarifarias). La factura no es un apunte libre: agrupa ítems del pedido, completos o parciales, respetando el contrato y la tarifa vigente.

### Control de sociedad/CIF

Antes de emitir una factura, el sistema valida que la sociedad facturadora sea la permitida para ese contrato y contexto. Si no está configurada, el sistema avisa de forma clara.

### Vista tipo Excel y vista moderna

El sistema ofrece dos formas de trabajar con los mismos datos:

- **Ciete Excel**: navegación rápida, tablas densas, paginación, ideal para trabajo diario con muchos registros.
- **Ciete Moderno**: vista más visual, tarjetas, navegación detallada, útil para revisión y análisis.

### Roles diferenciados

Cada perfil de usuario tiene acceso solo a lo que necesita. Un usuario de ejecución no accede a facturas ni al panel de cierre. Un usuario de contabilidad no gestiona trabajos directamente.

### Trazabilidad y auditoría

El sistema registra quién hizo qué y cuándo en los registros operativos. Esta trazabilidad está disponible para los perfiles autorizados.

### Mensajes internos y estado del sistema

El panel de inicio incluye mensajes internos, avisos del sistema y novedades. La administración técnica puede publicar avisos destacados que aparecen resaltados en la pantalla de inicio de todos los usuarios.

---

## 4. Conceptos básicos del ERP

| Concepto          | Descripción                                                                                                                                                           |
| ----------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Contexto**      | Ámbito de trabajo del usuario: MOEVE, REPSOL u OTROS CLIENTES. Determina qué datos son visibles y operables.                                                          |
| **Trabajo**       | Unidad operativa central. Representa una actuación realizada por CIETE. Todo pedido, ítem y factura está ligado a un trabajo.                                         |
| **Pedido**        | Solicitud económica asociada a un trabajo. Puede existir antes o después del trabajo.                                                                                 |
| **Ítem**          | Línea de detalle del pedido. Es la unidad mínima de facturación. Tiene concepto, cantidad e importe según tarifa.                                                     |
| **Factura**       | Documento económico que agrupa ítems del pedido, completos o parciales. Requiere sociedad/CIF permitida.                                                              |
| **Maestro**       | Datos de referencia del sistema: clientes, estaciones, contratos, sociedades facturadoras, tarifarios y líneas. Sin maestros correctos el flujo no puede completarse. |
| **Cierre**        | Proceso de revisión por Dirección para verificar que los trabajos terminados han sido correctamente pedidos y facturados.                                             |
| **Auditoría**     | Registro de actividad del sistema: quién modificó qué registro y cuándo.                                                                                              |
| **Soporte**       | Canal interno de incidencias técnicas, gestionado desde la administración técnica.                                                                                    |
| **Ciete Excel**   | Modo de interfaz con tablas densas estilo hoja de cálculo, paginación rápida y edición por celda.                                                                     |
| **Ciete Moderno** | Modo de interfaz más visual, con tarjetas y vistas de detalle expandidas.                                                                                             |

---

## 5. Acceso al sistema

### URL

El ERP CIETE v2.1.0 está disponible en:

```
http://130.110.232.84/
```

### Inicio de sesión

Al acceder a la URL se muestra la pantalla de inicio de sesión. Se introduce el correo electrónico y la contraseña del usuario correspondiente.

> Los datos de acceso son personales y no deben compartirse. Para crear o gestionar usuarios se utiliza el panel de administración técnica.

### Pantalla de inicio

Tras iniciar sesión, el usuario llega a la pantalla de inicio común. En ella aparecen:

- **Mensaje destacado**: aviso de mayor relevancia publicado por la administración.
- **Avisos internos**: comunicados generales del sistema.
- **Actualizaciones del sistema**: novedades técnicas o funcionales.
- **Novedades de la empresa**: información general de CIETE.

### Zona de perfil

En la esquina superior de la interfaz aparece el nombre del usuario, su avatar o iniciales y el acceso a **Mi perfil**, donde puede gestionar sus datos personales.

### Cierre de sesión

El cierre de sesión se realiza desde la zona de perfil, mediante el enlace **Cerrar sesión**.

---

## 6. Roles y uso diario

El ERP CIETE v2.1.0 define perfiles de usuario con accesos diferenciados. Cada perfil ve y puede operar solo lo que corresponde a su función.

### 6.1 Dirección

**Propósito del perfil:**

- Revisar el estado general de los trabajos.
- Consultar el panel de cierre.
- Controlar trabajos terminados pendientes de pedido o factura.
- Revisar facturas parciales o incompletas.
- Consultar la trazabilidad de cualquier registro.
- Acceder al resumen de actividad operativa.

**Acceso típico:** panel de cierre, trabajos en todos los estados, trazabilidad.

---

### 6.2 Contabilidad

**Propósito del perfil:**

- Trabajar principalmente con pedidos y facturas.
- Revisar que la sociedad/CIF sea la correcta antes de facturar.
- Comprobar los ítems facturables de cada pedido.
- Controlar facturas parciales o completas.
- No interviene directamente en la creación o edición de trabajos salvo que su perfil lo permita.

**Acceso típico:** módulo de pedidos, módulo de facturas, revisión de sociedad/CIF.

---

### 6.3 Ejecución MOEVE

**Propósito del perfil:**

- Consultar y gestionar los trabajos del contexto MOEVE.
- Operar con las estaciones, pedidos y datos propios de MOEVE.
- El sistema garantiza que no accede ni mezcla datos del contexto REPSOL.

**Acceso típico:** trabajos MOEVE, estaciones MOEVE, pedidos MOEVE.

---

### 6.4 Ejecución REPSOL

**Propósito del perfil:**

- Consultar y gestionar los trabajos del contexto REPSOL.
- Revisar campos específicos del contexto REPSOL: número de aviso, orden, tipo de documento y tipo de trabajo.
- El sistema garantiza que no accede ni mezcla datos del contexto MOEVE.

**Acceso típico:** trabajos REPSOL, campos extendidos REPSOL, pedidos REPSOL.

---

### 6.5 Usuario multicontexto

**Propósito del perfil:**

- Operar en más de un contexto: MOEVE, REPSOL y/o OTROS CLIENTES.
- Cambiar entre contextos desde el selector de contexto en la interfaz.
- Verificar que cada contexto mantiene sus datos separados.

> **Importante:** aunque el usuario pueda cambiar de contexto, siempre debe trabajar en un contexto específico. No existe una vista global de todos los contextos para operaciones de creación o edición.

---

### 6.6 Administración técnica

**Propósito del perfil:**

- Gestionar usuarios y sus accesos.
- Atender solicitudes de soporte interno.
- Revisar el estado del sistema.
- Publicar mensajes y avisos en la pantalla de inicio.
- Consultar la auditoría técnica del sistema.
- Realizar tareas de mantenimiento del entorno.

> La administración técnica no sustituye el trabajo operativo de Dirección, Contabilidad o Ejecución. Son perfiles con funciones complementarias, no equivalentes.

---

## 7. Flujo diario recomendado

El flujo habitual de trabajo en el ERP sigue esta secuencia:

```
Trabajo → Pedido → Ítems → Factura → Cierre
```

### Paso a paso

| Paso | Acción                                                                           |
| ---- | -------------------------------------------------------------------------------- |
| 1    | Entrar al ERP con las credenciales del perfil correspondiente.                   |
| 2    | Revisar la pantalla de inicio: mensajes, avisos y actualizaciones.               |
| 3    | Seleccionar o confirmar el contexto de trabajo (MOEVE, REPSOL u OTROS CLIENTES). |
| 4    | Consultar el listado de trabajos del contexto activo.                            |
| 5    | Revisar o crear un trabajo si el perfil tiene ese permiso.                       |
| 6    | Asociar o revisar el pedido vinculado al trabajo.                                |
| 7    | Revisar los ítems del pedido: conceptos, cantidades e importes.                  |
| 8    | Generar o revisar la factura correspondiente.                                    |
| 9    | Validar que la sociedad/CIF sea la permitida para ese contrato.                  |
| 10   | Revisar el panel de cierre si el perfil es de Dirección.                         |
| 11   | Consultar la auditoría o trazabilidad en caso de duda sobre un registro.         |

> **Regla de oro:** no se puede facturar lo que no tiene trabajo, y no se puede cerrar lo que no tiene factura completa o justificada.

---

## 8. Uso de Ciete Excel

**Ciete Excel** es el modo de interfaz diseñado para el trabajo diario con grandes volúmenes de registros. Está inspirado en la forma de trabajar con hojas de cálculo.

### Características principales

- **Navegación rápida** entre registros mediante tabla densa con scroll interno.
- **Paginación** configurable: el usuario puede elegir cuántos registros ver por página.
- **Ir a página concreta**: campo numérico para saltar directamente a una página específica, sin navegar de página en página.
- **Edición por celda**: en los campos habilitados, el usuario puede editar directamente el valor haciendo clic en la celda, sin necesidad de abrir un formulario separado.
- **Control de conflicto**: si un campo fue modificado recientemente por otro usuario, el sistema avisa y permite decidir si mantener el cambio propio o actualizar con el valor actual del servidor.
- **Scroll interno**: las tablas se desplazan horizontalmente dentro del contenedor sin romper el diseño de la página.
- **Filtros**: disponibles en las columnas principales para acotar la búsqueda.

### Cuándo usarlo

Ciete Excel es el modo recomendado para:

- Revisar listados largos de trabajos, pedidos o facturas.
- Actualizar campos individuales de forma rápida.
- Hacer seguimiento diario del estado de los registros.

---

## 9. Uso de Ciete Moderno

**Ciete Moderno** es el modo de interfaz con presentación más visual, pensado para revisión, detalle y navegación por registros individuales.

### Características principales

- **Tarjetas y tablas limpias** con información destacada de cada registro.
- **Vista de detalle expandida**: acceso rápido a toda la información de un trabajo, pedido o factura en una sola pantalla.
- **Mismos datos que Ciete Excel**: el contenido es idéntico, solo cambia la presentación.
- **Navegación más visual**: útil para presentaciones, revisiones con el equipo o análisis de registros específicos.

### Cuándo usarlo

Ciete Moderno es el modo recomendado para:

- Revisiones detalladas de un trabajo o factura en concreto.
- Presentaciones y demostraciones.
- Consultas en las que la claridad visual sea prioritaria sobre la velocidad.

---

## 10. Trabajos

### Qué representa un trabajo

Un **trabajo** es la unidad operativa central del ERP. Representa una actuación realizada por CIETE en una estación de servicio o ubicación concreta, dentro de un contexto (MOEVE, REPSOL u OTROS CLIENTES).

### Campos clave

| Campo             | Descripción                                                    |
| ----------------- | -------------------------------------------------------------- |
| Número de trabajo | Identificador operativo del trabajo.                           |
| Descripción       | Resumen de la actuación realizada.                             |
| Estado            | Situación actual del trabajo: en curso, terminado, finalizado. |
| Estación          | Estación de servicio asociada.                                 |
| Contexto          | MOEVE, REPSOL u OTROS CLIENTES.                                |
| Observaciones     | Notas adicionales sobre el trabajo.                            |

### Estados principales

| Estado         | Significado                                                  |
| -------------- | ------------------------------------------------------------ |
| **En curso**   | El trabajo está siendo ejecutado.                            |
| **Terminado**  | La ejecución está completa. Pendiente de revisión económica. |
| **Finalizado** | El trabajo ha pasado el proceso de cierre.                   |

### Diferencias entre MOEVE y REPSOL

- En contexto **REPSOL** los trabajos incluyen campos adicionales propios del sistema REPSOL: número de aviso, tipo de documento y tipo de trabajo.
- En contexto **MOEVE** los campos son más simples, adaptados a la operativa MOEVE.

### Relación con estación y pedido

- Cada trabajo está asociado a una estación de servicio del contexto correspondiente.
- Un trabajo puede tener cero, uno o varios pedidos asociados. El ERP gestiona esta relación sin impedir el registro del trabajo.

### Trazabilidad

Cada modificación sobre un trabajo queda registrada en el histórico de auditoría: quién la realizó, cuándo y qué cambió.

---

## 11. Pedidos e ítems

### El pedido como unidad económica del trabajo

Un **pedido** está siempre asociado a un trabajo. Puede registrarse antes, durante o después de la ejecución del trabajo. Representa el acuerdo económico vinculado a esa actuación.

### Los ítems como unidad mínima de facturación

Cada pedido tiene uno o varios **ítems**. Un ítem es una línea tarifaria con:

- Concepto o descripción del servicio.
- Cantidad.
- Precio unitario según tarifa del contrato vigente.
- Importe total.

La factura se construye siempre a partir de ítems. No es posible facturar un importe libre sin respaldo en ítems del pedido.

### Control de coherencia

El sistema verifica que los ítems sean coherentes con el trabajo, el contrato y el contexto activo. Si hay una incompatibilidad —por ejemplo, una tarifa que no corresponde al contexto— el sistema lo indica al usuario.

---

## 12. Facturas

### Cómo se genera una factura

La factura se genera a partir de los ítems del pedido. El usuario selecciona qué ítems se incluyen (todos o una parte) y el sistema genera el documento de factura correspondiente.

### Facturación parcial y completa

- **Factura completa**: incluye todos los ítems del pedido.
- **Factura parcial**: incluye solo una parte de los ítems. El resto puede facturarse en una operación posterior.

### Control de sociedad/CIF

Antes de confirmar una factura, el sistema valida que la sociedad facturadora elegida esté autorizada para ese contrato y contexto. Si la sociedad no está configurada correctamente en los maestros, el sistema muestra un aviso explicativo.

### Facturas con referencia de origen no normalizada

En algunos casos, datos importados del historial operativo presentan una referencia de número de factura que no sigue el formato estándar del sistema. Estas referencias se conservan como dato de origen y se muestran de forma visual controlada, sin afectar al flujo de facturación actual.

### Exportación

Las facturas pueden exportarse en formato CSV desde el listado o desde el detalle individual, respetando el contexto activo y los permisos del usuario.

---

## 13. Maestros

Los **maestros** son los datos de referencia sobre los que se apoya todo el flujo operativo. Sin maestros correctamente configurados, el flujo de trabajo → pedido → factura no puede completarse.

### Tipos de maestros

| Maestro                     | Descripción                                                                               |
| --------------------------- | ----------------------------------------------------------------------------------------- |
| **Clientes / Empresas**     | Entidades cliente a las que se asocian trabajos y contratos.                              |
| **Estaciones**              | Estaciones de servicio con código, nombre, municipio y provincia. Separadas por contexto. |
| **Contratos**               | Acuerdos marco que determinan las condiciones económicas de los trabajos.                 |
| **Sociedades facturadoras** | Entidades emisoras de factura autorizadas para cada contrato y contexto.                  |
| **Tarifarios**              | Catálogo de precios y condiciones económicas vigentes por contrato.                       |
| **Líneas de tarifa**        | Líneas de detalle del tarifario: concepto, precio y condiciones.                          |

### Importancia de los maestros

Si un maestro está incompleto o no configurado para el contexto correcto, el sistema avisará al usuario durante el flujo operativo. La corrección debe realizarse siempre en el módulo de Maestros, no directamente en el pedido o la factura.

---

## 14. Panel de cierre / Dirección

### Propósito

El panel de cierre está diseñado para que el perfil de **Dirección** pueda revisar el estado económico de los trabajos y asegurarse de que ninguno queda sin cobrar.

### Qué se puede revisar

| Sección              | Contenido                                                   |
| -------------------- | ----------------------------------------------------------- |
| Trabajos terminados  | Trabajos en estado "terminado" pendientes de revisión.      |
| Trabajos sin pedido  | Trabajos terminados que aún no tienen pedido asociado.      |
| Trabajos sin factura | Trabajos con pedido pero sin factura emitida.               |
| Facturas parciales   | Facturas que cubren solo una parte de los ítems del pedido. |
| Trazabilidad         | Historial de modificaciones sobre los registros revisados.  |

### Checklist de cierre

Antes de marcar un trabajo como **finalizado**, se recomienda verificar:

- [ ] El trabajo está en estado "terminado".
- [ ] Tiene al menos un pedido asociado.
- [ ] Todos los ítems del pedido están facturados o justificados.
- [ ] La sociedad/CIF de la factura es la correcta.
- [ ] No hay facturas parciales pendientes sin justificación.

### Cuándo finalizar

Un trabajo puede marcarse como **finalizado** cuando se han completado todas las verificaciones anteriores. Una vez finalizado, queda bloqueado para modificaciones ordinarias.

---

## 15. Administración técnica

### Propósito

La administración técnica gestiona el entorno del sistema sin intervenir en la operativa diaria de Dirección, Contabilidad o Ejecución.

### Funciones disponibles

| Función                | Descripción                                                                 |
| ---------------------- | --------------------------------------------------------------------------- |
| **Usuarios**           | Creación, edición y desactivación de usuarios del sistema.                  |
| **Soporte**            | Gestión de solicitudes de soporte interno: estado, respuesta y seguimiento. |
| **Avisos internos**    | Publicación y gestión de mensajes en la pantalla de inicio.                 |
| **Mantenimiento**      | Control del modo de mantenimiento del sistema.                              |
| **Estado del sistema** | Revisión de métricas y estado operativo del entorno.                        |
| **Auditoría técnica**  | Consulta del registro de actividad del sistema.                             |

### Diferencia con la operativa diaria

El administrador técnico **no gestiona** trabajos, pedidos, facturas ni maestros operativos. Su función es garantizar que el sistema funciona correctamente y que los usuarios tienen los accesos adecuados.

---

## 16. Mensajes de inicio

La pantalla de inicio muestra cuatro secciones de comunicación interna:

| Sección                         | Propósito                                                                                     |
| ------------------------------- | --------------------------------------------------------------------------------------------- |
| **Mensaje destacado**           | Aviso de mayor relevancia, resaltado visualmente. Se publica desde la administración técnica. |
| **Avisos internos**             | Comunicados generales: recordatorios, procedimientos, cambios de operativa.                   |
| **Actualizaciones del sistema** | Novedades técnicas o funcionales del ERP.                                                     |
| **Novedades de la empresa**     | Información general de CIETE para todos los usuarios.                                         |

Los mensajes son gestionados por la administración técnica. Cualquier usuario puede verlos al entrar al sistema.

---

## 17. Estado del sistema

### Qué puede ver el administrador técnico

- Métricas de uso del sistema.
- Estado de los procesos activos.
- Alertas técnicas si las hubiera.
- Registro de actividad reciente.

### Qué ve el resto de usuarios

Los usuarios no administradores no acceden al panel de estado del sistema. Si hay un mantenimiento programado o una incidencia, serán informados a través de los mensajes de inicio.

### Utilidad

El panel de estado del sistema es útil para:

- Verificar que el sistema funciona correctamente antes de una presentación.
- Revisar si hay actividad inusual.
- Apoyar la resolución de incidencias de soporte.

---

## 18. Ejemplo de recorrido de demostración

Los siguientes recorridos proponen una ruta práctica para presentar el ERP CIETE v2.1.0 de forma ordenada y representativa.

---

### Recorrido 1 — Entrada y visión general

1. Acceder a `http://130.110.232.84/` con un perfil de administración o dirección.
2. Mostrar la pantalla de inicio: mensaje destacado, avisos y actualizaciones.
3. Explicar el propósito de cada sección de la pantalla de inicio.
4. Mostrar el selector de contexto y cambiar entre MOEVE, REPSOL y OTROS CLIENTES.
5. Explicar que el contexto determina todos los datos que el usuario puede ver y operar.

---

### Recorrido 2 — Ejecución MOEVE

1. Acceder con el perfil de ejecución MOEVE.
2. Navegar al módulo de Trabajos en contexto MOEVE.
3. Abrir un trabajo real de MOEVE y revisar sus datos: número, estación, estado, observaciones.
4. Revisar el pedido asociado a ese trabajo.
5. Mostrar que el sistema solo muestra datos del contexto MOEVE para este perfil.

---

### Recorrido 3 — Ejecución REPSOL

1. Acceder con el perfil de ejecución REPSOL (o cambiar contexto si es perfil multicontexto).
2. Navegar al módulo de Trabajos en contexto REPSOL.
3. Abrir un trabajo REPSOL y señalar los campos específicos: número de aviso, tipo de documento, tipo de trabajo.
4. Comparar visualmente con un trabajo MOEVE para destacar las diferencias.

---

### Recorrido 4 — Contabilidad

1. Acceder con el perfil de contabilidad.
2. Navegar al módulo de Pedidos: mostrar listado con datos reales.
3. Abrir un pedido y revisar sus ítems: conceptos, cantidades, importes.
4. Navegar al módulo de Facturas: mostrar listado con datos reales.
5. Abrir una factura y revisar la sociedad/CIF asignada.
6. Explicar la diferencia entre factura parcial y completa.

---

### Recorrido 5 — Dirección

1. Acceder con el perfil de dirección.
2. Abrir el panel de cierre.
3. Revisar la sección de trabajos terminados sin pedido.
4. Revisar la sección de trabajos con factura parcial.
5. Explicar el checklist de cierre y el proceso para marcar un trabajo como finalizado.
6. Mostrar la trazabilidad de un trabajo concreto.

---

### Recorrido 6 — Administración técnica

1. Acceder con el perfil de administración técnica.
2. Abrir el panel de administración: mostrar la lista de usuarios.
3. Mostrar la sección de soporte: tickets y seguimiento.
4. Mostrar los mensajes de inicio y cómo se publica un aviso destacado.
5. Mostrar el panel de estado del sistema.
6. Subrayar que este perfil no gestiona trabajos ni facturas: su función es el entorno.

---

## 19. Buenas prácticas de uso

| Práctica                                             | Motivo                                                                                                            |
| ---------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------- |
| Trabajar siempre en el contexto correcto             | Evita mezclar datos de MOEVE, REPSOL u OTROS CLIENTES.                                                            |
| Revisar los avisos del sistema al entrar             | Los avisos pueden contener información relevante para la jornada.                                                 |
| No forzar datos si falta un maestro                  | El flujo no puede completarse sin maestros correctos. Hay que registrarlos primero en el módulo de Maestros.      |
| Usar Maestros para corregir datos de base            | Los datos de referencia (estaciones, contratos, tarifas) se corrigen en Maestros, no en los registros operativos. |
| Abrir una solicitud de soporte si hay una incidencia | El canal de soporte es el medio correcto para comunicar cualquier anomalía técnica al administrador.              |
| Revisar sociedad/CIF antes de confirmar una factura  | Una factura con sociedad incorrecta puede requerir correcciones posteriores.                                      |
| Usar paginación e "Ir a página" en listados grandes  | Facilita la localización de registros en contextos con miles de trabajos.                                         |

---

## 20. Preguntas frecuentes

**¿Por qué no aparece la opción "TODOS" como contexto de trabajo?**  
El contexto "TODOS" no está disponible para crear ni editar registros. Es una vista de solo lectura disponible para ciertos perfiles de supervisión. La creación siempre debe realizarse en un contexto específico.

**¿Por qué no puedo crear un trabajo desde una vista global?**  
Para crear un trabajo es necesario estar situado en un contexto concreto (MOEVE, REPSOL u OTROS CLIENTES). La vista global no permite operaciones de creación para evitar asignaciones incorrectas de contexto.

**¿Por qué no aparece una sociedad/CIF en la lista de la factura?**  
La lista de sociedades disponibles depende del contrato y del contexto. Si la sociedad no aparece, es posible que no esté configurada como permitida para ese contrato en el módulo de Maestros.

**¿Qué diferencia hay entre Ciete Excel y Ciete Moderno?**  
Son dos formas de ver los mismos datos. Ciete Excel está optimizado para trabajo rápido con muchos registros. Ciete Moderno ofrece una presentación más visual, útil para revisiones y análisis.

**¿Qué hago si una factura tiene una referencia de origen no estándar?**  
Las referencias de origen heredadas del historial operativo se conservan como dato y se muestran correctamente en el sistema. No es necesario modificarlas; son una referencia identificativa del origen del registro.

**¿Qué hago si un trabajo no tiene pedido asociado?**  
El panel de cierre de Dirección identifica los trabajos terminados sin pedido. Desde allí, el perfil autorizado puede asignar el pedido o marcar el trabajo para revisión.

**¿Qué puede hacer cada rol?**  
Consultar la sección [6. Roles y uso diario](#6-roles-y-uso-diario) de esta guía para ver el detalle de acceso y funciones de cada perfil.

**¿Dónde se revisa la trazabilidad de un registro?**  
La trazabilidad está disponible en el detalle de cada trabajo, pedido o factura para los perfiles con acceso a esa función. El perfil de Dirección y el de administración técnica tienen visibilidad completa.

**¿Dónde se gestionan los usuarios del sistema?**  
Los usuarios se gestionan desde el panel de administración técnica. Solo el administrador puede crear, editar o desactivar usuarios.

**¿Cómo avisa el sistema de un conflicto reciente en una celda?**  
Si un usuario modifica un campo en Ciete Excel y ese campo fue modificado por otro usuario en la última hora, el sistema muestra un aviso de conflicto. El usuario puede decidir si conserva su cambio o actualiza con el valor actual del sistema.

---

## 21. Cierre del documento

Esta guía resume el funcionamiento práctico del ERP CIETE v2.1.0 para su revisión operativa. El objetivo es facilitar una validación clara del flujo diario de trabajo y de los módulos principales del sistema.

Para cualquier consulta técnica sobre el entorno o los accesos, dirigirse al administrador técnico del sistema.

---

## 22. Capturas recomendadas para añadir después

Las siguientes capturas de pantalla se recomiendan para enriquecer el documento con evidencia visual:

- Pantalla de inicio con mensajes activos.
- Listado de Trabajos en Ciete Excel.
- Listado de Trabajos en Ciete Moderno.
- Detalle de un trabajo con sus datos clave.
- Listado de Pedidos con ítems expandidos.
- Listado de Facturas con sociedad/CIF visible.
- Panel de Cierre con secciones de revisión.
- Módulo de Maestros: estaciones y contratos.
- Panel de Administración técnica.
- Panel de Estado del sistema.

---

_ERP CIETE v2.1.0 — Documento de apoyo para revisión funcional — Mayo 2026_
