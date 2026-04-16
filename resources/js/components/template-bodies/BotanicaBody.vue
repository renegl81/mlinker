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
    <div class="menu-botanica">
        <svg class="leaf leaf-tl" viewBox="0 0 100 100" aria-hidden="true">
            <path d="M50 5 C70 20 90 40 95 60 C80 55 60 50 45 40 C35 30 40 15 50 5 Z" fill="currentColor" opacity="0.12" />
            <path d="M50 5 Q50 45 60 70" stroke="currentColor" stroke-width="0.8" fill="none" opacity="0.3" />
        </svg>
        <svg class="leaf leaf-br" viewBox="0 0 100 100" aria-hidden="true">
            <path d="M50 95 C30 80 10 60 5 40 C20 45 40 50 55 60 C65 70 60 85 50 95 Z" fill="currentColor" opacity="0.12" />
            <path d="M50 95 Q50 55 40 30" stroke="currentColor" stroke-width="0.8" fill="none" opacity="0.3" />
        </svg>

        <header class="hero">
            <p class="hero-kicker">
                <span class="leaf-glyph">❊</span>
                {{ menu.location?.name }}
                <span class="leaf-glyph">❊</span>
            </p>
            <h1 class="hero-title">{{ menu.name }}</h1>
            <p v-if="menu.description" class="hero-sub">{{ menu.description }}</p>
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
                    <span class="section-glyph" aria-hidden="true">❊</span>
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
                            <div class="dish-head">
                                <h3 class="dish-name">{{ product.name }}</h3>
                                <span v-if="menu.show_prices && product.price" class="dish-price">
                                    {{ formatPrice(product.price) }}
                                </span>
                                <AddToCartButton
                                    v-if="interactive && hasCart && menu.show_prices && product.price"
                                    :quantity="cartGetQuantity ? cartGetQuantity(product.id) : 0"
                                    @add="cartAddItem && cartAddItem(product.id)"
                                    @remove="cartRemoveItem && cartRemoveItem(product.id)"
                                />
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
                                        <span class="toggle-leaf">❊</span>
                                        {{ expandedProducts.has(product.id) ? lbl('hide_ingredients', 'Ocultar ingredientes') : lbl('show_ingredients', 'Ver ingredientes') }}
                                    </button>
                                    <p v-show="expandedProducts.has(product.id)" class="ingredients-list">
                                        {{ product.ingredients.map((i) => i.name).join(', ') }}
                                    </p>
                                </template>
                                <template v-else>
                                    <p class="ingredients-list-static">
                                        {{ product.ingredients.map((i) => i.name).join(', ') }}
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
            <div class="foot-glyphs" aria-hidden="true">❊ ❊ ❊</div>
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
.menu-botanica {
    --bg: oklch(0.955 0.022 88);
    --panel: oklch(0.98 0.015 88);
    --ink: oklch(0.22 0.015 120);
    --ink-soft: oklch(0.44 0.015 120);
    --ink-faint: oklch(0.60 0.015 120);
    --rule: oklch(0.82 0.03 120);
    --sage: oklch(0.56 0.07 140);
    --sage-dark: oklch(0.42 0.07 140);
    --terracotta: oklch(0.62 0.13 45);
    --font-display: 'Lora', Georgia, serif;
    --font-body: 'Nunito', ui-sans-serif, system-ui, sans-serif;

    position: relative;
    background: var(--bg);
    color: var(--ink);
    font-family: var(--font-body);
    -webkit-font-smoothing: antialiased;
    overflow: hidden;
    min-height: 100%;
}

.leaf {
    position: absolute;
    width: clamp(140px, 22vw, 240px);
    height: clamp(140px, 22vw, 240px);
    color: var(--sage);
    pointer-events: none;
    z-index: 1;
}

.leaf-tl { top: -30px; left: -30px; transform: rotate(-15deg); }
.leaf-br { bottom: -30px; right: -30px; transform: rotate(15deg); }

.hero {
    position: relative;
    z-index: 2;
    padding: clamp(2.5rem, 8vw, 5rem) clamp(1.5rem, 5vw, 3rem) clamp(1.5rem, 5vw, 3rem);
    max-width: 720px;
    margin: 0 auto;
    text-align: center;
}

.hero-kicker {
    display: inline-flex;
    align-items: center;
    gap: 0.9rem;
    font-family: var(--font-body);
    font-weight: 500;
    font-size: 0.72rem;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: var(--sage);
    margin: 0 0 1.1rem;
}

.leaf-glyph { font-size: 0.75rem; opacity: 0.8; }

.hero-title {
    font-family: var(--font-display);
    font-style: italic;
    font-weight: 500;
    font-size: clamp(2rem, 7vw, 3.8rem);
    line-height: 1;
    letter-spacing: -0.005em;
    margin: 0;
    color: var(--ink);
}

.hero-sub {
    margin: 1rem auto 0;
    max-width: 46ch;
    font-size: 0.9rem;
    line-height: 1.65;
    color: var(--ink-soft);
    font-weight: 300;
}

.main {
    position: relative;
    z-index: 2;
    max-width: 780px;
    margin: 0 auto;
    padding: 0 clamp(1.5rem, 5vw, 3rem);
}

.empty {
    text-align: center;
    padding: 3rem 0;
    color: var(--ink-faint);
    font-style: italic;
}

.section {
    margin-bottom: clamp(2.5rem, 6vw, 4rem);
}

.section-head { text-align: center; margin-bottom: 1.75rem; }

.section-glyph {
    display: inline-block;
    font-size: 1.35rem;
    color: var(--sage);
    margin-bottom: 0.5rem;
    opacity: 0.75;
}

.section-title {
    font-family: var(--font-display);
    font-style: italic;
    font-weight: 500;
    font-size: clamp(1.5rem, 4vw, 2.2rem);
    line-height: 1;
    letter-spacing: -0.005em;
    margin: 0;
    color: var(--ink);
}

.section-desc {
    max-width: 48ch;
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
    font-size: 0.9rem;
}

.dishes {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.dish {
    display: flex;
    gap: 1.25rem;
    align-items: flex-start;
    padding: 1.1rem 1.25rem;
    background: var(--panel);
    border-radius: 14px;
    border: 1px solid color-mix(in oklch, var(--rule) 60%, transparent);
}

.dish-image {
    flex: none;
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 10px;
    filter: saturate(0.9);
}

.dish-body { flex: 1; min-width: 0; }

.dish-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 0.3rem;
}

.dish-name {
    font-family: var(--font-display);
    font-weight: 500;
    font-size: 1rem;
    line-height: 1.35;
    margin: 0;
    color: var(--ink);
    flex: 1;
}

.dish-price {
    font-family: var(--font-display);
    font-weight: 500;
    font-style: italic;
    font-size: 1rem;
    color: var(--terracotta);
    font-variant-numeric: tabular-nums;
    white-space: nowrap;
    flex-shrink: 0;
}

.dish-meta-top {
    display: flex;
    flex-wrap: wrap;
    gap: 0.3rem;
    align-items: center;
    margin-bottom: 0.3rem;
}

.dish-kcal {
    font-size: 0.68rem;
    font-weight: 400;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--ink-faint);
    font-variant-numeric: tabular-nums;
}

.dish-tag {
    font-size: 0.75rem;
    color: var(--sage);
}

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
    gap: 0.4rem;
    padding: 0;
    background: transparent;
    border: none;
    cursor: pointer;
    font-family: var(--font-body);
    font-size: 0.78rem;
    font-weight: 500;
    color: var(--sage-dark);
    transition: color 200ms;
}

.ingredients-toggle:hover { color: var(--sage); outline: none; }

.toggle-leaf { font-size: 0.65rem; opacity: 0.7; }

.ingredients-list {
    margin: 0.5rem 0 0;
    padding: 0.5rem 0.75rem;
    font-size: 0.82rem;
    line-height: 1.6;
    color: var(--ink-soft);
    font-weight: 300;
    background: color-mix(in oklch, var(--sage) 8%, transparent);
    border-left: 2px solid var(--sage);
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
    color: var(--sage-dark);
    border: 1px solid color-mix(in oklch, var(--sage) 40%, transparent);
    padding: 0.1rem 0.28rem;
    border-radius: 4px;
    background: color-mix(in oklch, var(--sage) 10%, transparent);
}

.foot {
    position: relative;
    z-index: 2;
    max-width: 780px;
    margin: 0 auto;
    padding: clamp(2rem, 6vw, 4rem) clamp(1.5rem, 5vw, 3rem) clamp(1.5rem, 4vw, 2.5rem);
    text-align: center;
}

.foot-glyphs {
    color: var(--sage);
    font-size: 1rem;
    letter-spacing: 0.6em;
    margin-bottom: 1.1rem;
    opacity: 0.65;
}

.foot-name {
    font-family: var(--font-display);
    font-style: italic;
    font-weight: 500;
    font-size: 1.25rem;
    color: var(--ink);
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

.foot-branding a:hover { color: var(--sage-dark); border-bottom-color: currentColor; }
</style>
