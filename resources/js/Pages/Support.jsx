import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useI18n } from '@/i18n';
import { Head, useForm, usePage } from '@inertiajs/react';
import { CheckCircle, LifeBuoy, Send } from 'lucide-react';

export default function Support() {
    const { t } = useI18n();
    const user = usePage().props.auth.user;
    const status = usePage().props.flash?.status;

    const { data, setData, post, processing, errors, reset } = useForm({
        topic: '',
        subject: '',
        message: '',
        priority: 'normal',
    });

    const topics = [
        { value: 'acceso', label: t('supportPage.topics.access') },
        { value: 'datos', label: t('supportPage.topics.data') },
        { value: 'error', label: t('supportPage.topics.error') },
        { value: 'rendimiento', label: t('supportPage.topics.performance') },
        { value: 'funcionalidad', label: t('supportPage.topics.feature') },
        { value: 'permisos', label: t('supportPage.topics.permissions') },
        { value: 'facturacion', label: t('supportPage.topics.billing') },
        { value: 'obras', label: t('supportPage.topics.works') },
        { value: 'legalizaciones', label: t('supportPage.topics.legalizations') },
        { value: 'otro', label: t('supportPage.topics.other') },
    ];

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('support.send'), {
            onSuccess: () => reset(),
            preserveScroll: true,
        });
    };

    return (
        <AuthenticatedLayout header={t('supportPage.header')}>
            <Head title={t('supportPage.headTitle')} />

            <div className="ciete-page max-w-4xl">
                <div>
                    <p className="mb-1 text-[10px] font-bold uppercase tracking-widest text-text-hint">
                        {t('supportPage.eyebrow')}
                    </p>
                    <h2 className="text-xl font-semibold text-text-main">{t('supportPage.title')}</h2>
                    <p className="mt-1 text-sm text-text-muted">{t('supportPage.description')}</p>
                </div>

                {status === 'support-sent' && (
                    <div className="flex items-center gap-2 rounded-xl border border-state-done-dot/30 bg-state-done-bg px-4 py-3 text-sm text-state-done-text">
                        <CheckCircle size={16} />
                        {t('supportPage.sent')}
                    </div>
                )}

                <div className="rounded-xl border border-border bg-surface p-5 lg:p-6">
                    <div className="mb-5 grid gap-5 xl:grid-cols-[minmax(0,15rem)_minmax(0,1fr)]">
                        <div className="rounded-xl border border-border bg-surface-2 p-4">
                            <div className="flex items-center gap-2.5">
                                <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-surface">
                                    <LifeBuoy size={16} className="text-text-muted" />
                                </div>
                                <div className="min-w-0">
                                    <p className="truncate text-xs font-semibold text-text-main">{user?.nombre} {user?.apellidos}</p>
                                    <p className="truncate text-[10px] text-text-hint">{user?.email}</p>
                                </div>
                            </div>
                        </div>

                        <form onSubmit={handleSubmit} className="space-y-4">
                        <div>
                            <label className="mb-1 block text-[10px] font-bold uppercase tracking-widest text-text-hint">
                                {t('supportPage.topicLabel')}
                            </label>
                            <select
                                value={data.topic}
                                onChange={(e) => setData('topic', e.target.value)}
                                className="w-full rounded-lg border border-border bg-surface px-3 py-2 text-sm text-text-main focus:border-primary focus:outline-hidden focus:ring-1 focus:ring-primary"
                            >
                                <option value="">{t('supportPage.selectTopic')}</option>
                                {topics.map((topic) => (
                                    <option key={topic.value} value={topic.value}>
                                        {topic.label}
                                    </option>
                                ))}
                            </select>
                            {errors.topic && <p className="mt-1 text-xs text-state-blocked-dot">{errors.topic}</p>}
                        </div>

                        <div>
                            <label className="mb-1 block text-[10px] font-bold uppercase tracking-widest text-text-hint">
                                {t('supportPage.subjectLabel')}
                            </label>
                            <input
                                type="text"
                                value={data.subject}
                                onChange={(e) => setData('subject', e.target.value)}
                                placeholder={t('supportPage.subjectPlaceholder')}
                                className="w-full rounded-lg border border-border bg-surface px-3 py-2 text-sm text-text-main placeholder:text-text-hint focus:border-primary focus:outline-hidden focus:ring-1 focus:ring-primary"
                                maxLength={255}
                            />
                            {errors.subject && <p className="mt-1 text-xs text-state-blocked-dot">{errors.subject}</p>}
                        </div>

                        <div>
                            <label className="mb-1 block text-[10px] font-bold uppercase tracking-widest text-text-hint">
                                {t('supportPage.messageLabel')}
                            </label>
                            <textarea
                                value={data.message}
                                onChange={(e) => setData('message', e.target.value)}
                                rows={6}
                                placeholder={t('supportPage.messagePlaceholder')}
                                className="w-full resize-none rounded-lg border border-border bg-surface px-3 py-2 text-sm text-text-main placeholder:text-text-hint focus:border-primary focus:outline-hidden focus:ring-1 focus:ring-primary"
                                maxLength={5000}
                            />
                            {errors.message && <p className="mt-1 text-xs text-state-blocked-dot">{errors.message}</p>}
                        </div>

                        <div>
                            <label className="mb-1 block text-[10px] font-bold uppercase tracking-widest text-text-hint">
                                {t('supportPage.priorityLabel')}
                            </label>
                            <select
                                value={data.priority}
                                onChange={(e) => setData('priority', e.target.value)}
                                className="w-full rounded-lg border border-border bg-surface px-3 py-2 text-sm text-text-main focus:border-primary focus:outline-hidden focus:ring-1 focus:ring-primary"
                            >
                                <option value="normal">{t('supportPage.priorityNormal')}</option>
                                <option value="alta">{t('supportPage.priorityHigh')}</option>
                                <option value="urgente">{t('supportPage.priorityUrgent')}</option>
                            </select>
                        </div>

                        <div className="flex justify-end pt-2">
                            <button
                                type="submit"
                                disabled={processing}
                                className="inline-flex w-full items-center justify-center gap-1.5 rounded-lg bg-primary px-4 py-2 text-xs font-bold text-white transition hover:opacity-90 disabled:opacity-50 sm:w-auto"
                            >
                                <Send size={14} />
                                {t('supportPage.submit')}
                            </button>
                        </div>
                        </form>
                    </div>
                </div>

                <p className="text-center text-[10px] uppercase tracking-widest text-text-hint">
                    {t('supportPage.footer')}
                </p>
            </div>
        </AuthenticatedLayout>
    );
}
