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

function toRoman(n: number): string {
    const ROMAN: Array<[number, string]> = [[10, 'X'], [9, 'IX'], [5, 'V'], [4, 'IV'], [1, 'I']];
    let num = n;
    let result = '';
    for (const [value, symbol] of ROMAN) {
        while (num >= value) { result += symbol; num -= value; }
    }
    return result;
}

const sections = (props.menu.sections ?? []).map((section, index) => ({
    ...section,
    products: section.products ?? [],
    anchor: `section-${section.id}`,
    numeral: toRoman(index + 1),
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
    const MAP: Record<string, string> = {
        vegetarian: '🌿',
        vegan: '🌱',
        gluten_free: 'GF',
        spicy: '🌶',
    };
    return (tags ?? []).filter((c) => c in MAP).map((c) => ({ code: c, glyph: MAP[c] }));
}

const lbl = (key: string, fallback: string) => props.labels?.[key] ?? fallback;

const showAllergens = () => props.layout?.showAllergens ?? true;
const showIngredients = () => props.layout?.showIngredients ?? true;
const showProductImages = () => props.layout?.showProductImages ?? true;
const showSectionDescriptions = () => props.layout?.showSectionDescriptions ?? true;
</script>

<template>
    <div class="menu-editorial">
        <!-- Grain texture overlay -->
        <svg class="grain" aria-hidden="true">
            <filter id="menu-grain-b">
                <feTurbulence type="fractalNoise" baseFrequency="0.9" numOctaves="2" stitchTiles="stitch" />
                <feColorMatrix type="saturate" values="0" />
            </filter>
            <rect width="100%" height="100%" filter="url(#menu-grain-b)" />
        </svg>

        <!-- HERO -->
        <header class="hero">
            <div
                v-if="menu.image_path || (menu as any).image_url"
                class="hero-image"
                :style="{ backgroundImage: `url(${(menu as any).image_url ?? menu.image_path})` }"
            />
            <div class="hero-inner">
                <div class="hero-kicker">
                    <span>{{ menu.location?.name }}</span>
                </div>
                <h1 class="hero-title">{{ menu.name }}</h1>
                <p v-if="menu.description" class="hero-sub">{{ menu.description }}</p>
                <div class="hero-ornament" aria-hidden="true">
                    <span class="rule" />
                    <span class="mark">✦</span>
                    <span class="rule" />
                </div>
            </div>
        </header>

        <!-- Secciones -->
        <main class="main">
            <div v-if="sections.length === 0" class="empty">
                <p>{{ lbl('empty', 'Este menú no tiene secciones todavía') }}</p>
            </div>

            <section
                v-for="(section, sIdx) in sections"
                :id="section.anchor"
                :key="section.id"
                class="section"
                :style="{ '--i': sIdx } as Record<string, number>"
            >
                <div class="section-head">
                    <span class="section-numeral" aria-hidden="true">{{ section.numeral }}</span>
                    <div class="section-title-wrap">
                        <h2 class="section-title">{{ section.name }}</h2>
                        <p v-if="showSectionDescriptions() && section.description" class="section-desc">{{ section.description }}</p>
                    </div>
                </div>

                <ul v-if="section.products.length > 0" class="product-list">
                    <li
                        v-for="(product, pIdx) in section.products"
                        :key="product.id"
                        class="product"
                        :style="{ '--j': pIdx } as Record<string, number>"
                    >
                        <div class="product-main">
                            <div class="product-row">
                                <h3 class="product-name">
                                    <span>{{ product.name }}</span>
                                    <span
                                        v-if="menu.show_calories && product.calories"
                                        class="product-calories"
                                    >
                                        · {{ product.calories }} {{ lbl('kcal', 'kcal') }}
                                    </span>
                                </h3>
                                <template v-if="menu.show_prices && product.price">
                                    <span class="product-dots" aria-hidden="true" />
                                    <span class="product-price">{{ formatPrice(product.price) }}</span>
                                    <AddToCartButton
                                        v-if="interactive && hasCart"
                                        :quantity="cartGetQuantity?.(product.id) ?? 0"
                                        @add="cartAddItem?.(product.id)"
                                        @remove="cartRemoveItem?.(product.id)"
                                    />
                                </template>
                            </div>

                            <p v-if="product.description" class="product-desc">
                                {{ product.description }}
                            </p>

                            <div
                                v-if="showIngredients() && product.ingredients && product.ingredients.length > 0"
                                class="product-ingredients-wrap"
                            >
                                <template v-if="interactive">
                                    <button
                                        type="button"
                                        class="product-ingredients-toggle"
                                        :aria-expanded="expandedProducts.has(product.id)"
                                        :aria-controls="`ingredients-${product.id}`"
                                        @click="toggleIngredients(product.id)"
                                    >
                                        <span class="tog-line" aria-hidden="true" />
                                        <span class="tog-label">
                                            {{ expandedProducts.has(product.id) ? lbl('hide_ingredients', 'Ocultar ingredientes') : lbl('show_ingredients', 'Ver ingredientes') }}
                                        </span>
                                        <svg
                                            class="tog-caret"
                                            :class="{ 'is-open': expandedProducts.has(product.id) }"
                                            viewBox="0 0 10 10"
                                            width="9"
                                            height="9"
                                            aria-hidden="true"
                                        >
                                            <path d="M2 3.5L5 6.5L8 3.5" stroke="currentColor" stroke-width="1.4" fill="none" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </button>
                                    <p
                                        v-show="expandedProducts.has(product.id)"
                                        :id="`ingredients-${product.id}`"
                                        class="product-ingredients"
                                    >
                                        {{ product.ingredients.map((i) => i.name).join(', ') }}
                                    </p>
                                </template>
                                <template v-else>
                                    <span class="product-ingredients-static">
                                        {{ product.ingredients.map((i) => i.name).join(', ') }}
                                    </span>
                                </template>
                            </div>

                            <div
                                v-if="tagsFor(product.tags).length > 0 || (showAllergens() && product.allergens && product.allergens.length > 0)"
                                class="product-meta"
                            >
                                <span
                                    v-for="tag in tagsFor(product.tags)"
                                    :key="tag.code"
                                    class="product-tag"
                                    :title="tag.code"
                                >{{ tag.glyph }}</span>
                                <div v-if="showAllergens() && product.allergens && product.allergens.length > 0" class="product-allergens">
                                    <span
                                        v-for="allergen in product.allergens"
                                        :key="allergen.id"
                                        class="allergen-badge"
                                        :title="allergen.name"
                                    >{{ allergen.code }}</span>
                                </div>
                            </div>
                        </div>

                        <img
                            v-if="showProductImages() && productImage(product)"
                            :src="productImage(product)!"
                            :alt="product.name"
                            class="product-image"
                            loading="lazy"
                        />
                    </li>
                </ul>

                <p v-else class="section-empty">{{ lbl('section_empty', 'Esta sección no tiene productos') }}</p>
            </section>
        </main>

        <!-- Footer -->
        <footer class="foot">
            <div class="foot-ornament" aria-hidden="true">
                <span class="rule" />
                <span class="mark">◆</span>
                <span class="rule" />
            </div>
            <p class="foot-name">{{ menu.location?.name }}</p>
            <p v-if="menu.location?.address" class="foot-line">{{ menu.location.address }}</p>
            <p v-if="menu.location?.phone" class="foot-line">{{ menu.location.phone }}</p>

            <div v-if="showBranding && tenantSlug" class="foot-branding">
                <a
                    :href="`https://menulinker.com?ref=${tenantSlug}`"
                    target="_blank"
                    rel="noopener"
                >{{ lbl('branding', 'Menú digital por MenuLinker') }}</a>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.menu-editorial {
    --menu-bg: oklch(0.972 0.015 82);
    --menu-paper: oklch(0.99 0.008 82);
    --menu-ink: oklch(0.22 0.025 40);
    --menu-ink-soft: oklch(0.46 0.02 40);
    --menu-ink-faint: oklch(0.66 0.015 40);
    --menu-accent: oklch(0.56 0.15 35);
    --menu-accent-soft: oklch(0.56 0.15 35 / 0.12);
    --menu-rule: oklch(0.85 0.02 40);
    --menu-serif: 'Fraunces', 'Instrument Serif', Georgia, serif;
    --menu-sans: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;

    position: relative;
    background: var(--menu-bg);
    color: var(--menu-ink);
    font-family: var(--menu-sans);
    font-feature-settings: 'ss01', 'cv01';
    -webkit-font-smoothing: antialiased;
    overflow: hidden;
    min-height: 100%;
}

.grain {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    opacity: 0.11;
    mix-blend-mode: multiply;
    z-index: 1;
}

/* ============ HERO ============ */
.hero {
    position: relative;
    z-index: 2;
    min-height: clamp(280px, 55svh, 500px);
    display: flex;
    align-items: flex-end;
    padding: clamp(1.25rem, 4vw, 3rem);
    padding-top: clamp(4rem, 12vw, 8rem);
    overflow: hidden;
}

.hero-image {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center;
    filter: saturate(0.92) contrast(1.02);
}

.hero-image::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(
        180deg,
        oklch(0 0 0 / 0.15) 0%,
        oklch(0 0 0 / 0.25) 40%,
        oklch(0 0 0 / 0.7) 100%
    );
}

.hero-image ~ .hero-inner {
    color: oklch(0.99 0 0);
}

.hero-inner {
    position: relative;
    z-index: 2;
    max-width: 720px;
    margin-left: auto;
    margin-right: auto;
    width: 100%;
    text-align: center;
}

.hero-kicker {
    font-family: var(--menu-sans);
    font-size: 0.68rem;
    font-weight: 500;
    letter-spacing: 0.32em;
    text-transform: uppercase;
    opacity: 0.85;
    margin-bottom: 1rem;
}

.hero-title {
    font-family: var(--menu-serif);
    font-style: italic;
    font-weight: 300;
    font-size: clamp(2rem, 8vw, 4rem);
    line-height: 0.95;
    letter-spacing: -0.02em;
    margin: 0;
}

.hero-sub {
    margin-top: 0.85rem;
    font-size: 0.9rem;
    line-height: 1.55;
    max-width: 52ch;
    margin-left: auto;
    margin-right: auto;
    opacity: 0.92;
}

.hero-ornament {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.85rem;
    margin-top: 1.25rem;
}

.hero-ornament .rule { height: 1px; width: 60px; background: currentColor; opacity: 0.4; }
.hero-ornament .mark { font-family: var(--menu-serif); font-size: 0.85rem; opacity: 0.7; }

/* Fallback sin imagen hero */
.hero:not(:has(.hero-image)) {
    background: var(--menu-bg);
    color: var(--menu-ink);
    min-height: auto;
    padding-top: clamp(2.5rem, 8vw, 5rem);
    padding-bottom: clamp(1.5rem, 5vw, 3rem);
}

/* ============ MAIN ============ */
.main {
    position: relative;
    z-index: 2;
    max-width: 720px;
    margin: 0 auto;
    padding: clamp(1.5rem, 5vw, 3.5rem) clamp(1.25rem, 4vw, 2rem) 0;
}

.empty {
    text-align: center;
    padding: 3rem 0;
    color: var(--menu-ink-soft);
    font-style: italic;
    font-family: var(--menu-serif);
}

.section {
    margin-bottom: clamp(2.5rem, 7vw, 4.5rem);
}

.section-head {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    margin-bottom: 1.75rem;
    padding-bottom: 1.25rem;
    border-bottom: 1px solid var(--menu-rule);
    position: relative;
}

.section-head::after {
    content: '';
    position: absolute;
    bottom: -3px;
    left: 0;
    width: 48px;
    height: 2px;
    background: var(--menu-accent);
}

.section-numeral {
    font-family: var(--menu-serif);
    font-style: italic;
    font-weight: 300;
    font-size: 1.2rem;
    line-height: 1;
    color: var(--menu-accent);
    padding-top: 0.5rem;
    min-width: 2ch;
}

.section-title-wrap { flex: 1; }

.section-title {
    font-family: var(--menu-serif);
    font-style: italic;
    font-weight: 400;
    font-size: clamp(1.4rem, 4vw, 2rem);
    line-height: 1;
    letter-spacing: -0.015em;
    margin: 0;
}

.section-desc {
    margin-top: 0.35rem;
    font-size: 0.82rem;
    color: var(--menu-ink-soft);
    line-height: 1.5;
    max-width: 52ch;
}

.section-empty {
    font-family: var(--menu-serif);
    font-style: italic;
    text-align: center;
    color: var(--menu-ink-faint);
    padding: 2rem 0;
    font-size: 0.95rem;
}

/* ============ PRODUCT ============ */
.product-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.product {
    display: flex;
    gap: 1rem;
    align-items: flex-start;
}

.product-main {
    flex: 1;
    min-width: 0;
}

.product-row {
    display: flex;
    align-items: baseline;
    gap: 0.5rem;
    width: 100%;
}

.product-name {
    font-family: var(--menu-sans);
    font-weight: 500;
    font-size: 0.97rem;
    line-height: 1.35;
    margin: 0;
    color: var(--menu-ink);
    flex: 0 1 auto;
    max-width: 100%;
}

.product-calories {
    font-family: var(--menu-serif);
    font-style: italic;
    font-weight: 300;
    font-size: 0.78rem;
    color: var(--menu-ink-faint);
    margin-left: 0.3rem;
}

.product-dots {
    flex: 1 1 auto;
    min-width: 1.5rem;
    height: 0;
    align-self: flex-end;
    margin-bottom: 0.28em;
    border-bottom: 1.5px dotted var(--menu-ink-faint);
    opacity: 0.55;
}

.product-price {
    font-family: var(--menu-serif);
    font-weight: 500;
    font-size: 1rem;
    color: var(--menu-accent);
    font-variant-numeric: tabular-nums;
    white-space: nowrap;
    flex: none;
}

.product-desc {
    margin: 0.3rem 0 0;
    font-size: 0.8rem;
    line-height: 1.55;
    color: var(--menu-ink-soft);
    max-width: 56ch;
}

.product-ingredients-wrap {
    margin-top: 0.55rem;
    max-width: 58ch;
}

.product-ingredients-toggle {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0;
    background: transparent;
    border: none;
    cursor: pointer;
    font-family: var(--menu-serif);
    font-style: italic;
    font-weight: 400;
    font-size: 0.75rem;
    color: var(--menu-ink-soft);
    letter-spacing: 0.01em;
    transition: color 200ms;
}

.product-ingredients-toggle:hover,
.product-ingredients-toggle:focus-visible {
    color: var(--menu-accent);
    outline: none;
}

.product-ingredients-toggle .tog-line {
    display: inline-block;
    width: 16px;
    height: 1px;
    background: currentColor;
    opacity: 0.55;
}

.product-ingredients-toggle .tog-caret {
    flex-shrink: 0;
    opacity: 0.7;
    transition: transform 240ms cubic-bezier(.2,.65,.2,1);
}

.product-ingredients-toggle .tog-caret.is-open {
    transform: rotate(180deg);
}

.product-ingredients {
    margin: 0.45rem 0 0;
    padding: 0.55rem 0.75rem 0.6rem;
    border-left: 2px solid var(--menu-accent);
    background: color-mix(in oklch, var(--menu-accent) 7%, transparent);
    border-radius: 0 6px 6px 0;
    font-family: var(--menu-serif);
    font-style: italic;
    font-weight: 300;
    font-size: 0.78rem;
    line-height: 1.55;
    color: var(--menu-ink);
}

.product-ingredients-static {
    display: block;
    margin-top: 0.35rem;
    font-family: var(--menu-serif);
    font-style: italic;
    font-weight: 300;
    font-size: 0.75rem;
    line-height: 1.5;
    color: var(--menu-ink-faint);
}

.product-meta {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.4rem 0.5rem;
    margin-top: 0.6rem;
}

.product-tag {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 1.3rem;
    height: 1.3rem;
    padding: 0 0.38rem;
    border-radius: 999px;
    font-family: var(--menu-serif);
    font-style: italic;
    font-weight: 500;
    font-size: 0.7rem;
    color: var(--menu-accent);
    background: var(--menu-accent-soft);
    border: 1px solid color-mix(in oklch, var(--menu-accent) 18%, transparent);
    line-height: 1;
}

.product-allergens {
    display: flex;
    align-items: center;
    gap: 0.2rem;
    flex-wrap: wrap;
    padding-left: 0.4rem;
    margin-left: 0.15rem;
    border-left: 1px solid var(--menu-rule);
    opacity: 0.75;
}

.allergen-badge {
    font-size: 0.62rem;
    font-weight: 600;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: var(--menu-ink-soft);
    background: color-mix(in oklch, var(--menu-rule) 60%, transparent);
    padding: 0.1rem 0.3rem;
    border-radius: 3px;
}

.product-image {
    flex: none;
    width: 88px;
    height: 88px;
    object-fit: cover;
    border-radius: 10px;
    filter: saturate(0.95);
    box-shadow:
        0 1px 2px oklch(0 0 0 / 0.07),
        0 14px 28px -10px oklch(0 0 0 / 0.22);
}

/* ============ FOOT ============ */
.foot {
    position: relative;
    z-index: 2;
    max-width: 720px;
    margin: 0 auto;
    padding: clamp(2rem, 6vw, 4rem) clamp(1.25rem, 4vw, 2rem) clamp(1.5rem, 4vw, 2.5rem);
    text-align: center;
    color: var(--menu-ink-soft);
}

.foot-ornament {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.85rem;
    margin-bottom: 1.5rem;
    color: var(--menu-accent);
}

.foot-ornament .rule { height: 1px; width: 60px; background: currentColor; opacity: 0.4; }
.foot-ornament .mark { font-family: var(--menu-serif); font-size: 0.85rem; }

.foot-name {
    font-family: var(--menu-serif);
    font-style: italic;
    font-weight: 400;
    font-size: 1.2rem;
    color: var(--menu-ink);
    margin: 0 0 0.4rem;
}

.foot-line { margin: 0.1rem 0; font-size: 0.78rem; letter-spacing: 0.01em; }

.foot-branding {
    margin-top: 1.75rem;
    font-size: 0.68rem;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    opacity: 0.65;
}

.foot-branding a {
    color: inherit;
    text-decoration: none;
    transition: color 200ms;
    border-bottom: 1px solid transparent;
    padding-bottom: 1px;
}

.foot-branding a:hover {
    color: var(--menu-accent);
    border-bottom-color: var(--menu-accent);
}
</style>
