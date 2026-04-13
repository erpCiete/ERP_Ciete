# Manual de usuario — ERP Ciete

> **Versión:** 1.0 (13 de abril de 2026)  
> **Sistema:** ERP Ciete — Gestión de trabajos para estaciones de servicio  
> **Acceso:** https://erp.ciete.es _(o servidor local en `http://localhost:8000`)_

### Leyenda de colores

En este manual y en el propio ERP, los dos clientes principales se identifican con colores propios:

- <span style="color:#FF6B00">**MOEVE**</span> — Naranja. Todo lo referente al cliente Moeve.
- <span style="color:#DC2626">**REPSOL**</span> — Rojo. Todo lo referente al cliente Repsol.

---

## Índice

1. [Acceso al sistema](#1-acceso-al-sistema)
2. [Recuperar contraseña](#2-recuperar-contraseña)
3. [Interfaz general](#3-interfaz-general)
4. [Dashboard (Panel de empleado)](#4-dashboard-panel-de-empleado)
5. [Módulo de Clientes](#5-módulo-de-clientes)
6. [Módulo de Estaciones de servicio](#6-módulo-de-estaciones-de-servicio)
7. [Perfil de usuario](#7-perfil-de-usuario)
8. [Tema e idioma](#8-tema-e-idioma)
9. [Roles y permisos](#9-roles-y-permisos)
10. [Contextos: qué datos puedo ver](#10-contextos-qué-datos-puedo-ver)
11. [Panel de Administración (solo admin)](#11-panel-de-administración-solo-admin)
12. [Panel de Cierre (solo rol cierre)](#12-panel-de-cierre-solo-rol-cierre)
13. [Modo mantenimiento](#13-modo-mantenimiento)
14. [Módulos en desarrollo](#14-módulos-en-desarrollo)
15. [Preguntas frecuentes](#15-preguntas-frecuentes)

---

## 1. Acceso al sistema

### 1.1 Pantalla de login

Al acceder a la URL del ERP se muestra la pantalla de inicio de sesión con el logo de Ciete Ingenieros. Introduzca su **correo electrónico** y **contraseña**.

- Puede mostrar u ocultar la contraseña con el icono del ojo a la derecha del campo.
- Si introduce un email o contraseña incorrectos, se mostrará un mensaje de error debajo del campo.
- Si su cuenta está desactivada, se mostrará un aviso indicando que no tiene acceso.
- Tras 5 intentos fallidos consecutivos, la cuenta se bloquea temporalmente por seguridad.

### 1.2 Usuarios del sistema

| Usuario          | Email            | Contraseña   | Rol                                                  | Contextos visibles                                                                                   |
| ---------------- | ---------------- | ------------ | ---------------------------------------------------- | ---------------------------------------------------------------------------------------------------- |
| Administrador    | admin@ciete.es   | Admin1234!   | Administrador                                        | <span style="color:#FF6B00">**MOEVE**</span> + <span style="color:#DC2626">**REPSOL**</span> + CIETE |
| César (Cierre)   | cesar@ciete.es   | Cesar1234!   | Control de cierre                                    | <span style="color:#FF6B00">**MOEVE**</span> + <span style="color:#DC2626">**REPSOL**</span> + CIETE |
| Usuario estándar | usuario@ciete.es | Usuario1234! | Usuario                                              | <span style="color:#FF6B00">**MOEVE**</span> + <span style="color:#DC2626">**REPSOL**</span>         |
| Gestor Moeve     | moeve@ciete.es   | Moeve1234!   | Gestor <span style="color:#FF6B00">**MOEVE**</span>  | Solo <span style="color:#FF6B00">**MOEVE**</span>                                                    |
| Gestor Repsol    | repsol@ciete.es  | Repsol1234!  | Gestor <span style="color:#DC2626">**REPSOL**</span> | Solo <span style="color:#DC2626">**REPSOL**</span>                                                   |

### 1.3 Cerrar sesión

Pulse sobre su nombre de usuario en la esquina inferior izquierda del sidebar y seleccione **Cerrar sesión**.

---

## 2. Recuperar contraseña

Si ha olvidado su contraseña, puede recuperarla sin necesidad de contactar al administrador:

1. En la pantalla de login, pulse el enlace **"¿Olvidaste tu contraseña?"** debajo del campo de contraseña.
2. Se abrirá la pantalla de recuperación. Introduzca el **correo electrónico** de su cuenta.
3. Pulse **"Enviar enlace de restablecimiento"**.
4. Revise su bandeja de entrada (y la carpeta de spam). Recibirá un correo con asunto **"Restablecer contraseña — ERP Ciete"**.
5. Pulse el botón **"Restablecer contraseña"** del correo. Se abrirá la pantalla de nueva contraseña.
6. Introduzca y confirme su **nueva contraseña** (mínimo 8 caracteres).
7. Pulse **"Restablecer contraseña"**. Será redirigido al login con un mensaje de éxito.

> **Nota:** El enlace del correo caduca en 60 minutos. Si no lo usa a tiempo, repita el proceso.

---

## 3. Interfaz general

### 3.1 Sidebar (barra lateral izquierda)

La barra lateral de 240px es la navegación principal. Se organiza en grupos:

| Grupo           | Enlace          | Quién lo ve                        | Para qué sirve                                          |
| --------------- | --------------- | ---------------------------------- | ------------------------------------------------------- |
| **General**     | Dashboard       | Todos                              | Resumen de su actividad: obras, pedidos, legalizaciones |
| **General**     | Panel de cierre | Solo rol "Control de cierre"       | Gestión de cierre de trabajos                           |
| **Maestros**    | Clientes        | Usuarios con permiso de empresas   | Ver y gestionar empresas registradas                    |
| **Maestros**    | Estaciones      | Usuarios con permiso de estaciones | Ver y gestionar estaciones de servicio                  |
| **Operaciones** | Obras           | _(Próximamente)_                   | Gestión de trabajos                                     |
| **Operaciones** | Pedidos         | _(Próximamente)_                   | Pedidos de material/servicio                            |
| **Operaciones** | Legalizaciones  | _(Próximamente)_                   | Legalizaciones por trabajo                              |
| **Informes**    | Informes        | _(Próximamente)_                   | Informes y exportaciones                                |

> Si no ve un enlace en el sidebar, es porque su usuario no tiene permiso para ese módulo. Contacte con el administrador si cree que debería tener acceso.

### 3.2 Parte inferior del sidebar

Muestra su información personal:

- Su **avatar** (personalizable desde perfil).
- Su **nombre**.
- Su **correo electrónico**.
- Enlace a **Configuración** (perfil).
- Botón **Cerrar sesión**.

### 3.3 Encabezado de la página

Contiene:

- El **título** de la sección actual.
- El **selector de idioma** (ES/EN) para cambiar el idioma de la interfaz.

---

## 4. Dashboard (Panel de empleado)

**Cómo llegar:** Sidebar → **Dashboard** (o pulse el logo de Ciete en la parte superior del sidebar).

Esta es la primera pantalla que verá tras iniciar sesión. Muestra:

- **Saludo personalizado** con su nombre.
- **Acceso rápido** a su perfil.
- **Enlace al Panel de Administración** (solo si es admin).
- **Tarjetas resumen:**
    - Mis obras activas (cuántas tiene asignadas).
    - Pedidos pendientes (cuántos pedidos tiene por gestionar).
    - Legalizaciones (cuántas tiene pendientes).
- **Tablas:**
    - Mis obras asignadas.
    - Mis legalizaciones pendientes.
    - Mis pedidos.

> **Estado actual:** Las tarjetas y tablas mostrarán datos reales a medida que se implementen los módulos de Obras, Pedidos y Legalizaciones.

---

## 5. Módulo de Clientes

**Cómo llegar:** Sidebar → Maestros → **Clientes**  
**Permiso necesario:** `empresas_contactos.gestionar`

### 5.1 Listado de clientes

Verá una tabla con las empresas registradas en el sistema que pertenecen a sus contextos:

| Columna                     | Qué muestra                                                                                                      |
| --------------------------- | ---------------------------------------------------------------------------------------------------------------- |
| Nombre                      | Nombre de la empresa                                                                                             |
| Nombre comercial (Operador) | Marca: <span style="color:#FF6B00">**MOEVE**</span>, <span style="color:#DC2626">**REPSOL**</span>, CIETE u OTRO |
| CIF                         | Número de identificación fiscal                                                                                  |
| Activo                      | Indicador verde (activo) o rojo (inactivo)                                                                       |
| Acciones                    | Botones de editar y eliminar                                                                                     |

**Buscar:** Escriba en el campo de búsqueda para filtrar por nombre, CIF o nombre comercial.

**Crear nuevo cliente:** Pulse el botón **"Nuevo cliente"** en la esquina superior derecha.

### 5.2 Crear / Editar un cliente

Rellene el formulario:

| Campo            | ¿Obligatorio? | Cómo rellenarlo                                                                                                                       |
| ---------------- | ------------- | ------------------------------------------------------------------------------------------------------------------------------------- |
| Nombre           | Sí            | Nombre oficial de la empresa. Ejemplo: "Moeve Energy S.A."                                                                            |
| Nombre comercial | No            | Seleccione del desplegable: CIETE, <span style="color:#FF6B00">**MOEVE**</span>, <span style="color:#DC2626">**REPSOL**</span> u OTRO |
| Razón social     | No            | Razón social legal completa                                                                                                           |
| CIF              | No            | CIF/NIF español. Ejemplo: A12345678, B87654321                                                                                        |
| Web              | No            | Dirección web completa. Ejemplo: https://www.moeve.com                                                                                |
| Observaciones    | No            | Notas internas de texto libre                                                                                                         |
| Activo           | —             | Marque o desmarque para activar/desactivar la empresa                                                                                 |

**Validaciones que puede encontrar:**

- "Este campo es obligatorio" → el nombre no puede estar vacío.
- "Introduce un NIF, NIE o CIF español válido" → el formato del CIF no es correcto.
- "Introduce una URL válida, incluyendo el protocolo" → la web debe empezar por http:// o https://.

**Guardar:** Pulse "Guardar". Si todo es correcto, volverá al listado con un mensaje de confirmación.

**Cancelar:** Pulse "Cancelar" para volver sin guardar cambios.

### 5.3 Eliminar un cliente

1. En el listado, pulse el icono de papelera en la fila del cliente.
2. Se mostrará un diálogo de confirmación.
3. Confirme para eliminar. La acción es **definitiva**.

---

## 6. Módulo de Estaciones de servicio

**Cómo llegar:** Sidebar → Maestros → **Estaciones**  
**Permiso necesario:** `estaciones.ver` (solo consultar) o `estaciones.gestionar` (crear/editar/eliminar)

### 6.1 Listado de estaciones

Tabla con las estaciones accesibles según sus contextos:

| Columna         | Qué muestra                                                                                                                          |
| --------------- | ------------------------------------------------------------------------------------------------------------------------------------ |
| Nombre          | Nombre de la estación. Ejemplo: "E.S. Alcalá de Henares"                                                                             |
| Empresa         | Empresa propietaria (<span style="color:#FF6B00">**MOEVE**</span> o <span style="color:#DC2626">**REPSOL**</span>)                   |
| Código estación | Código identificativo (concesión <span style="color:#FF6B00">**Moeve**</span> / C.EMP <span style="color:#DC2626">**Repsol**</span>) |
| Estado          | Estado operativo: operativa, en obras, cerrada, baja                                                                                 |
| Provincia       | Provincia de ubicación                                                                                                               |
| Activo          | Indicador verde/rojo                                                                                                                 |
| Acciones        | Editar / Eliminar                                                                                                                    |

**Buscar:** Filtre por nombre, código o provincia.

**Crear nueva estación:** Botón **"Nueva estación"** (necesita permiso `estaciones.gestionar`).

### 6.2 Crear / Editar una estación

| Campo              | ¿Obligatorio? | Cómo rellenarlo                                                                                                                |
| ------------------ | ------------- | ------------------------------------------------------------------------------------------------------------------------------ |
| Empresa            | Sí            | Seleccione la empresa del desplegable                                                                                          |
| Nombre             | Sí            | Nombre de la estación                                                                                                          |
| Código estación    | No            | Código único. <span style="color:#FF6B00">**Moeve**</span>: nº concesión. <span style="color:#DC2626">**Repsol**</span>: C.EMP |
| Estado             | Sí            | Seleccione: operativa, en_obras, cerrada, baja                                                                                 |
| Dirección          | No            | Calle y número                                                                                                                 |
| Código postal      | No            | 5 dígitos. Ejemplo: 28001                                                                                                      |
| Localidad          | No            | Municipio                                                                                                                      |
| Provincia          | No            | Provincia                                                                                                                      |
| Comunidad autónoma | No            |                                                                                                                                |
| Latitud            | No            | Coordenada GPS decimal. Ejemplo: 40.4168                                                                                       |
| Longitud           | No            | Coordenada GPS decimal. Ejemplo: -3.7038                                                                                       |
| Observaciones      | No            | Notas de texto libre                                                                                                           |
| Activo             | —             | Marque para activar                                                                                                            |

**Validaciones:**

- Código postal: debe tener 5 dígitos y el prefijo debe estar entre 01 y 52.
- Latitud: entre -90 y 90.
- Longitud: entre -180 y 180.

### 6.3 ¿Solo puedo ver pero no editar?

Si tiene el permiso `estaciones.ver` pero NO `estaciones.gestionar`, puede:

- Ver el listado completo.
- Ver los detalles de cualquier estación.

Pero NO puede:

- Crear nuevas estaciones.
- Editar estaciones existentes.
- Eliminar estaciones.

---

## 7. Perfil de usuario

**Cómo llegar:** Sidebar → zona inferior → **Configuración** (o pulse su nombre)

### 7.1 Cambiar avatar

1. En la sección "Avatar de perfil", verá un catálogo de **10 avatares** predefinidos.
2. Pulse sobre el que desee.
3. Pulse **Guardar**. Su nuevo avatar se mostrará en el sidebar y en otras partes de la interfaz.

### 7.2 Cambiar contraseña

1. Vaya a la sección "Actualizar contraseña".
2. Introduzca su **contraseña actual**.
3. Introduzca la **nueva contraseña** (mínimo 8 caracteres).
4. **Confirme** la nueva contraseña (debe coincidir exactamente).
5. Pulse **Guardar**.

---

## 8. Tema e idioma

### 8.1 Cambiar idioma

El ERP está disponible en **español** e **inglés**.

1. Pulse el selector **ES / EN** en el encabezado de cualquier página.
2. El cambio se aplica inmediatamente a toda la interfaz.
3. El idioma se guarda en su sesión. Al volver a entrar, mantendrá el último idioma usado.

### 8.2 Tema claro / oscuro

El sistema soporta modo claro y modo oscuro.

1. Pulse el icono de sol/luna en la interfaz.
2. El tema se aplica inmediatamente.
3. La preferencia se mantiene entre sesiones.

---

## 9. Roles y permisos

Cada usuario tiene un **rol** asignado. El rol determina qué puede ver y hacer en el sistema.

| Rol                                                  | Qué puede hacer                                                                                 | Qué ve                                                      |
| ---------------------------------------------------- | ----------------------------------------------------------------------------------------------- | ----------------------------------------------------------- |
| **Administrador**                                    | Todo: gestionar usuarios, clientes, estaciones, trabajos. Activar/desactivar mantenimiento      | Todos los contextos y módulos                               |
| **Usuario**                                          | Gestionar clientes, estaciones, trabajos, pedidos según permisos                                | Solo los contextos asignados                                |
| **Control de cierre**                                | Revisar trabajos terminados, cerrarlos o reabrirlos                                             | Todos los contextos (especializado en cierre)               |
| **Gestor <span style="color:#FF6B00">MOEVE</span>**  | Gestionar datos de <span style="color:#FF6B00">**Moeve**</span>: estaciones, trabajos, pedidos  | Solo contexto <span style="color:#FF6B00">**MOEVE**</span>  |
| **Gestor <span style="color:#DC2626">REPSOL</span>** | Gestionar datos de <span style="color:#DC2626">**Repsol**</span>: estaciones, trabajos, pedidos | Solo contexto <span style="color:#DC2626">**REPSOL**</span> |

### Permisos por módulo

| Módulo              | Permiso para consultar  | Permiso para crear/editar/eliminar    |
| ------------------- | ----------------------- | ------------------------------------- |
| Clientes / Empresas | _(incluido en gestión)_ | `empresas_contactos.gestionar`        |
| Estaciones          | `estaciones.ver`        | `estaciones.gestionar`                |
| Trabajos (Obras)    | `trabajos.ver`          | `trabajos.crear`, `trabajos.editar`   |
| Pedidos             | `pedidos.ver`           | `pedidos.gestionar`                   |
| Facturas            | `facturas.ver`          | `facturas.gestionar`                  |
| Legalizaciones      | `legalizaciones.ver`    | `legalizaciones.gestionar`            |
| Cierre de trabajos  | —                       | `trabajos.cerrar`, `trabajos.reabrir` |
| Usuarios            | —                       | `usuarios.gestionar`                  |

> **Error 403:** Si intenta acceder a una sección sin permiso, verá la página "Acceso no disponible para tu perfil".

---

## 10. Contextos: qué datos puedo ver

Un **contexto** es un ámbito de datos. Cada contexto corresponde a un cliente:

| ID  | Contexto                                      | Color   | Descripción                            |
| --- | --------------------------------------------- | ------- | -------------------------------------- |
| 1   | <span style="color:#FF6B00">**MOEVE**</span>  | Naranja | Datos y operaciones del cliente Moeve  |
| 2   | <span style="color:#DC2626">**REPSOL**</span> | Rojo    | Datos y operaciones del cliente Repsol |
| 3   | CIETE                                         | —       | Datos internos de Ciete Ingenieros     |

**Regla fundamental:** Solo puede ver datos de los contextos que tiene asignados.

| Si su usuario es... | Ve datos de...                                                                                              | NO ve datos de...                                     |
| ------------------- | ----------------------------------------------------------------------------------------------------------- | ----------------------------------------------------- |
| admin@ciete.es      | Todo (<span style="color:#FF6B00">**MOEVE**</span> + <span style="color:#DC2626">**REPSOL**</span> + CIETE) | —                                                     |
| cesar@ciete.es      | Todo (<span style="color:#FF6B00">**MOEVE**</span> + <span style="color:#DC2626">**REPSOL**</span> + CIETE) | —                                                     |
| usuario@ciete.es    | <span style="color:#FF6B00">**MOEVE**</span> + <span style="color:#DC2626">**REPSOL**</span>                | CIETE                                                 |
| moeve@ciete.es      | Solo <span style="color:#FF6B00">**MOEVE**</span>                                                           | <span style="color:#DC2626">**REPSOL**</span> y CIETE |
| repsol@ciete.es     | Solo <span style="color:#DC2626">**REPSOL**</span>                                                          | <span style="color:#FF6B00">**MOEVE**</span> y CIETE  |

Esto significa que si accede al listado de Clientes o Estaciones, solo verá las empresas y estaciones de sus contextos. Un gestor de <span style="color:#FF6B00">**Moeve**</span> nunca verá datos de <span style="color:#DC2626">**Repsol**</span>.

---

## 11. Panel de Administración (solo admin)

**Cómo llegar:** Solo visible si es admin. Sidebar → General → o desde Dashboard → **Panel de Administración**.

El panel de administración muestra una visión general del sistema:

- **7 tarjetas de métricas:** obras en curso, usuarios, pedidos pendientes, facturación, legalizaciones, clientes y estaciones registrados.
- **Tarjeta de usuario:** su nombre, email y contexto actual.
- **Modo mantenimiento:** estado actual (activado/desactivado) y botones para activar/desactivar.
- **Módulos del sistema:** enlaces rápidos a cada módulo.
- **Actividad reciente:** últimas acciones realizadas en el sistema.
- **Usuarios activos hoy:** lista de usuarios que han accedido.

### Modo mantenimiento desde el panel admin

Ver [sección 13](#13-modo-mantenimiento) para instrucciones detalladas.

---

## 12. Panel de Cierre (solo rol cierre)

**Cómo llegar:** Solo visible si tiene rol "Control de cierre". Sidebar → General → **Panel de cierre**.

El panel de cierre es el centro de control para la revisión y cierre definitivo de trabajos:

- **Filtros:** por contexto (<span style="color:#FF6B00">**MOEVE**</span>/<span style="color:#DC2626">**REPSOL**</span>/Todos), estado y búsqueda rápida.
- **Métricas:** pendientes de cierre, pendientes de revisión, listos para cerrar, bloqueados, cerrados hoy.
- **Tabla de trabajos:** con columnas de cliente, estación, nº aviso, nº pedido, tipo de trabajo, responsable, fechas, importes, estado de legalización y estado de cierre.
- **Acciones:** revisar, validar, cerrar, reabrir un trabajo.
- **Detalle de revisión:** al pulsar "Revisar" en un trabajo, se abre un panel lateral con:
    - Datos base del trabajo.
    - Estado del flujo (encargo → pedido → ejecución → terminado → revisión → cerrado).
    - Control económico (importe pedido vs importe trabajo).
    - Estado de legalización.
    - Checklist de validación (9 puntos bloqueantes + 1 informativo).
    - Trazabilidad: quién y cuándo marcó terminado, cerró, reabrió.
    - Botón de acción final (cerrar o reabrir).
- **Exportar:** puede exportar trabajos pendientes o bloqueados a CSV.

---

## 13. Modo mantenimiento

El modo mantenimiento permite al administrador poner el ERP en estado de "no disponible" mientras se realizan tareas técnicas.

### ¿Qué ocurre cuando el mantenimiento está activo?

- Los usuarios **no admin** que accedan al sistema verán una **página de mantenimiento** indicando que el sistema está temporalmente no disponible.
- Los usuarios **admin** pueden seguir usando el sistema con normalidad (no se ven afectados).
- El login sigue funcionando para todos, pero tras identificarse los usuarios no-admin son redirigidos a la página de mantenimiento.

### Cómo activar/desactivar (solo admin)

1. Inicie sesión como admin.
2. Vaya al **Panel de Administración** (sidebar → General o vía enlace en el dashboard).
3. En la sección **"Modo mantenimiento"**, verá el estado actual:
    - Badge verde: "Modo mantenimiento desactivado" → normal.
    - Badge rojo: "Modo mantenimiento activado" → en mantenimiento.
4. Pulse el botón:
    - **"Activar mantenimiento"** → activa el modo. Los usuarios no-admin verán la página de mantenimiento.
    - **"Desactivar mantenimiento"** → vuelve a la normalidad.

### Página de mantenimiento (lo que ve el usuario)

Cuando un usuario no-admin accede durante el mantenimiento, ve:

- Logo de Ciete.
- Badge animado "Modo mantenimiento".
- Título: "El ERP está en mantenimiento".
- Instrucciones: "Si necesitas acceso urgente, contacta con el administrador del sistema."
- Botón para cerrar sesión.

---

## 14. Módulos en desarrollo

Los siguientes módulos están siendo implementados y estarán disponibles próximamente:

| Módulo                | Qué permitirá hacer                                                                                                                                                                           | Disponibilidad prevista |
| --------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ----------------------- |
| **Obras (Trabajos)**  | Crear y gestionar trabajos: asignación, seguimiento, estado, cierre. Campos diferentes según sea <span style="color:#FF6B00">**MOEVE**</span> o <span style="color:#DC2626">**REPSOL**</span> | Semana del 13 de abril  |
| **Pedidos**           | Pedidos de material/servicio vinculados a trabajos, con items de tarifa                                                                                                                       | Semana del 20 de abril  |
| **Facturación**       | Facturas por trabajo. <span style="color:#FF6B00">**MOEVE**</span>: doble numeración (factura + factura CCP). <span style="color:#DC2626">**REPSOL**</span>: 1ª y 2ª factura                  | Semana del 20 de abril  |
| **Legalizaciones**    | Legalizaciones por trabajo con flujo de comentarios                                                                                                                                           | Semana del 27 de abril  |
| **Control de cierre** | Flujo completo de cierre/reapertura con validación checklist                                                                                                                                  | Semana del 4 de mayo    |
| **Cobros**            | Registro de cobros contra facturas                                                                                                                                                            | Semana del 4 de mayo    |
| **Presupuestos**      | Presupuestos con líneas de detalle                                                                                                                                                            | Semana del 4 de mayo    |
| **Tarifarios**        | Tabla de tarifas (<span style="color:#DC2626">**Repsol**</span>: 370 líneas)                                                                                                                  | Semana del 11 de mayo   |
| **Contratos**         | Contratos marco y directos                                                                                                                                                                    | Semana del 11 de mayo   |
| **Informes**          | Informes con gráficos y exportación a Excel/CSV                                                                                                                                               | Semana del 11 de mayo   |
| **Importación Excel** | Carga masiva de estaciones desde Excel <span style="color:#FF6B00">**Moeve**</span>/<span style="color:#DC2626">**Repsol**</span>                                                             | Semana del 18 de mayo   |
| **Ayuda in-app**      | Páginas de ayuda accesibles desde el dashboard, con instrucciones según su rol                                                                                                                | Semana del 25 de mayo   |

---

## 15. Preguntas frecuentes

### No veo ningún dato en Clientes / Estaciones

Su usuario puede tener acceso limitado a ciertos contextos. Ejemplo: si es `moeve@ciete.es`, solo verá empresas y estaciones del contexto <span style="color:#FF6B00">**MOEVE**</span>. Las de <span style="color:#DC2626">**REPSOL**</span> no aparecerán.

### No me aparece el enlace de Clientes / Estaciones en el sidebar

Su rol no tiene permisos para ese módulo. Contacte con el administrador.

### Intento acceder a una página y me da error 403

Significa "Acceso denegado". Su sesión no tiene permisos suficientes. Esto es normal si intenta acceder a una URL directa de un módulo al que no tiene acceso.

### No me llega el correo de recuperación de contraseña

1. Revise la carpeta de **spam** o **correo no deseado**.
2. Verifique que el correo introducido es el mismo con el que se registró.
3. Si sigue sin llegar, contacte con el administrador.

### ¿Puedo cambiar mi rol o permisos?

No. Los roles y permisos son asignados por el administrador. No puede cambiarlos usted mismo.

### ¿Qué diferencia hay entre "ver" y "gestionar" estaciones?

- **Ver:** Puede consultar la lista y detalles, pero NO modificar nada.
- **Gestionar:** Puede crear, editar y eliminar además de consultar.

### ¿Qué significan los estados de una estación?

| Estado        | Significado                           |
| ------------- | ------------------------------------- |
| **Operativa** | Estación en funcionamiento normal     |
| **En obras**  | Estación con trabajos en curso        |
| **Cerrada**   | Estación temporalmente cerrada        |
| **Baja**      | Estación dada de baja definitivamente |

### ¿Qué significa "activo" en clientes y estaciones?

El campo **activo** indica si el registro está vigente. Un registro inactivo no aparece en los desplegables de selección pero sigue guardado en la base de datos.

### ¿Puedo usar el ERP desde el móvil?

Sí. La interfaz es **responsive** y se adapta a tablet y móvil, aunque la experiencia óptima es en escritorio.

### ¿Qué hago si veo la página de mantenimiento?

El administrador ha puesto el sistema en modo mantenimiento. No puede hacer nada hasta que lo desactive. Si necesita acceso urgente, contacte con el administrador.

### ¿Cómo sé si el ERP se ha actualizado?

Las actualizaciones se despliegan automáticamente. Basta con recargar la página (F5) para ver la última versión.

### ¿Dónde reporto un problema?

Contacte con el equipo de soporte o informe directamente a Pablo Sevillano.

---

_Documento generado el 13/04/2026 — ERP Ciete v1.0_
