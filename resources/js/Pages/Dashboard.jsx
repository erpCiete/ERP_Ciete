import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useI18n } from '@/i18n';
import { Head, Link, usePage } from '@inertiajs/react';

function resolveStatusKey(status) {
    const normalized = String(status ?? '').trim().toLowerCase();
    if (['en curso', 'in progress', 'en_curso'].includes(normalized)) return 'inProgress';
    if (['aprobado', 'approved', 'terminado'].includes(normalized)) return 'approved';
    if (['pendiente', 'pending', 'borrador'].includes(normalized)) return 'pending';
    if (['por validar', 'to validate'].includes(normalized)) return 'toValidate';
    if (['urgente', 'urgent'].includes(normalized)) return 'urgent';
    if (['activo', 'active'].includes(normalized)) return 'active';
    return null;
}

function statusBadgeClass(statusKey) {
    const map = {
        inProgress: 'bg-state-done-bg text-state-done-text',
        approved: 'bg-state-done-bg text-state-done-text',
        pending: 'bg-state-pending-bg text-state-pending-text',
        toValidate: 'bg-state-pending-bg text-state-pending-text',
        urgent: 'bg-state-blocked-bg text-state-blocked-text',
        active: 'bg-state-done-bg text-state-done-text',
    };
    return map[statusKey] ?? 'bg-surface-2 text-text-muted';
}

export default function Dashboard({ obras = [], pedidos = [], legalizaciones = [] }) {
    const user = usePage().props.auth.user;
    const { t } = useI18n();

    const formatStatus = (status) => {
        const key = resolveStatusKey(status);
        return key ? t(`status.${key}`) : status;
    };

    const getBadgeClass = (status) => {
        const key = resolveStatusKey(status);
        return statusBadgeClass(key);
    };

    return (
        <AuthenticatedLayout
            header={<h2 className="text-xl font-semibold leading-tight text-(--ciete-slate)">{t('dashboard.header')}</h2>}
        >
            <Head title={t('dashboard.headTitle')} />

            <div className="mx-auto max-w-7xl space-y-6 px-6 py-8">
                {/* BIENVENIDA */}
                <div className="flex items-start justify-between">
                    <div>
                        <p className="mb-1 text-[10px] font-bold uppercase tracking-widest text-text-hint">{t('dashboard.sectionLabel')}</p>
                        <h2 className="text-xl font-semibold text-text-main">{t('dashboard.welcomeUser', { name: user.nombre_usuario })}</h2>
                    </div>
                    <div className="flex flex-col items-end gap-2 text-right">
                        <p className="text-[11px] text-text-hint">{t('dashboard.lastAccessToday')}</p>
                        <Link href={route('profile.edit')} className="ciete-btn-secondary text-xs">{t('dashboard.myProfile')}</Link>
                    </div>
                </div>

                {/* BOTONES DE ACCIÓN */}
                <div className="flex flex-wrap gap-3">
                    {user.can_access_direction_panel && (
                        <Link href={route('cierre.dashboard')} className="ciete-btn-secondary">{t('nav.closurePanel')}</Link>
                    )}
                    {user.is_admin && (
                        <Link href={route('admin.dashboard')} className="ciete-btn-primary">{t('dashboard.adminPanel')}</Link>
                    )}
                </div>

                {/* MÉTRICAS (Simples Cards) */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div className="rounded-[12px] border border-border border-l-[3px] border-l-primary bg-surface p-5 shadow-sm">
                        <p className="mb-2 text-[10px] font-bold uppercase tracking-widest text-text-muted">{t('dashboard.metrics.activeWorks')}</p>
                        <p className="text-4xl font-medium text-text-main">{obras.length}</p>
                    </div>
                    <div className="rounded-[12px] border border-border border-l-[3px] border-l-state-pending-dot bg-surface p-5 shadow-sm">
                        <p className="mb-2 text-[10px] font-bold uppercase tracking-widest text-text-muted">{t('dashboard.metrics.pendingOrders')}</p>
                        <p className="text-4xl font-medium text-text-main">{pedidos.length}</p>
                    </div>
                    <div className="rounded-[12px] border border-border border-l-[3px] border-l-accent bg-surface p-5 shadow-sm">
                        <p className="mb-2 text-[10px] font-bold uppercase tracking-widest text-text-muted">{t('dashboard.metrics.legalizations')}</p>
                        <p className="text-4xl font-medium text-text-main">{legalizaciones.length}</p>
                    </div>
                </div>

                {/* CUADRÍCULA DE TABLAS (Aquí está la magia) */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    
                    {/* COLUMNA IZQUIERDA: OBRAS */}
                    <div className="overflow-hidden rounded-2xl border border-border bg-surface shadow-sm">
                        <div className="border-b border-border px-4 py-3 flex justify-between items-center bg-surface-2">
                            <h3 className="text-sm font-medium text-text-main">{t('dashboard.tables.assignedWorks')}</h3>
                            <Link href={route('trabajos.index')} className="text-xs font-semibold text-(--ciete-red) hover:underline">
                                {t('common.actions.viewAll')}
                            </Link>
                        </div>
                        {obras.length === 0 ? (
                            <div className="px-4 py-8 text-center text-sm text-text-hint">{t('dashboard.tables.noWorks')}</div>
                        ) : (
                            <div className="divide-y divide-border">
                                {obras.map((obra, index) => (
                                    <div key={index} className="flex items-center justify-between px-4 py-3 hover:bg-surface-2 transition-colors">
                                        <div className="min-w-0 flex-1">
                                            <p className="text-sm font-medium text-text-main truncate">{obra.numero_trabajo}</p>
                                            <p className="text-[10px] text-text-hint truncate">{obra.descripcion_trabajo}</p>
                                        </div>
                                        <span className={`ml-4 inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold whitespace-nowrap ${getBadgeClass(obra.estado)}`}>
                                            {obra.estado?.toUpperCase().replace('_', ' ')}
                                        </span>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>

                    {/* COLUMNA DERECHA: PEDIDOS */}
                    <div className="overflow-hidden rounded-2xl border border-border bg-surface shadow-sm">
                    <div className="border-b border-border px-4 py-3 flex justify-between items-center bg-surface-2">
                        <h3 className="text-sm font-medium text-text-main">{t('dashboard.tables.myOrders')}</h3>
                        <Link href={route('pedidos.index')} className="text-xs font-semibold text-(--ciete-red) hover:underline">
                            {t('common.actions.viewAll')}
                        </Link>
                    </div>
                        {pedidos.length === 0 ? (
                            <div className="px-4 py-8 text-center text-sm text-text-hint">{t('dashboard.tables.noOrders')}</div>
                        ) : (
                            <div className="divide-y divide-border">
                                {pedidos.map((pedido, index) => (
                                    <div key={index} className="flex items-center justify-between px-4 py-3 hover:bg-surface-2 transition-colors">
                                        <div>
                                            <p className="text-sm font-medium text-text-main">{pedido.ref}</p>
                                            <p className="text-[10px] text-text-hint">{pedido.obra}</p>
                                        </div>
                                        <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold ${getBadgeClass(pedido.estado)}`}>
                                            {formatStatus(pedido.estado)}
                                        </span>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>

                    {/* ABAJO (Ancho completo o una columna): LEGALIZACIONES */}
                    <div className="overflow-hidden rounded-2xl border border-border bg-surface shadow-sm lg:col-span-2">
                        <div className="border-b border-border px-4 py-3 bg-surface-2">
                            <h3 className="text-sm font-medium text-text-main">{t('dashboard.tables.pendingLegalizations')}</h3>
                        </div>
                        {legalizaciones.length === 0 ? (
                            <div className="px-4 py-8 text-center text-sm text-text-hint">{t('dashboard.tables.noPendingLegalizations')}</div>
                        ) : (
                            <div className="overflow-x-auto">
                                <table className="w-full">
                                    <thead className="bg-surface-2 border-b border-border">
                                        <tr className="text-left text-[10px] font-bold uppercase tracking-widest text-text-muted">
                                            <th className="px-4 py-3">{t('dashboard.tables.refCol')}</th>
                                            <th className="px-4 py-3">{t('dashboard.tables.dueDateCol')}</th>
                                            <th className="px-4 py-3">{t('dashboard.tables.statusCol')}</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-border">
                                        {legalizaciones.map((leg, index) => (
                                            <tr key={index} className="transition hover:bg-surface-2">
                                                <td className="px-4 py-3 text-sm font-medium text-text-main">{leg.ref}</td>
                                                <td className="px-4 py-3 text-sm text-text-muted">{leg.vencimiento}</td>
                                                <td className="px-4 py-3">
                                                    <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold ${getBadgeClass(leg.estado)}`}>
                                                        {formatStatus(leg.estado)}
                                                    </span>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
