<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import MenuSeoHead from '@/components/public/MenuSeoHead.vue';
import MenuLanguageSwitcher from '@/components/public/MenuLanguageSwitcher.vue';
import ShareMenu from '@/components/public/ShareMenu.vue';
import AllergenIcon from '@/components/ui/AllergenIcon.vue';
import { useMenuFormatter } from '@/composables/useMenuFormatter';
import { useMenuCustomization } from '@/composables/useMenuCustomization';
import type { Menu } from '@/types';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

interface MetaProps { title: string; description: string; image: string | null; url: string }
interface JsonLd { '@context': string; '@type': string; [key: string]: unknown }
interface LocaleMeta { native: string; flag: string }

interface Props {
    menu: Menu;
    showBranding: boolean;
    tenantSlug: string;
    meta: MetaProps;
    jsonLd: JsonLd;
    shareUrl: string;
    locale: string;
    hasMultilang: boolean;
    availableLocales: string[];
    supportedLocales: Record<string, LocaleMeta>;
    customization?: Record<string, any> | null;
}

const props = defineProps<Props>();
const { formatPrice, tagsFor, productImage } = useMenuFormatter(props.menu);
const { cssVars, fontLinks, layout } = useMenuCustomization(props.customization ?? null);
const { t } = useI18n();

const sections = computed(() => {
    if (!props.menu.sections) return [];
    return props.menu.sections.map((section) => ({
        ...section,
        products: section.products || [],
    }));
});

const expandedProducts = ref<Set<number>>(new Set());

function toggleIngredients(productId: number) {
    const next = new Set(expandedProducts.value);
    if (next.has(productId)) next.delete(productId);
    else next.add(productId);
    expandedProducts.value = next;
}
</script>

<template>
    <MenuSeoHead :meta="meta" :jsonLd="jsonLd" />
    <Head>
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
        <link
            href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@0,400;0,500;0,600;1,400;1,500&family=Nunito:wght@300;400;500;600&display=swap"
            rel="stylesheet"
        />
        <link v-for="url in fontLinks" :key="url" rel="stylesheet" :href="url" />
    </Head>

    <div class="menu-botanica" :style="cssVars">
        <!-- Decorative leaves -->
        <svg class="leaf leaf-tl" viewBox="0 0 100 100" aria-hidden="true">
            <path d="M50 5 C70 20 90 40 95 60 C80 55 60 50 45 40 C35 30 40 15 50 5 Z" fill="currentColor" opacity="0.12" />
            <path d="M50 5 Q50 45 60 70" stroke="currentColor" stroke-width="0.8" fill="none" opacity="0.3" />
        </svg>
        <svg class="leaf leaf-br" viewBox="0 0 100 100" aria-hidden="true">
            <path d="M50 95 C30 80 10 60 5 40 C20 45 40 50 55 60 C65 70 60 85 50 95 Z" fill="currentColor" opacity="0.12" />
            <path d="M50 95 Q50 55 40 30" stroke="currentColor" stroke-width="0.8" fill="none" opacity="0.3" />
        </svg>

        <div
            v-if="hasMultilang && availableLocales.length > 1"
            class="topbar"
        >
            <MenuLanguageSwitcher
                :current="locale"
                :available="availableLocales"
                :locales-meta="supportedLocales"
            />
        </div>

        <header class="hero">
            <p class="hero-kicker">
                <span class="leaf-glyph">❊</span>
                {{ menu.location?.name }}
                <span class="leaf-glyph">❊</span>
            </p>
            <h1 class="hero-title">{{ menu.name }}</h1>
            <p v-if="menu.description" class="hero-sub">{{ menu.description }}</p>
            <div
                v-if="menu.image_path"
                class="hero-image"
                :style="{ backgroundImage: `url(${menu.image_path})` }"
            />
        </header>

        <main class="main">
            <div v-if="sections.length === 0" class="empty">
                {{ t('public_menu.empty') }}
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
                    <p v-if="layout.showSectionDescriptions && section.description" class="section-desc">{{ section.description }}</p>
                </header>

                <ul v-if="section.products.length > 0" class="dishes">
                    <li
                        v-for="(product, pIdx) in section.products"
                        :key="product.id"
                        class="dish"
                        :style="{ '--j': pIdx } as Record<string, number>"
                    >
                        <img
                            v-if="layout.showProductImages && productImage(product)"
                            :src="productImage(product)!"
                            :alt="product.name"
                            class="dish-image"
                            loading="lazy"
                        />

                        <div class="dish-body">
                            <div class="dish-head">
                                <h3 class="dish-name">{{ product.name }}</h3>
                                <span
                                    v-if="menu.show_prices && product.price"
                                    class="dish-price"
                                >{{ formatPrice(product.price) }}</span>
                            </div>

                            <p v-if="product.description" class="dish-desc">
                                {{ product.description }}
                            </p>

                            <div
                                v-if="layout.showIngredients && product.ingredients && product.ingredients.length > 0"
                                class="dish-ingredients-inline"
                            >
                                <span class="ing-label">de la huerta:</span>
                                <span class="ing-names">
                                    {{ product.ingredients.slice(0, 4).map((i) => i.name).join(' · ') }}
                                    <button
                                        v-if="product.ingredients.length > 4"
                                        type="button"
                                        class="ing-more"
                                        @click="toggleIngredients(product.id)"
                                    >
                                        {{ expandedProducts.has(product.id) ? '− menos' : `+ ${product.ingredients.length - 4} más` }}
                                    </button>
                                </span>
                                <span v-show="expandedProducts.has(product.id)" class="ing-extra">
                                    · {{ product.ingredients.slice(4).map((i) => i.name).join(' · ') }}
                                </span>
                            </div>

                            <div class="dish-meta">
                                <span
                                    v-if="menu.show_calories && product.calories"
                                    class="dish-kcal"
                                >{{ product.calories }} {{ t('public_menu.kcal') }}</span>
                                <span
                                    v-for="tag in tagsFor(product.tags)"
                                    :key="tag.code"
                                    class="dish-tag"
                                    :title="t(`public_menu.tags.${tag.code}`)"
                                >{{ tag.glyph }}</span>
                                <div v-if="layout.showAllergens && product.allergens && product.allergens.length > 0" class="dish-allergens">
                                    <AllergenIcon
                                        v-for="allergen in product.allergens"
                                        :key="allergen.id"
                                        :code="allergen.code"
                                        size="sm"
                                        :title="allergen.name"
                                    />
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>

                <p v-else class="section-empty">{{ t('public_menu.section_empty') }}</p>
            </section>
        </main>

        <footer class="foot">
            <div class="foot-glyphs" aria-hidden="true">❊ ❊ ❊</div>
            <p class="foot-name">{{ menu.location?.name }}</p>
            <p v-if="menu.location?.address" class="foot-line">{{ menu.location.address }}</p>
            <p v-if="menu.location?.phone" class="foot-line">{{ menu.location.phone }}</p>
            <div v-if="showBranding" class="foot-branding">
                <a :href="`https://menulinker.com?ref=${tenantSlug}`" target="_blank" rel="noopener">
                    {{ t('public_menu.branding') }}
                </a>
            </div>
        </footer>

        <ShareMenu :url="shareUrl" :menu-name="menu.name" :location-name="menu.location?.name ?? ''" />
    </div>
</template>

<style scoped>
.menu-botanica {
    /* Canonical ML variables (overridable by customization) */
    --ml-bg: oklch(0.955 0.022 88);
    --ml-ink: oklch(0.22 0.015 120);
    --ml-ink-soft: oklch(0.44 0.015 120);
    --ml-accent: oklch(0.56 0.07 140);
    --ml-rule: oklch(0.82 0.03 120);
    --ml-font-display: 'Lora', Georgia, serif;
    --ml-font-body: 'Nunito', ui-sans-serif, system-ui, sans-serif;
    --ml-spacing: 1;

    /* Template internal variables now read from ML canonical */
    --bg: var(--ml-bg);
    --panel: oklch(0.98 0.015 88);
    --ink: var(--ml-ink);
    --ink-soft: var(--ml-ink-soft);
    --ink-faint: oklch(0.60 0.015 120);
    --rule: var(--ml-rule);
    --sage: var(--ml-accent);
    --sage-dark: oklch(0.42 0.07 140);
    --terracotta: oklch(0.62 0.13 45);
    --menu-paper: var(--panel);
    --menu-ink: var(--ink);
    --menu-rule: var(--rule);
    --menu-accent: var(--sage);

    --font-display: var(--ml-font-display);
    --font-body: var(--ml-font-body);

    position: relative;
    min-height: 100vh;
    background: var(--bg);
    color: var(--ink);
    font-family: var(--font-body);
    -webkit-font-smoothing: antialiased;
    overflow-x: hidden;
}

/* Decorative leaves */
.leaf {
    position: absolute;
    width: clamp(180px, 28vw, 320px);
    height: clamp(180px, 28vw, 320px);
    color: var(--sage);
    pointer-events: none;
    z-index: 1;
}

.leaf-tl {
    top: -40px;
    left: -40px;
    transform: rotate(-15deg);
}

.leaf-br {
    bottom: -40px;
    right: -40px;
    transform: rotate(15deg);
}

.topbar {
    position: absolute;
    top: clamp(1rem, 3vw, 1.5rem);
    right: clamp(1rem, 3vw, 1.5rem);
    z-index: 20;
}

/* ============ HERO ============ */
.hero {
    position: relative;
    z-index: 2;
    padding: clamp(5rem, 12vw, 8rem) clamp(1.5rem, 5vw, 3rem) clamp(3rem, 8vw, 5rem);
    max-width: 720px;
    margin: 0 auto;
    text-align: center;
    animation: fade-up 900ms ease-out both;
}

.hero-kicker {
    display: inline-flex;
    align-items: center;
    gap: 0.9rem;
    font-family: var(--font-body);
    font-weight: 500;
    font-size: 0.78rem;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: var(--sage);
    margin: 0 0 1.5rem;
}

.leaf-glyph {
    font-size: 0.8rem;
    opacity: 0.8;
}

.hero-title {
    font-family: var(--font-display);
    font-style: italic;
    font-weight: 500;
    font-size: clamp(2.8rem, 9vw, 5rem);
    line-height: 1;
    letter-spacing: -0.005em;
    margin: 0;
    color: var(--ink);
}

.hero-sub {
    margin: 1.3rem auto 0;
    max-width: 46ch;
    font-size: clamp(0.98rem, 1.8vw, 1.1rem);
    line-height: 1.65;
    color: var(--ink-soft);
    font-weight: 300;
}

.hero-image {
    margin-top: 2.5rem;
    width: 100%;
    aspect-ratio: 3 / 2;
    background-size: cover;
    background-position: center;
    border-radius: 200px 200px 8px 8px;
    filter: saturate(0.95);
    box-shadow: 0 30px 60px -30px oklch(0 0 0 / 0.25);
}

/* ============ MAIN ============ */
.main {
    position: relative;
    z-index: 2;
    max-width: 780px;
    margin: 0 auto;
    padding: 0 clamp(1.5rem, 5vw, 3rem);
}

.empty {
    text-align: center;
    padding: 4rem 0;
    color: var(--ink-faint);
    font-style: italic;
    font-family: var(--font-display);
}

.section {
    margin-bottom: clamp(4rem, 9vw, 6rem);
    animation: fade-up 800ms cubic-bezier(.2,.65,.2,1) both;
    animation-delay: calc(var(--i, 0) * 100ms + 150ms);
}

.section-head {
    text-align: center;
    margin-bottom: 2.5rem;
}

.section-glyph {
    display: inline-block;
    font-size: 1.5rem;
    color: var(--sage);
    margin-bottom: 0.7rem;
    opacity: 0.75;
}

.section-title {
    font-family: var(--font-display);
    font-style: italic;
    font-weight: 500;
    font-size: clamp(1.9rem, 5vw, 2.8rem);
    line-height: 1;
    letter-spacing: -0.005em;
    margin: 0;
    color: var(--ink);
}

.section-desc {
    max-width: 48ch;
    margin: 1rem auto 0;
    font-size: 0.95rem;
    line-height: 1.6;
    color: var(--ink-soft);
    font-weight: 300;
}

.section-empty {
    text-align: center;
    font-style: italic;
    color: var(--ink-faint);
    padding: 2rem 0;
}

/* ============ DISHES ============ */
.dishes {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 1.8rem;
}

.dish {
    display: flex;
    gap: 1.25rem;
    align-items: flex-start;
    padding: 1.5rem;
    background: var(--panel);
    border-radius: 16px;
    border: 1px solid color-mix(in oklch, var(--rule) 60%, transparent);
    animation: fade-up 650ms cubic-bezier(.2,.65,.2,1) both;
    animation-delay: calc(min(var(--j, 0), 8) * 50ms + 250ms);
    transition: box-shadow 300ms, transform 300ms;
}

.dish:hover {
    box-shadow: 0 20px 40px -20px oklch(0.4 0.05 140 / 0.25);
    transform: translateY(-2px);
}

.dish-image {
    flex: none;
    width: 90px;
    height: 90px;
    object-fit: cover;
    border-radius: 45px 45px 8px 45px;
    filter: saturate(0.95);
}

@media (min-width: 640px) {
    .dish-image { width: 108px; height: 108px; border-radius: 54px 54px 8px 54px; }
}

.dish-body { flex: 1; min-width: 0; }

.dish-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 0.4rem;
}

.dish-name {
    font-family: var(--font-display);
    font-weight: 500;
    font-size: 1.08rem;
    line-height: 1.35;
    margin: 0;
    color: var(--ink);
    flex: 1;
}

.dish-price {
    font-family: var(--font-display);
    font-weight: 500;
    font-style: italic;
    font-size: 1.08rem;
    color: var(--terracotta);
    font-variant-numeric: tabular-nums;
    white-space: nowrap;
    flex-shrink: 0;
}

.dish-desc {
    margin: 0;
    font-size: 0.88rem;
    line-height: 1.6;
    color: var(--ink-soft);
    font-weight: 300;
}

.dish-ingredients-inline {
    margin-top: 0.55rem;
    font-size: 0.8rem;
    line-height: 1.55;
    color: var(--sage-dark);
    font-weight: 400;
}

.ing-label {
    font-style: italic;
    font-family: var(--font-display);
    margin-right: 0.4rem;
}

.ing-more {
    background: transparent;
    border: none;
    padding: 0 0 0 0.3rem;
    color: var(--sage);
    cursor: pointer;
    font-family: inherit;
    font-size: inherit;
    font-style: italic;
    text-decoration: underline;
    text-decoration-style: dotted;
    text-underline-offset: 2px;
}

.ing-more:hover { color: var(--sage-dark); }

.ing-extra {
    display: inline;
    animation: fade-in 300ms ease-out;
}

.dish-meta {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.45rem 0.6rem;
    margin-top: 0.7rem;
}

.dish-kcal {
    font-size: 0.7rem;
    font-weight: 500;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: var(--ink-faint);
    padding: 0.15rem 0.5rem;
    background: color-mix(in oklch, var(--sage) 10%, transparent);
    border-radius: 999px;
}

.dish-tag {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 1.4rem;
    height: 1.4rem;
    padding: 0 0.45rem;
    border-radius: 999px;
    font-family: var(--font-display);
    font-style: italic;
    font-weight: 500;
    font-size: 0.72rem;
    color: var(--sage-dark);
    background: color-mix(in oklch, var(--sage) 14%, transparent);
}

.dish-allergens {
    display: flex;
    align-items: center;
    gap: 0.1rem;
    flex-wrap: wrap;
    padding-left: 0.4rem;
    margin-left: 0.15rem;
    border-left: 1px solid var(--rule);
    opacity: 0.8;
    font-size: 0.78em;
}

/* ============ FOOTER ============ */
.foot {
    position: relative;
    z-index: 2;
    max-width: 780px;
    margin: 0 auto;
    padding: clamp(4rem, 9vw, 6rem) clamp(1.5rem, 5vw, 3rem) clamp(3rem, 6vw, 4rem);
    text-align: center;
}

.foot-glyphs {
    color: var(--sage);
    font-size: 1.1rem;
    letter-spacing: 0.6em;
    margin-bottom: 1.5rem;
    opacity: 0.65;
}

.foot-name {
    font-family: var(--font-display);
    font-style: italic;
    font-weight: 500;
    font-size: 1.4rem;
    color: var(--ink);
    margin: 0 0 0.7rem;
}

.foot-line {
    margin: 0.2rem 0;
    font-size: 0.85rem;
    color: var(--ink-soft);
    font-weight: 300;
}

.foot-branding {
    margin-top: 2rem;
    font-size: 0.72rem;
    color: var(--ink-faint);
    font-weight: 400;
    letter-spacing: 0.04em;
}

.foot-branding a {
    color: inherit;
    text-decoration: none;
    border-bottom: 1px solid var(--sage);
    padding-bottom: 1px;
}

.foot-branding a:hover { color: var(--sage); }

@keyframes fade-up {
    from { opacity: 0; transform: translateY(14px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes fade-in {
    from { opacity: 0; }
    to { opacity: 1; }
}

@media (prefers-reduced-motion: reduce) {
    .hero, .section, .dish { animation: none !important; }
}
</style>
