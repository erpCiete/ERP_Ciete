import { useEffect, useState } from 'react';

function buildInitials(user) {
    const seed = [
        [user?.nombre, user?.apellidos].filter(Boolean).join(' ').trim(),
        user?.nombre_usuario,
        user?.email,
    ].find((value) => typeof value === 'string' && value.trim() !== '');

    if (!seed) {
        return 'U';
    }

    const chunks = seed
        .split(/[\s@._-]+/)
        .map((part) => part.trim())
        .filter(Boolean);

    if (chunks.length === 0) {
        return 'U';
    }

    const initials = chunks
        .slice(0, 2)
        .map((part) => (part[0] ?? '').toUpperCase())
        .join('');

    return initials || 'U';
}

function buildDisplayName(user) {
    const fullName = [user?.nombre, user?.apellidos]
        .filter(Boolean)
        .join(' ')
        .trim();

    if (fullName !== '') {
        return fullName;
    }

    return user?.nombre_usuario || user?.email || 'Usuario';
}

export default function UserAccountAvatar({
    user,
    className = 'h-8 w-8',
    fallbackClassName = 'bg-white/10 text-white/75',
}) {
    const avatarUrl = user?.avatar_url || null;
    const [hasImageError, setHasImageError] = useState(false);

    useEffect(() => {
        setHasImageError(false);
    }, [avatarUrl]);

    const shouldShowImage = Boolean(avatarUrl) && !hasImageError;
    const initials = buildInitials(user);
    const displayName = buildDisplayName(user);

    return (
        <span
            aria-hidden="true"
            className={`inline-flex shrink-0 items-center justify-center overflow-hidden rounded-full border border-white/20 ${className} ${shouldShowImage ? '' : fallbackClassName}`}
        >
            {shouldShowImage ? (
                <img
                    src={avatarUrl}
                    alt={`Avatar ${displayName}`}
                    className="h-full w-full object-cover"
                    onError={() => setHasImageError(true)}
                />
            ) : (
                <span className="text-[10px] font-bold uppercase tracking-wide">{initials}</span>
            )}
        </span>
    );
}
