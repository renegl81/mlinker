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
    chapter_word?: string;
    chapter_meta?: string;
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
    chapter: index + 1,
}));

const expandedProducts = ref<Set<number>>(new Set());

function toggleIngredients(productId: number) {
    const next = new Set(expandedProducts.value);
    if (next.has(productId)) next.delete(productId);
    else next.add(productId);
    expandedProducts.value = next;
}

function tagsFor(tags?: string[] | null): Array<{ code: string; glyph: string }> {
    const MAP: Record<string, string> = { vegetarian: '🌿', vegan: '🌱', gluten_free: 'GF', spicy: '🌶' };
    return (tags ?? []).filter((c) => c in MAP).map((c) => ({ code: c, glyph: MAP[c] }));
}

const lbl = (key: string, fallback: string) => props.labels?.[key] ?? fallback;

const showAllergens = () => props.layout?.showAllergens ?? true;
const showIngredients = () => props.layout?.showIngredients ?? true;
const showSectionDescriptions = () => props.layout?.showSectionDescriptions ?? true;

const chapterWord = props.labels?.chapter_word ?? 'Capítulo';
</script>

<template>
    <div class="menu-chapter">
        <header class="hero">
            <p class="hero-kicker">— {{ menu.location?.name }} —</p>
            <div class="hero-rule" aria-hidden="true" />
            <h1 class="hero-title">{{ menu.name }}</h1>
            <div class="hero-rule" aria-hidden="true" />
            <p v-if="menu.description" class="hero-sub">{{ menu.description }}</p>
            <p class="hero-meta">
                {{ lbl('chapter_meta', 'Degustación') }} · {{ sections.length }}
                {{ sections.length === 1 ? chapterWord.toLowerCase() : `${chapterWord.toLowerCase()}s` }}
            </p>
        </header>

        <main class="main">
            <p v-if="sections.length === 0" class="empty">
                {{ lbl('empty', 'Este menú no tiene secciones todavía') }}
            </p>

            <section
                v-for="(section, sIdx) in sections"
                :key="section.id"
                class="chapter"
                :style="{ '--i': sIdx } as Record<string, number>"
            >
                <header class="chapter-head">
                    <div class="chapter-label">
                        <span class="chapter-word">{{ chapterWord }}</span>
                        <span class="chapter-num">{{ section.numeral }}</span>
                    </div>
                    <h2 class="chapter-title">{{ section.name }}</h2>
                    <p v-if="showSectionDescriptions() && section.description" class="chapter-desc">{{ section.description }}</p>
                </header>

                <ol v-if="section.products.length > 0" class="dishes">
                    <li
                        v-for="(product, pIdx) in section.products"
                        :key="product.id"
                        class="dish"
                        :style="{ '--j': pIdx } as Record<string, number>"
                    >
                        <div class="dish-index">{{ String(pIdx + 1).padStart(2, '0') }}</div>
                        <div class="dish-body">
                            <h3 class="dish-name">{{ product.name }}</h3>

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
                                        {{ expandedProducts.has(product.id) ? '—' : '+' }}
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
                        <div class="dish-price-wrap">
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
                    </li>
                </ol>

                <p v-else class="section-empty">{{ lbl('section_empty', 'Esta sección no tiene productos') }}</p>

                <div class="chapter-end" aria-hidden="true">
                    <span>✦</span>
                </div>
            </section>
        </main>

        <footer class="foot">
            <div class="foot-rule" aria-hidden="true" />
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
.menu-chapter {
    --bg: oklch(0.962 0.012 85);
    --ink: oklch(0.16 0.01 40);
    --ink-soft: oklch(0.42 0.012 40);
    --ink-faint: oklch(0.58 0.01 40);
    --rule: oklch(0.82 0.01 60);
    --gold: oklch(0.62 0.12 75);
    --gold-dark: oklch(0.48 0.11 75);
    --font-display: 'Bodoni Moda', 'Playfair Display', Georgia, serif;
    --font-body: 'Lora', Georgia, serif;

    position: relative;
    background: var(--bg);
    color: var(--ink);
    font-family: var(--font-body);
    font-weight: 400;
    -webkit-font-smoothing: antialiased;
    overflow: hidden;
    min-height: 100%;
}

.hero {
    max-width: 620px;
    margin: 0 auto;
    padding: clamp(2.5rem, 8vw, 5.5rem) clamp(1.5rem, 5vw, 3rem) clamp(2rem, 6vw, 4rem);
    text-align: center;
}

.hero-kicker {
    font-family: var(--font-body);
    font-style: italic;
    font-weight: 400;
    font-size: 0.72rem;
    letter-spacing: 0.24em;
    text-transform: uppercase;
    color: var(--gold-dark);
    margin: 0 0 1.1rem;
}

.hero-rule {
    width: 40px;
    height: 1px;
    background: var(--ink);
    margin: 1.1rem auto;
    opacity: 0.5;
}

.hero-title {
    font-family: var(--font-display);
    font-weight: 400;
    font-style: italic;
    font-size: clamp(2.2rem, 7vw, 4rem);
    line-height: 1;
    letter-spacing: -0.01em;
    margin: 0;
    color: var(--ink);
}

.hero-sub {
    margin: 1.1rem auto 0.75rem;
    max-width: 42ch;
    font-size: 0.92rem;
    line-height: 1.7;
    font-style: italic;
    color: var(--ink-soft);
}

.hero-meta {
    font-family: var(--font-body);
    font-style: italic;
    font-size: 0.72rem;
    letter-spacing: 0.1em;
    color: var(--ink-faint);
    margin: 1.5rem 0 0;
}

.main {
    max-width: 620px;
    margin: 0 auto;
    padding: 0 clamp(1.5rem, 5vw, 3rem);
}

.empty {
    text-align: center;
    padding: 3.5rem 0;
    font-style: italic;
    color: var(--ink-faint);
}

.chapter {
    margin-bottom: clamp(3rem, 8vw, 5.5rem);
}

.chapter-head { text-align: center; margin-bottom: 2rem; }

.chapter-label {
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    gap: 0.15rem;
    margin-bottom: 0.75rem;
}

.chapter-word {
    font-family: var(--font-body);
    font-style: italic;
    font-size: 0.65rem;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    color: var(--gold-dark);
}

.chapter-num {
    font-family: var(--font-display);
    font-weight: 500;
    font-size: 1.4rem;
    line-height: 1;
    color: var(--gold);
    letter-spacing: 0.04em;
}

.chapter-title {
    font-family: var(--font-display);
    font-style: italic;
    font-weight: 400;
    font-size: clamp(1.5rem, 4.5vw, 2.3rem);
    line-height: 1;
    letter-spacing: -0.005em;
    margin: 0 0 0.7rem;
    color: var(--ink);
}

.chapter-desc {
    max-width: 42ch;
    margin: 0 auto;
    font-style: italic;
    font-size: 0.88rem;
    line-height: 1.65;
    color: var(--ink-soft);
}

.dishes {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 1.75rem;
}

.dish {
    display: grid;
    grid-template-columns: auto 1fr auto;
    gap: 1.1rem;
    align-items: flex-start;
}

.dish-index {
    font-family: var(--font-display);
    font-style: italic;
    font-weight: 400;
    font-size: 0.72rem;
    color: var(--gold);
    letter-spacing: 0.02em;
    padding-top: 0.15rem;
    font-variant-numeric: tabular-nums;
}

.dish-body { min-width: 0; }

.dish-name {
    font-family: var(--font-display);
    font-weight: 500;
    font-size: 1.05rem;
    line-height: 1.3;
    letter-spacing: -0.005em;
    margin: 0 0 0.3rem;
    color: var(--ink);
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
    font-style: italic;
}

.dish-tag { font-size: 0.75rem; color: var(--gold-dark); }

.dish-desc {
    margin: 0;
    font-style: italic;
    font-size: 0.86rem;
    line-height: 1.65;
    color: var(--ink-soft);
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
    font-style: italic;
    font-size: 0.8rem;
    color: var(--ink-faint);
    transition: color 200ms;
}

.ingredients-toggle:hover { color: var(--gold-dark); outline: none; }

.ingredients-list {
    margin: 0.5rem 0 0;
    padding: 0.5rem 0;
    border-top: 1px solid var(--rule);
    font-style: italic;
    font-size: 0.85rem;
    line-height: 1.6;
    color: var(--ink-soft);
}

.ingredients-list-static {
    margin: 0.35rem 0 0;
    font-style: italic;
    font-size: 0.8rem;
    line-height: 1.5;
    color: var(--ink-faint);
}

.dish-allergens {
    display: flex;
    flex-wrap: wrap;
    gap: 0.2rem;
    margin-top: 0.5rem;
}

.allergen-badge {
    font-size: 0.6rem;
    font-weight: 400;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: var(--ink-faint);
    font-style: italic;
    border: 1px solid var(--rule);
    padding: 0.1rem 0.28rem;
    border-radius: 3px;
}

.dish-price-wrap { padding-top: 0.15rem; }

.dish-price {
    font-family: var(--font-display);
    font-weight: 400;
    font-size: 0.95rem;
    color: var(--ink);
    font-variant-numeric: tabular-nums;
    white-space: nowrap;
}

.section-empty {
    text-align: center;
    font-style: italic;
    color: var(--ink-faint);
    padding: 1.5rem 0;
    font-size: 0.9rem;
}

.chapter-end {
    text-align: center;
    margin-top: 2rem;
    color: var(--gold);
    opacity: 0.55;
    font-size: 0.82rem;
}

.foot {
    max-width: 620px;
    margin: 0 auto;
    padding: clamp(2.5rem, 7vw, 5rem) clamp(1.5rem, 5vw, 3rem) clamp(1.5rem, 4vw, 2.5rem);
    text-align: center;
}

.foot-rule {
    width: 35px;
    height: 1px;
    background: var(--gold);
    margin: 0 auto 1.5rem;
    opacity: 0.7;
}

.foot-name {
    font-family: var(--font-display);
    font-style: italic;
    font-weight: 400;
    font-size: 1.2rem;
    color: var(--ink);
    margin: 0 0 0.5rem;
}

.foot-line { margin: 0.15rem 0; font-size: 0.82rem; font-style: italic; color: var(--ink-soft); }

.foot-branding {
    margin-top: 1.75rem;
    font-size: 0.68rem;
    font-style: italic;
    color: var(--ink-faint);
}

.foot-branding a {
    color: inherit;
    text-decoration: none;
    border-bottom: 1px solid transparent;
    transition: border-color 200ms, color 200ms;
}

.foot-branding a:hover { color: var(--gold-dark); border-bottom-color: currentColor; }
</style>
