<script setup lang="ts">
import { ref } from 'vue';
import AddToCartButton from '@/components/public/AddToCartButton.vue';
import MenuLanguageSwitcher from '@/components/public/MenuLanguageSwitcher.vue';
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
    /** Language switcher - only used when interactive=true */
    hasMultilang?: boolean;
    availableLocales?: string[];
    supportedLocales?: Record<string, { native: string; flag: string }>;
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

const currentLocale = props.locale ?? 'ES';
</script>

<template>
    <div class="menu-modern">
        <div class="topbar">
            <div class="topbar-brand">
                <span class="brand-dot" />
                <span class="brand-text">{{ menu.location?.name }}</span>
            </div>
            <MenuLanguageSwitcher
                v-if="interactive && hasMultilang && availableLocales && availableLocales.length > 1"
                :current="currentLocale"
                :available="availableLocales"
                :locales-meta="supportedLocales ?? {}"
            />
        </div>

        <!-- HERO -->
        <header class="hero">
            <div
                v-if="menu.image_path || (menu as any).image_url"
                class="hero-image"
                :style="{ backgroundImage: `url(${(menu as any).image_url ?? menu.image_path})` }"
            />
            <div class="hero-overlay" />
            <div class="hero-content">
                <div class="hero-kicker">
                    <span class="hero-dash" />
                    <span>MENÚ · {{ currentLocale.toUpperCase() }}</span>
                </div>
                <h1 class="hero-title">{{ menu.name }}</h1>
                <p v-if="menu.description" class="hero-sub">{{ menu.description }}</p>
            </div>
        </header>

        <!-- Main -->
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
                <div class="section-head">
                    <span class="section-num">{{ section.number }}</span>
                    <div class="section-meta">
                        <h2 class="section-title">{{ section.name }}</h2>
                        <p v-if="showSectionDescriptions() && section.description" class="section-desc">{{ section.description }}</p>
                    </div>
                </div>

                <ul v-if="section.products.length > 0" class="cards">
                    <li
                        v-for="(product, pIdx) in section.products"
                        :key="product.id"
                        class="card"
                        :style="{ '--j': pIdx } as Record<string, number>"
                    >
                        <div v-if="showProductImages() && productImage(product)" class="card-image">
                            <img :src="productImage(product)!" :alt="product.name" loading="lazy" />
                        </div>
                        <div v-else-if="showProductImages() && !productImage(product)" class="card-image card-image-fallback">
                            <span>{{ product.name.charAt(0).toUpperCase() }}</span>
                        </div>

                        <div class="card-body">
                            <div class="card-top">
                                <h3 class="card-name">{{ product.name }}</h3>
                                <span
                                    v-if="menu.show_prices && product.price"
                                    class="card-price"
                                >{{ formatPrice(product.price) }}</span>
                                <AddToCartButton
                                    v-if="interactive && hasCart && menu.show_prices && product.price"
                                    :quantity="cartGetQuantity?.(product.id) ?? 0"
                                    @add="cartAddItem?.(product.id)"
                                    @remove="cartRemoveItem?.(product.id)"
                                />
                            </div>

                            <div class="card-meta-top">
                                <span
                                    v-if="menu.show_calories && product.calories"
                                    class="card-kcal"
                                >{{ product.calories }} {{ lbl('kcal', 'kcal') }}</span>
                                <span
                                    v-for="tag in tagsFor(product.tags)"
                                    :key="tag.code"
                                    class="card-tag"
                                    :title="tag.code"
                                >{{ tag.glyph }}</span>
                            </div>

                            <p v-if="product.description" class="card-desc">
                                {{ product.description }}
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
                                        <span>
                                            {{ expandedProducts.has(product.id) ? lbl('hide_ingredients', 'Ocultar ingredientes') : lbl('show_ingredients', 'Ver ingredientes') }}
                                        </span>
                                        <svg
                                            :class="{ 'is-open': expandedProducts.has(product.id) }"
                                            viewBox="0 0 10 10"
                                            width="10"
                                            height="10"
                                            aria-hidden="true"
                                        >
                                            <path d="M2 3.5L5 6.5L8 3.5" stroke="currentColor" stroke-width="1.6" fill="none" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
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
                                class="card-allergens"
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

        <!-- Footer -->
        <footer class="foot">
            <div class="foot-rule" />
            <p class="foot-name">{{ menu.location?.name }}</p>
            <div class="foot-info">
                <p v-if="menu.location?.address">{{ menu.location.address }}</p>
                <p v-if="menu.location?.phone">{{ menu.location.phone }}</p>
            </div>
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
.menu-modern {
    --bg: oklch(0.975 0.006 270);
    --panel: oklch(0.99 0.004 270);
    --panel-2: oklch(0.96 0.008 270);
    --ink: oklch(0.16 0.02 270);
    --ink-soft: oklch(0.45 0.015 270);
    --ink-faint: oklch(0.60 0.01 270);
    --rule: oklch(0.90 0.008 270);
    --accent: oklch(0.50 0.18 270);
    --accent-glow: oklch(0.50 0.18 270 / 0.15);
    --font-display: 'Syne', ui-sans-serif, system-ui, sans-serif;
    --font-body: 'Manrope', ui-sans-serif, system-ui, sans-serif;

    background: var(--bg);
    color: var(--ink);
    font-family: var(--font-body);
    -webkit-font-smoothing: antialiased;
    overflow: hidden;
    min-height: 100%;
}

.topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 0.75rem clamp(1rem, 4vw, 2rem);
    background: color-mix(in oklch, var(--bg) 80%, transparent);
    border-bottom: 1px solid var(--rule);
}

.topbar-brand {
    display: flex;
    align-items: center;
    gap: 0.55rem;
    font-size: 0.65rem;
    font-weight: 600;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: var(--ink-soft);
}

.brand-dot {
    width: 6px;
    height: 6px;
    border-radius: 999px;
    background: var(--accent);
    box-shadow: 0 0 12px var(--accent-glow);
}

.hero {
    position: relative;
    min-height: clamp(200px, 40svh, 340px);
    display: flex;
    align-items: flex-end;
    padding: clamp(1.5rem, 5vw, 3rem) clamp(1.25rem, 5vw, 3.5rem) clamp(1.5rem, 5vw, 3rem);
    overflow: hidden;
    background: color-mix(in oklch, var(--bg) 50%, oklch(0.10 0.02 270));
}

.hero-image {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center;
    filter: contrast(1.05) saturate(0.92);
}

.hero-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, oklch(0 0 0 / 0.1) 0%, oklch(0 0 0 / 0.2) 40%, var(--bg) 100%);
    pointer-events: none;
}

.hero-content {
    position: relative;
    z-index: 2;
    max-width: 1100px;
    width: 100%;
    margin: 0 auto;
    color: oklch(0.97 0.005 270);
}

.hero-kicker {
    display: flex;
    align-items: center;
    gap: 0.9rem;
    font-size: 0.65rem;
    font-weight: 600;
    letter-spacing: 0.28em;
    text-transform: uppercase;
    color: var(--accent);
    margin-bottom: 0.75rem;
}

.hero-dash {
    display: inline-block;
    width: 30px;
    height: 2px;
    background: var(--accent);
}

.hero-title {
    font-family: var(--font-display);
    font-weight: 800;
    font-size: clamp(2rem, 8vw, 4.5rem);
    line-height: 0.92;
    letter-spacing: -0.035em;
    margin: 0;
    text-transform: uppercase;
}

.hero-sub {
    margin-top: 0.85rem;
    max-width: 56ch;
    font-size: 0.88rem;
    line-height: 1.55;
    color: var(--ink-soft);
}

.main {
    max-width: 1100px;
    margin: 0 auto;
    padding: clamp(1.5rem, 5vw, 3.5rem) clamp(1.25rem, 5vw, 3.5rem) 0;
}

.empty {
    text-align: center;
    padding: 4rem 0;
    color: var(--ink-faint);
    font-family: var(--font-display);
    font-size: 0.92rem;
}

.section { margin-bottom: clamp(2.5rem, 6vw, 4rem); }

.section-head {
    display: flex;
    align-items: flex-start;
    gap: clamp(1rem, 3vw, 2rem);
    margin-bottom: clamp(1.25rem, 3vw, 2rem);
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--rule);
}

.section-num {
    font-family: var(--font-display);
    font-weight: 800;
    font-size: clamp(1.5rem, 4vw, 2.2rem);
    line-height: 0.9;
    color: var(--accent);
    letter-spacing: -0.02em;
    padding-top: 0.2rem;
}

.section-meta { flex: 1; min-width: 0; }

.section-title {
    font-family: var(--font-display);
    font-weight: 700;
    font-size: clamp(1.2rem, 3.5vw, 2rem);
    line-height: 1;
    letter-spacing: -0.02em;
    text-transform: uppercase;
    margin: 0;
}

.section-desc {
    margin-top: 0.5rem;
    font-size: 0.85rem;
    color: var(--ink-soft);
    line-height: 1.5;
}

.section-empty {
    text-align: center;
    padding: 2rem 0;
    color: var(--ink-faint);
    font-style: italic;
    font-size: 0.88rem;
}

.cards {
    list-style: none;
    margin: 0;
    padding: 0;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.85rem;
}

.card {
    display: flex;
    flex-direction: column;
    background: var(--panel);
    border: 1px solid var(--rule);
    border-radius: 12px;
    overflow: hidden;
    position: relative;
}

.card-image {
    position: relative;
    width: 100%;
    aspect-ratio: 4 / 3;
    overflow: hidden;
    background: var(--panel-2);
}

.card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    filter: saturate(0.95) contrast(1.02);
}

.card-image-fallback {
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--panel-2), var(--panel));
}

.card-image-fallback span {
    font-family: var(--font-display);
    font-weight: 800;
    font-size: clamp(2rem, 6vw, 3.5rem);
    color: var(--accent);
    opacity: 0.85;
    text-transform: uppercase;
}

.card-body {
    padding: 1rem 1rem 1.1rem;
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
    flex: 1;
}

.card-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.75rem;
}

.card-name {
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 0.92rem;
    line-height: 1.25;
    margin: 0;
    letter-spacing: -0.01em;
    color: var(--ink);
    flex: 1;
}

.card-price {
    font-family: var(--font-display);
    font-weight: 800;
    font-size: 1.15rem;
    line-height: 0.95;
    letter-spacing: -0.025em;
    color: var(--accent);
    font-variant-numeric: tabular-nums;
    white-space: nowrap;
    flex-shrink: 0;
}

.card-meta-top {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.3rem;
}

.card-kcal {
    font-size: 0.62rem;
    font-weight: 600;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    padding: 0.15rem 0.4rem;
    border-radius: 4px;
    background: var(--panel-2);
    color: var(--ink-soft);
    font-variant-numeric: tabular-nums;
}

.card-tag {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 1.2rem;
    height: 1.2rem;
    padding: 0 0.35rem;
    border-radius: 4px;
    font-size: 0.65rem;
    font-weight: 700;
    color: oklch(0.12 0.01 270);
    background: var(--accent);
}

.card-desc {
    margin: 0;
    font-size: 0.78rem;
    line-height: 1.5;
    color: var(--ink-soft);
}

.ingredients-wrap { margin-top: 0.2rem; }

.ingredients-toggle {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0;
    background: transparent;
    border: none;
    cursor: pointer;
    font-family: var(--font-body);
    font-size: 0.68rem;
    font-weight: 600;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--ink-soft);
    transition: color 200ms;
}

.ingredients-toggle:hover,
.ingredients-toggle:focus-visible {
    color: var(--accent);
    outline: none;
}

.ingredients-toggle svg {
    transition: transform 240ms cubic-bezier(.2,.7,.2,1);
    opacity: 0.7;
}

.ingredients-toggle svg.is-open { transform: rotate(180deg); }

.ingredients-list {
    margin: 0.5rem 0 0;
    padding: 0.6rem 0.8rem;
    font-size: 0.78rem;
    line-height: 1.55;
    color: var(--ink);
    background: var(--panel-2);
    border-left: 2px solid var(--accent);
    border-radius: 0 6px 6px 0;
}

.ingredients-list-static {
    margin: 0.3rem 0 0;
    font-size: 0.72rem;
    line-height: 1.5;
    color: var(--ink-faint);
    font-style: italic;
}

.card-allergens {
    display: flex;
    gap: 0.2rem;
    flex-wrap: wrap;
    margin-top: auto;
    padding-top: 0.4rem;
    border-top: 1px solid var(--rule);
    opacity: 0.8;
}

.allergen-badge {
    font-size: 0.58rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: var(--ink-soft);
    background: var(--panel-2);
    padding: 0.1rem 0.28rem;
    border-radius: 3px;
    border: 1px solid var(--rule);
}

.foot {
    max-width: 1100px;
    margin: 0 auto;
    padding: clamp(2rem, 5vw, 4rem) clamp(1.25rem, 5vw, 3.5rem) clamp(1.5rem, 4vw, 3rem);
    text-align: center;
}

.foot-rule {
    width: 40px;
    height: 2px;
    background: var(--accent);
    margin: 0 auto 1.5rem;
    box-shadow: 0 0 14px var(--accent-glow);
}

.foot-name {
    font-family: var(--font-display);
    font-weight: 700;
    font-size: 1.2rem;
    letter-spacing: -0.01em;
    color: var(--ink);
    margin: 0 0 0.6rem;
    text-transform: uppercase;
}

.foot-info p {
    margin: 0.15rem 0;
    font-size: 0.78rem;
    color: var(--ink-soft);
}

.foot-branding {
    margin-top: 2rem;
    font-size: 0.65rem;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--ink-faint);
}

.foot-branding a {
    color: inherit;
    text-decoration: none;
    transition: color 200ms;
}

.foot-branding a:hover { color: var(--accent); }
</style>
