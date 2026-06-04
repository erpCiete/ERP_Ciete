> Documento preparado para impresion en blanco y negro.
>
> ERP CIETE v2.1.0
> Documento de apoyo para reunion
> Entorno: http://130.110.232.84/

---

# ERP CIETE v2.1.0

## Tutorial practico de uso

Datos de cabecera:

- Entorno: http://130.110.232.84/
- Version: ERP CIETE v2.1.0
- Finalidad: guia practica para revision del flujo diario

---

## Indice

1. Introduccion
2. Acceso al sistema
3. Pantalla de inicio
4. Contextos de trabajo
5. Roles y permisos
6. Ciete Excel y Ciete Moderno
7. Trabajos
8. Pedidos e items
9. Facturas
10. Maestros
11. Panel de cierre
12. Administracion tecnica
13. Mensajes internos y estado del sistema
14. Recorridos recomendados de demostracion
15. Buenas practicas
16. Preguntas frecuentes

---

## 1. Introduccion

El ERP CIETE centraliza el flujo operativo diario en una unica plataforma. El eje de trabajo es el registro de trabajos y su evolucion hasta el cierre funcional.

Esta version utiliza datos reales de origen para validar el recorrido completo:

- trabajo;
- pedido;
- items;
- factura;
- revision y cierre.

Esta guia acompana la demostracion practica y tambien sirve como referencia posterior en papel.

> [INFO] El objetivo del documento es facilitar una lectura clara en reunion y una consulta rapida despues de la sesion.

---

## 2. Acceso al sistema

1. Abrir navegador y entrar a: http://130.110.232.84/
2. Iniciar sesion con usuario habilitado.
3. Verificar pantalla de inicio comun.
4. Revisar perfil de usuario activo.
5. Cerrar sesion desde el menu de perfil al finalizar.

[PENDIENTE] No incluir ni compartir claves de acceso en documentos impresos.

---

## 3. Pantalla de inicio

La pantalla de inicio funciona como punto de coordinacion operativa.

Elementos habituales:

- mensaje destacado;
- avisos internos;
- actualizaciones del sistema;
- novedades de empresa.

Utilidad:

- comunicar avisos comunes al equipo;
- recordar prioridades del dia;
- dar visibilidad de cambios de operacion.

---

## 4. Contextos de trabajo

Contextos disponibles:

- MOEVE;
- REPSOL;
- OTROS CLIENTES.

Reglas de uso:

1. Trabajar siempre dentro de un contexto activo.
2. Cambiar de contexto solo cuando sea necesario.
3. Confirmar el contexto antes de crear o editar datos.

Cada contexto mantiene separacion de datos. No se utiliza una vista global para operativa de alta o edicion.

---

## 5. Roles y permisos

### 5.1 Direccion

Que ve:

- panel de cierre;
- trazabilidad;
- estado funcional de pendientes.

Que puede hacer:

- revisar circuito completo y casos pendientes;
- validar avance de cierre operativo.

Que no debe hacer:

- operar tareas de mantenimiento tecnico diario.

Para que sirve en el flujo diario:

- control funcional y seguimiento de cierre.

### 5.2 Contabilidad

Que ve:

- pedidos, items y facturas;
- validaciones de sociedad/CIF.

Que puede hacer:

- revisar coherencia economica del pedido;
- gestionar facturacion parcial o completa.

Que no debe hacer:

- operar como perfil de ejecucion en campo si no esta asignado.

Para que sirve en el flujo diario:

- garantizar facturacion consistente y validada.

### 5.3 Ejecucion MOEVE

Que ve:

- trabajos y datos del contexto MOEVE.

Que puede hacer:

- gestionar flujo operativo de trabajos MOEVE.

Que no debe hacer:

- mezclar operativa de otros contextos.

Para que sirve en el flujo diario:

- registrar y actualizar actividad operativa de MOEVE.

### 5.4 Ejecucion REPSOL

Que ve:

- trabajos y campos propios de REPSOL.

Que puede hacer:

- gestionar actividad REPSOL con sus datos de origen.

Que no debe hacer:

- operar registros fuera de su alcance funcional asignado.

Para que sirve en el flujo diario:

- asegurar seguimiento ordenado de trabajos REPSOL.

### 5.5 Usuario multicontexto

Que ve:

- los contextos habilitados en su perfil.

Que puede hacer:

- cambiar entre contextos permitidos segun necesidad.

Que no debe hacer:

- asumir que los datos se comparten entre contextos.

Para que sirve en el flujo diario:

- facilitar continuidad cuando una funcion requiere revision en mas de un contexto.

### 5.6 Administracion tecnica

Que ve:

- usuarios;
- soporte;
- auditoria tecnica;
- mantenimiento;
- avisos y estado del sistema.

Que puede hacer:

- gestionar entorno de demostracion;
- coordinar soporte interno;
- publicar avisos operativos.

Que no debe hacer:

- sustituir la funcion de Direccion, Contabilidad o Ejecucion.

Para que sirve en el flujo diario:

- mantener disponibilidad y orden del entorno.

---

## 6. Ciete Excel y Ciete Moderno

### Ciete Excel

Uso recomendado:

- trabajo con muchos registros;
- tablas densas;
- paginacion rapida;
- opcion de ir a pagina;
- edicion por celda donde aplique.

Ventaja principal:

- velocidad de operacion en volumen.

### Ciete Moderno

Uso recomendado:

- revision visual;
- lectura clara por fichas y listados;
- apoyo para explicacion en reunion.

Ventaja principal:

- comprension funcional de casos concretos.

> [INFO] Ambos modos trabajan sobre el mismo dato de origen y el mismo flujo operativo.

---

## 7. Trabajos

Un trabajo es la unidad operativa principal del ERP.

Campos principales a revisar:

- estacion;
- contexto;
- estado;
- responsable;
- fechas;
- pedido asociado;
- observaciones;
- trazabilidad.

Estados principales:

- En curso;
- Terminado;
- Pendiente de facturar;
- Facturado;
- Finalizado;
- Cancelado.

[PASO] En demostracion, abrir un trabajo real, revisar estado y seguir su relacion con pedido e items.

---

## 8. Pedidos e items

Relacion funcional:

- el pedido se asocia al trabajo;
- los items representan lineas economicas;
- cada item define cantidad, concepto e importe.

Puntos de revision:

1. Coherencia entre trabajo y pedido.
2. Coherencia entre contexto y lineas economicas.
3. Revision de cantidades e importes.
4. Confirmacion de lineas tarifarias aplicadas.

---

## 9. Facturas

La factura se construye a partir de items del pedido.

Escenarios de uso:

- factura parcial;
- factura completa.

Validaciones funcionales:

- sociedad/CIF;
- relacion con pedido y trabajo;
- coherencia de importes por linea.

En los casos donde la fuente no aporta un numero completo, se utilizan referencias normalizadas de origen para mantener trazabilidad y continuidad de revision.

---

## 10. Maestros

Elementos clave:

- clientes y empresas;
- estaciones;
- contratos;
- sociedades facturadoras;
- tarifarios;
- lineas tarifarias.

Importancia operativa:

- los maestros sostienen la consistencia del flujo diario;
- si un dato base no esta actualizado, se debe revisar en maestro antes de continuar.

---

## 11. Panel de cierre

Uso principal:

- herramienta orientada a Direccion para revision operativa.

Que permite revisar:

- trabajos terminados;
- pendientes de pedido;
- pendientes de factura;
- facturas parciales;
- checklist de avance;
- trazabilidad por caso.

[PASO] Durante la reunion, mostrar un recorrido de pendientes y su criterio de cierre.

---

## 12. Administracion tecnica

Funciones principales:

- gestion de usuarios;
- soporte;
- auditoria tecnica;
- mantenimiento;
- avisos;
- estado del sistema.

Marco de uso:

- rol de soporte de entorno;
- no reemplaza funciones operativas de otras areas.

---

## 13. Mensajes internos y estado del sistema

Elementos de comunicacion:

- mensajes visibles en inicio;
- avisos internos;
- actualizaciones;
- novedades;
- estado del sistema.

Utilidad:

- seguimiento coordinado;
- comunicacion de puntos pendientes;
- visibilidad de avisos relevantes.

---

## 14. Recorridos recomendados de demostracion

### Recorrido 1 - Entrada general

1. Acceder al sistema.
2. Revisar inicio.
3. Explicar version y mensajes vigentes.

### Recorrido 2 - MOEVE

1. Cambiar contexto a MOEVE.
2. Abrir trabajos.
3. Revisar vista Ciete Excel.
4. Abrir vista Ciete Moderno.

### Recorrido 3 - REPSOL

1. Cambiar contexto a REPSOL.
2. Revisar campos especificos.
3. Explicar aviso, orden, tipo documento y tipo trabajo.

### Recorrido 4 - Contabilidad

1. Abrir pedidos.
2. Revisar items.
3. Abrir facturas.
4. Revisar sociedad/CIF.

### Recorrido 5 - Direccion

1. Abrir panel de cierre.
2. Revisar pendientes.
3. Explicar checklist de seguimiento.

### Recorrido 6 - Administracion tecnica

1. Abrir panel de administracion.
2. Revisar usuarios, soporte, avisos y estado del sistema.

---

## 15. Buenas practicas

- trabajar siempre en el contexto correcto;
- revisar mensajes de inicio;
- usar paginacion rapida en alto volumen;
- usar Maestros para corregir datos base;
- no forzar una factura si falta sociedad/CIF;
- consultar trazabilidad ante cambios recientes;
- usar soporte si hay incidencia;
- revisar antes de finalizar un circuito.

---

## 16. Preguntas frecuentes

**1) Que diferencia hay entre Ciete Excel y Ciete Moderno?**

Ciete Excel prioriza velocidad con volumen. Ciete Moderno prioriza lectura y revision visual.

**2) Por que no aparece una vista global para crear?**

Porque la operativa se controla por contexto para evitar cruces de datos.

**3) Que hago si no encuentro una estacion?**

Revisar Maestros de estaciones en el contexto correcto.

**4) Por que no aparece una sociedad/CIF?**

Validar contratos y sociedades facturadoras en Maestros.

**5) Que significa una factura parcial?**

Que se factura solo una parte de los items disponibles del pedido.

**6) Donde se revisan los trabajos pendientes?**

En paneles de listados operativos y en el panel de cierre para revision de Direccion.

**7) Donde se gestionan usuarios?**

En Administracion tecnica.

**8) Donde se ven los avisos?**

En la pantalla de inicio y en modulos de administracion habilitados.

**9) Como se cambia de contexto?**

Desde el selector de contexto del sistema, segun permisos del usuario.

**10) Que pasa si otra persona cambia un dato recientemente?**

Se revisa trazabilidad y, en vistas de alto volumen, se atienden los avisos de modificacion reciente para validar el dato antes de continuar.

---

## Capturas opcionales para incorporar despues

- Vista de inicio con mensaje destacado.
- Cambio de contexto MOEVE/REPSOL.
- Tabla de trabajos en Ciete Excel.
- Ficha de trabajo en Ciete Moderno.
- Pedido con items.
- Factura con validacion de sociedad/CIF.
- Panel de cierre con pendientes.
- Panel de administracion tecnica con estado del sistema.

---

Documento de apoyo para revision funcional del ERP CIETE v2.1.0.
