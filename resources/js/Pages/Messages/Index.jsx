import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useI18n } from '@/i18n';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { Archive, Inbox, Mail, Send, AlertTriangle, Plus, X } from 'lucide-react';
import { useState } from 'react';

function TabButton({ active, icon: Icon, label, count, onClick }) {
    return (
        <button
            type="button"
            onClick={onClick}
            className={`inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-medium transition-colors ${
                active
                    ? 'bg-primary text-white'
                    : 'bg-surface-2 text-text-muted hover:bg-border hover:text-text-main'
            }`}
        >
            <Icon size={14} />
            {label}
            {count > 0 && (
                <span className="ml-1 inline-flex h-4 min-w-4 items-center justify-center rounded-full bg-state-blocked-dot px-1 text-[9px] font-bold text-white">
                    {count}
                </span>
            )}
        </button>
    );
}

function PriorityBadge({ priority, t }) {
    const map = {
        urgente: 'bg-state-blocked-bg text-state-blocked-text',
        alta: 'bg-state-pending-bg text-state-pending-text',
        normal: 'bg-surface-2 text-text-hint',
    };
    return (
        <span className={`inline-flex items-center gap-1 rounded-full px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-widest ${map[priority] ?? map.normal}`}>
            {priority === 'urgente' && <AlertTriangle size={10} />}
            {t(`messages.priority.${priority}`)}
        </span>
    );
}

function ComposeModal({ users, canBroadcastNotices, onClose, t }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        id_destinatario: '',
        asunto: '',
        cuerpo: '',
        prioridad: 'normal',
    });

    const [isBroadcast, setIsBroadcast] = useState(false);

    const handleSubmit = (e) => {
        e.preventDefault();
        if (isBroadcast && canBroadcastNotices) {
            router.post(route('messages.broadcast'), {
                asunto: data.asunto,
                cuerpo: data.cuerpo,
                prioridad: data.prioridad,
            }, {
                onSuccess: () => { reset(); onClose(); },
                preserveScroll: true,
            });
        } else {
            post(route('messages.store'), {
                onSuccess: () => { reset(); onClose(); },
                preserveScroll: true,
            });
        }
    };

    return (
        <div className="fixed inset-0 z-50 flex items-end justify-center bg-black/40 p-4 sm:items-center" onClick={onClose}>
            <div
                className="w-full max-w-lg max-h-[calc(100dvh-2rem)] overflow-y-auto rounded-2xl border border-border bg-surface p-5 shadow-xl"
                onClick={(e) => e.stopPropagation()}
            >
                <div className="mb-4 flex items-center justify-between">
                    <h3 className="text-sm font-semibold text-text-main">{t('messages.compose')}</h3>
                    <button type="button" onClick={onClose} className="rounded-lg p-1 text-text-hint transition hover:bg-surface-2 hover:text-text-main">
                        <X size={16} />
                    </button>
                </div>

                <form onSubmit={handleSubmit} className="space-y-3">
                    {canBroadcastNotices && (
                        <label className="flex items-center gap-2 text-xs text-text-muted">
                            <input
                                type="checkbox"
                                checked={isBroadcast}
                                onChange={(e) => setIsBroadcast(e.target.checked)}
                                className="rounded border-border text-primary focus:ring-primary"
                            />
                            {t('messages.broadcastAll')}
                        </label>
                    )}

                    {!isBroadcast && (
                        <div>
                            <label className="mb-1 block text-[10px] font-bold uppercase tracking-widest text-text-hint">
                                {t('messages.to')}
                            </label>
                            <select
                                value={data.id_destinatario}
                                onChange={(e) => setData('id_destinatario', e.target.value)}
                                className="w-full rounded-lg border border-border bg-surface px-3 py-2 text-sm text-text-main focus:border-primary focus:outline-hidden focus:ring-1 focus:ring-primary"
                            >
                                <option value="">{t('messages.selectUser')}</option>
                                {users.map((u) => (
                                    <option key={u.id_usuario} value={u.id_usuario}>
                                        {u.nombre} {u.apellidos} — {u.email}
                                    </option>
                                ))}
                            </select>
                            {errors.id_destinatario && <p className="mt-1 text-xs text-state-blocked-dot">{errors.id_destinatario}</p>}
                        </div>
                    )}

                    <div>
                        <label className="mb-1 block text-[10px] font-bold uppercase tracking-widest text-text-hint">
                            {t('messages.subject')}
                        </label>
                        <input
                            type="text"
                            value={data.asunto}
                            onChange={(e) => setData('asunto', e.target.value)}
                            className="w-full rounded-lg border border-border bg-surface px-3 py-2 text-sm text-text-main focus:border-primary focus:outline-hidden focus:ring-1 focus:ring-primary"
                            maxLength={255}
                        />
                        {errors.asunto && <p className="mt-1 text-xs text-state-blocked-dot">{errors.asunto}</p>}
                    </div>

                    <div>
                        <label className="mb-1 block text-[10px] font-bold uppercase tracking-widest text-text-hint">
                            {t('messages.body')}
                        </label>
                        <textarea
                            value={data.cuerpo}
                            onChange={(e) => setData('cuerpo', e.target.value)}
                            rows={5}
                            className="w-full resize-none rounded-lg border border-border bg-surface px-3 py-2 text-sm text-text-main focus:border-primary focus:outline-hidden focus:ring-1 focus:ring-primary"
                            maxLength={5000}
                        />
                        {errors.cuerpo && <p className="mt-1 text-xs text-state-blocked-dot">{errors.cuerpo}</p>}
                    </div>

                    <div>
                        <label className="mb-1 block text-[10px] font-bold uppercase tracking-widest text-text-hint">
                            {t('messages.priorityLabel')}
                        </label>
                        <select
                            value={data.prioridad}
                            onChange={(e) => setData('prioridad', e.target.value)}
                            className="w-full rounded-lg border border-border bg-surface px-3 py-2 text-sm text-text-main focus:border-primary focus:outline-hidden focus:ring-1 focus:ring-primary"
                        >
                            <option value="normal">{t('messages.priority.normal')}</option>
                            <option value="alta">{t('messages.priority.alta')}</option>
                            <option value="urgente">{t('messages.priority.urgente')}</option>
                        </select>
                    </div>

                    <div className="flex flex-col-reverse gap-2 pt-2 sm:flex-row sm:justify-end">
                        <button
                            type="button"
                            onClick={onClose}
                            className="w-full rounded-lg border border-border bg-surface px-3 py-1.5 text-xs font-medium text-text-main transition hover:bg-surface-2 sm:w-auto"
                        >
                            {t('common.actions.cancel')}
                        </button>
                        <button
                            type="submit"
                            disabled={processing}
                            className="w-full rounded-lg bg-primary px-4 py-1.5 text-xs font-bold text-white transition hover:opacity-90 disabled:opacity-50 sm:w-auto"
                        >
                            <Send size={12} className="mr-1 inline" />
                            {t('messages.send')}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    );
}

export default function MessagesIndex({ messages, tab = 'inbox', unreadCount = 0, users = [] }) {
    const { t } = useI18n();
    const user = usePage().props.auth.user;
    const canBroadcastNotices = Boolean(user?.can_manage_notices);
    const [showCompose, setShowCompose] = useState(false);

    const switchTab = (newTab) => {
        router.get(route('messages.index'), { tab: newTab }, { preserveState: true });
    };

    return (
        <AuthenticatedLayout header={t('messages.header')}>
            <Head title={t('messages.headTitle')} />

            <div className="ciete-page ciete-page-reading">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p className="mb-1 text-[10px] font-bold uppercase tracking-widest text-text-hint">
                            {t('messages.eyebrow')}
                        </p>
                        <h2 className="text-xl font-semibold text-text-main">{t('messages.title')}</h2>
                    </div>
                    <button
                        type="button"
                        onClick={() => setShowCompose(true)}
                        className="inline-flex w-full items-center justify-center gap-1.5 rounded-lg bg-primary px-3 py-1.5 text-xs font-bold text-white transition hover:opacity-90 sm:w-auto"
                    >
                        <Plus size={14} />
                        {t('messages.newMessage')}
                    </button>
                </div>

                <div className="flex flex-wrap gap-2">
                    <TabButton active={tab === 'inbox'} icon={Inbox} label={t('messages.tabs.inbox')} count={unreadCount} onClick={() => switchTab('inbox')} />
                    <TabButton active={tab === 'sent'} icon={Send} label={t('messages.tabs.sent')} count={0} onClick={() => switchTab('sent')} />
                    <TabButton active={tab === 'archived'} icon={Archive} label={t('messages.tabs.archived')} count={0} onClick={() => switchTab('archived')} />
                </div>

                <div className="overflow-hidden rounded-xl border border-border bg-surface">
                    {messages.data?.length === 0 ? (
                        <div className="px-4 py-12 text-center">
                            <Mail size={32} className="mx-auto mb-2 text-text-hint" />
                            <p className="text-sm text-text-hint">{t('messages.empty')}</p>
                        </div>
                    ) : (
                        <div className="divide-y divide-border">
                            {messages.data?.map((msg) => {
                                const isUnread = tab === 'inbox' && !msg.leido_at;
                                const sender = msg.remitente;
                                const senderName = sender ? `${sender.nombre} ${sender.apellidos ?? ''}`.trim() : '—';

                                return (
                                    <Link
                                        key={msg.id_mensaje}
                                        href={route('messages.show', msg.id_mensaje)}
                                        className={`flex items-start gap-3 px-4 py-3 transition-colors hover:bg-surface-2 ${isUnread ? 'bg-primary/5' : ''}`}
                                    >
                                        <div className="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-surface-2 text-[10px] font-bold text-text-muted">
                                            {senderName.charAt(0)}
                                        </div>
                                        <div className="min-w-0 flex-1">
                                            <div className="flex flex-wrap items-center justify-between gap-2">
                                                <p className={`truncate text-xs ${isUnread ? 'font-bold text-text-main' : 'text-text-muted'}`}>
                                                    {tab === 'sent' ? (msg.destinatario ? `${msg.destinatario.nombre} ${msg.destinatario.apellidos ?? ''}`.trim() : '—') : senderName}
                                                </p>
                                                <span className="shrink-0 text-[10px] text-text-hint">
                                                    {new Date(msg.created_at).toLocaleDateString()}
                                                </span>
                                            </div>
                                            <p className={`truncate text-xs ${isUnread ? 'font-semibold text-text-main' : 'text-text-muted'}`}>
                                                {msg.asunto}
                                            </p>
                                            <div className="mt-1 flex items-center gap-2">
                                                {msg.prioridad !== 'normal' && <PriorityBadge priority={msg.prioridad} t={t} />}
                                                {msg.es_aviso_sistema && (
                                                    <span className="text-[9px] font-bold uppercase tracking-widest text-accent">
                                                        {t('messages.systemNotice')}
                                                    </span>
                                                )}
                                            </div>
                                        </div>
                                        {isUnread && <div className="mt-2 h-2 w-2 shrink-0 rounded-full bg-primary" />}
                                    </Link>
                                );
                            })}
                        </div>
                    )}
                </div>

                {messages.last_page > 1 && (
                    <div className="flex items-center justify-center gap-2">
                        {messages.links?.map((link, i) => (
                            <Link
                                key={i}
                                href={link.url ?? '#'}
                                className={`rounded-lg px-3 py-1 text-xs transition ${
                                    link.active
                                        ? 'bg-primary text-white'
                                        : link.url
                                          ? 'bg-surface-2 text-text-muted hover:bg-border'
                                          : 'cursor-default text-text-hint opacity-50'
                                }`}
                                dangerouslySetInnerHTML={{ __html: link.label }}
                                preserveState
                            />
                        ))}
                    </div>
                )}
            </div>

            {showCompose && (
                <ComposeModal
                    users={users}
                    canBroadcastNotices={canBroadcastNotices}
                    onClose={() => setShowCompose(false)}
                    t={t}
                />
            )}
        </AuthenticatedLayout>
    );
}
