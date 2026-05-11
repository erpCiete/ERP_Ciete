# Sprint 1 FRONT - Tutorial de mantenimiento de tema (claro/oscuro)

> **Documento histórico.**  
> Este documento refleja una decisión, planificación o análisis anterior del proyecto.  
> Puede contener nombres, estados, modelos o prioridades ya superadas.  
> Fuente de verdad vigente: `docs/02_CLIENTE/DECISIONES_FUNCIONALES_ERP_CIETE_2026-05-03.md`.

## Objetivo
Este documento define como crear nuevas paginas y componentes sin romper el sistema global de tema claro/oscuro del ERP.

## Alcance
- Frontend React + Inertia.
- Modos activos: `light` y `dark`.
- Selector de tema global junto al selector de idioma.
- Persistencia local en navegador con `localStorage`.

## Arquitectura actual (referencia rapida)
- Variables globales:
  - `resources/css/variables.css`
  - `:root` (tema claro)
  - `[data-theme='dark']` (tema oscuro)
- Provider global:
  - `resources/js/theme/index.js`
- Integracion principal:
  - `resources/js/app.jsx` (envuelve la app con `ThemeProvider`)
  - `resources/views/app.blade.php` (inicializacion temprana para evitar parpadeo)
  - `resources/js/Components/ThemeSelector.jsx` (selector reutilizable)
  - `resources/js/Components/GlobalPreferenceSelectors.jsx` (tema + idioma)

## Regla base obligatoria
No usar colores hardcodeados en JSX (`bg-white`, `text-gray-*`, `border-slate-*`, etc.).
Siempre usar tokens de color conectados a variables CSS.

## Tokens recomendados para nuevas paginas
- Fondo base de pantalla: `bg-page`
- Superficie de tarjetas/bloques: `bg-surface`
- Superficie secundaria: `bg-surface-2`
- Borde estandar: `border-border`
- Borde fuerte: `border-border-heavy`
- Texto principal: `text-text-main`
- Texto secundario: `text-text-muted`
- Texto de apoyo: `text-text-hint`
- Accion principal: `bg-primary`, `hover:bg-primary-hover`

## Flujo estandar para NUEVA pagina
1. Crear pagina en `resources/js/Pages/...`.
2. Usar layout global (`AuthenticatedLayout` o `GuestLayout`) para heredar tema y selectores globales.
3. Aplicar solo clases de token en fondos, textos y bordes.
4. Evitar colores fijos salvo casos de marca puntuales ya definidos en variables.

## Ejemplo minimo (nueva pagina)
```jsx
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { useI18n } from '@/i18n';

export default function ProjectsIndex() {
    const { t } = useI18n();

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold text-text-main">{t('projects.title')}</h2>}
        >
            <Head title={t('projects.headTitle')} />

            <section className="rounded-xl border border-border bg-surface p-6">
                <p className="text-sm text-text-muted">{t('projects.emptyState')}</p>
            </section>
        </AuthenticatedLayout>
    );
}
```

## Uso del selector de tema
- Navbar publica/autenticada: `PortalNavbar` usa `GlobalPreferenceSelectors`.
- Vistas guest: `GuestLayout` usa `GlobalPreferenceSelectors`.
- Login: usa `GlobalPreferenceSelectors` en la esquina superior derecha.

No duplicar controles en paginas hijas si ya vienen del layout.

## Flujo estandar para NUEVO componente
1. Estructura visual con tokens (`bg-surface`, `border-border`, `text-text-main`).
2. Estados hover/focus usando tokens y no grises fijos.
3. Verificar contraste en ambos modos.

## Como funciona la persistencia
- Clave local: `ciete.theme`.
- Valor guardado: `light` o `dark`.
- El provider actualiza `document.documentElement[data-theme]`.
- `app.blade.php` aplica tema antes de hidratar React para evitar FOUC.

## Checklist de calidad antes de cerrar tarea
1. La pantalla se ve correcta en `light` y `dark`.
2. No hay colores hardcodeados en JSX nuevo.
3. El selector de tema aparece al lado del selector de idioma (si aplica layout global).
4. No se rompe legibilidad de textos ni contraste de botones.
5. El tema elegido persiste tras recargar navegador.

## Errores frecuentes y como evitarlos
### 1) Mezclar tokens con grises fijos
Causa: usar clases de Tailwind por defecto (`text-gray-*`, `bg-white`) en componentes nuevos.
Solucion: usar `text-text-*`, `bg-surface`, `bg-page`, `border-border`.

### 2) Selector duplicado
Causa: agregar selector dentro de pagina que ya usa layout con selector global.
Solucion: dejarlo solo en componentes globales (`PortalNavbar`, `GuestLayout`, `Login`).

### 3) Parpadeo de tema al cargar
Causa: no inicializar tema antes de montar React.
Solucion: mantener el script de inicializacion en `resources/views/app.blade.php`.

## Definicion de terminado (DoD) para tema
Una pagina se considera lista cuando:
1. Respeta tokens globales de color.
2. Funciona en claro y oscuro sin ajustes aislados por pantalla.
3. No introduce hardcodes de color que rompan el cambio global.
4. Mantiene integracion con controles globales de preferencia (tema + idioma).
