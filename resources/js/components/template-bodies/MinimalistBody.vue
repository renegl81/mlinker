<script setup lang="ts">
import { ref } from 'vue';
import AddToCartButton from '@/components/public/AddToCartButton.vue';
import type { BodyMenu } from './fakeMenu';

interface LayoutFlags {
    showAllergens?: boolean;
    showIngredients?: boolean;
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
    numeral: toRoman(index + 1),
}));

const expandedProducts = ref<Set<number>>(new Set());

function toggleIngredients(productId: number) {
    const next = new Set(expandedProducts.value);
    if (next.has(productId)) next.delete(productId);
    else next.add(productId);
    expandedProducts.value = next;
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
const showSectionDescriptions = () => props.layout?.showSectionDescriptions ?? true;
</script>

<template>
    <div class="menu-minimal">
        <header class="hero">
            <p class="hero-kicker">{{ menu.location?.name }}</p>
            <div class="hero-divider" aria-hidden="true" />
            <h1 class="hero-title">{{ menu.name }}</h1>
            <p v-if="menu.description" class="hero-sub">{{ menu.description }}</p>
        </header>

        <main class="main">
            <p v-if="sections.length === 0" class="empty">
                {{ lbl('empty', 'Este menú no tiene secciones todavía') }}
            </p>

            <section
                v-for="section in sections"
                :key="section.id"
                class="section"
            >
                <header class="section-head">
                    <div class="section-divider" aria-hidden="true" />
                    <div class="section-title-block">
                        <span class="section-numeral" aria-hidden="true">{{ section.numeral }}</span>
                        <h2 class="section-title">{{ section.name }}</h2>
                    </div>
                    <div class="section-divider" aria-hidden="true" />
                </header>

                <p v-if="showSectionDescriptions() && section.description" class="section-desc">
                    {{ section.description }}
                </p>

                <ul v-if="section.products.length > 0" class="products">
                    <li
                        v-for="product in section.products"
                        :key="product.id"
                        class="product"
                    >
                        <div class="product-head">
                            <h3 class="product-name">
                                {{ product.name }}
                                <span
                                    v-for="tag in tagsFor(product.tags)"
                                    :key="tag.code"
                                    class="product-tag"
                                    :title="tag.code"
                                >{{ tag.glyph }}</span>
                            </h3>
                            <span
                                v-if="menu.show_prices && product.price"
                                class="product-price"
                            >{{ formatPrice(product.price) }}</span>
                            <AddToCartButton
                                v-if="interactive && hasCart && menu.show_prices && product.price"
                                :quantity="cartGetQuantity?.(product.id) ?? 0"
                                @add="cartAddItem?.(product.id)"
                                @remove="cartRemoveItem?.(product.id)"
                            />
                        </div>

                        <p v-if="product.description" class="product-desc">
                            {{ product.description }}
                        </p>

                        <p
                            v-if="menu.show_calories && product.calories"
                            class="product-kcal"
                        >
                            {{ product.calories }} {{ lbl('kcal', 'kcal') }}
                        </p>

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
                                    {{ expandedProducts.has(product.id) ? '—' : '+' }}
                                    <span>
                                        {{ expandedProducts.has(product.id) ? lbl('hide_ingredients', 'Ocultar ingredientes') : lbl('show_ingredients', 'Ver ingredientes') }}
                                    </span>
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
                            class="product-allergens"
                        >
                            <span
                                v-for="allergen in product.allergens"
                                :key="allergen.id"
                                class="allergen-badge"
                                :title="allergen.name"
                            >{{ allergen.code }}</span>
                        </div>
                    </li>
                </ul>

                <p v-else class="section-empty">{{ lbl('section_empty', 'Esta sección no tiene productos') }}</p>
            </section>
        </main>

        <footer class="foot">
            <div class="foot-divider" aria-hidden="true" />
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
.menu-minimal {
    --bg: oklch(0.985 0 0);
    --ink: oklch(0.14 0.003 260);
    --ink-soft: oklch(0.42 0.004 260);
    --ink-faint: oklch(0.62 0.004 260);
    --rule: oklch(0.84 0.002 260);
    --font-serif: 'Cormorant Garamond', 'Garamond', 'Times New Roman', serif;

    background: var(--bg);
    color: var(--ink);
    font-family: var(--font-serif);
    font-weight: 400;
    -webkit-font-smoothing: antialiased;
    overflow: hidden;
    min-height: 100%;
}

.hero {
    padding: clamp(2.5rem, 8vw, 5rem) clamp(1.5rem, 5vw, 3rem) clamp(1.5rem, 5vw, 3rem);
    text-align: center;
    max-width: 620px;
    margin: 0 auto;
}

.hero-kicker {
    font-size: 0.75rem;
    font-weight: 400;
    letter-spacing: 0.35em;
    text-transform: uppercase;
    color: var(--ink-soft);
    margin: 0 0 1.25rem;
}

.hero-divider {
    width: 1px;
    height: 36px;
    margin: 0 auto 1.25rem;
    background: var(--rule);
}

.hero-title {
    font-family: var(--font-serif);
    font-style: italic;
    font-weight: 400;
    font-size: clamp(2rem, 7vw, 3.5rem);
    line-height: 1.05;
    letter-spacing: -0.01em;
    margin: 0;
}

.hero-sub {
    margin-top: 1rem;
    font-size: 0.92rem;
    line-height: 1.65;
    color: var(--ink-soft);
    font-style: italic;
    max-width: 46ch;
    margin-left: auto;
    margin-right: auto;
}

.main {
    max-width: 560px;
    margin: 0 auto;
    padding: 0 clamp(1.5rem, 5vw, 3rem);
}

.empty {
    text-align: center;
    padding: 3.5rem 0;
    color: var(--ink-faint);
    font-style: italic;
    font-size: 1rem;
}

.section { margin-bottom: clamp(2.5rem, 7vw, 4rem); }

.section-head {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin: 0 0 1.5rem;
}

.section-divider { flex: 1; height: 1px; background: var(--rule); }

.section-title-block {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.25rem;
}

.section-numeral {
    font-family: var(--font-serif);
    font-style: italic;
    font-weight: 400;
    font-size: 0.7rem;
    letter-spacing: 0.16em;
    color: var(--ink-faint);
}

.section-title {
    font-family: var(--font-serif);
    font-weight: 500;
    font-size: 0.88rem;
    letter-spacing: 0.32em;
    text-transform: uppercase;
    margin: 0;
    color: var(--ink);
    white-space: nowrap;
}

.section-desc {
    text-align: center;
    max-width: 44ch;
    margin: -0.5rem auto 1.5rem;
    font-style: italic;
    font-size: 0.9rem;
    line-height: 1.6;
    color: var(--ink-soft);
}

.section-empty {
    text-align: center;
    color: var(--ink-faint);
    font-style: italic;
    font-size: 0.92rem;
    padding: 1.5rem 0;
}

.products {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 1.75rem;
}

.product { text-align: center; }

.product-head {
    display: flex;
    align-items: baseline;
    justify-content: center;
    gap: 0.9rem;
    flex-wrap: wrap;
    margin-bottom: 0.25rem;
}

.product-name {
    font-family: var(--font-serif);
    font-weight: 500;
    font-size: 1.2rem;
    line-height: 1.2;
    margin: 0;
    color: var(--ink);
}

.product-tag {
    display: inline-block;
    margin-left: 0.2rem;
    font-family: var(--font-serif);
    font-style: italic;
    font-weight: 400;
    font-size: 0.75rem;
    color: var(--ink-faint);
}

.product-tag::before { content: '('; }
.product-tag::after { content: ')'; }

.product-price {
    font-family: var(--font-serif);
    font-weight: 500;
    font-size: 1.05rem;
    color: var(--ink);
    font-variant-numeric: tabular-nums;
    white-space: nowrap;
}

.product-desc {
    margin: 0.25rem auto 0;
    max-width: 42ch;
    font-style: italic;
    font-weight: 400;
    font-size: 0.88rem;
    line-height: 1.6;
    color: var(--ink-soft);
}

.product-kcal {
    margin: 0.4rem 0 0;
    font-size: 0.7rem;
    font-weight: 400;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: var(--ink-faint);
    font-variant-numeric: tabular-nums;
}

.ingredients-wrap { margin-top: 0.5rem; }

.ingredients-toggle {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0;
    background: transparent;
    border: none;
    cursor: pointer;
    font-family: var(--font-serif);
    font-style: italic;
    font-size: 0.82rem;
    color: var(--ink-faint);
    transition: color 200ms;
}

.ingredients-toggle:hover,
.ingredients-toggle:focus-visible {
    color: var(--ink);
    outline: none;
}

.ingredients-list {
    margin: 0.7rem auto 0;
    max-width: 42ch;
    padding-top: 0.7rem;
    border-top: 1px solid var(--rule);
    font-style: italic;
    font-size: 0.88rem;
    line-height: 1.6;
    color: var(--ink-soft);
}

.ingredients-list-static {
    margin: 0.4rem auto 0;
    max-width: 42ch;
    font-style: italic;
    font-size: 0.8rem;
    line-height: 1.5;
    color: var(--ink-faint);
}

.product-allergens {
    display: flex;
    justify-content: center;
    gap: 0.3rem;
    margin-top: 0.5rem;
    flex-wrap: wrap;
}

.allergen-badge {
    font-size: 0.62rem;
    font-weight: 500;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: var(--ink-faint);
    border: 1px solid var(--rule);
    padding: 0.1rem 0.28rem;
    border-radius: 3px;
}

.foot {
    max-width: 560px;
    margin: 0 auto;
    padding: clamp(2.5rem, 7vw, 4.5rem) clamp(1.5rem, 5vw, 3rem) clamp(1.5rem, 4vw, 2.5rem);
    text-align: center;
}

.foot-divider {
    width: 1px;
    height: 30px;
    margin: 0 auto 1.5rem;
    background: var(--rule);
}

.foot-name {
    font-family: var(--font-serif);
    font-weight: 500;
    font-size: 0.98rem;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: var(--ink);
    margin: 0 0 0.75rem;
}

.foot-line {
    margin: 0.2rem 0;
    font-size: 0.82rem;
    font-style: italic;
    color: var(--ink-soft);
}

.foot-branding {
    margin-top: 2rem;
    font-size: 0.68rem;
    font-style: italic;
    color: var(--ink-faint);
}

.foot-branding a {
    color: inherit;
    text-decoration: none;
    border-bottom: 1px solid transparent;
    transition: border-color 200ms;
}

.foot-branding a:hover { border-bottom-color: currentColor; }
</style>
