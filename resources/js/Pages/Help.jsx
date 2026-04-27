import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useI18n } from '@/i18n';
import { Head, usePage } from '@inertiajs/react';
import { ChevronDown, Search } from 'lucide-react';
import { useMemo, useState } from 'react';

function Section({ id, title, icon, children, isOpen, onToggle }) {
    return (
        <div className="overflow-hidden rounded-xl border border-border bg-surface">
            <button
                type="button"
                onClick={() => onToggle(id)}
                className="flex w-full items-center justify-between px-4 py-3 text-left transition-colors hover:bg-surface-2 focus-visible:outline-hidden focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-inset"
                aria-expanded={isOpen}
                aria-controls={`help-section-${id}`}
            >
                <span className="flex items-center gap-2.5">
                    <span className="text-base">{icon}</span>
                    <span className="text-sm font-medium text-text-main">{title}</span>
                </span>
                <ChevronDown
                    size={16}
                    className={`shrink-0 text-text-hint transition-transform duration-200 ${isOpen ? 'rotate-180' : ''}`}
                />
            </button>
            {isOpen && (
                <div
                    id={`help-section-${id}`}
                    className="border-t border-border px-4 py-4 text-sm leading-relaxed text-text-muted"
                >
                    {children}
                </div>
            )}
        </div>
    );
}

function HelpField({ label, description }) {
    return (
        <div className="flex flex-col gap-0.5 rounded-lg bg-surface-2 px-3 py-2">
            <span className="text-xs font-semibold text-text-main">{label}</span>
            <span className="text-xs text-text-muted">{description}</span>
        </div>
    );
}

function HelpStep({ number, text }) {
    return (
        <div className="flex items-start gap-2.5">
            <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-primary text-[10px] font-bold text-white">
                {number}
            </span>
            <span className="text-xs text-text-muted">{text}</span>
        </div>
    );
}

function RoleBadge({ label, color = 'bg-accent/15 text-accent' }) {
    return (
        <span className={`inline-flex items-center rounded-full px-2 py-0.5 text-[9px] font-bold uppercase tracking-widest ${color}`}>
            {label}
        </span>
    );
}

export default function Help() {
    const user = usePage().props.auth.user;
    const { t } = useI18n();
    const [openSections, setOpenSections] = useState(new Set(['getting-started']));
    const [search, setSearch] = useState('');

    const isAdmin = user?.is_admin;
    const isCierre = user?.can_access_direction_panel;
    const canManageClientes = user?.permission_slugs?.includes('empresas_contactos.gestionar');
    const canViewEstaciones = user?.permission_slugs?.some((p) =>
        ['estaciones.ver', 'estaciones.gestionar'].includes(p),
    );

    const toggleSection = (id) => {
        setOpenSections((prev) => {
            const next = new Set(prev);
            if (next.has(id)) {
                next.delete(id);
            } else {
                next.add(id);
            }
            return next;
        });
    };

    const sections = useMemo(
        () => [
            { id: 'getting-started', icon: '🚀', visible: true },
            { id: 'login', icon: '🔐', visible: true },
            { id: 'password-recovery', icon: '🔑', visible: true },
            { id: 'home', icon: '🏠', visible: true },
            { id: 'profile', icon: '👤', visible: true },
            { id: 'settings', icon: '⚙️', visible: true },
            { id: 'dashboard', icon: '📊', visible: true },
            { id: 'clients', icon: '🏢', visible: canManageClientes },
            { id: 'stations', icon: '⛽', visible: canViewEstaciones },
            { id: 'works', icon: '🔧', visible: true },
            { id: 'orders', icon: '📦', visible: true },
            { id: 'legalizations', icon: '📋', visible: true },
            { id: 'reports', icon: '📈', visible: true },
            { id: 'messages', icon: '💬', visible: true },
            { id: 'support', icon: '🛟', visible: true },
            { id: 'status', icon: '🟢', visible: true },
            { id: 'admin', icon: '🛡️', visible: isAdmin },
            { id: 'maintenance', icon: '🔧', visible: isAdmin },
            { id: 'excel-import', icon: '📥', visible: isAdmin },
            { id: 'closure', icon: '✅', visible: isCierre },
            { id: 'faq', icon: '❓', visible: true },
        ],
        [isAdmin, isCierre, canManageClientes, canViewEstaciones],
    );

    const filteredSections = useMemo(() => {
        const visible = sections.filter((s) => s.visible);
        if (!search.trim()) return visible;

        const q = search.toLowerCase();
        return visible.filter((s) => {
            const title = t(`help.sections.${s.id}.title`).toLowerCase();
            const desc = t(`help.sections.${s.id}.intro`).toLowerCase();
            return title.includes(q) || desc.includes(q);
        });
    }, [sections, search, t]);

    const renderContent = (sectionId) => {
        switch (sectionId) {
            case 'getting-started':
                return (
                    <div className="space-y-3">
                        <p>{t('help.sections.getting-started.intro')}</p>
                        <div className="space-y-2">
                            <HelpStep number={1} text={t('help.sections.getting-started.step1')} />
                            <HelpStep number={2} text={t('help.sections.getting-started.step2')} />
                            <HelpStep number={3} text={t('help.sections.getting-started.step3')} />
                            <HelpStep number={4} text={t('help.sections.getting-started.step4')} />
                        </div>
                    </div>
                );
            case 'login':
                return (
                    <div className="space-y-3">
                        <p>{t('help.sections.login.intro')}</p>
                        <div className="grid gap-2 sm:grid-cols-2">
                            <HelpField label={t('help.sections.login.emailField')} description={t('help.sections.login.emailDesc')} />
                            <HelpField label={t('help.sections.login.passwordField')} description={t('help.sections.login.passwordDesc')} />
                        </div>
                        <p className="text-xs italic text-text-hint">{t('help.sections.login.tip')}</p>
                    </div>
                );
            case 'password-recovery':
                return (
                    <div className="space-y-3">
                        <p>{t('help.sections.password-recovery.intro')}</p>
                        <div className="space-y-2">
                            <HelpStep number={1} text={t('help.sections.password-recovery.step1')} />
                            <HelpStep number={2} text={t('help.sections.password-recovery.step2')} />
                            <HelpStep number={3} text={t('help.sections.password-recovery.step3')} />
                            <HelpStep number={4} text={t('help.sections.password-recovery.step4')} />
                            <HelpStep number={5} text={t('help.sections.password-recovery.step5')} />
                        </div>
                        <p className="text-xs italic text-text-hint">{t('help.sections.password-recovery.tip')}</p>
                    </div>
                );
            case 'home':
                return (
                    <div className="space-y-3">
                        <p>{t('help.sections.home.intro')}</p>
                        <div className="grid gap-2 sm:grid-cols-2">
                            <HelpField label={t('help.sections.home.notices')} description={t('help.sections.home.noticesDesc')} />
                            <HelpField label={t('help.sections.home.updates')} description={t('help.sections.home.updatesDesc')} />
                            <HelpField label={t('help.sections.home.news')} description={t('help.sections.home.newsDesc')} />
                            <HelpField label={t('help.sections.home.dock')} description={t('help.sections.home.dockDesc')} />
                        </div>
                    </div>
                );
            case 'profile':
                return (
                    <div className="space-y-3">
                        <p>{t('help.sections.profile.intro')}</p>
                        <div className="grid gap-2 sm:grid-cols-2">
                            <HelpField label={t('help.sections.profile.avatar')} description={t('help.sections.profile.avatarDesc')} />
                            <HelpField label={t('help.sections.profile.password')} description={t('help.sections.profile.passwordDesc')} />
                            <HelpField label={t('help.sections.profile.recoveryEmail')} description={t('help.sections.profile.recoveryEmailDesc')} />
                        </div>
                    </div>
                );
            case 'settings':
                return (
                    <div className="space-y-3">
                        <p>{t('help.sections.settings.intro')}</p>
                        <div className="grid gap-2 sm:grid-cols-2">
                            <HelpField label={t('help.sections.settings.theme')} description={t('help.sections.settings.themeDesc')} />
                            <HelpField label={t('help.sections.settings.language')} description={t('help.sections.settings.languageDesc')} />
                        </div>
                    </div>
                );
            case 'dashboard':
                return (
                    <div className="space-y-3">
                        <p>{t('help.sections.dashboard.intro')}</p>
                        <div className="grid gap-2 sm:grid-cols-2">
                            <HelpField label={t('help.sections.dashboard.metrics')} description={t('help.sections.dashboard.metricsDesc')} />
                            <HelpField label={t('help.sections.dashboard.works')} description={t('help.sections.dashboard.worksDesc')} />
                            <HelpField label={t('help.sections.dashboard.orders')} description={t('help.sections.dashboard.ordersDesc')} />
                            <HelpField label={t('help.sections.dashboard.legalizations')} description={t('help.sections.dashboard.legalizationsDesc')} />
                        </div>
                    </div>
                );
            case 'clients':
                return (
                    <div className="space-y-3">
                        <p>{t('help.sections.clients.intro')}</p>
                        <div className="grid gap-2 sm:grid-cols-2">
                            <HelpField label={t('help.sections.clients.name')} description={t('help.sections.clients.nameDesc')} />
                            <HelpField label={t('help.sections.clients.context')} description={t('help.sections.clients.contextDesc')} />
                            <HelpField label={t('help.sections.clients.cif')} description={t('help.sections.clients.cifDesc')} />
                            <HelpField label={t('help.sections.clients.web')} description={t('help.sections.clients.webDesc')} />
                            <HelpField label={t('help.sections.clients.businessName')} description={t('help.sections.clients.businessNameDesc')} />
                            <HelpField label={t('help.sections.clients.notes')} description={t('help.sections.clients.notesDesc')} />
                        </div>
                        <div className="space-y-2">
                            <p className="text-xs font-semibold text-text-main">{t('help.sections.clients.createTitle')}</p>
                            <HelpStep number={1} text={t('help.sections.clients.createStep1')} />
                            <HelpStep number={2} text={t('help.sections.clients.createStep2')} />
                            <HelpStep number={3} text={t('help.sections.clients.createStep3')} />
                        </div>
                    </div>
                );
            case 'stations':
                return (
                    <div className="space-y-3">
                        <p>{t('help.sections.stations.intro')}</p>
                        <div className="grid gap-2 sm:grid-cols-2">
                            <HelpField label={t('help.sections.stations.name')} description={t('help.sections.stations.nameDesc')} />
                            <HelpField label={t('help.sections.stations.code')} description={t('help.sections.stations.codeDesc')} />
                            <HelpField label={t('help.sections.stations.client')} description={t('help.sections.stations.clientDesc')} />
                            <HelpField label={t('help.sections.stations.location')} description={t('help.sections.stations.locationDesc')} />
                            <HelpField label={t('help.sections.stations.postal')} description={t('help.sections.stations.postalDesc')} />
                            <HelpField label={t('help.sections.stations.status')} description={t('help.sections.stations.statusDesc')} />
                        </div>
                    </div>
                );
            case 'works':
            case 'orders':
            case 'legalizations':
            case 'reports':
                return (
                    <div className="space-y-3">
                        <p>{t(`help.sections.${sectionId}.intro`)}</p>
                        <div className="rounded-lg border border-border bg-surface-2 px-4 py-3 text-center">
                            <p className="text-[10px] font-bold uppercase tracking-widest text-text-hint">
                                {t('help.comingSoon')}
                            </p>
                        </div>
                    </div>
                );
            case 'messages':
                return (
                    <div className="space-y-3">
                        <p>{t('help.sections.messages.intro')}</p>
                        <div className="space-y-2">
                            <HelpStep number={1} text={t('help.sections.messages.step1')} />
                            <HelpStep number={2} text={t('help.sections.messages.step2')} />
                            <HelpStep number={3} text={t('help.sections.messages.step3')} />
                        </div>
                        <div className="grid gap-2 sm:grid-cols-2">
                            <HelpField label={t('help.sections.messages.inbox')} description={t('help.sections.messages.inboxDesc')} />
                            <HelpField label={t('help.sections.messages.sent')} description={t('help.sections.messages.sentDesc')} />
                            <HelpField label={t('help.sections.messages.archived')} description={t('help.sections.messages.archivedDesc')} />
                            <HelpField label={t('help.sections.messages.priority')} description={t('help.sections.messages.priorityDesc')} />
                        </div>
                    </div>
                );
            case 'support':
                return (
                    <div className="space-y-3">
                        <p>{t('help.sections.support.intro')}</p>
                        <div className="space-y-2">
                            <HelpStep number={1} text={t('help.sections.support.step1')} />
                            <HelpStep number={2} text={t('help.sections.support.step2')} />
                            <HelpStep number={3} text={t('help.sections.support.step3')} />
                        </div>
                    </div>
                );
            case 'status':
                return (
                    <div className="space-y-3">
                        <p>{t('help.sections.status.intro')}</p>
                        <div className="rounded-lg border border-primary/30 bg-primary/5 px-3 py-2">
                            <p className="text-xs text-text-body">{t('help.sections.status.visibility')}</p>
                        </div>
                        <div className="grid gap-2 sm:grid-cols-2">
                            <HelpField label={t('help.sections.status.database')} description={t('help.sections.status.databaseDesc')} />
                            <HelpField label={t('help.sections.status.storage')} description={t('help.sections.status.storageDesc')} />
                            <HelpField label={t('help.sections.status.mail')} description={t('help.sections.status.mailDesc')} />
                            <HelpField label={t('help.sections.status.queue')} description={t('help.sections.status.queueDesc')} />
                            <HelpField label={t('help.sections.status.app')} description={t('help.sections.status.appDesc')} />
                            <HelpField label={t('help.sections.status.maintenance')} description={t('help.sections.status.maintenanceDesc')} />
                        </div>
                    </div>
                );
            case 'admin':
                return (
                    <div className="space-y-3">
                        <RoleBadge label="Admin" color="bg-primary/15 text-primary" />
                        <p>{t('help.sections.admin.intro')}</p>
                        <div className="rounded-lg border border-primary/30 bg-primary/5 px-3 py-2">
                            <HelpField label={t('help.sections.admin.visibility')} description={t('help.sections.admin.visibilityDesc')} />
                        </div>
                        <div className="grid gap-2 sm:grid-cols-2">
                            <HelpField label={t('help.sections.admin.overview')} description={t('help.sections.admin.overviewDesc')} />
                            <HelpField label={t('help.sections.admin.users')} description={t('help.sections.admin.usersDesc')} />
                            <HelpField label={t('help.sections.admin.modules')} description={t('help.sections.admin.modulesDesc')} />
                            <HelpField label={t('help.sections.admin.activity')} description={t('help.sections.admin.activityDesc')} />
                        </div>
                    </div>
                );
            case 'maintenance':
                return (
                    <div className="space-y-3">
                        <RoleBadge label="Admin" color="bg-primary/15 text-primary" />
                        <p>{t('help.sections.maintenance.intro')}</p>
                        <div className="space-y-2">
                            <HelpStep number={1} text={t('help.sections.maintenance.step1')} />
                            <HelpStep number={2} text={t('help.sections.maintenance.step2')} />
                            <HelpStep number={3} text={t('help.sections.maintenance.step3')} />
                        </div>
                        <p className="text-xs italic text-text-hint">{t('help.sections.maintenance.tip')}</p>
                    </div>
                );
            case 'excel-import':
                return (
                    <div className="space-y-3">
                        <RoleBadge label="Admin" color="bg-primary/15 text-primary" />
                        <p>{t('help.sections.excel-import.intro')}</p>
                        <div className="space-y-2">
                            <HelpStep number={1} text={t('help.sections.excel-import.step1')} />
                            <HelpStep number={2} text={t('help.sections.excel-import.step2')} />
                            <HelpStep number={3} text={t('help.sections.excel-import.step3')} />
                            <HelpStep number={4} text={t('help.sections.excel-import.step4')} />
                            <HelpStep number={5} text={t('help.sections.excel-import.step5')} />
                        </div>
                        <div className="grid gap-2 sm:grid-cols-2">
                            <HelpField label={t('help.sections.excel-import.validation')} description={t('help.sections.excel-import.validationDesc')} />
                            <HelpField label={t('help.sections.excel-import.formats')} description={t('help.sections.excel-import.formatsDesc')} />
                        </div>
                        <p className="text-xs italic text-text-hint">{t('help.sections.excel-import.tip')}</p>
                    </div>
                );
            case 'closure':
                return (
                    <div className="space-y-3">
                        <RoleBadge label="Cierre" color="bg-state-progress-bg text-state-progress-text" />
                        <p>{t('help.sections.closure.intro')}</p>
                        <div className="grid gap-2 sm:grid-cols-2">
                            <HelpField label={t('help.sections.closure.flow')} description={t('help.sections.closure.flowDesc')} />
                            <HelpField label={t('help.sections.closure.review')} description={t('help.sections.closure.reviewDesc')} />
                            <HelpField label={t('help.sections.closure.checklist')} description={t('help.sections.closure.checklistDesc')} />
                            <HelpField label={t('help.sections.closure.incidents')} description={t('help.sections.closure.incidentsDesc')} />
                            <HelpField label={t('help.sections.closure.export')} description={t('help.sections.closure.exportDesc')} />
                            <HelpField label={t('help.sections.closure.traceability')} description={t('help.sections.closure.traceabilityDesc')} />
                        </div>
                    </div>
                );
            case 'faq':
                return (
                    <div className="space-y-3">
                        {[1, 2, 3, 4, 5, 6, 7, 8].map((n) => (
                            <div key={n} className="rounded-lg bg-surface-2 px-3 py-2">
                                <p className="text-xs font-semibold text-text-main">{t(`help.sections.faq.q${n}`)}</p>
                                <p className="mt-1 text-xs text-text-muted">{t(`help.sections.faq.a${n}`)}</p>
                            </div>
                        ))}
                    </div>
                );
            default:
                return null;
        }
    };

    return (
        <AuthenticatedLayout header={t('help.header')}>
            <Head title={t('help.headTitle')} />

            <div className="mx-auto max-w-3xl space-y-5 pb-10">
                <div>
                    <p className="mb-1 text-[10px] font-bold uppercase tracking-widest text-text-hint">
                        {t('help.eyebrow')}
                    </p>
                    <h2 className="text-xl font-semibold text-text-main">{t('help.title')}</h2>
                    <p className="mt-1 text-sm text-text-muted">{t('help.description')}</p>
                </div>

                <div className="relative">
                    <Search size={16} className="absolute left-3 top-1/2 -translate-y-1/2 text-text-hint" />
                    <input
                        type="text"
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        placeholder={t('help.searchPlaceholder')}
                        className="w-full rounded-xl border border-border bg-surface py-2.5 pl-9 pr-4 text-sm text-text-main placeholder:text-text-hint focus:border-primary focus:outline-hidden focus:ring-1 focus:ring-primary"
                    />
                </div>

                <div className="flex flex-wrap gap-1.5">
                    {isAdmin && <RoleBadge label="Admin" color="bg-primary/15 text-primary" />}
                    {isCierre && <RoleBadge label="Cierre" color="bg-state-progress-bg text-state-progress-text" />}
                    <RoleBadge label={t('help.roleBadgeUser')} />
                </div>

                <div className="space-y-2">
                    {filteredSections.map((section) => (
                        <Section
                            key={section.id}
                            id={section.id}
                            title={t(`help.sections.${section.id}.title`)}
                            icon={section.icon}
                            isOpen={openSections.has(section.id)}
                            onToggle={toggleSection}
                        >
                            {renderContent(section.id)}
                        </Section>
                    ))}

                    {filteredSections.length === 0 && (
                        <div className="py-12 text-center text-sm text-text-hint">
                            {t('help.noResults')}
                        </div>
                    )}
                </div>

                <p className="text-center text-[10px] uppercase tracking-widest text-text-hint">
                    {t('help.footer')}
                </p>
            </div>
        </AuthenticatedLayout>
    );
}
