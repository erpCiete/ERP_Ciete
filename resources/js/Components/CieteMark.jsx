export default function CieteMark({ className = '', ...props }) {
    // Logo base en SVG para reutilizar.
    return (
        <svg
            viewBox="0 0 136 136"
            xmlns="http://www.w3.org/2000/svg"
            className={`aspect-square ${className}`.trim()}
            {...props}
        >
            <rect x="20" y="20" width="64" height="64" fill="#FE0000" />
            <rect x="52" y="52" width="64" height="64" fill="#003EFF" />
            <rect x="52" y="52" width="32" height="32" fill="#000000" />
        </svg>
    );
}
