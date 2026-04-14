const EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const DNI_CONTROL_LETTERS = 'TRWAGMYFPDXBNJZSQVHLCKE';
const CIF_CONTROL_LETTERS = 'JABCDEFGHI';
const CIF_DIGIT_ONLY_TYPES = new Set(['A', 'B', 'E', 'H']);
const CIF_LETTER_ONLY_TYPES = new Set(['N', 'P', 'Q', 'R', 'S', 'W']);
const SPANISH_POSTAL_CODE_PATTERN = /^(0[1-9]|[1-4]\d|5[0-2])\d{3}$/;
const INTERNAL_STATION_CODE_PATTERN = /^[A-Z0-9]{2,10}(?:-[A-Z0-9]{2,10})*-\d{2,6}$/;
const REPSOL_STATION_CODE_PATTERN = /^REPSOL-\d{4,6}$/;
const MOEVE_STATION_CODE_PATTERN = /^MOEVE-\d{4,6}$/;

export function isBlank(value) {
    return String(value ?? '').trim() === '';
}

export function exceedsMaxLength(value, maxLength) {
    return String(value ?? '').length > maxLength;
}

export function hasValidEmailFormat(value) {
    return EMAIL_PATTERN.test(String(value ?? '').trim());
}

export function hasValidUrlFormat(value) {
    const normalizedValue = String(value ?? '').trim();

    if (normalizedValue === '') {
        return true;
    }

    try {
        new URL(normalizedValue);
        return true;
    } catch {
        return false;
    }
}

export function normalizeTaxId(value) {
    return String(value ?? '').trim().toUpperCase().replace(/[\s-]+/g, '');
}

export function normalizePostalCode(value) {
    return String(value ?? '').replace(/\D+/g, '').slice(0, 5);
}

export function normalizeStationCode(value) {
    return String(value ?? '').trim().toUpperCase().replace(/\s+/g, '');
}

export function hasValidSpanishTaxId(value) {
    const normalizedValue = normalizeTaxId(value);

    if (normalizedValue === '') {
        return true;
    }

    if (/^\d{8}[A-Z]$/.test(normalizedValue)) {
        const digits = normalizedValue.slice(0, 8);
        const expectedLetter = DNI_CONTROL_LETTERS[Number(digits) % 23];

        return expectedLetter === normalizedValue.at(-1);
    }

    if (/^[XYZ]\d{7}[A-Z]$/.test(normalizedValue)) {
        const digits = normalizedValue
            .slice(0, 8)
            .replace('X', '0')
            .replace('Y', '1')
            .replace('Z', '2');
        const expectedLetter = DNI_CONTROL_LETTERS[Number(digits) % 23];

        return expectedLetter === normalizedValue.at(-1);
    }

    if (!/^[ABCDEFGHJNPQRSUVW]\d{7}[0-9A-J]$/.test(normalizedValue)) {
        return false;
    }

    const entityType = normalizedValue[0];
    const digits = normalizedValue.slice(1, 8);
    const control = normalizedValue.at(-1);

    let oddSum = 0;
    let evenSum = 0;

    for (const [index, character] of [...digits].entries()) {
        const digit = Number(character);

        if (index % 2 === 0) {
            const doubled = digit * 2;
            oddSum += Math.trunc(doubled / 10) + (doubled % 10);
            continue;
        }

        evenSum += digit;
    }

    const controlDigit = (10 - ((oddSum + evenSum) % 10)) % 10;
    const controlLetter = CIF_CONTROL_LETTERS[controlDigit];

    if (CIF_DIGIT_ONLY_TYPES.has(entityType)) {
        return control === String(controlDigit);
    }

    if (CIF_LETTER_ONLY_TYPES.has(entityType)) {
        return control === controlLetter;
    }

    return control === String(controlDigit) || control === controlLetter;
}

export function hasValidSpanishPostalCode(value) {
    const normalizedValue = normalizePostalCode(value);

    if (normalizedValue === '') {
        return true;
    }

    return SPANISH_POSTAL_CODE_PATTERN.test(normalizedValue);
}

export function hasValidInternalStationCode(value) {
    const normalizedValue = normalizeStationCode(value);

    if (normalizedValue === '') {
        return true;
    }

    return INTERNAL_STATION_CODE_PATTERN.test(normalizedValue);
}

export function hasValidRepsolStationCode(value) {
    const normalizedValue = normalizeStationCode(value);

    if (normalizedValue === '') {
        return true;
    }

    return REPSOL_STATION_CODE_PATTERN.test(normalizedValue);
}

export function hasValidMoeveStationCode(value) {
    const normalizedValue = normalizeStationCode(value);

    if (normalizedValue === '') {
        return true;
    }

    return MOEVE_STATION_CODE_PATTERN.test(normalizedValue);
}
