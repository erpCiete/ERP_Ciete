import Modal from '@/Components/Modal';
import { useI18n } from '@/i18n';

export default function ModalConfirmacion({
    isOpen,
    title,
    message,
    onClose,
    onConfirm,
    confirmLabel,
}) {
    const { t } = useI18n();

    return (
        <Modal show={isOpen} maxWidth="md" onClose={onClose}>
            <div className="bg-surface">
                <div className="border-b border-border px-6 py-5">
                    <h3 className="text-lg font-semibold text-text-main">{title}</h3>
                    <p className="mt-2 text-sm text-text-muted">{message}</p>
                </div>

                <div className="flex flex-col-reverse gap-3 px-6 py-4 sm:flex-row sm:justify-end">
                    <button
                        type="button"
                        onClick={onClose}
                        className="w-full rounded-md border border-border bg-surface px-4 py-2 text-xs font-semibold uppercase tracking-widest text-text-muted transition hover:bg-surface-2 hover:text-text-main sm:w-auto"
                    >
                        {t('common.actions.cancel')}
                    </button>
                    <button
                        type="button"
                        onClick={onConfirm}
                        className="w-full rounded-md bg-(--ciete-red) px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-(--ciete-red-dark) sm:w-auto"
                    >
                        {confirmLabel || t('common.actions.confirm')}
                    </button>
                </div>
            </div>
        </Modal>
    );
}
