import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useI18n } from '@/i18n';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { ArrowLeft, Archive, CheckCheck, AlertTriangle } from 'lucide-react';

export default function MessagesShow({ message }) {
    const { t } = useI18n();
    const user = usePage().props.auth.user;
    const isRecipient = message.id_destinatario === user?.id_usuario;
    const sender = message.remitente;
    const recipient = message.destinatario;
    const senderName = sender ? `${sender.nombre} ${sender.apellidos ?? ''}`.trim() : '—';
    const recipientName = recipient ? `${recipient.nombre} ${recipient.apellidos ?? ''}`.trim() : '—';

    const priorityMap = {
        urgente: 'bg-state-blocked-bg text-state-blocked-text',
        alta: 'bg-state-pending-bg text-state-pending-text',
        normal: 'bg-surface-2 text-text-hint',
    };

    return (
        <AuthenticatedLayout header={t('messages.header')}>
            <Head title={message.asunto} />

            <div className="ciete-page ciete-page-reading">
                <Link
                    href={route('messages.index')}
                    className="inline-flex items-center gap-1.5 text-xs text-text-muted transition hover:text-text-main"
                >
                    <ArrowLeft size={14} />
                    {t('common.actions.back')}
                </Link>

                <div className="overflow-hidden rounded-xl border border-border bg-surface">
                    <div className="border-b border-border px-5 py-4">
                        <h2 className="text-base font-semibold text-text-main">{message.asunto}</h2>
                        <div className="mt-2 flex flex-wrap items-center gap-2 text-xs text-text-muted">
                            <span>
                                <span className="font-medium text-text-main">{t('messages.from')}:</span> {senderName}
                                <span className="ml-1 text-text-hint">({sender?.email})</span>
                            </span>
                            <span className="text-text-hint">→</span>
                            <span>
                                <span className="font-medium text-text-main">{t('messages.toLabel')}:</span> {recipientName}
                            </span>
                        </div>
                        <div className="mt-2 flex items-center gap-2">
                            <span className="text-[10px] text-text-hint">
                                {new Date(message.created_at).toLocaleString()}
                            </span>
                            {message.prioridad !== 'normal' && (
                                <span className={`inline-flex items-center gap-1 rounded-full px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-widest ${priorityMap[message.prioridad]}`}>
                                    {message.prioridad === 'urgente' && <AlertTriangle size={10} />}
                                    {t(`messages.priority.${message.prioridad}`)}
                                </span>
                            )}
                            {message.es_aviso_sistema && (
                                <span className="text-[9px] font-bold uppercase tracking-widest text-accent">
                                    {t('messages.systemNotice')}
                                </span>
                            )}
                        </div>
                    </div>

                    <div className="px-5 py-5">
                        <div className="whitespace-pre-wrap text-sm leading-relaxed text-text-main">
                            {message.cuerpo}
                        </div>
                    </div>

                    {isRecipient && (
                        <div className="flex flex-col gap-2 border-t border-border px-5 py-3 sm:flex-row sm:flex-wrap">
                            {!message.leido_at && (
                                <button
                                    type="button"
                                    onClick={() => router.post(route('messages.read', message.id_mensaje), {}, { preserveScroll: true })}
                                    className="inline-flex w-full items-center justify-center gap-1 rounded-lg border border-border bg-surface px-3 py-1.5 text-[10px] font-bold uppercase tracking-widest text-text-main transition hover:bg-surface-2 sm:w-auto"
                                >
                                    <CheckCheck size={12} />
                                    {t('messages.markRead')}
                                </button>
                            )}
                            {!message.archivado && (
                                <button
                                    type="button"
                                    onClick={() => router.post(route('messages.archive', message.id_mensaje), {}, { preserveScroll: true, onSuccess: () => router.visit(route('messages.index')) })}
                                    className="inline-flex w-full items-center justify-center gap-1 rounded-lg border border-border bg-surface px-3 py-1.5 text-[10px] font-bold uppercase tracking-widest text-text-main transition hover:bg-surface-2 sm:w-auto"
                                >
                                    <Archive size={12} />
                                    {t('messages.archiveAction')}
                                </button>
                            )}
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
