# Guía de estilos y UI

## Stack de estilos

- **Tailwind CSS 4** con configuración personalizada en `tailwind.config.js`
- **Variables CSS** para los colores de marca en `:root`
- **Modo oscuro** disponible (toggle en cabecera)
- **i18n**: español (es) y inglés (en) en `resources/js/i18n/locales/`

## Colores de marca

```css
--ciete-red: color principal (botones, foco, acciones)
--ciete-red-dark: hover del rojo
--ciete-slate: color de texto de cabeceras
--color-client-moeve: identificador visual MOEVE
--color-client-repsol: identificador visual REPSOL
--color-client-others: identificador visual OTROS
```

## Componentes principales

### TrabajosExcelView.jsx
La tabla principal de trabajos. Funciona como una hoja de cálculo:
- Edición inline por celda (clic → edita → Tab/Enter para confirmar)
- Buscador global y filtros por estado, responsable, estación, municipio, provincia
- Ordenación por columna (3 clics: ASC → DESC → sin ordenar)
- Cancelados siempre al final del listado
- La tabla no tiene sidebar para aprovechar el ancho completo de pantalla

### ConflictDialog
Modal de conflicto de concurrencia. Aparece cuando el guardado optimista detecta que otro usuario modificó el mismo campo en los últimos 60 minutos. Se renderiza como modal global sobre toda la aplicación para quedar por encima de tablas, overlays y modales secundarios. Muestra:
- Campo afectado
- Quién lo modificó y cuándo
- Valor anterior guardado antes del cambio de la otra persona
- Valor actual en servidor
- Campo editable con el valor que el usuario intentaba guardar
- Botones: Cancelar (cierra la alerta y conserva lo escrito) y Guardar cambios (reintenta con el texto actual)

### ObservacionesModal
Modal inline para editar las observaciones de un trabajo. Si se cancela el ConflictDialog desde aquí, el borrador del usuario se conserva y puede seguir editando. El mismo patrón se aplica a `descripcion_trabajo` y al resto de campos editables de trabajos.

### CorreoMoeveModal
Modal "Preparar correo Moeve" en la ficha del pedido. Genera asunto y cuerpo copiables, botones de exportación y checklist de envío.

## Modo Ciete Excel vs Modo Moderno

**Ciete Excel** es el modo por defecto para todos los usuarios. La preferencia se guarda en `usuarios.interface_mode`; si el usuario cambia manualmente a Ciete Moderno desde `/profile`, esa elección se mantiene en sesiones posteriores y no se pisa por el frontend.

- **Ciete Excel**: tabla densa editable inline, vista de hoja de cálculo. La recomendada para uso diario.
- **Ciete Moderno**: fichas individuales con formularios detallados. Útil para ediciones complejas.

## Sidebar

La sidebar muestra el menú contextual por rol. En la pantalla de Trabajos, la sidebar está **oculta por defecto** para aprovechar el ancho completo de la tabla. El usuario la abre con el botón de hamburguesa.

## Internacionalización

Los textos están en `resources/js/i18n/locales/es.js` y `en.js`. El idioma se cambia desde el panel de preferencias (cabecera). Si necesitas añadir un texto nuevo:

1. Añade la clave en `es.js` (y en `en.js` si aplica)
2. Usa el hook `useI18n()` → `const { t } = useI18n()`
3. Usa `t('clave.del.texto')` en el JSX
