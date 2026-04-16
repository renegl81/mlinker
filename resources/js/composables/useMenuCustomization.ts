import { computed, type Ref } from 'vue';
import type { MenuCustomization } from '@/types';
import { buildGoogleFontsUrl } from '@/config/fontPairings';

export interface LayoutFlags {
    showAllergens: boolean;
    showIngredients: boolean;
    showProductImages: boolean;
    showSectionDescriptions: boolean;
    imagePosition: 'left' | 'right' | 'top' | 'hidden';
    priceAlignment: 'right' | 'inline';
}

export interface SectionStyleFlags {
    dividerStyle: 'line' | 'ornament' | 'none';
    titleAlignment: 'left' | 'center';
    numbering: 'none' | 'roman' | 'arabic';
}

export interface HeaderStyleFlags {
    logoUrl: string | null;
    showRestaurantName: boolean;
    tagline: string | null;
    nameDisplayStyle: 'default' | 'uppercase' | 'italic';
}

const SPACING_MAP: Record<string, number> = {
    compact: 0.8,
    comfortable: 1,
    spacious: 1.25,
};

export function useMenuCustomization(customization: Ref<MenuCustomization | null | undefined> | MenuCustomization | null | undefined) {
    const custom = computed(() => {
        if (!customization) return null;
        if (typeof customization === 'object' && 'value' in customization) return customization.value ?? null;
        return customization as MenuCustomization | null;
    });

    const cssVars = computed<Record<string, string>>(() => {
        const c = custom.value;
        if (!c) return {};
        const vars: Record<string, string> = {};

        // Set canonical --ml-* AND common template aliases so the override
        // works regardless of how the template references the variable.
        if (c.colors?.accent) {
            vars['--ml-accent'] = c.colors.accent;
            // Template aliases that map to accent
            vars['--menu-accent'] = c.colors.accent;
            vars['--wine'] = c.colors.accent;
            vars['--gold'] = c.colors.accent;
            vars['--neon'] = c.colors.accent;
            vars['--sage'] = c.colors.accent;
        }
        if (c.colors?.bg) {
            vars['--ml-bg'] = c.colors.bg;
            vars['--bg'] = c.colors.bg;
            vars['--menu-bg'] = c.colors.bg;
            vars['--menu-paper'] = c.colors.bg;
        }
        if (c.colors?.ink) {
            vars['--ml-ink'] = c.colors.ink;
            vars['--ink'] = c.colors.ink;
            vars['--menu-ink'] = c.colors.ink;
        }
        if (c.colors?.ink_soft) {
            vars['--ml-ink-soft'] = c.colors.ink_soft;
            vars['--ink-soft'] = c.colors.ink_soft;
            vars['--menu-ink-soft'] = c.colors.ink_soft;
        }
        if (c.colors?.rule) {
            vars['--ml-rule'] = c.colors.rule;
            vars['--rule'] = c.colors.rule;
            vars['--menu-rule'] = c.colors.rule;
        }

        if (c.fonts?.display) {
            const val = `'${c.fonts.display}', Georgia, serif`;
            vars['--ml-font-display'] = val;
            vars['--font-display'] = val;
            vars['--menu-serif'] = val;
            vars['--font-serif'] = val;
        }
        if (c.fonts?.body) {
            const val = `'${c.fonts.body}', system-ui, sans-serif`;
            vars['--ml-font-body'] = val;
            vars['--font-body'] = val;
            vars['--menu-sans'] = val;
        }

        if (c.spacing?.density) {
            vars['--ml-spacing'] = String(SPACING_MAP[c.spacing.density] ?? 1);
        }

        return vars;
    });

    const fontLinks = computed<string[]>(() => {
        const c = custom.value;
        if (!c?.fonts) return [];
        const families: string[] = [];
        if (c.fonts.display) families.push(c.fonts.display);
        if (c.fonts.body && c.fonts.body !== c.fonts.display) families.push(c.fonts.body);
        if (families.length === 0) return [];
        const url = buildGoogleFontsUrl(families);
        return url ? [url] : [];
    });

    const layout = computed<LayoutFlags>(() => ({
        showAllergens: custom.value?.layout?.show_allergens ?? true,
        showIngredients: custom.value?.layout?.show_ingredients ?? true,
        showProductImages: custom.value?.layout?.show_product_images ?? true,
        showSectionDescriptions: custom.value?.layout?.show_section_descriptions ?? true,
        imagePosition: custom.value?.layout?.image_position ?? 'left',
        priceAlignment: custom.value?.layout?.price_alignment ?? 'right',
    }));

    const spacingMultiplier = computed(() => {
        const density = custom.value?.spacing?.density ?? 'comfortable';
        return SPACING_MAP[density] ?? 1;
    });

    const sectionStyle = computed<SectionStyleFlags>(() => ({
        dividerStyle: custom.value?.sections?.divider_style ?? 'line',
        titleAlignment: custom.value?.sections?.title_alignment ?? 'left',
        numbering: custom.value?.sections?.numbering ?? 'none',
    }));

    const headerStyle = computed<HeaderStyleFlags>(() => ({
        logoUrl: custom.value?.header?.logo_url ?? null,
        showRestaurantName: custom.value?.header?.show_restaurant_name ?? true,
        tagline: custom.value?.header?.tagline ?? null,
        nameDisplayStyle: custom.value?.header?.name_display_style ?? 'default',
    }));

    return {
        cssVars,
        fontLinks,
        layout,
        spacingMultiplier,
        sectionStyle,
        headerStyle,
    };
}
