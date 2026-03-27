# Sprint 1 FRONT - Tutorial de mantenimiento i18n (ES/EN)

## Objetivo
Este documento define como crear nuevas paginas, agregar elementos nuevos y modificar paginas existentes sin romper el sistema de idiomas del ERP.

## Alcance
- Frontend React + Inertia.
- Idiomas activos: `es` y `en`.
- Selector de idioma global en navbar y en vistas guest.

## Arquitectura actual (referencia rapida)
- Provider global: `resources/js/i18n/index.js`
- Diccionarios:
  - `resources/js/i18n/locales/es.js`
  - `resources/js/i18n/locales/en.js`
- Integracion principal:
  - `resources/js/app.jsx` (envuelve toda la app con `I18nProvider`)
  - `resources/js/Components/PortalNavbar.jsx` (selector global en navbar)
  - `resources/js/Components/LanguageSelector.jsx` (componente reutilizable de idioma)
  - `resources/js/Components/GlobalPreferenceSelectors.jsx` (tema + idioma)

## Regla base obligatoria
No escribir textos visibles hardcodeados en JSX.
Siempre usar `t('clave')` desde `useI18n()`.

## Flujo estandar para NUEVA pagina
1. Crear la pagina en `resources/js/Pages/...`.
2. Importar hook:

```jsx
import { useI18n } from '@/i18n';
```

3. Dentro del componente:

```jsx
const { t } = useI18n();
```

4. Usar `t()` para titulos, botones, labels, placeholders y mensajes:

```jsx
<Head title={t('projects.headTitle')} />
<h1>{t('projects.title')}</h1>
<button>{t('projects.actions.create')}</button>
```

5. Crear las claves en ambos diccionarios:
- `resources/js/i18n/locales/es.js`
- `resources/js/i18n/locales/en.js`

## Flujo estandar para NUEVO elemento en pagina existente
Cuando agregues un campo, boton, tarjeta o texto nuevo:
1. Definir clave semantica en `es.js` y `en.js`.
2. Reemplazar literal en JSX por `t('...')`.
3. Si el texto lleva variable dinamica, usar interpolacion:

```jsx
// diccionario: welcomeUser: 'Bienvenido, {name}'
{t('dashboard.welcomeUser', { name: user.nombre_usuario })}
```

## Flujo estandar para MODIFICAR textos existentes
1. Buscar clave actual en diccionarios.
2. Editar valor en `es.js` y `en.js`.
3. No tocar el JSX si la clave ya existe.

Resultado: cambio centralizado y seguro.

## Convencion de claves (obligatoria)
Agrupar por dominio funcional. Ejemplos actuales:
- `common.*`
- `nav.*`
- `auth.login.*`
- `auth.register.*`
- `dashboard.*`
- `profile.updatePassword.*`

Para nuevas paginas usar bloque propio:
- `projects.*`
- `companies.*`
- `contacts.*`

## Plantilla recomendada para nueva seccion en diccionarios
```js
projects: {
    headTitle: 'Proyectos',
    title: 'Gestion de proyectos',
    emptyState: 'No hay proyectos disponibles.',
    actions: {
        create: 'Crear proyecto',
        export: 'Exportar',
    },
}
```

(Replicar estructura equivalente en `en.js`)

## Uso del selector de idioma
- Navbar autenticada: `PortalNavbar` ya incluye `GlobalPreferenceSelectors`.
- Vistas guest: `GuestLayout` ya incluye `GlobalPreferenceSelectors`.
- Login: usa `GlobalPreferenceSelectors` para mantener acceso inmediato.

No duplicar selectores en paginas hijas si ya vienen del layout.

## Checklist de calidad antes de cerrar tarea
1. Todo texto visible pasa por `t()`.
2. Todas las claves nuevas existen en `es.js` y `en.js`.
3. No hay claves huertanas (definidas y no usadas) en cambios nuevos.
4. El cambio funciona en ES y EN (revisar navbar, botones, titulos y formularios).
5. No hay JSX dentro de archivos `.js` (si hay JSX, usar `.jsx` o JS puro sin JSX).

## Errores frecuentes y como evitarlos
### 1) Error Vite import-analysis por sintaxis invalida
Causa comun: JSX dentro de un archivo `.js`.
Solucion:
- Renombrar a `.jsx`, o
- Mantener `.js` sin JSX (ejemplo: usar `createElement` en vez de `<Provider>`).

### 2) Falta traduccion en un idioma
Si falta clave, `t()` devuelve la clave textual.
Accion: agregar la clave en ambos diccionarios.

### 3) Clave mal nombrada
Usar nombres claros y estables por dominio funcional.
Evitar claves genericas tipo `text1`, `label2`, etc.

## Ejemplo completo minimo (nueva pagina)
```jsx
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useI18n } from '@/i18n';
import { Head } from '@inertiajs/react';

export default function ProjectsIndex() {
    const { t } = useI18n();

    return (
        <AuthenticatedLayout
            header={<h2>{t('projects.title')}</h2>}
        >
            <Head title={t('projects.headTitle')} />
            <p>{t('projects.emptyState')}</p>
        </AuthenticatedLayout>
    );
}
```

## Definicion de terminado (DoD) para i18n
Una pagina se considera lista cuando:
1. Cambia correctamente entre ES y EN desde el selector.
2. No contiene textos hardcodeados visibles.
3. Tiene todas sus claves en ambos diccionarios.
4. Mantiene consistencia con layouts y componentes globales.
