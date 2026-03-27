import CieteMark from '@/Components/CieteMark';

export default function ApplicationLogo({ className = '', ...props }) {
    // Alias para mantener compatibilidad donde se importe ApplicationLogo.
    return <CieteMark className={className} {...props} />;
}
