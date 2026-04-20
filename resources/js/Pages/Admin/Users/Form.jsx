import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useI18n } from '@/i18n';
import { Head, Link, useForm } from '@inertiajs/react';

export default function UsersForm({ user = null, roles = [], contextos = [] }) {
    const { t } = useI18n();
    const isEditing = !!user;

    const { data, setData, post, put, processing, errors } = useForm({
        nombre: user?.nombre ?? '',
        apellidos: user?.apellidos ?? '',
        nombre_usuario: user?.nombre_usuario ?? '',
        email: user?.email ?? '',
        password: '',
        telefono: user?.telefono ?? '',
        id_contexto: user?.id_contexto ?? (contextos[0]?.id_contexto ?? ''),
        activo: user?.activo ?? true,
        roles: user?.roles?.map((r) => r.id_rol) ?? [],
        contextos: user?.contextos?.map((c) => c.id_contexto) ?? [],
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        if (isEditing) {
            put(route('admin.users.update', user.id_usuario));
        } else {
            post(route('admin.users.store'));
        }
    };

    const toggleRole = (id) => {
        setData('roles', data.roles.includes(id) ? data.roles.filter((r) => r !== id) : [...data.roles, id]);
    };

    const toggleContexto = (id) => {
        setData(
            'contextos',
            data.contextos.includes(id) ? data.contextos.filter((c) => c !== id) : [...data.contextos, id],
        );
    };

    const fieldClass = (field) =>
        `w-full rounded-md border px-3 py-1.5 text-sm text-text-main placeholder:text-text-hint focus:outline-none ${
            errors[field] ? 'border-state-blocked-dot focus:border-state-blocked-dot' : 'border-border bg-surface-2 focus:border-primary'
        }`;

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-(--ciete-slate)">
                    {isEditing ? t('adminUsers.editUser') : t('adminUsers.newUser')}
                </h2>
            }
        >
            <Head title={isEditing ? t('adminUsers.editUser') : t('adminUsers.newUser')} />

            <div className="mx-auto max-w-3xl space-y-5 px-6 py-8">
                <form onSubmit={handleSubmit} className="space-y-5">
                    {/* Datos básicos */}
                    <section className="rounded-[12px] border border-border bg-surface p-5 space-y-4">
                        <h3 className="text-sm font-medium text-text-main">{t('adminUsers.sections.basicInfo')}</h3>

                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <label className="mb-1 block text-[10px] font-bold uppercase tracking-widest text-text-muted">
                                    {t('adminUsers.fields.nombre')}
                                </label>
                                <input type="text" value={data.nombre} onChange={(e) => setData('nombre', e.target.value)} className={fieldClass('nombre')} />
                                {errors.nombre && <p className="mt-1 text-[10px] text-state-blocked-text">{errors.nombre}</p>}
                            </div>
                            <div>
                                <label className="mb-1 block text-[10px] font-bold uppercase tracking-widest text-text-muted">
                                    {t('adminUsers.fields.apellidos')}
                                </label>
                                <input type="text" value={data.apellidos} onChange={(e) => setData('apellidos', e.target.value)} className={fieldClass('apellidos')} />
                                {errors.apellidos && <p className="mt-1 text-[10px] text-state-blocked-text">{errors.apellidos}</p>}
                            </div>
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <label className="mb-1 block text-[10px] font-bold uppercase tracking-widest text-text-muted">
                                    {t('adminUsers.fields.nombre_usuario')}
                                </label>
                                <input type="text" value={data.nombre_usuario} onChange={(e) => setData('nombre_usuario', e.target.value)} className={fieldClass('nombre_usuario')} />
                                {errors.nombre_usuario && <p className="mt-1 text-[10px] text-state-blocked-text">{errors.nombre_usuario}</p>}
                            </div>
                            <div>
                                <label className="mb-1 block text-[10px] font-bold uppercase tracking-widest text-text-muted">
                                    {t('adminUsers.fields.email')}
                                </label>
                                <input type="email" value={data.email} onChange={(e) => setData('email', e.target.value)} className={fieldClass('email')} />
                                {errors.email && <p className="mt-1 text-[10px] text-state-blocked-text">{errors.email}</p>}
                            </div>
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <label className="mb-1 block text-[10px] font-bold uppercase tracking-widest text-text-muted">
                                    {t('adminUsers.fields.password')} {isEditing && <span className="normal-case font-normal">({t('adminUsers.fields.passwordHint')})</span>}
                                </label>
                                <input type="password" value={data.password} onChange={(e) => setData('password', e.target.value)} className={fieldClass('password')} autoComplete="new-password" />
                                {errors.password && <p className="mt-1 text-[10px] text-state-blocked-text">{errors.password}</p>}
                            </div>
                            <div>
                                <label className="mb-1 block text-[10px] font-bold uppercase tracking-widest text-text-muted">
                                    {t('adminUsers.fields.telefono')}
                                </label>
                                <input type="text" value={data.telefono} onChange={(e) => setData('telefono', e.target.value)} className={fieldClass('telefono')} />
                                {errors.telefono && <p className="mt-1 text-[10px] text-state-blocked-text">{errors.telefono}</p>}
                            </div>
                        </div>
                    </section>

                    {/* Contexto y estado */}
                    <section className="rounded-[12px] border border-border bg-surface p-5 space-y-4">
                        <h3 className="text-sm font-medium text-text-main">{t('adminUsers.sections.contextStatus')}</h3>

                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <label className="mb-1 block text-[10px] font-bold uppercase tracking-widest text-text-muted">
                                    {t('adminUsers.fields.contextoPrincipal')}
                                </label>
                                <select
                                    value={data.id_contexto}
                                    onChange={(e) => setData('id_contexto', Number(e.target.value))}
                                    className={fieldClass('id_contexto')}
                                >
                                    {contextos.map((ctx) => (
                                        <option key={ctx.id_contexto} value={ctx.id_contexto}>
                                            {ctx.nombre} ({ctx.codigo})
                                        </option>
                                    ))}
                                </select>
                                {errors.id_contexto && <p className="mt-1 text-[10px] text-state-blocked-text">{errors.id_contexto}</p>}
                            </div>
                            <div className="flex items-end">
                                <label className="flex items-center gap-2 text-sm text-text-main">
                                    <input
                                        type="checkbox"
                                        checked={data.activo}
                                        onChange={(e) => setData('activo', e.target.checked)}
                                        className="rounded border-border text-primary focus:ring-primary"
                                    />
                                    {t('adminUsers.fields.activo')}
                                </label>
                            </div>
                        </div>

                        <div>
                            <label className="mb-2 block text-[10px] font-bold uppercase tracking-widest text-text-muted">
                                {t('adminUsers.fields.contextosAcceso')}
                            </label>
                            <div className="flex flex-wrap gap-2">
                                {contextos.map((ctx) => (
                                    <button
                                        key={ctx.id_contexto}
                                        type="button"
                                        onClick={() => toggleContexto(ctx.id_contexto)}
                                        className={`rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-widest transition ${
                                            data.contextos.includes(ctx.id_contexto)
                                                ? 'bg-primary text-white'
                                                : 'border border-border bg-surface-2 text-text-muted hover:bg-border'
                                        }`}
                                    >
                                        {ctx.codigo}
                                    </button>
                                ))}
                            </div>
                        </div>
                    </section>

                    {/* Roles */}
                    <section className="rounded-[12px] border border-border bg-surface p-5 space-y-4">
                        <h3 className="text-sm font-medium text-text-main">{t('adminUsers.sections.roles')}</h3>
                        <div className="flex flex-wrap gap-2">
                            {roles.map((role) => (
                                <button
                                    key={role.id_rol}
                                    type="button"
                                    onClick={() => toggleRole(role.id_rol)}
                                    className={`rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-widest transition ${
                                        data.roles.includes(role.id_rol)
                                            ? 'bg-accent text-white'
                                            : 'border border-border bg-surface-2 text-text-muted hover:bg-border'
                                    }`}
                                >
                                    {role.nombre}
                                </button>
                            ))}
                        </div>
                        {errors.roles && <p className="mt-1 text-[10px] text-state-blocked-text">{errors.roles}</p>}
                    </section>

                    {/* Acciones */}
                    <div className="flex items-center justify-between">
                        <Link href={route('admin.users.index')} className="text-xs text-text-hint hover:text-text-muted">
                            ← {t('adminUsers.backToList')}
                        </Link>
                        <button type="submit" disabled={processing} className="ciete-btn-primary text-xs">
                            {processing ? t('adminUsers.saving') : isEditing ? t('adminUsers.updateUser') : t('adminUsers.createUser')}
                        </button>
                    </div>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
