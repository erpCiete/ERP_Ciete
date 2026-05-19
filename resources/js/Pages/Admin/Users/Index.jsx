import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useI18n } from '@/i18n';
import { Head, Link, router } from '@inertiajs/react';
import { useCallback, useEffect, useState } from 'react';

export default function UsersIndex({ users, filtros = {}, rolesInfo = [] }) {
    const { t } = useI18n();

    const [search, setSearch] = useState(filtros.search ?? '');
    const [activoFilter, setActivoFilter] = useState(filtros.activo ?? '');
    const [status, setStatus] = useState('ready');

    const rows = users?.data ?? [];
    const pagination = users?.meta ?? users;
    const roleInfoBySlug = Object.fromEntries((rolesInfo ?? []).map((role) => [role.slug, role]));

    const aplicarFiltros = useCallback(
        (overrides = {}) => {
            const params = {
                search: overrides.search !== undefined ? overrides.search : search,
                activo: overrides.activo !== undefined ? overrides.activo : activoFilter,
                page: overrides.page !== undefined ? overrides.page : undefined,
            };
            Object.keys(params).forEach((k) => {
                if (params[k] === '' || params[k] === undefined || params[k] === null) delete params[k];
            });

            router.get(route('admin.users.index'), params, {
                preserveState: true,
                preserveScroll: true,
                replace: true,
                onStart: () => setStatus('loading'),
                onFinish: () => setStatus('ready'),
            });
        },
        [search, activoFilter],
    );

    useEffect(() => {
        const timer = setTimeout(() => {
            if (search !== (filtros.search ?? '')) {
                aplicarFiltros({ search });
            }
        }, 400);
        return () => clearTimeout(timer);
    }, [search]);

    const toggleActivo = (user) => {
        if (!confirm(t('adminUsers.confirmToggle', { name: user.nombre }))) return;
        router.post(route('admin.users.toggle', user.id_usuario), {}, { preserveScroll: true });
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-(--ciete-slate)">
                    {t('adminUsers.header')}
                </h2>
            }
        >
            <Head title={t('adminUsers.headTitle')} />

            <div className="ciete-page ciete-page-wide">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p className="mb-1 text-[10px] font-bold uppercase tracking-widest text-text-hint">
                            {t('adminUsers.panelLabel')}
                        </p>
                        <h2 className="text-xl font-semibold text-text-main">{t('adminUsers.title')}</h2>
                    </div>
                    <Link href={route('admin.users.create')} className="ciete-btn-primary text-xs">
                        {t('adminUsers.createUser')}
                    </Link>
                </div>

                <div className="ciete-filter-bar">
                    <div className="ciete-filter-row items-end">
                        <div className="flex-1">
                            <label className="mb-1 block text-[10px] font-bold uppercase tracking-widest text-text-muted">
                                {t('adminUsers.search')}
                            </label>
                            <input
                                type="text"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder={t('adminUsers.searchPlaceholder')}
                                className="w-full rounded-md border border-border bg-surface-2 px-3 py-1.5 text-sm text-text-main placeholder:text-text-hint focus:border-primary focus:outline-none"
                            />
                        </div>
                        <div>
                            <label className="mb-1 block text-[10px] font-bold uppercase tracking-widest text-text-muted">
                                {t('adminUsers.status')}
                            </label>
                            <select
                                value={activoFilter}
                                onChange={(e) => {
                                    setActivoFilter(e.target.value);
                                    aplicarFiltros({ activo: e.target.value });
                                }}
                                className="rounded-md border border-border bg-surface-2 px-3 py-1.5 text-sm text-text-main focus:border-primary focus:outline-none"
                            >
                                <option value="">{t('adminUsers.allStatuses')}</option>
                                <option value="1">{t('adminUsers.active')}</option>
                                <option value="0">{t('adminUsers.inactive')}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div className="overflow-hidden rounded-[12px] border border-border bg-surface">
                    <p className="ciete-table-hint lg:hidden">{t('help.sections.mobile.tablesNote')}</p>
                    <div className="overflow-x-auto overscroll-x-contain lg:overflow-visible">
                        <table className="w-full min-w-[760px] table-auto lg:min-w-0">
                            <thead className="border-b border-border">
                                <tr className="text-left text-[10px] font-bold uppercase tracking-widest text-text-muted">
                                    <th className="px-4 py-2">{t('adminUsers.cols.name')}</th>
                                    <th className="px-4 py-2">{t('adminUsers.cols.username')}</th>
                                    <th className="px-4 py-2">{t('adminUsers.cols.email')}</th>
                                    <th className="px-4 py-2">{t('adminUsers.cols.context')}</th>
                                    <th className="px-4 py-2">{t('adminUsers.cols.roles')}</th>
                                    <th className="px-4 py-2">{t('adminUsers.cols.status')}</th>
                                    <th className="px-4 py-2 text-right">{t('adminUsers.cols.actions')}</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-border">
                                {rows.length === 0 ? (
                                    <tr>
                                        <td colSpan={7} className="px-4 py-8 text-center text-sm text-text-hint">
                                            {t('adminUsers.noUsers')}
                                        </td>
                                    </tr>
                                ) : (
                                    rows.map((user) => (
                                        <tr key={user.id_usuario} className="transition hover:bg-surface-2">
                                            <td className="px-4 py-3 text-sm font-medium text-text-main">
                                                {user.nombre} {user.apellidos ?? ''}
                                            </td>
                                            <td className="px-4 py-3 text-sm text-text-muted whitespace-nowrap">{user.nombre_usuario}</td>
                                            <td className="px-4 py-3 text-sm text-text-muted break-words">{user.email}</td>
                                            <td className="px-4 py-3 text-sm text-text-muted">
                                                <div className="flex flex-wrap gap-1">
                                                    {(user.contextos_asignados?.length ? user.contextos_asignados : [user.contexto].filter(Boolean)).map((contexto) => (
                                                        <span
                                                            key={`${user.id_usuario}-${contexto?.id_contexto ?? contexto?.codigo}`}
                                                            className="inline-flex items-center rounded-full bg-surface-2 px-2 py-0.5 text-[9px] font-bold text-text-muted"
                                                        >
                                                            {contexto?.codigo ?? '—'}
                                                        </span>
                                                    ))}
                                                </div>
                                            </td>
                                            <td className="px-4 py-3">
                                                <div className="flex flex-wrap gap-1">
                                                    {(user.roles ?? []).map((role) => (
                                                        <span
                                                            key={role.id_rol}
                                                            className="inline-flex items-center rounded-full bg-accent/10 px-2 py-0.5 text-[9px] font-bold text-accent"
                                                        >
                                                            {role.nombre}
                                                        </span>
                                                    ))}
                                                </div>
                                                <p className="mt-1 text-[10px] text-text-hint">
                                                    {(user.roles ?? []).some((role) => (roleInfoBySlug[role.slug]?.scope_summary?.operational_mutation?.length ?? 0) > 0)
                                                        ? t('adminUsers.scopeMutationEnabled')
                                                        : t('adminUsers.scopeReadOnly')}
                                                </p>
                                            </td>
                                            <td className="px-4 py-3">
                                                <span
                                                    className={`inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold ${
                                                        user.activo
                                                            ? 'bg-state-done-bg text-state-done-text'
                                                            : 'bg-state-blocked-bg text-state-blocked-text'
                                                    }`}
                                                >
                                                    {user.activo ? t('adminUsers.active') : t('adminUsers.inactive')}
                                                </span>
                                            </td>
                                            <td className="px-4 py-3 text-right">
                                                <div className="flex flex-wrap items-center justify-end gap-2">
                                                    <Link
                                                        href={route('admin.users.edit', user.id_usuario)}
                                                        className="text-[10px] font-bold uppercase tracking-widest text-primary hover:underline"
                                                    >
                                                        {t('common.actions.edit')}
                                                    </Link>
                                                    <button
                                                        type="button"
                                                        onClick={() => toggleActivo(user)}
                                                        className={`text-[10px] font-bold uppercase tracking-widest ${
                                                            user.activo ? 'text-state-blocked-text' : 'text-state-done-text'
                                                        } hover:underline`}
                                                    >
                                                        {user.activo ? t('adminUsers.deactivate') : t('adminUsers.activate')}
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>

                {pagination?.last_page > 1 && (
                    <div className="flex flex-wrap items-center justify-center gap-2 pt-2">
                        {Array.from({ length: pagination.last_page }, (_, i) => i + 1).map((page) => (
                            <button
                                key={page}
                                type="button"
                                onClick={() => aplicarFiltros({ page })}
                                className={`rounded-md px-3 py-1 text-xs font-medium transition ${
                                    page === pagination.current_page
                                        ? 'bg-primary text-white'
                                        : 'border border-border bg-surface text-text-muted hover:bg-surface-2'
                                }`}
                            >
                                {page}
                            </button>
                        ))}
                    </div>
                )}

                <div className="pt-2">
                    <Link
                        href={route('admin.dashboard')}
                        className="inline-flex items-center rounded-md border border-border px-4 py-2 text-sm font-semibold text-text-main transition hover:bg-surface-2"
                    >
                        {t('adminUsers.backToAdmin')}
                    </Link>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
