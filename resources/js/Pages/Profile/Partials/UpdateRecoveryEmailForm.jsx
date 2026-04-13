import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { useI18n } from '@/i18n';
import { Transition } from '@headlessui/react';
import { useForm, usePage } from '@inertiajs/react';

export default function UpdateRecoveryEmailForm({ className = '' }) {
    const user = usePage().props.auth.user;
    const { t } = useI18n();

    const {
        data,
        setData,
        patch,
        errors,
        processing,
        recentlySuccessful,
    } = useForm({
        avatar_key: user.avatar_key,
        email_recuperacion: user.email_recuperacion ?? '',
    });

    const submit = (e) => {
        e.preventDefault();
        patch(route('profile.update'), { preserveScroll: true });
    };

    return (
        <section className={className}>
            <header>
                <h2 className="text-lg font-medium text-text-main">
                    {t('profile.recoveryEmail.title')}
                </h2>
                <p className="mt-1 text-sm text-text-muted">
                    {t('profile.recoveryEmail.description')}
                </p>
            </header>

            <form onSubmit={submit} className="mt-6 space-y-6">
                <div>
                    <InputLabel
                        htmlFor="email_recuperacion"
                        value={t('profile.recoveryEmail.label')}
                    />
                    <TextInput
                        id="email_recuperacion"
                        type="email"
                        value={data.email_recuperacion}
                        onChange={(e) => setData('email_recuperacion', e.target.value)}
                        className="mt-1 block w-full"
                        placeholder={t('profile.recoveryEmail.placeholder')}
                    />
                    <p className="mt-1 text-xs text-text-hint">
                        {t('profile.recoveryEmail.hint')}
                    </p>
                    <InputError message={errors.email_recuperacion} className="mt-2" />
                </div>

                <div className="flex items-center gap-4">
                    <PrimaryButton disabled={processing}>
                        {t('common.actions.save')}
                    </PrimaryButton>

                    <Transition
                        show={recentlySuccessful}
                        enter="transition ease-in-out"
                        enterFrom="opacity-0"
                        leave="transition ease-in-out"
                        leaveTo="opacity-0"
                    >
                        <p className="text-sm text-text-muted">{t('profile.saved')}</p>
                    </Transition>
                </div>
            </form>
        </section>
    );
}
