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
                `inline-flex items-center rounded-md border border-transparent bg-(--ciete-red) px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-(--ciete-red-dark) focus:bg-(--ciete-red-dark) focus:outline-hidden focus:ring-2 focus:ring-(--ciete-red) focus:ring-offset-2 active:bg-(--ciete-red-dark) ${
                    disabled && 'opacity-25'
                } ` + className
            }
            disabled={disabled}
        >
            {children}
        </button>
    );
}
