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
    locale?: string;
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

const sections = (props.menu.sections ?? []).map((section, index) => ({
    ...section,
    products: section.products ?? [],
    anchor: `section-${section.id}`,
    number: String(index + 1).padStart(2, '0'),
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
    <div class="menu-neon">
        <div class="scanlines" aria-hidden="true" />

        <div class="topbar">
            <div class="brand">
                <span class="brand-bar" />
                <span class="brand-text">{{ menu.location?.name }}</span>
                <span class="brand-bar" />
            </div>
        </div>

        <header class="hero">
            <div class="hero-overlay" aria-hidden="true" />
            <div class="hero-inner">
                <div class="hero-marks" aria-hidden="true">
                    <span>◢◤</span>
                    <span class="hero-code">MENU_{{ menu.id }}</span>
                    <span>◥◣</span>
                </div>
                <h1 class="hero-title">{{ menu.name }}</h1>
                <p v-if="menu.description" class="hero-sub">{{ menu.description }}</p>
                <div class="hero-cta" aria-hidden="true">
                    <span class="cta-line" />
                    <span class="cta-dot" />
                    <span class="cta-line" />
                </div>
            </div>
        </header>

        <main class="main">
            <div v-if="sections.length === 0" class="empty">
                {{ lbl('empty', 'Este menú no tiene secciones todavía') }}
            </div>

            <section
                v-for="(section, sIdx) in sections"
                :id="section.anchor"
                :key="section.id"
                class="section"
                :style="{ '--i': sIdx } as Record<string, number>"
            >
                <header class="section-head">
                    <div class="section-label">
                        <span class="section-num">{{ section.number }}</span>
                        <span class="section-bar" />
                    </div>
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
                        <div v-if="showProductImages() && productImage(product)" class="dish-image-wrap">
                            <img :src="productImage(product)!" :alt="product.name" class="dish-image" loading="lazy" />
                        </div>
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
                                        <span class="toggle-bracket">[</span>
                                        {{ expandedProducts.has(product.id) ? lbl('hide_ingredients', 'OCULTAR') : lbl('show_ingredients', 'INGREDIENTES') }}
                                        <span class="toggle-bracket">]</span>
                                    </button>
                                    <p v-show="expandedProducts.has(product.id)" class="ingredients-list">
                                        {{ product.ingredients.map((i) => i.name).join(' / ') }}
                                    </p>
                                </template>
                                <template v-else>
                                    <p class="ingredients-list-static">
                                        {{ product.ingredients.map((i) => i.name).join(' / ') }}
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
            <div class="foot-bar" aria-hidden="true" />
            <p class="foot-name">{{ menu.location?.name }}</p>
            <p v-if="menu.location?.address" class="foot-line">◆ {{ menu.location.address }}</p>
            <p v-if="menu.location?.phone" class="foot-line">◆ {{ menu.location.phone }}</p>

            <div v-if="showBranding && tenantSlug" class="foot-branding">
                <a :href="`https://menulinker.com?ref=${tenantSlug}`" target="_blank" rel="noopener">
                    {{ lbl('branding', 'menulinker.com') }}
                </a>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.menu-neon {
    --bg: oklch(0.10 0.012 300);
    --panel: oklch(0.15 0.015 300);
    --ink: oklch(0.97 0.005 300);
    --ink-soft: oklch(0.75 0.012 300);
    --ink-faint: oklch(0.50 0.015 300);
    --rule: oklch(0.25 0.02 300);
    --neon: oklch(0.70 0.32 0);
    --neon-glow: oklch(0.70 0.32 0 / 0.55);
    --neon-cyan: oklch(0.82 0.17 200);
    --font-display: 'Big Shoulders Display', 'Impact', sans-serif;
    --font-body: 'Manrope', ui-sans-serif, system-ui, sans-serif;
    --font-mono: 'JetBrains Mono', ui-monospace, monospace;

    background: var(--bg);
    color: var(--ink);
    font-family: var(--font-body);
    -webkit-font-smoothing: antialiased;
    overflow: hidden;
    min-height: 100%;
}

.scanlines {
    position: absolute;
    inset: 0;
    pointer-events: none;
    background-image: repeating-linear-gradient(
        0deg,
        transparent 0,
        transparent 2px,
        oklch(1 0 0 / 0.015) 2px,
        oklch(1 0 0 / 0.015) 3px
    );
    z-index: 1;
    mix-blend-mode: screen;
}

.topbar {
    position: relative;
    z-index: 2;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 0.75rem clamp(1rem, 4vw, 2rem);
    background: color-mix(in oklch, var(--bg) 90%, transparent);
    border-bottom: 1px solid var(--rule);
}

.brand {
    display: flex;
    align-items: center;
    gap: 0.7rem;
    font-family: var(--font-mono);
    font-size: 0.64rem;
    font-weight: 500;
    letter-spacing: 0.25em;
    text-transform: uppercase;
    color: var(--ink-soft);
}

.brand-bar {
    width: 16px;
    height: 2px;
    background: var(--neon);
    box-shadow: 0 0 10px var(--neon-glow);
}

.hero {
    position: relative;
    z-index: 2;
    min-height: clamp(180px, 35svh, 300px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: clamp(1.5rem, 5vw, 3rem) clamp(1rem, 4vw, 2rem);
    overflow: hidden;
    background: linear-gradient(180deg, oklch(0.14 0.02 300) 0%, var(--bg) 100%);
}

.hero-overlay {
    position: absolute;
    inset: 0;
    background: radial-gradient(ellipse at center, transparent 30%, oklch(0 0 0 / 0.4) 100%);
    pointer-events: none;
}

.hero-inner {
    position: relative;
    z-index: 2;
    text-align: center;
    max-width: 900px;
}

.hero-marks {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.9rem;
    color: var(--neon);
    font-family: var(--font-mono);
    font-size: 0.65rem;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    text-shadow: 0 0 10px var(--neon-glow);
    margin-bottom: 1rem;
}

.hero-code { font-weight: 700; }

.hero-title {
    font-family: var(--font-display);
    font-weight: 900;
    font-size: clamp(2.2rem, 10vw, 5.5rem);
    line-height: 0.9;
    letter-spacing: -0.02em;
    text-transform: uppercase;
    color: var(--ink);
    margin: 0;
    text-shadow: 0 0 20px var(--neon-glow), 0 0 40px var(--neon-glow);
}

.hero-sub {
    margin: 1rem auto 0;
    max-width: 52ch;
    font-size: 0.88rem;
    line-height: 1.6;
    color: var(--ink-soft);
}

.hero-cta {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.8rem;
    margin-top: 1.5rem;
}

.cta-line { width: 32px; height: 2px; background: var(--neon); box-shadow: 0 0 10px var(--neon-glow); }
.cta-dot { width: 6px; height: 6px; background: var(--neon); border-radius: 999px; box-shadow: 0 0 12px var(--neon); }

.main {
    position: relative;
    z-index: 2;
    max-width: 1000px;
    margin: 0 auto;
    padding: clamp(1.5rem, 5vw, 3rem) clamp(1.25rem, 4vw, 2.5rem) 0;
}

.empty {
    text-align: center;
    padding: 3rem 0;
    color: var(--ink-faint);
    font-family: var(--font-mono);
    font-size: 0.85rem;
}

.section { margin-bottom: clamp(2.5rem, 6vw, 4rem); }
.section-head { margin-bottom: 1.75rem; }

.section-label {
    display: flex;
    align-items: center;
    gap: 0.9rem;
    margin-bottom: 0.6rem;
}

.section-num {
    font-family: var(--font-display);
    font-weight: 900;
    font-size: 1.35rem;
    line-height: 1;
    color: var(--neon);
    text-shadow: 0 0 12px var(--neon-glow);
    letter-spacing: -0.02em;
}

.section-bar {
    flex: 1;
    height: 1px;
    background: linear-gradient(90deg, var(--neon), transparent);
}

.section-title {
    font-family: var(--font-display);
    font-weight: 700;
    font-size: clamp(1.5rem, 4vw, 2.4rem);
    line-height: 0.95;
    letter-spacing: -0.02em;
    text-transform: uppercase;
    margin: 0;
    color: var(--ink);
}

.section-desc {
    margin-top: 0.5rem;
    font-size: 0.86rem;
    color: var(--ink-soft);
    max-width: 60ch;
    line-height: 1.55;
}

.section-empty {
    text-align: center;
    padding: 2rem 0;
    color: var(--ink-faint);
    font-family: var(--font-mono);
    font-size: 0.8rem;
}

.dishes {
    list-style: none;
    padding: 0;
    margin: 0;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.85rem;
}

.dish {
    display: flex;
    flex-direction: column;
    background: var(--panel);
    border: 1px solid var(--rule);
    overflow: hidden;
    position: relative;
}

.dish::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 12px;
    height: 12px;
    border-top: 2px solid var(--neon);
    border-left: 2px solid var(--neon);
    pointer-events: none;
    opacity: 0.7;
}

.dish::before {
    content: '';
    position: absolute;
    bottom: 0;
    right: 0;
    width: 12px;
    height: 12px;
    border-bottom: 2px solid var(--neon);
    border-right: 2px solid var(--neon);
    pointer-events: none;
    opacity: 0.7;
}

.dish-image-wrap {
    width: 100%;
    aspect-ratio: 16/9;
    overflow: hidden;
}

.dish-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    filter: saturate(0.8) contrast(1.1);
}

.dish-body {
    padding: 1rem 1.1rem 1.1rem;
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
    flex: 1;
}

.dish-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.75rem;
}

.dish-name {
    font-family: var(--font-display);
    font-weight: 700;
    font-size: 0.95rem;
    line-height: 1.2;
    letter-spacing: -0.01em;
    text-transform: uppercase;
    margin: 0;
    color: var(--ink);
    flex: 1;
}

.dish-price {
    font-family: var(--font-mono);
    font-weight: 700;
    font-size: 0.85rem;
    color: var(--neon);
    font-variant-numeric: tabular-nums;
    white-space: nowrap;
    text-shadow: 0 0 8px var(--neon-glow);
}

.dish-meta-top {
    display: flex;
    flex-wrap: wrap;
    gap: 0.25rem;
    align-items: center;
}

.dish-kcal {
    font-family: var(--font-mono);
    font-size: 0.6rem;
    color: var(--neon-cyan);
    font-variant-numeric: tabular-nums;
    text-transform: uppercase;
}

.dish-tag {
    font-size: 0.65rem;
    color: var(--ink-soft);
}

.dish-desc {
    margin: 0;
    font-size: 0.78rem;
    line-height: 1.5;
    color: var(--ink-soft);
}

.ingredients-wrap { margin-top: 0.3rem; }

.ingredients-toggle {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0;
    background: transparent;
    border: none;
    cursor: pointer;
    font-family: var(--font-mono);
    font-size: 0.65rem;
    font-weight: 500;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--neon-cyan);
    transition: color 200ms;
}

.ingredients-toggle:hover { color: var(--neon); outline: none; }

.toggle-bracket { opacity: 0.6; }

.ingredients-list {
    margin: 0.4rem 0 0;
    padding: 0.5rem 0.65rem;
    font-family: var(--font-mono);
    font-size: 0.7rem;
    line-height: 1.55;
    color: var(--ink-soft);
    background: oklch(0.18 0.015 300);
    border-left: 2px solid var(--neon);
}

.ingredients-list-static {
    margin: 0.3rem 0 0;
    font-family: var(--font-mono);
    font-size: 0.65rem;
    line-height: 1.5;
    color: var(--ink-faint);
}

.dish-allergens {
    display: flex;
    flex-wrap: wrap;
    gap: 0.2rem;
    margin-top: 0.4rem;
}

.allergen-badge {
    font-family: var(--font-mono);
    font-size: 0.58rem;
    font-weight: 500;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: var(--neon-cyan);
    border: 1px solid var(--rule);
    padding: 0.1rem 0.28rem;
}

.foot {
    position: relative;
    z-index: 2;
    text-align: center;
    max-width: 1000px;
    margin: 0 auto;
    padding: clamp(2rem, 6vw, 4rem) clamp(1.5rem, 4vw, 3rem) clamp(1.5rem, 4vw, 2.5rem);
}

.foot-bar {
    width: 50px;
    height: 2px;
    background: var(--neon);
    margin: 0 auto 1.5rem;
    box-shadow: 0 0 14px var(--neon-glow);
}

.foot-name {
    font-family: var(--font-display);
    font-weight: 900;
    font-size: 1.35rem;
    text-transform: uppercase;
    letter-spacing: -0.01em;
    color: var(--ink);
    margin: 0 0 0.75rem;
    text-shadow: 0 0 12px var(--neon-glow);
}

.foot-line {
    margin: 0.15rem 0;
    font-family: var(--font-mono);
    font-size: 0.72rem;
    color: var(--ink-soft);
    letter-spacing: 0.03em;
}

.foot-branding {
    margin-top: 1.75rem;
    font-family: var(--font-mono);
    font-size: 0.62rem;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--ink-faint);
}

.foot-branding a {
    color: inherit;
    text-decoration: none;
    transition: color 200ms;
}

.foot-branding a:hover { color: var(--neon); }
</style>
