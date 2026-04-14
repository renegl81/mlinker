<script setup lang="ts">
import { ref } from 'vue';
import AddToCartButton from '@/components/public/AddToCartButton.vue';
import type { BodyMenu } from './fakeMenu';

interface LayoutFlags {
    showAllergens?: boolean;
    showIngredients?: boolean;
    showProductImages?: boolean;
    showSectionDescriptions?: boolean;
}

interface Labels {
    empty?: string;
    section_empty?: string;
    kcal?: string;
    show_ingredients?: string;
    hide_ingredients?: string;
    branding?: string;
    [key: string]: string | undefined;
}

const props = defineProps<{
    menu: BodyMenu;
    interactive?: boolean;
    layout?: LayoutFlags;
    labels?: Labels;
    showBranding?: boolean;
    tenantSlug?: string;
    /** Cart integration - only used when interactive=true and hasCart=true */
    hasCart?: boolean;
    cartGetQuantity?: (productId: number) => number;
    cartAddItem?: (productId: number) => void;
    cartRemoveItem?: (productId: number) => void;
}>();

function formatPrice(price: number | string | null | undefined): string {
    if (price === null || price === undefined) return '';
    const numeric = typeof price === 'number' ? price : Number(price);
    if (Number.isNaN(numeric)) return '';
    const currency = props.menu.show_currency ? `${props.menu.location?.currency ?? '€'} ` : '';
    return `${currency}${numeric.toFixed(2)}`;
}

const sections = (props.menu.sections ?? []).map((section) => ({
    ...section,
    products: section.products ?? [],
}));

const expandedProducts = ref<Set<number>>(new Set());

function toggleIngredients(productId: number) {
    const next = new Set(expandedProducts.value);
    if (next.has(productId)) next.delete(productId);
    else next.add(productId);
    expandedProducts.value = next;
}

function productImage(product: BodyMenu['sections'] extends Array<infer S> ? (S extends { products?: Array<infer P> } ? P : never) : never): string | null {
    return (product as { image_url?: string | null; image_path?: string | null }).image_url
        ?? (product as { image_url?: string | null; image_path?: string | null }).image_path
        ?? null;
}

function tagsFor(tags?: string[] | null): Array<{ code: string; glyph: string }> {
    const MAP: Record<string, string> = { vegetarian: '🌿', vegan: '🌱', gluten_free: 'GF', spicy: '🌶' };
    return (tags ?? []).filter((c) => c in MAP).map((c) => ({ code: c, glyph: MAP[c] }));
}

const lbl = (key: string, fallback: string) => props.labels?.[key] ?? fallback;

const showAllergens = () => props.layout?.showAllergens ?? true;
const showIngredients = () => props.layout?.showIngredients ?? true;
const showProductImages = () => props.layout?.showProductImages ?? true;
const showSectionDescriptions = () => props.layout?.showSectionDescriptions ?? true;
</script>

<template>
    <div class="menu-riviera">
        <svg class="wave-top" viewBox="0 0 1200 120" preserveAspectRatio="none" aria-hidden="true">
            <path d="M0 60 Q 150 20 300 60 T 600 60 T 900 60 T 1200 60 L 1200 120 L 0 120 Z" fill="currentColor" />
        </svg>

        <header class="hero">
            <div class="hero-gradient" aria-hidden="true" />
            <div class="hero-inner">
                <div class="hero-sun" aria-hidden="true">
                    <svg viewBox="0 0 80 80" width="56" height="56">
                        <circle cx="40" cy="40" r="14" fill="currentColor" />
                        <g stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <line x1="40" y1="6" x2="40" y2="18" />
                            <line x1="40" y1="62" x2="40" y2="74" />
                            <line x1="6" y1="40" x2="18" y2="40" />
                            <line x1="62" y1="40" x2="74" y2="40" />
                            <line x1="16" y1="16" x2="24" y2="24" />
                            <line x1="56" y1="56" x2="64" y2="64" />
                            <line x1="16" y1="64" x2="24" y2="56" />
                            <line x1="56" y1="24" x2="64" y2="16" />
                        </g>
                    </svg>
                </div>
                <p class="hero-kicker">{{ menu.location?.name }}</p>
                <h1 class="hero-title">{{ menu.name }}</h1>
                <p v-if="menu.description" class="hero-sub">{{ menu.description }}</p>
            </div>
        </header>

        <main class="main">
            <div v-if="sections.length === 0" class="empty">
                {{ lbl('empty', 'Este menú no tiene secciones todavía') }}
            </div>

            <section
                v-for="(section, sIdx) in sections"
                :key="section.id"
                class="section"
                :style="{ '--i': sIdx } as Record<string, number>"
            >
                <header class="section-head">
                    <svg class="section-wave" viewBox="0 0 120 20" aria-hidden="true">
                        <path d="M0 10 Q 15 0 30 10 T 60 10 T 90 10 T 120 10" stroke="currentColor" stroke-width="1.5" fill="none" />
                    </svg>
                    <h2 class="section-title">{{ section.name }}</h2>
                    <p v-if="showSectionDescriptions() && section.description" class="section-desc">{{ section.description }}</p>
                </header>

                <ul v-if="section.products.length > 0" class="dishes">
                    <li
                        v-for="(product, pIdx) in section.products"
                        :key="product.id"
                        class="dish"
                        :style="{ '--j': pIdx } as Record<string, number>"
                    >
                        <img
                            v-if="showProductImages() && productImage(product)"
                            :src="productImage(product)!"
                            :alt="product.name"
                            class="dish-image"
                            loading="lazy"
                        />
                        <div class="dish-body">
                            <div class="dish-row">
                                <h3 class="dish-name">{{ product.name }}</h3>
                                <span v-if="menu.show_prices && product.price" class="dish-price">
                                    {{ formatPrice(product.price) }}
                                </span>
                            </div>

                            <div class="dish-meta-top" v-if="tagsFor(product.tags).length > 0 || (menu.show_calories && product.calories)">
                                <span v-if="menu.show_calories && product.calories" class="dish-kcal">
                                    {{ product.calories }} {{ lbl('kcal', 'kcal') }}
                                </span>
                                <span
                                    v-for="tag in tagsFor(product.tags)"
                                    :key="tag.code"
                                    class="dish-tag"
                                    :title="tag.code"
                                >{{ tag.glyph }}</span>
                            </div>

                            <p v-if="product.description" class="dish-desc">{{ product.description }}</p>

                            <div
                                v-if="showIngredients() && product.ingredients && product.ingredients.length > 0"
                                class="ingredients-wrap"
                            >
                                <template v-if="interactive">
                                    <button
                                        type="button"
                                        class="ingredients-toggle"
                                        :aria-expanded="expandedProducts.has(product.id)"
                                        @click="toggleIngredients(product.id)"
                                    >
                                        {{ expandedProducts.has(product.id) ? lbl('hide_ingredients', 'Ocultar ingredientes') : lbl('show_ingredients', 'Ver ingredientes') }}
                                    </button>
                                    <p v-show="expandedProducts.has(product.id)" class="ingredients-list">
                                        {{ product.ingredients.map((i) => i.name).join(' · ') }}
                                    </p>
                                </template>
                                <template v-else>
                                    <p class="ingredients-list-static">
                                        {{ product.ingredients.map((i) => i.name).join(' · ') }}
                                    </p>
                                </template>
                            </div>

                            <div
                                v-if="showAllergens() && product.allergens && product.allergens.length > 0"
                                class="dish-allergens"
                            >
                                <span
                                    v-for="allergen in product.allergens"
                                    :key="allergen.id"
                                    class="allergen-badge"
                                    :title="allergen.name"
                                >{{ allergen.code }}</span>
                            </div>
                        </div>
                    </li>
                </ul>

                <p v-else class="section-empty">{{ lbl('section_empty', 'Esta sección no tiene productos') }}</p>
            </section>
        </main>

        <footer class="foot">
            <svg class="foot-wave" viewBox="0 0 120 20" aria-hidden="true">
                <path d="M0 10 Q 15 0 30 10 T 60 10 T 90 10 T 120 10" stroke="currentColor" stroke-width="1.5" fill="none" />
            </svg>
            <p class="foot-name">{{ menu.location?.name }}</p>
            <p v-if="menu.location?.address" class="foot-line">{{ menu.location.address }}</p>
            <p v-if="menu.location?.phone" class="foot-line">{{ menu.location.phone }}</p>

            <div v-if="showBranding && tenantSlug" class="foot-branding">
                <a :href="`https://menulinker.com?ref=${tenantSlug}`" target="_blank" rel="noopener">
                    {{ lbl('branding', 'Menú digital por MenuLinker') }}
                </a>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.menu-riviera {
    --bg: oklch(0.985 0.015 220);
    --panel: oklch(0.995 0.008 70);
    --ink: oklch(0.22 0.04 240);
    --ink-soft: oklch(0.45 0.035 240);
    --ink-faint: oklch(0.62 0.03 240);
    --rule: oklch(0.84 0.02 220);
    --sea: oklch(0.48 0.12 230);
    --sea-deep: oklch(0.34 0.11 240);
    --gold: oklch(0.76 0.14 80);
    --gold-dark: oklch(0.60 0.14 70);
    --font-display: 'Yeseva One', Georgia, serif;
    --font-body: 'DM Sans', ui-sans-serif, system-ui, sans-serif;

    position: relative;
    background: linear-gradient(180deg, oklch(0.96 0.03 220) 0%, var(--bg) 30%, var(--bg) 100%);
    color: var(--ink);
    font-family: var(--font-body);
    -webkit-font-smoothing: antialiased;
    overflow: hidden;
    min-height: 100%;
}

.wave-top {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    width: 100%;
    height: 60px;
    color: oklch(0.92 0.035 220);
    z-index: 0;
}

.hero {
    position: relative;
    z-index: 2;
    min-height: clamp(200px, 40svh, 320px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: clamp(2.5rem, 8vw, 4.5rem) clamp(1.5rem, 5vw, 3rem);
    overflow: hidden;
}

.hero-gradient {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, oklch(0 0 0 / 0.05) 0%, oklch(0 0 0 / 0.15) 60%, var(--bg) 100%);
    pointer-events: none;
}

.hero-inner {
    position: relative;
    z-index: 2;
    text-align: center;
    max-width: 720px;
}

.hero-sun {
    color: var(--gold);
    margin: 0 auto 1rem;
    filter: drop-shadow(0 0 14px color-mix(in oklch, var(--gold) 35%, transparent));
}

.hero-kicker {
    font-family: var(--font-body);
    font-weight: 500;
    font-size: 0.72rem;
    letter-spacing: 0.32em;
    text-transform: uppercase;
    margin: 0 0 0.75rem;
    opacity: 0.85;
}

.hero-title {
    font-family: var(--font-display);
    font-weight: 400;
    font-size: clamp(2.2rem, 8vw, 4.5rem);
    line-height: 0.95;
    letter-spacing: -0.01em;
    margin: 0;
    color: var(--sea-deep);
}

.hero-sub {
    margin: 1.1rem auto 0;
    max-width: 50ch;
    font-size: 0.9rem;
    line-height: 1.6;
    font-weight: 300;
    opacity: 0.92;
}

.main {
    position: relative;
    z-index: 2;
    max-width: 760px;
    margin: 0 auto;
    padding: clamp(1.5rem, 5vw, 3rem) clamp(1.5rem, 5vw, 3rem) 0;
}

.empty {
    text-align: center;
    padding: 3rem 0;
    color: var(--ink-faint);
    font-style: italic;
}

.section { margin-bottom: clamp(2.5rem, 6vw, 4rem); }

.section-head { text-align: center; margin-bottom: 1.75rem; }

.section-wave {
    width: 50px;
    height: 10px;
    color: var(--gold);
    margin: 0 auto 0.75rem;
    opacity: 0.85;
}

.section-title {
    font-family: var(--font-display);
    font-weight: 400;
    font-size: clamp(1.5rem, 4vw, 2.2rem);
    line-height: 1;
    letter-spacing: -0.005em;
    margin: 0;
    color: var(--sea-deep);
}

.section-desc {
    max-width: 50ch;
    margin: 0.75rem auto 0;
    font-size: 0.9rem;
    line-height: 1.6;
    color: var(--ink-soft);
    font-weight: 300;
}

.section-empty {
    text-align: center;
    padding: 2rem 0;
    color: var(--ink-faint);
    font-style: italic;
}

.dishes {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 1.1rem;
}

.dish {
    display: flex;
    gap: 1.3rem;
    align-items: flex-start;
    padding: 1rem 1.1rem;
    background: var(--panel);
    border-radius: 14px;
    box-shadow:
        0 1px 2px oklch(0 0 0 / 0.04),
        0 10px 28px -16px oklch(0.34 0.11 240 / 0.18);
    border: 1px solid color-mix(in oklch, var(--sea) 8%, transparent);
}

.dish-image {
    flex: none;
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 10px;
    filter: saturate(0.95);
}

.dish-body { flex: 1; min-width: 0; }

.dish-row {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: 0.75rem;
    margin-bottom: 0.3rem;
}

.dish-name {
    font-family: var(--font-display);
    font-weight: 400;
    font-size: 1rem;
    line-height: 1.3;
    margin: 0;
    color: var(--sea-deep);
    flex: 1;
}

.dish-price {
    font-family: var(--font-display);
    font-weight: 400;
    font-size: 1.05rem;
    color: var(--gold-dark);
    font-variant-numeric: tabular-nums;
    white-space: nowrap;
    flex-shrink: 0;
}

.dish-meta-top {
    display: flex;
    flex-wrap: wrap;
    gap: 0.3rem;
    align-items: center;
    margin-bottom: 0.25rem;
}

.dish-kcal {
    font-size: 0.68rem;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--ink-faint);
    font-variant-numeric: tabular-nums;
    font-weight: 300;
}

.dish-tag { font-size: 0.75rem; color: var(--sea); }

.dish-desc {
    margin: 0;
    font-size: 0.82rem;
    line-height: 1.6;
    color: var(--ink-soft);
    font-weight: 300;
}

.ingredients-wrap { margin-top: 0.5rem; }

.ingredients-toggle {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.2rem 0.6rem;
    background: color-mix(in oklch, var(--gold) 10%, transparent);
    border: 1px solid color-mix(in oklch, var(--gold) 30%, transparent);
    border-radius: 999px;
    cursor: pointer;
    font-family: var(--font-body);
    font-size: 0.7rem;
    font-weight: 500;
    color: var(--gold-dark);
    transition: background 200ms, border-color 200ms;
}

.ingredients-toggle:hover { background: color-mix(in oklch, var(--gold) 18%, transparent); outline: none; }

.ingredients-list {
    margin: 0.5rem 0 0;
    padding: 0.5rem 0.75rem;
    font-size: 0.82rem;
    line-height: 1.6;
    color: var(--ink-soft);
    font-weight: 300;
    background: color-mix(in oklch, var(--sea) 6%, transparent);
    border-left: 2px solid var(--gold);
    border-radius: 0 8px 8px 0;
}

.ingredients-list-static {
    margin: 0.35rem 0 0;
    font-size: 0.78rem;
    line-height: 1.5;
    color: var(--ink-faint);
    font-weight: 300;
    font-style: italic;
}

.dish-allergens {
    display: flex;
    flex-wrap: wrap;
    gap: 0.2rem;
    margin-top: 0.5rem;
}

.allergen-badge {
    font-size: 0.6rem;
    font-weight: 500;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: var(--sea);
    border: 1px solid color-mix(in oklch, var(--sea) 35%, transparent);
    padding: 0.1rem 0.28rem;
    border-radius: 4px;
}

.foot {
    position: relative;
    z-index: 2;
    max-width: 760px;
    margin: 0 auto;
    padding: clamp(2rem, 6vw, 4rem) clamp(1.5rem, 5vw, 3rem) clamp(1.5rem, 4vw, 2.5rem);
    text-align: center;
}

.foot-wave {
    width: 60px;
    height: 12px;
    color: var(--gold);
    margin: 0 auto 1.25rem;
    opacity: 0.85;
}

.foot-name {
    font-family: var(--font-display);
    font-weight: 400;
    font-size: 1.25rem;
    color: var(--sea-deep);
    margin: 0 0 0.5rem;
}

.foot-line { margin: 0.15rem 0; font-size: 0.82rem; color: var(--ink-soft); font-weight: 300; }

.foot-branding {
    margin-top: 1.75rem;
    font-size: 0.68rem;
    color: var(--ink-faint);
    font-weight: 300;
}

.foot-branding a {
    color: inherit;
    text-decoration: none;
    border-bottom: 1px solid transparent;
    transition: border-color 200ms, color 200ms;
}

.foot-branding a:hover { color: var(--gold-dark); border-bottom-color: currentColor; }
</style>
