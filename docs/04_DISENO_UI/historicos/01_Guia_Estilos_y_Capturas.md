# ERP CIETE · Guía de estilos

> **Documento histórico.**  
> Este documento refleja una decisión, planificación o análisis anterior del proyecto.  
> Puede contener nombres, estados, modelos o prioridades ya superadas.  
> Fuente de verdad vigente: `docs/02_CLIENTE/DECISIONES_FUNCIONALES_ERP_CIETE_2026-05-03.md`.

Proyecto: ERP Ciete  
Documento: Guía de estilos UI/UX y capturas  
Versión: 1.0 (base)  
Fecha: Marzo 2026

## Autores

- Equipo 1 ERP Ciete

## Índice

1. Introducción
2. Concepto del producto
3. Estructura funcional de pantallas
4. Componentes de interfaz
5. Identidad visual
6. Tipografía
7. Uso de imágenes y capturas
8. Iconografía
9. Normas de contenido
10. Accesibilidad
11. Plantilla por pantalla
12. Cierre

## 1. Introducción

Este documento define la guía de estilos del ERP Ciete.
Su objetivo es mantener coherencia visual, funcional y de contenido en todas las pantallas del sistema.

La guía se usa como referencia para:

- Diseño de interfaces.
- Desarrollo frontend.
- Revisión funcional con cliente.
- Mantenimiento y evolución del producto.

## 2. Concepto del producto

ERP Ciete es una aplicación web interna para centralizar la gestión operativa y administrativa.

Pilares del producto:

- Trazabilidad completa del trabajo.
- Separación operativa por cliente principal.
- Control de estados y permisos.
- Claridad en seguimiento de obra, pedido y facturación.

## 3. Estructura funcional de pantallas

Pantallas base del ERP:

- Login.
- Dashboard.
- Obras (listado, detalle, edición).
- Pedidos.
- Estaciones.
- Legalizaciones y comentarios.
- Facturación.
- Informes.
- Usuarios y roles.

Norma de navegación:

- Menú principal estable.
- Breadcrumb en vistas de detalle.
- Acciones principales visibles en primer nivel.

## 4. Componentes de interfaz

Componentes mínimos obligatorios:

- Header y navegación lateral.
- Tablas de datos con filtros, búsqueda y orden.
- Formularios con validación visible.
- Badges de estado.
- Modales de confirmación.
- Alertas de éxito, aviso y error.
- Paginación y selector de tamaño de página.

Normas:

- Todas las tablas deben ser escaneables y consistentes.
- Todos los estados de proceso deben tener color y texto.
- Toda acción crítica debe pedir confirmación.

## 5. Identidad visual

La referencia visual de Ciete transmite una imagen técnica, sobria e institucional.
La web pública de la empresa prioriza el contenido, la estructura y la exposición de trabajos reales por encima del efecto visual decorativo.

El ERP debe heredar ese carácter profesional, pero adaptado a una interfaz moderna de gestión.

Principios visuales del ERP:

- Estética limpia y funcional.
- Predominio de fondos claros y neutros.
- Jerarquía visual basada en estructura, espaciado y contraste.
- Uso contenido del color corporativo para acciones, foco y estados importantes.
- Evitar efectos innecesarios, gradientes agresivos o recursos puramente ornamentales.

Criterios base:

- Fondo principal: blanco o gris muy claro.
- Superficies secundarias: gris técnico suave.
- Texto principal: gris muy oscuro o negro suavizado.
- Bordes: finos, discretos y constantes.
- Sombras: suaves y cortas, solo para separar capas funcionales.
- Radio de borde: moderado, sin aspecto excesivamente redondeado.

Aplicación del color:

- El color corporativo de énfasis debe reservarse para:
    - Botón principal.
    - Elemento activo en navegación.
    - Indicadores de selección.
    - Estados críticos o prioritarios.

Paleta funcional recomendada:

- Color corporativo principal: rojo CIETE.
- Neutro base: blanco.
- Neutro suave: gris muy claro.
- Texto principal: grafito oscuro.
- Texto secundario: gris medio.
- Borde: gris claro técnico.

Estados semánticos recomendados:

- Pendiente: ámbar.
- En curso: azul.
- Terminado: verde.
- Facturado: verde azulado o azul petróleo.
- Cerrado: gris oscuro.
- Bloqueado: rojo oscuro.

Norma general:

La interfaz debe parecer una herramienta de trabajo seria y precisa, no una web comercial.

## 6. Tipografía

La referencia pública de Ciete proyecta una lectura sobria, técnica y corporativa.
Por coherencia con esa identidad, la tipografía del ERP debe priorizar legibilidad, neutralidad y orden visual.

### Tipografía principal del ERP

Se define como tipografía principal una sans serif limpia, moderna y de alta legibilidad en pantalla.

Configuración recomendada:

- Tipografía principal: `Inter`
- Tipografías de respaldo: `Segoe UI`, `Roboto`, `Arial`, `sans-serif`

Motivo de elección:

- Mantiene un tono serio y técnico.
- Mejora la lectura en tablas, formularios y paneles.
- Funciona bien en tamaños pequeños y medianos.
- Aporta una actualización visual sin romper el carácter corporativo.

### Jerarquía tipográfica

- H1: 30-32 px / peso 700
- H2: 24-26 px / peso 700
- H3: 18-20 px / peso 600
- Texto base: 15-16 px / peso 400-500
- Etiquetas de formulario: 13-14 px / peso 600
- Texto de ayuda o soporte: 12-13 px / peso 400
- Texto de tabla: 14 px / peso 400-500
- Badges y estados: 12-13 px / peso 600

### Normas tipográficas

- Priorizar siempre legibilidad frente a estilo.
- Mantener una única familia tipográfica en toda la aplicación.
- Usar pesos distintos para jerarquía, no familias distintas.
- Evitar mayúsculas sostenidas en bloques largos.
- Reservar mayúsculas para siglas, estados muy cortos o etiquetas concretas.
- Mantener interlineado cómodo en vistas con mucho dato.
- En tablas y campos numéricos, favorecer alineación clara y lectura rápida.

### Estilo de tono visual

La tipografía del ERP debe transmitir:

- orden,
- fiabilidad,
- precisión,
- contexto técnico,
- sensación de control.

## 7. Uso de imágenes y capturas

La referencia pública de Ciete apoya su credibilidad en imágenes reales de proyectos y obras ejecutadas.
En el ERP, la evidencia visual principal no serán fotografías decorativas, sino capturas funcionales del propio sistema.

Ubicación oficial de capturas:

- `docs/04_DISENO_UI/02_CAPTURAS_DISENO/`

Convención de nombres:

- `modulo_pantalla_vNN.ext`
- Ejemplos:
    - `obras_listado_v01.png`
    - `pedidos_formulario_v02.png`
    - `dashboard_admin_v03.png`

Normas de uso:

- Una captura por estado relevante.
- Priorizar capturas útiles para validar estructura, flujo y funcionalidad.
- No usar imágenes genéricas como sustituto de una captura real.
- No subir capturas con datos sensibles reales.
- Las imágenes de obra o ingeniería solo podrán usarse en:
    - login,
    - dashboard institucional,
    - módulos de presentación,
    - documentación de contexto.

Estados mínimos a capturar:

- Vista vacía.
- Vista con datos.
- Vista con filtros activos.
- Vista con error o validación.
- Vista responsive si aplica.

## 8. Iconografía

La web pública de Ciete no basa su identidad en iconos protagonistas, sino en texto y contenido técnico.
Por eso, en el ERP la iconografía debe tener una función de apoyo, nunca de adorno.

### Librería oficial

Se recomienda usar una única librería de iconos para toda la aplicación.

Librería recomendada:

- `Lucide`

Motivos:

- Estilo limpio y técnico.
- Trazo fino y consistente.
- Muy adecuada para interfaces de gestión.
- Buena integración con React.

### Principios de uso

- El icono acompaña al texto, no lo sustituye.
- En navegación principal siempre debe haber texto visible.
- Mantener un único estilo de icono en todo el sistema.
- No mezclar iconos de trazo con iconos rellenos.
- No usar iconos decorativos sin función real.

### Tamaños recomendados

- Navegación lateral: 20 px
- Botones con texto: 16 px
- Cabeceras de bloque: 18-20 px
- Indicadores pequeños o acciones en tabla: 14-16 px

### Usos permitidos

- Navegación principal.
- Acciones de tabla.
- Estados visuales.
- Alertas.
- Filtros.
- Adjuntos, comentarios, documentos, usuarios, pedidos, facturas, informes.

### Usos a evitar

- Sustituir textos importantes por solo iconos.
- Colocar iconos distintos para acciones equivalentes.
- Sobrecargar cards, tablas o formularios con exceso de iconografía.
- Usar iconos de alerta en acciones no críticas.

### Criterio visual

La iconografía del ERP debe sentirse:

- clara,
- ligera,
- funcional,
- consistente,
- secundaria frente al dato.

## Nota de diseño

La web pública de Ciete sirve como referencia de tono corporativo y seriedad técnica.
El ERP no debe copiar literalmente su estética web informativa, sino traducir su identidad a una interfaz de trabajo más clara, más rápida y más operativa.

## 9. Normas de contenido

Reglas para textos de interfaz:

- Lenguaje claro y directo.
- Etiquetas cortas y consistentes.
- Mensajes de error accionables.
- Botones con verbos concretos.

Ejemplos:

- Correcto: `Guardar cambios`, `Cerrar obra`, `Ver factura`.
- Evitar: `Haz clic aquí`, `Continuar`, `Aceptar` (sin contexto).

## 10. Accesibilidad

Criterios mínimos:

- Navegación por teclado.
- Foco visible.
- Labels asociados a campos.
- Contraste suficiente texto/fondo.
- Mensajes de error comprensibles.

## 11. Plantilla por pantalla

Para cada pantalla nueva documentar:

- Objetivo de la pantalla.
- Campos y acciones.
- Estados posibles.
- Reglas de validación.
- Permisos por rol.
- Capturas asociadas.

## 12. Cierre

Toda nueva pantalla o componente del ERP debe respetar esta guía para mantener coherencia visual y funcional en el sistema completo.
