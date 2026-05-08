import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useI18n } from '@/i18n';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { ArrowLeft, LifeBuoy, MessageSquareText, Send } from 'lucide-react';

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

function AuthorTypeBadge({ type, t }) {
    const map = {
        user: 'bg-surface-2 text-text-muted',
        admin: 'bg-accent/10 text-accent',
        support: 'bg-primary/10 text-primary',
        system: 'bg-secondary/10 text-secondary',
    };

    return (
        <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-[9px] font-bold uppercase tracking-widest ${map[type] ?? map.user}`}>
            {t(`messages.senderType.${type}`)}
        </span>
    );
}

export default function SupportShow({ ticket }) {
    const { t } = useI18n();
    const flash = usePage().props.flash ?? {};
    const user = usePage().props.auth.user;
    const { data, setData, post, processing, errors, reset } = useForm({
        message: '',
    });

    const handleSubmit = (event) => {
        event.preventDefault();
        post(route('support.reply', ticket.id_solicitud_soporte), {
            preserveScroll: true,
            onSuccess: () => reset('message'),
        });
    };

    return (
        <AuthenticatedLayout header={t('supportPage.header')}>
            <Head title={`${t('supportPage.header')} #${ticket.id_solicitud_soporte}`} />

            <div className="ciete-page ciete-page-reading">
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <Link href={route('support')} className="inline-flex items-center gap-1.5 text-xs text-text-muted transition hover:text-text-main">
                        <ArrowLeft size={14} />
                        {t('supportPage.backToList')}
                    </Link>

                    {user?.can_manage_support && (
                        <Link
                            href={route('admin.support.show', ticket.id_solicitud_soporte)}
                            className="inline-flex items-center gap-1.5 rounded-lg border border-border bg-surface px-3 py-1.5 text-xs font-medium text-text-main transition hover:bg-surface-2"
                        >
                            <LifeBuoy size={14} />
                            {t('supportPage.manageTicket')}
                        </Link>
                    )}
                </div>

                {flash.success && (
                    <div className="rounded-xl border border-state-done-dot/30 bg-state-done-bg px-4 py-3 text-sm text-state-done-text">
                        {flash.success}
                    </div>
                )}

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

                    <div className="mt-4 grid gap-3 text-xs text-text-muted lg:grid-cols-3">
                        <div className="rounded-lg bg-surface-2 px-3 py-2">
                            <p className="text-[10px] font-bold uppercase tracking-widest text-text-hint">{t('supportPage.createdAt')}</p>
                            <p className="mt-1">{new Date(ticket.created_at).toLocaleString()}</p>
                        </div>
                        <div className="rounded-lg bg-surface-2 px-3 py-2">
                            <p className="text-[10px] font-bold uppercase tracking-widest text-text-hint">{t('supportPage.updatedAt')}</p>
                            <p className="mt-1">{new Date(ticket.updated_at).toLocaleString()}</p>
                        </div>
                        <div className="rounded-lg bg-surface-2 px-3 py-2">
                            <p className="text-[10px] font-bold uppercase tracking-widest text-text-hint">{t('supportPage.assignedTo')}</p>
                            <p className="mt-1">
                                {ticket.asignado ? `${ticket.asignado.nombre} ${ticket.asignado.apellidos ?? ''}`.trim() : t('supportPage.unassigned')}
                            </p>
                        </div>
                    </div>
                </div>

                <div className="rounded-xl border border-border bg-surface">
                    <div className="border-b border-border px-5 py-4">
                        <div className="flex items-center gap-2">
                            <MessageSquareText size={16} className="text-text-hint" />
                            <h3 className="text-sm font-semibold text-text-main">{t('supportPage.conversation')}</h3>
                        </div>
                    </div>

                    <div className="divide-y divide-border">
                        {ticket.comentarios.map((comment) => (
                            <div key={comment.id_comentario_soporte} className="px-5 py-4">
                                <div className="flex flex-wrap items-center justify-between gap-2">
                                    <div className="flex flex-wrap items-center gap-2">
                                        <p className="text-sm font-medium text-text-main">
                                            {comment.autor ? `${comment.autor.nombre} ${comment.autor.apellidos ?? ''}`.trim() : '—'}
                                        </p>
                                        <AuthorTypeBadge type={comment.tipo_autor} t={t} />
                                    </div>
                                    <span className="text-[10px] uppercase tracking-widest text-text-hint">
                                        {new Date(comment.created_at).toLocaleString()}
                                    </span>
                                </div>
                                <div className="mt-2 whitespace-pre-wrap text-sm leading-relaxed text-text-main">
                                    {comment.mensaje}
                                </div>
                            </div>
                        ))}
                    </div>
                </div>

                {ticket.can_reply && (
                    <div className="rounded-xl border border-border bg-surface p-5">
                        <h3 className="text-sm font-semibold text-text-main">{t('supportPage.replyTitle')}</h3>

                        <form onSubmit={handleSubmit} className="mt-4 space-y-4">
                            <div>
                                <label className="mb-1 block text-[10px] font-bold uppercase tracking-widest text-text-hint">
                                    {t('supportPage.messageLabel')}
                                </label>
                                <textarea
                                    value={data.message}
                                    onChange={(event) => setData('message', event.target.value)}
                                    rows={5}
                                    className="w-full resize-none rounded-lg border border-border bg-surface px-3 py-2 text-sm text-text-main focus:border-primary focus:outline-hidden focus:ring-1 focus:ring-primary"
                                    maxLength={5000}
                                />
                                {errors.message && <p className="mt-1 text-xs text-state-blocked-dot">{errors.message}</p>}
                            </div>

                            <div className="flex justify-end">
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="inline-flex w-full items-center justify-center gap-1.5 rounded-lg bg-primary px-4 py-2 text-xs font-bold text-white transition hover:opacity-90 disabled:opacity-50 sm:w-auto"
                                >
                                    <Send size={14} />
                                    {t('supportPage.sendReply')}
                                </button>
                            </div>
                        </form>
                    </div>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
