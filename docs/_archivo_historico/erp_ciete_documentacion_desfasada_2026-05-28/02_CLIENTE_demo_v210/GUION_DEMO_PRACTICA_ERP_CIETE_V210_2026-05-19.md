# Guion para demostración práctica

## ERP CIETE v2.1.0

**Guion de apoyo para presentación oral — Mayo 2026**

> Documento para uso interno durante la presentación. No es un manual técnico.  
> Entorno: **http://130.110.232.84/**

---

## Índice

1. [Introducción breve](#1-introducción-breve)
2. [Qué se pidió en la reunión anterior](#2-qué-se-pidió-en-la-reunión-anterior)
3. [Qué se ha preparado ahora](#3-qué-se-ha-preparado-ahora)
4. [Datos reales cargados](#4-datos-reales-cargados)
5. [Recorrido por roles](#5-recorrido-por-roles)
6. [Flujo trabajo → pedido → factura](#6-flujo-trabajo--pedido--factura)
7. [Dirección y cierre](#7-dirección-y-cierre)
8. [Administración técnica](#8-administración-técnica)
9. [Ciete Excel y Ciete Moderno](#9-ciete-excel-y-ciete-moderno)
10. [Cierre de presentación](#10-cierre-de-presentación)

---

## 1. Introducción breve

_[Hablar mientras se muestra la pantalla de inicio del ERP]_

> "Esto es el ERP CIETE v2.1.0, disponible en http://130.110.232.84/.  
> Lo que vamos a ver hoy es un recorrido por el flujo real de trabajo: cómo entra la información, cómo se organiza por contexto y cómo el sistema acompaña desde el trabajo hasta la factura y el cierre."

**Puntos de apoyo:**

- El sistema ya tiene datos reales cargados.
- Está organizado en tres contextos: MOEVE, REPSOL y OTROS CLIENTES.
- Cada usuario solo ve lo que le corresponde.

---

## 2. Qué se pidió en la reunión anterior

_[No hace falta mostrar pantalla; es presentación verbal]_

> "En la reunión anterior se revisó la necesidad de que el ERP no fuese solo una pantalla de consulta, sino una herramienta de trabajo diaria. Por eso esta versión se centra en el flujo real: trabajos, pedidos, ítems, facturas, cierre y roles."

**Puntos clave que se pidieron:**

- El trabajo como eje: todo parte de un trabajo, no de una factura.
- Separación total entre MOEVE, REPSOL y OTROS CLIENTES.
- Pedidos flexibles: pueden llegar antes o después del trabajo.
- Facturas basadas en ítems tarifados, no en importes libres.
- Control de que no quede ningún trabajo sin cobrar.
- Roles diferenciados: no todo el mundo hace lo mismo ni ve lo mismo.
- Trazabilidad: saber quién tocó qué y cuándo.
- Mensajes y avisos para toda la organización desde el inicio del sistema.

---

## 3. Qué se ha preparado ahora

_[Mostrar brevemente la pantalla de inicio y el selector de contexto]_

> "El ERP CIETE v2.1.0 cubre todos esos puntos. Vamos a verlos uno a uno con datos reales."

**Qué está listo:**

- Flujo completo: trabajo → pedido → ítems → factura → cierre.
- Contextos separados con aislamiento de datos.
- Dos modos de interfaz: Ciete Excel y Ciete Moderno.
- Roles operativos: Dirección, Contabilidad, Ejecución MOEVE, Ejecución REPSOL, Administración técnica.
- Validación de sociedad/CIF antes de facturar.
- Panel de cierre para Dirección.
- Mensajes de inicio y avisos internos.
- Auditoría y trazabilidad operativa.
- Módulo de Maestros: estaciones, contratos, tarifarios, sociedades facturadoras.
- Exportación de facturas en CSV.

---

## 4. Datos reales cargados

_[Mostrar el listado de Trabajos en Ciete Excel]_

> "El sistema no trabaja con datos inventados. Tiene cargado el historial real de CIETE: más de once mil trabajos, casi diez mil pedidos, más de dos mil setecientas facturas."

**Cifras del sistema:**

| Entidad          | Registros cargados |
| ---------------- | ------------------ |
| Trabajos         | más de 11.600      |
| Pedidos          | más de 9.600       |
| Ítems de pedido  | más de 9.700       |
| Facturas         | más de 2.700       |
| Ítems de factura | más de 9.100       |

**Fuentes:**

- Archivos reales de control de trabajos MOEVE y REPSOL.
- Listado real de estaciones de servicio MOEVE.
- Distintas categorías de trabajos REPSOL: Diseño, Edificación, Obras, Licencias, FV, Estructuras, Mantenimiento, Puntos de Recarga.

> "Esto permite hacer una revisión funcional sobre datos de trabajo reales, no sobre ejemplos de laboratorio."

---

## 5. Recorrido por roles

_[Ir mostrando según cada perfil]_

### Ejecución MOEVE

> "El usuario de ejecución MOEVE solo ve trabajos y pedidos de MOEVE. No puede acceder a REPSOL ni a facturas de otras áreas."

- Mostrar listado de trabajos MOEVE.
- Abrir un trabajo real: número, estación, estado.
- Mostrar el pedido asociado.

---

### Ejecución REPSOL

> "El contexto REPSOL tiene campos específicos que no existen en MOEVE: número de aviso, tipo de documento, tipo de trabajo. Están cargados con datos reales."

- Cambiar a contexto REPSOL.
- Abrir un trabajo REPSOL y señalar los campos exclusivos.

---

### Contabilidad

> "El perfil de contabilidad trabaja con pedidos y facturas. Su responsabilidad clave es verificar que la sociedad/CIF sea la correcta antes de confirmar una factura."

- Mostrar listado de pedidos con ítems.
- Mostrar listado de facturas.
- Abrir una factura y señalar la sociedad/CIF.
- Mencionar factura parcial vs. factura completa.

---

### Dirección

> "El perfil de Dirección tiene acceso al panel de cierre, que le permite ver de un vistazo qué trabajos están terminados pero no tienen pedido, qué tienen pedido pero no factura, y qué facturas están parciales."

- Abrir el panel de cierre.
- Señalar las secciones: sin pedido, sin factura, facturas parciales.
- Explicar el checklist para marcar un trabajo como finalizado.

---

### Administración técnica

> "El administrador técnico gestiona el entorno: usuarios, soporte, mensajes de inicio y estado del sistema. No interviene en la operativa diaria."

- Mostrar el panel de administración.
- Señalar la gestión de usuarios.
- Mostrar la publicación de un aviso de inicio.

---

## 6. Flujo trabajo → pedido → factura

_[Mostrar el flujo de forma lineal con un ejemplo real]_

> "El flujo de trabajo siempre sigue la misma secuencia: el trabajo existe primero, el pedido lo acompaña, los ítems son las líneas tarifadas, y la factura agrupa esos ítems."

```
Trabajo → Pedido → Ítems → Factura → Cierre
```

**Paso a paso con un ejemplo:**

1. Localizamos un trabajo real en estado "terminado".
2. Vemos su pedido asociado.
3. Revisamos los ítems del pedido: concepto, cantidad, importe.
4. Abrimos la factura correspondiente.
5. Verificamos la sociedad/CIF.
6. El trabajo puede pasar a "finalizado" desde el panel de cierre.

> "Todo está encadenado. No se puede facturar sin ítems, no hay ítems sin pedido, y no hay pedido sin trabajo."

---

## 7. Dirección y cierre

_[Mostrar el panel de cierre]_

> "El panel de cierre es la herramienta de control económico de Dirección. Responde a la pregunta clave de CIETE: ¿hay trabajos terminados que no se han cobrado?"

**Qué muestra el panel:**

- Trabajos terminados sin pedido asociado.
- Trabajos con pedido pero sin factura.
- Facturas emitidas pero solo parciales.
- Historial de trazabilidad de cada registro.

> "Con esto, la dirección puede actuar sobre los casos pendientes antes de que se acabe el período de facturación."

---

## 8. Administración técnica

_[Mostrar el panel de administración]_

> "La administración técnica es el perfil que mantiene el sistema funcionando: crea usuarios, gestiona el soporte interno y publica los mensajes que aparecen en el inicio para todos."

**Funciones principales:**

- Gestión de usuarios y accesos.
- Seguimiento de solicitudes de soporte.
- Publicación de avisos en la pantalla de inicio.
- Revisión del estado del sistema.
- Auditoría técnica.

> "Es importante entender que este perfil no opera trabajos ni facturas. Su función es el entorno, no la operativa."

---

## 9. Ciete Excel y Ciete Moderno

_[Mostrar las dos vistas del mismo listado de trabajos]_

> "El sistema tiene dos formas de presentar los mismos datos según cómo prefiera trabajar el usuario."

**Ciete Excel:**

> "Ciete Excel está pensado para el trabajo diario con muchos registros. Permite editar directamente por celda, paginar rápidamente, ir a una página concreta sin navegar paso a paso, y detecta cuando otro usuario ha modificado un campo recientemente."

- Mostrar tabla de trabajos en modo Excel.
- Hacer clic en una celda editable para demostrar la edición directa.
- Mostrar el campo "Ir a página".

**Ciete Moderno:**

> "Ciete Moderno es más visual. Útil para revisar un registro en detalle, para presentaciones, o cuando se necesita más claridad sobre un caso concreto."

- Cambiar a vista Moderna del mismo listado.
- Abrir el detalle de un trabajo.

---

## 10. Cierre de presentación

_[Pantalla de inicio del ERP visible]_

> "El ERP CIETE v2.1.0 cubre el flujo completo de trabajo de CIETE: desde que se registra una actuación en una estación hasta que se factura y se cierra. Los datos están separados por contexto, los roles están diferenciados, y la trazabilidad garantiza que cualquier modificación queda registrada."

**Resumen de lo visto:**

- Datos reales de MOEVE y REPSOL correctamente cargados.
- Flujo trabajo → pedido → ítems → factura → cierre operativo.
- Roles diferenciados con acceso controlado.
- Ciete Excel para trabajo diario, Ciete Moderno para revisión.
- Panel de cierre para Dirección.
- Administración técnica para el entorno.
- Mensajes y avisos internos desde el inicio.

> "Cualquier ajuste de datos de referencia —estaciones, contratos, tarifas, sociedades— se realiza en el módulo de Maestros. El sistema está preparado para trabajar con los datos definitivos de CIETE en cuanto estén confirmados."

---

**Fin del guion.**

_ERP CIETE v2.1.0 — Guion de demostración — Mayo 2026_
