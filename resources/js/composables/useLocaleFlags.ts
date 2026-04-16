/**
 * SVG flag icons for locales that lack standard Unicode emoji flags.
 * Returns an inline data URI suitable for <img src="..."> or background-image.
 * For locales with standard emoji (es, en, fr, etc.) returns null — use the emoji directly.
 */

const regionalFlags: Record<string, string> = {
    // Senyera (Cataluña / Valencia) — 4 red stripes on gold
    ca: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 14"><rect width="20" height="14" fill="#FCDD09"/><rect y="1.56" width="20" height="1.56" fill="#DA121A"/><rect y="4.67" width="20" height="1.56" fill="#DA121A"/><rect y="7.78" width="20" height="1.56" fill="#DA121A"/><rect y="10.89" width="20" height="1.11" fill="#DA121A"/></svg>`,

    // Galicia — white with blue diagonal stripe
    gl: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 14"><rect width="20" height="14" fill="#fff"/><polygon points="0,0 8,7 0,14" fill="#0032A0"/><polygon points="20,0 12,7 20,14" fill="#0032A0"/></svg>`,

    // Ikurriña (País Vasco) — red background, green saltire, white cross
    eu: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 14"><rect width="20" height="14" fill="#D52B1E"/><line x1="0" y1="0" x2="20" y2="14" stroke="#39825A" stroke-width="2.2"/><line x1="20" y1="0" x2="0" y2="14" stroke="#39825A" stroke-width="2.2"/><line x1="10" y1="0" x2="10" y2="14" stroke="#fff" stroke-width="1.8"/><line x1="0" y1="7" x2="20" y2="7" stroke="#fff" stroke-width="1.8"/></svg>`,
};

export function getLocaleFlag(code: string): { type: 'emoji'; value: string } | { type: 'svg'; value: string } {
    const svg = regionalFlags[code];
    if (svg) {
        const encoded = `data:image/svg+xml,${encodeURIComponent(svg)}`;
        return { type: 'svg', value: encoded };
    }
    // Standard emoji flags
    const emojiMap: Record<string, string> = {
        es: '🇪🇸',
        en: '🇬🇧',
        fr: '🇫🇷',
        de: '🇩🇪',
        it: '🇮🇹',
        pt: '🇵🇹',
    };
    return { type: 'emoji', value: emojiMap[code] ?? code.toUpperCase() };
}
