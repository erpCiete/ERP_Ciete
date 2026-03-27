import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import { useI18n } from '@/i18n';
import { Transition } from '@headlessui/react';
import { useForm, usePage } from '@inertiajs/react';

export default function SelectProfileAvatarForm({ className = '', avatarCatalog = [] }) {
    const user = usePage().props.auth.user;
    const { t } = useI18n();
    const defaultAvatarKey = avatarCatalog[0]?.key ?? 'avatar-ciete-logo';
    const initialAvatarKey = avatarCatalog.some((avatar) => avatar.key === user.avatar_key)
        ? user.avatar_key
        : defaultAvatarKey;

    const { data, setData, patch, errors, processing, recentlySuccessful } = useForm({
        avatar_key: initialAvatarKey,
    });

    const submit = (e) => {
        e.preventDefault();
        patch(route('profile.update'), { preserveScroll: true });
    };

    return (
        <section className={className}>
            <header>
                <h2 className="text-lg font-medium text-text-main">{t('profile.avatar.title')}</h2>
            </header>

            <form onSubmit={submit} className="mt-6 space-y-6">
                <div>
                    <InputLabel value={t('profile.avatar.selectLabel')} />
                    <div className="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-5">
                        {avatarCatalog.map((avatar) => {
                            const selected = data.avatar_key === avatar.key;
                            return (
                                <button
                                    key={avatar.key}
                                    type="button"
                                    onClick={() => setData('avatar_key', avatar.key)}
                                    className={`rounded-xl border p-2 transition ${
                                        selected
                                            ? 'border-primary ring-2 ring-primary/20'
                                            : 'border-border hover:border-primary/40'
                                    }`}
                                    aria-pressed={selected}
                                >
                                    <img
                                        src={avatar.src}
                                        alt={avatar.label}
                                        className="mx-auto h-14 w-14"
                                    />
                                    <p className="mt-2 truncate text-center text-[11px] font-medium text-text-muted">
                                        {avatar.label}
                                    </p>
                                </button>
                            );
                        })}
                    </div>
                    <InputError className="mt-2" message={errors.avatar_key} />
                </div>

                <div className="flex items-center gap-4">
                    <PrimaryButton disabled={processing}>{t('common.actions.save')}</PrimaryButton>

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
