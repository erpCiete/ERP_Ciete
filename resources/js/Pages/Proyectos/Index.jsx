import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useI18n } from '@/i18n';
import { Head, usePage } from '@inertiajs/react';

export default function ProyectosIndex() {
    const user = usePage().props.auth.user;
    const { t } = useI18n();

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold leading-tight text-(--ciete-slate)">{t('proyectos.header')}</h2>}
        >
            <Head title={t('proyectos.headTitle')} />

            <div className="ciete-panel mx-auto max-w-4xl space-y-4">
                <h3 className="text-2xl font-semibold text-(--ciete-slate)">
                    {t('proyectos.title')}
                </h3>
                <p className="text-sm text-text-muted">{t('proyectos.description')}</p>
                <p className="text-sm text-text-hint">
                    {user.is_admin ? t('proyectos.accessAsAdmin') : t('proyectos.accessAsAuthorized')}
                </p>

                <div className="rounded-2xl border border-border p-4">
                    <p className="text-xs uppercase tracking-[0.16em] text-text-muted">{t('proyectos.userPermissions')}</p>
                    <div className="mt-3 flex flex-wrap gap-2">
                        {user.permission_slugs.map((permission) => (
                            <span
                                key={permission}
                                className="rounded-full border border-border px-3 py-1 text-sm text-text-main"
                            >
                                {permission}
                            </span>
                        ))}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
