import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import ContextSelector from '@/Components/ContextSelector';
import VisualStyleSelector from '@/Components/VisualStyleSelector';
import { useI18n } from '@/i18n';
import { Head } from '@inertiajs/react';
import SelectProfileAvatarForm from './Partials/SelectProfileAvatarForm';
import UpdatePasswordForm from './Partials/UpdatePasswordForm';
import UpdateRecoveryEmailForm from './Partials/UpdateRecoveryEmailForm';

export default function Edit({ avatarCatalog = [] }) {
    const { t } = useI18n();

    return (
        <AuthenticatedLayout
            header={t('profileEdit.header')}
        >
            <Head title={t('profileEdit.headTitle')} />

            <div className="mx-auto max-w-4xl space-y-6">
                <div className="ciete-panel">
                    <section className="space-y-4" aria-labelledby="profile-context-title">
                        <div>
                            <p className="text-[10px] font-bold uppercase tracking-[0.18em] text-text-hint">
                                Contexto
                            </p>
                            <h3 id="profile-context-title" className="mt-1 text-lg font-semibold text-text-main">
                                Contexto activo
                            </h3>
                            <p className="mt-1 max-w-2xl text-sm text-text-muted">
                                Selecciona el contexto de trabajo para altas y consultas contextuales.
                            </p>
                        </div>
                        <ContextSelector />
                    </section>
                </div>

                <div className="ciete-panel">
                    <VisualStyleSelector className="max-w-4xl" />
                </div>

                <div className="ciete-panel">
                    <SelectProfileAvatarForm className="max-w-4xl" avatarCatalog={avatarCatalog} />
                </div>

                <div className="ciete-panel">
                    <UpdateRecoveryEmailForm className="max-w-xl" />
                </div>

                <div className="ciete-panel">
                    <UpdatePasswordForm className="max-w-xl" />
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
