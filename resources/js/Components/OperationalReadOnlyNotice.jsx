import { usePage } from '@inertiajs/react';
import { Shield } from 'lucide-react';
import { useI18n } from '@/i18n';

export default function OperationalReadOnlyNotice({ className = '' }) {
    const { t } = useI18n();
    const user = usePage().props.auth?.user;

    if (!user?.is_technical_admin || user?.can_mutate_operational_data) {
        return null;
    }

    const classes = [
        'mb-4 rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sky-900',
        className,
    ].filter(Boolean).join(' ');

    return (
        <div className={classes}>
            <div className="flex items-start gap-3">
                <div className="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/70 text-sky-700">
                    <Shield size={16} />
                </div>
                <div>
                    <p className="text-sm font-semibold">{t('supportReadOnly.title')}</p>
                    <p className="mt-1 text-sm text-sky-800/90">{t('supportReadOnly.description')}</p>
                </div>
            </div>
        </div>
    );
}
