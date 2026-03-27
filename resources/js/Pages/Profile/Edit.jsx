import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useI18n } from '@/i18n';
import { Head } from '@inertiajs/react';
import SelectProfileAvatarForm from './Partials/SelectProfileAvatarForm';
import UpdatePasswordForm from './Partials/UpdatePasswordForm';

export default function Edit({ avatarCatalog = [] }) {
    const { t } = useI18n();

    return (
        <AuthenticatedLayout
            header={t('profileEdit.header')}
        >
            <Head title={t('profileEdit.headTitle')} />

            <div className="mx-auto max-w-4xl space-y-6">
                <div className="ciete-panel">
                    <SelectProfileAvatarForm className="max-w-4xl" avatarCatalog={avatarCatalog} />
                </div>

                <div className="ciete-panel">
                    <UpdatePasswordForm className="max-w-xl" />
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
