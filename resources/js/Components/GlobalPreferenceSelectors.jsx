import LanguageSelector from '@/Components/LanguageSelector';
import ThemeSelector from '@/Components/ThemeSelector';

export default function GlobalPreferenceSelectors({ compact = false, className = '' }) {
    return (
        <div className={`flex items-center gap-1 ${className}`.trim()}>
            <ThemeSelector compact={compact} />
            <LanguageSelector compact={compact} />
        </div>
    );
}
