import { createPortal } from 'react-dom';

export default function Modal({
    children,
    show = false,
    maxWidth = '2xl',
    closeable = true,
    onClose = () => {},
}) {
    if (!show) {
        return null;
    }

    const close = () => {
        if (closeable) {
            onClose();
        }
    };

    const maxWidthClass = {
        sm: 'max-w-sm',
        md: 'max-w-md',
        lg: 'max-w-lg',
        xl: 'max-w-xl',
        '2xl': 'max-w-2xl',
    }[maxWidth] ?? 'max-w-2xl';

    const modal = (
        <div
            className="fixed inset-0 flex items-center justify-center p-4"
            style={{ zIndex: 2147483647 }}
            role="presentation"
        >
            <button
                type="button"
                aria-label="Cerrar modal"
                className="absolute inset-0 h-full w-full cursor-default bg-black/60"
                onClick={close}
                tabIndex={closeable ? 0 : -1}
            />

            <div
                role="dialog"
                aria-modal="true"
                className={`relative w-full ${maxWidthClass} max-h-[calc(100dvh-2rem)] overflow-y-auto overscroll-contain rounded-2xl border border-border bg-surface text-text-main shadow-2xl`}
                style={{ zIndex: 2147483647 }}
            >
                {children}
            </div>
        </div>
    );

    if (typeof document === 'undefined') {
        return modal;
    }

    return createPortal(modal, document.body);
}
