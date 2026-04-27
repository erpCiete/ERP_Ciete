import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useI18n } from '@/i18n';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { ArrowLeft, Save, Send } from 'lucide-react';

function StatusBadge({ status, t }) {
    const map = {
        pending: 'bg-state-pending-bg text-state-pending-text',
        in_review: 'bg-primary/10 text-primary',
        resolved: 'bg-state-done-bg text-state-done-text',
        archived: 'bg-surface-2 text-text-hint',
    };

    return (
        <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-widest ${map[status] ?? map.pending}`}>
            {t(`supportPage.status.${status}`)}
        </span>
    );
}

function PriorityBadge({ priority, t }) {
    const map = {
        baja: 'bg-surface-2 text-text-hint',
        normal: 'bg-surface-2 text-text-hint',
        alta: 'bg-state-pending-bg text-state-pending-text',
        urgente: 'bg-state-blocked-bg text-state-blocked-text',
    };

    return (
        <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-[9px] font-bold uppercase tracking-widest ${map[priority] ?? map.normal}`}>
            {t(`supportPage.priority.${priority}`)}
        </span>
    );
}

export default function AdminSupportShow({ ticket }) {
    const { t } = useI18n();
    const flash = usePage().props.flash ?? {};
    const statusForm = useForm({
        status: ticket.estado,
    });
    const replyForm = useForm({
        message: '',
        status: ticket.estado === 'pending' ? 'in_review' : ticket.estado,
    });

    const updateStatus = (event) => {
        event.preventDefault();
        statusForm.patch(route('admin.support.status', ticket.id_solicitud_soporte), {
            preserveScroll: true,
        });
    };

    const submitReply = (event) => {
        event.preventDefault();
        replyForm.post(route('admin.support.reply', ticket.id_solicitud_soporte), {
            preserveScroll: true,
            onSuccess: () => replyForm.reset('message'),
        });
    };

    return (
        <AuthenticatedLayout header={t('supportAdmin.header')}>
            <Head title={`${t('supportAdmin.header')} #${ticket.id_solicitud_soporte}`} />

            <div className="mx-auto max-w-6xl space-y-5 pb-10">
                <Link href={route('admin.support.index')} className="inline-flex items-center gap-1.5 text-xs text-text-muted transition hover:text-text-main">
                    <ArrowLeft size={14} />
                    {t('supportAdmin.backToList')}
                </Link>

                {flash.success && (
                    <div className="rounded-xl border border-state-done-dot/30 bg-state-done-bg px-4 py-3 text-sm text-state-done-text">
                        {flash.success}
                    </div>
                )}

                <div className="grid gap-5 lg:grid-cols-[minmax(0,1fr)_320px]">
                    <div className="space-y-5">
                        <div className="rounded-xl border border-border bg-surface p-5">
                            <div className="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <p className="mb-1 text-[10px] font-bold uppercase tracking-widest text-text-hint">
                                        {t('supportPage.ticketNumber', { id: ticket.id_solicitud_soporte })}
                                    </p>
                                    <h2 className="text-xl font-semibold text-text-main">{ticket.asunto}</h2>
                                    <p className="mt-1 text-sm text-text-muted">{t(`supportPage.topics.${ticket.tema}`)}</p>
                                </div>
                                <div className="flex flex-wrap items-center gap-2">
                                    <StatusBadge status={ticket.estado} t={t} />
                                    <PriorityBadge priority={ticket.prioridad} t={t} />
                                </div>
                            </div>

                            <div className="mt-4 grid gap-3 text-xs text-text-muted sm:grid-cols-2">
                                <div className="rounded-lg bg-surface-2 px-3 py-2">
                                    <p className="text-[10px] font-bold uppercase tracking-widest text-text-hint">{t('supportAdmin.requester')}</p>
                                    <p className="mt-1 text-sm text-text-main">
                                        {ticket.solicitante ? `${ticket.solicitante.nombre} ${ticket.solicitante.apellidos ?? ''}`.trim() : '—'}
                                    </p>
                                    <p className="text-[11px] text-text-hint">{ticket.solicitante?.email ?? '—'}</p>
                                </div>
                                <div className="rounded-lg bg-surface-2 px-3 py-2">
                                    <p className="text-[10px] font-bold uppercase tracking-widest text-text-hint">{t('supportPage.assignedTo')}</p>
                                    <p className="mt-1 text-sm text-text-main">
                                        {ticket.asignado ? `${ticket.asignado.nombre} ${ticket.asignado.apellidos ?? ''}`.trim() : t('supportPage.unassigned')}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div className="rounded-xl border border-border bg-surface">
                            <div className="border-b border-border px-5 py-4">
                                <h3 className="text-sm font-semibold text-text-main">{t('supportPage.conversation')}</h3>
                            </div>

                            <div className="divide-y divide-border">
                                {ticket.comentarios.map((comment) => (
                                    <div key={comment.id_comentario_soporte} className="px-5 py-4">
                                        <div className="flex flex-wrap items-center justify-between gap-2">
                                            <p className="text-sm font-medium text-text-main">
                                                {comment.autor ? `${comment.autor.nombre} ${comment.autor.apellidos ?? ''}`.trim() : '—'}
                                            </p>
                                            <span className="text-[10px] uppercase tracking-widest text-text-hint">
                                                {new Date(comment.created_at).toLocaleString()}
                                            </span>
                                        </div>
                                        <p className="mt-1 text-[10px] font-bold uppercase tracking-widest text-text-hint">
                                            {t(`messages.senderType.${comment.tipo_autor}`)}
                                        </p>
                                        <div className="mt-2 whitespace-pre-wrap text-sm leading-relaxed text-text-main">
                                            {comment.mensaje}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>

                    <div className="space-y-5">
                        <div className="rounded-xl border border-border bg-surface p-5">
                            <h3 className="text-sm font-semibold text-text-main">{t('supportAdmin.updateStatus')}</h3>

                            <form onSubmit={updateStatus} className="mt-4 space-y-4">
                                <div>
                                    <label className="mb-1 block text-[10px] font-bold uppercase tracking-widest text-text-hint">
                                        {t('supportAdmin.filterStatus')}
                                    </label>
                                    <select
                                        value={statusForm.data.status}
                                        onChange={(event) => statusForm.setData('status', event.target.value)}
                                        className="w-full rounded-lg border border-border bg-surface px-3 py-2 text-sm text-text-main focus:border-primary focus:outline-hidden"
                                    >
                                        <option value="pending">{t('supportPage.status.pending')}</option>
                                        <option value="in_review">{t('supportPage.status.in_review')}</option>
                                        <option value="resolved">{t('supportPage.status.resolved')}</option>
                                        <option value="archived">{t('supportPage.status.archived')}</option>
                                    </select>
                                    {statusForm.errors.status && <p className="mt-1 text-xs text-state-blocked-dot">{statusForm.errors.status}</p>}
                                </div>

                                <button
                                    type="submit"
                                    disabled={statusForm.processing}
                                    className="inline-flex items-center gap-1.5 rounded-lg border border-border bg-surface-2 px-4 py-2 text-xs font-bold text-text-main transition hover:bg-border disabled:opacity-50"
                                >
                                    <Save size={14} />
                                    {t('supportAdmin.saveStatus')}
                                </button>
                            </form>
                        </div>

                        {ticket.estado !== 'archived' && (
                            <div className="rounded-xl border border-border bg-surface p-5">
                                <h3 className="text-sm font-semibold text-text-main">{t('supportAdmin.replyTitle')}</h3>

                                <form onSubmit={submitReply} className="mt-4 space-y-4">
                                    <div>
                                        <label className="mb-1 block text-[10px] font-bold uppercase tracking-widest text-text-hint">
                                            {t('supportPage.messageLabel')}
                                        </label>
                                        <textarea
                                            value={replyForm.data.message}
                                            onChange={(event) => replyForm.setData('message', event.target.value)}
                                            rows={6}
                                            className="w-full resize-none rounded-lg border border-border bg-surface px-3 py-2 text-sm text-text-main focus:border-primary focus:outline-hidden"
                                            maxLength={5000}
                                        />
                                        {replyForm.errors.message && <p className="mt-1 text-xs text-state-blocked-dot">{replyForm.errors.message}</p>}
                                    </div>

                                    <div>
                                        <label className="mb-1 block text-[10px] font-bold uppercase tracking-widest text-text-hint">
                                            {t('supportAdmin.replyStatus')}
                                        </label>
                                        <select
                                            value={replyForm.data.status}
                                            onChange={(event) => replyForm.setData('status', event.target.value)}
                                            className="w-full rounded-lg border border-border bg-surface px-3 py-2 text-sm text-text-main focus:border-primary focus:outline-hidden"
                                        >
                                            <option value="pending">{t('supportPage.status.pending')}</option>
                                            <option value="in_review">{t('supportPage.status.in_review')}</option>
                                            <option value="resolved">{t('supportPage.status.resolved')}</option>
                                            <option value="archived">{t('supportPage.status.archived')}</option>
                                        </select>
                                    </div>

                                    <button
                                        type="submit"
                                        disabled={replyForm.processing}
                                        className="inline-flex items-center gap-1.5 rounded-lg bg-primary px-4 py-2 text-xs font-bold text-white transition hover:opacity-90 disabled:opacity-50"
                                    >
                                        <Send size={14} />
                                        {t('supportAdmin.sendReply')}
                                    </button>
                                </form>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
