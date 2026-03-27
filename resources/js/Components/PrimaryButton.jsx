export default function PrimaryButton({
    className = '',
    disabled,
    children,
    ...props
}) {
    return (
        <button
            {...props}
            className={
                `inline-flex items-center rounded-md border border-transparent bg-[var(--ciete-red)] px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-[var(--ciete-red-dark)] focus:bg-[var(--ciete-red-dark)] focus:outline-none focus:ring-2 focus:ring-[var(--ciete-red)] focus:ring-offset-2 active:bg-[var(--ciete-red-dark)] ${
                    disabled && 'opacity-25'
                } ` + className
            }
            disabled={disabled}
        >
            {children}
        </button>
    );
}
