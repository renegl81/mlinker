export interface FontPairing {
    id: string;
    name: string;
    display: string;
    body: string;
    displayFallback: string;
    bodyFallback: string;
    category: 'elegant' | 'modern' | 'rustic' | 'playful' | 'minimal';
}

export const FONT_PAIRINGS: FontPairing[] = [
    {
        id: 'classic-serif',
        name: 'Classic Elegance',
        display: 'Playfair Display',
        body: 'Lora',
        displayFallback: 'Georgia, serif',
        bodyFallback: 'Georgia, serif',
        category: 'elegant',
    },
    {
        id: 'modern-clean',
        name: 'Modern Clean',
        display: 'Syne',
        body: 'Inter',
        displayFallback: 'system-ui, sans-serif',
        bodyFallback: 'system-ui, sans-serif',
        category: 'modern',
    },
    {
        id: 'italian-classic',
        name: 'Italian Classic',
        display: 'Libre Bodoni',
        body: 'Lora',
        displayFallback: 'Georgia, serif',
        bodyFallback: 'Georgia, serif',
        category: 'elegant',
    },
    {
        id: 'french-bistro',
        name: 'French Bistro',
        display: 'Cormorant Garamond',
        body: 'Nunito',
        displayFallback: 'Garamond, serif',
        bodyFallback: 'system-ui, sans-serif',
        category: 'elegant',
    },
    {
        id: 'bold-modern',
        name: 'Bold Modern',
        display: 'Big Shoulders Display',
        body: 'Manrope',
        displayFallback: 'Impact, sans-serif',
        bodyFallback: 'system-ui, sans-serif',
        category: 'modern',
    },
    {
        id: 'warm-rustic',
        name: 'Warm Rustic',
        display: 'Fraunces',
        body: 'Instrument Sans',
        displayFallback: 'Georgia, serif',
        bodyFallback: 'system-ui, sans-serif',
        category: 'rustic',
    },
    {
        id: 'editorial',
        name: 'Editorial',
        display: 'Bodoni Moda',
        body: 'DM Sans',
        displayFallback: 'Georgia, serif',
        bodyFallback: 'system-ui, sans-serif',
        category: 'elegant',
    },
    {
        id: 'coastal',
        name: 'Coastal',
        display: 'Yeseva One',
        body: 'DM Sans',
        displayFallback: 'Georgia, serif',
        bodyFallback: 'system-ui, sans-serif',
        category: 'playful',
    },
    {
        id: 'minimalist',
        name: 'Pure Minimal',
        display: 'Cormorant Garamond',
        body: 'Cormorant Garamond',
        displayFallback: 'Garamond, serif',
        bodyFallback: 'Garamond, serif',
        category: 'minimal',
    },
    {
        id: 'garden-fresh',
        name: 'Garden Fresh',
        display: 'Lora',
        body: 'Nunito',
        displayFallback: 'Georgia, serif',
        bodyFallback: 'system-ui, sans-serif',
        category: 'rustic',
    },
];

export function buildGoogleFontsUrl(families: string[]): string | null {
    if (families.length === 0) return null;
    const params = families
        .map(f => `family=${f.replace(/ /g, '+')}:wght@300;400;500;600;700`)
        .join('&');
    return `https://fonts.googleapis.com/css2?${params}&display=swap`;
}
