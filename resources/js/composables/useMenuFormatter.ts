import type { Menu } from '@/types';

export type TagCode = 'vegetarian' | 'vegan' | 'spicy' | 'gluten_free';

export interface TagDescriptor {
    code: TagCode;
    glyph: string;
}

const TAG_DESCRIPTORS: Record<TagCode, TagDescriptor> = {
    vegetarian: { code: 'vegetarian', glyph: 'v' },
    vegan: { code: 'vegan', glyph: 've' },
    spicy: { code: 'spicy', glyph: '✦' },
    gluten_free: { code: 'gluten_free', glyph: 'gf' },
};

const ROMAN: Array<[number, string]> = [
    [10, 'X'], [9, 'IX'], [5, 'V'], [4, 'IV'], [1, 'I'],
];

type MenuLike = Pick<Menu, 'show_prices' | 'show_currency'> & { location?: Menu['location'] | null };

export function useMenuFormatter(menu: MenuLike) {
    const formatPrice = (price: number | string | null | undefined): string => {
        if (!menu.show_prices || price === null || price === undefined) return '';
        const numeric = typeof price === 'number' ? price : Number(price);
        if (Number.isNaN(numeric)) return '';
        const formatted = numeric.toFixed(2);
        return menu.show_currency
            ? `${menu.location?.currency || '€'} ${formatted}`
            : formatted;
    };

    const tagsFor = (tags: string[] | null | undefined): TagDescriptor[] => {
        if (!tags || tags.length === 0) return [];
        return tags
            .filter((t): t is TagCode => t in TAG_DESCRIPTORS)
            .map((t) => TAG_DESCRIPTORS[t]);
    };

    const toRoman = (n: number): string => {
        if (n <= 0) return '';
        let num = n;
        let result = '';
        for (const [value, symbol] of ROMAN) {
            while (num >= value) {
                result += symbol;
                num -= value;
            }
        }
        return result;
    };

    const productImage = (product: { image_path?: string | null; image_url?: string | null }): string | null => {
        return product.image_path ?? product.image_url ?? null;
    };

    return { formatPrice, tagsFor, toRoman, productImage };
}
