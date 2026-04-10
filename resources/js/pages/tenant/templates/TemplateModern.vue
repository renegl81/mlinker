<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import MenuSeoHead from '@/components/public/MenuSeoHead.vue';
import MenuLanguageSwitcher from '@/components/public/MenuLanguageSwitcher.vue';
import ShareMenu from '@/components/public/ShareMenu.vue';
import AllergenIcon from '@/components/ui/AllergenIcon.vue';
import { useMenuFormatter } from '@/composables/useMenuFormatter';
import type { Menu } from '@/types';
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';

interface MetaProps {
    title: string;
    description: string;
    image: string | null;
    url: string;
}

interface JsonLd {
    '@context': string;
    '@type': string;
    [key: string]: unknown;
}

interface LocaleMeta {
    native: string;
    flag: string;
}

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
}

const props = defineProps<Props>();
const { formatPrice, tagsFor, productImage } = useMenuFormatter(props.menu);
const { t } = useI18n();

const sections = computed(() => {
    if (!props.menu.sections) return [];
    return props.menu.sections.map((section, index) => ({
        ...section,
        products: section.products || [],
        anchor: `section-${section.id}`,
        number: String(index + 1).padStart(2, '0'),
    }));
});

const activeAnchor = ref<string | null>(null);
const expandedProducts = ref<Set<number>>(new Set());

function toggleIngredients(productId: number) {
    const next = new Set(expandedProducts.value);
    if (next.has(productId)) next.delete(productId);
    else next.add(productId);
    expandedProducts.value = next;
}

function scrollToSection(anchor: string) {
    const el = document.getElementById(anchor);
    if (!el) return;
    const top = el.getBoundingClientRect().top + window.scrollY - 90;
    window.scrollTo({ top, behavior: 'smooth' });
}

onMounted(() => {
    if (!('IntersectionObserver' in window)) return;
    const observer = new IntersectionObserver(
        (entries) => {
            for (const entry of entries) {
                if (entry.isIntersecting) activeAnchor.value = entry.target.id;
            }
        },
        { rootMargin: '-35% 0px -55% 0px', threshold: 0 },
    );
    sections.value.forEach((s) => {
        const el = document.getElementById(s.anchor);
        if (el) observer.observe(el);
    });
});
</script>

<template>
    <MenuSeoHead :meta="meta" :jsonLd="jsonLd" />
    <Head>
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
        <link
            href="https://fonts.googleapis.com/css2?family=Manrope:wght@300;400;500;600;700&family=Syne:wght@500;700;800&display=swap"
            rel="stylesheet"
        />
    </Head>

    <div class="menu-modern">
        <!-- Top bar -->
        <div class="topbar">
            <div class="topbar-brand">
                <span class="brand-dot" />
                <span class="brand-text">{{ menu.location?.name }}</span>
            </div>
            <MenuLanguageSwitcher
                v-if="hasMultilang && availableLocales.length > 1"
                :current="locale"
                :available="availableLocales"
                :locales-meta="supportedLocales"
            />
        </div>

        <!-- HERO -->
        <header class="hero">
            <div
                v-if="menu.image_path"
                class="hero-image"
                :style="{ backgroundImage: `url(${menu.image_path})` }"
            />
            <div class="hero-overlay" />
            <div class="hero-content">
                <div class="hero-kicker">
                    <span class="hero-dash" />
                    <span>{{ t('public_menu.language') }} · {{ locale.toUpperCase() }}</span>
                </div>
                <h1 class="hero-title">{{ menu.name }}</h1>
                <p v-if="menu.description" class="hero-sub">{{ menu.description }}</p>
            </div>
        </header>

        <!-- Section nav -->
        <nav v-if="sections.length > 1" class="section-nav" aria-label="Secciones">
            <div class="section-nav-inner">
                <button
                    v-for="s in sections"
                    :key="s.id"
                    type="button"
                    class="chip"
                    :class="{ 'is-active': activeAnchor === s.anchor }"
                    @click="scrollToSection(s.anchor)"
                >
                    <span class="chip-num">{{ s.number }}</span>
                    <span class="chip-name">{{ s.name }}</span>
                </button>
            </div>
        </nav>

        <!-- Main -->
        <main class="main">
            <div v-if="sections.length === 0" class="empty">
                {{ t('public_menu.empty') }}
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
                        <p v-if="section.description" class="section-desc">{{ section.description }}</p>
                    </div>
                </div>

                <ul v-if="section.products.length > 0" class="cards">
                    <li
                        v-for="(product, pIdx) in section.products"
                        :key="product.id"
                        class="card"
                        :style="{ '--j': pIdx } as Record<string, number>"
                    >
                        <div v-if="productImage(product)" class="card-image">
                            <img :src="productImage(product)!" :alt="product.name" loading="lazy" />
                        </div>
                        <div v-else class="card-image card-image-fallback">
                            <span>{{ product.name.charAt(0).toUpperCase() }}</span>
                        </div>

                        <div class="card-body">
                            <div class="card-top">
                                <h3 class="card-name">{{ product.name }}</h3>
                                <span
                                    v-if="menu.show_prices && product.price"
                                    class="card-price"
                                >{{ formatPrice(product.price) }}</span>
                            </div>

                            <div class="card-meta-top">
                                <span
                                    v-if="menu.show_calories && product.calories"
                                    class="card-kcal"
                                >{{ product.calories }} {{ t('public_menu.kcal') }}</span>
                                <span
                                    v-for="tag in tagsFor(product.tags)"
                                    :key="tag.code"
                                    class="card-tag"
                                    :title="t(`public_menu.tags.${tag.code}`)"
                                >{{ tag.glyph }}</span>
                            </div>

                            <p v-if="product.description" class="card-desc">
                                {{ product.description }}
                            </p>

                            <div
                                v-if="product.ingredients && product.ingredients.length > 0"
                                class="ingredients-wrap"
                            >
                                <button
                                    type="button"
                                    class="ingredients-toggle"
                                    :aria-expanded="expandedProducts.has(product.id)"
                                    @click="toggleIngredients(product.id)"
                                >
                                    <span>
                                        {{ expandedProducts.has(product.id) ? t('public_menu.hide_ingredients') : t('public_menu.show_ingredients') }}
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
                            </div>

                            <div
                                v-if="product.allergens && product.allergens.length > 0"
                                class="card-allergens"
                            >
                                <AllergenIcon
                                    v-for="allergen in product.allergens"
                                    :key="allergen.id"
                                    :code="allergen.code"
                                    size="sm"
                                    :title="allergen.name"
                                />
                            </div>
                        </div>
                    </li>
                </ul>

                <p v-else class="section-empty">{{ t('public_menu.section_empty') }}</p>
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
            <div v-if="showBranding" class="foot-branding">
                <a
                    :href="`https://menulinker.com?ref=${tenantSlug}`"
                    target="_blank"
                    rel="noopener"
                >{{ t('public_menu.branding') }}</a>
            </div>
        </footer>

        <ShareMenu
            :url="shareUrl"
            :menu-name="menu.name"
            :location-name="menu.location?.name ?? ''"
        />
    </div>
</template>

<style scoped>
.menu-modern {
    --bg: oklch(0.135 0.012 270);
    --panel: oklch(0.175 0.014 270);
    --panel-2: oklch(0.22 0.016 270);
    --ink: oklch(0.97 0.004 270);
    --ink-soft: oklch(0.72 0.008 270);
    --ink-faint: oklch(0.52 0.01 270);
    --rule: oklch(0.28 0.014 270);
    --accent: oklch(0.89 0.18 100);
    --accent-glow: oklch(0.89 0.18 100 / 0.3);
    --menu-paper: var(--panel);
    --menu-ink: var(--ink);
    --menu-rule: var(--rule);
    --menu-accent: var(--accent);

    --font-display: 'Syne', ui-sans-serif, system-ui, sans-serif;
    --font-body: 'Manrope', ui-sans-serif, system-ui, sans-serif;

    min-height: 100vh;
    background: var(--bg);
    color: var(--ink);
    font-family: var(--font-body);
    -webkit-font-smoothing: antialiased;
    overflow-x: hidden;
}

/* ============ TOPBAR ============ */
.topbar {
    position: sticky;
    top: 0;
    z-index: 30;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 0.9rem clamp(1rem, 4vw, 2rem);
    background: color-mix(in oklch, var(--bg) 80%, transparent);
    backdrop-filter: blur(16px);
    border-bottom: 1px solid var(--rule);
}

.topbar-brand {
    display: flex;
    align-items: center;
    gap: 0.55rem;
    font-size: 0.72rem;
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

/* ============ HERO ============ */
.hero {
    position: relative;
    min-height: clamp(440px, 68svh, 640px);
    display: flex;
    align-items: flex-end;
    padding: clamp(2rem, 6vw, 4rem) clamp(1.25rem, 5vw, 3.5rem) clamp(2.5rem, 7vw, 5rem);
    overflow: hidden;
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
    background:
        linear-gradient(180deg, oklch(0 0 0 / 0.3) 0%, oklch(0 0 0 / 0.2) 40%, var(--bg) 100%),
        radial-gradient(ellipse at 30% 30%, transparent 0%, oklch(0 0 0 / 0.4) 100%);
    pointer-events: none;
}

.hero-content {
    position: relative;
    z-index: 2;
    max-width: 1100px;
    width: 100%;
    margin: 0 auto;
    animation: hero-in 900ms cubic-bezier(.15,.7,.2,1) both;
}

.hero-kicker {
    display: flex;
    align-items: center;
    gap: 0.9rem;
    font-size: 0.7rem;
    font-weight: 600;
    letter-spacing: 0.28em;
    text-transform: uppercase;
    color: var(--accent);
    margin-bottom: 1.2rem;
}

.hero-dash {
    display: inline-block;
    width: 36px;
    height: 2px;
    background: var(--accent);
    box-shadow: 0 0 12px var(--accent-glow);
}

.hero-title {
    font-family: var(--font-display);
    font-weight: 800;
    font-size: clamp(2.75rem, 10vw, 6.5rem);
    line-height: 0.92;
    letter-spacing: -0.035em;
    margin: 0;
    text-transform: uppercase;
}

.hero-sub {
    margin-top: 1.25rem;
    max-width: 56ch;
    font-size: clamp(0.95rem, 1.6vw, 1.08rem);
    line-height: 1.55;
    color: var(--ink-soft);
    font-weight: 400;
}

/* ============ SECTION NAV ============ */
.section-nav {
    position: sticky;
    top: 54px;
    z-index: 25;
    background: color-mix(in oklch, var(--bg) 86%, transparent);
    backdrop-filter: blur(14px);
    border-bottom: 1px solid var(--rule);
}

.section-nav-inner {
    display: flex;
    gap: 0.5rem;
    overflow-x: auto;
    padding: 0.75rem clamp(1rem, 5vw, 3.5rem);
    scroll-snap-type: x proximity;
    scrollbar-width: none;
}

.section-nav-inner::-webkit-scrollbar { display: none; }

.chip {
    flex: none;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0.9rem;
    border-radius: 999px;
    border: 1px solid var(--rule);
    background: transparent;
    color: var(--ink-soft);
    font-family: var(--font-body);
    font-size: 0.74rem;
    font-weight: 500;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    cursor: pointer;
    scroll-snap-align: start;
    transition: all 200ms;
    white-space: nowrap;
}

.chip:hover {
    color: var(--ink);
    border-color: var(--accent);
}

.chip:focus-visible {
    outline: 2px solid var(--accent);
    outline-offset: 2px;
}

.chip.is-active {
    background: var(--accent);
    color: oklch(0.12 0.01 270);
    border-color: var(--accent);
}

.chip-num {
    font-family: var(--font-display);
    font-weight: 700;
    font-size: 0.68rem;
    opacity: 0.65;
}

.chip.is-active .chip-num { opacity: 0.85; }
.chip-name { font-weight: 600; }

/* ============ MAIN ============ */
.main {
    max-width: 1100px;
    margin: 0 auto;
    padding: clamp(3rem, 8vw, 6rem) clamp(1.25rem, 5vw, 3.5rem) 0;
}

.empty {
    text-align: center;
    padding: 6rem 0;
    color: var(--ink-faint);
    font-family: var(--font-display);
    font-size: 1rem;
    font-weight: 500;
}

.section {
    margin-bottom: clamp(4rem, 9vw, 6rem);
    animation: fade-up 700ms cubic-bezier(.15,.7,.2,1) both;
    animation-delay: calc(var(--i, 0) * 80ms + 120ms);
}

.section-head {
    display: flex;
    align-items: flex-start;
    gap: clamp(1rem, 3vw, 2rem);
    margin-bottom: clamp(1.75rem, 5vw, 3rem);
    padding-bottom: 1.5rem;
    border-bottom: 1px solid var(--rule);
}

.section-num {
    font-family: var(--font-display);
    font-weight: 800;
    font-size: clamp(2rem, 5vw, 3rem);
    line-height: 0.9;
    color: var(--accent);
    letter-spacing: -0.02em;
    padding-top: 0.2rem;
}

.section-meta { flex: 1; min-width: 0; }

.section-title {
    font-family: var(--font-display);
    font-weight: 700;
    font-size: clamp(1.75rem, 5vw, 2.75rem);
    line-height: 1;
    letter-spacing: -0.02em;
    text-transform: uppercase;
    margin: 0;
}

.section-desc {
    margin-top: 0.7rem;
    font-size: 0.92rem;
    color: var(--ink-soft);
    line-height: 1.55;
    max-width: 62ch;
}

.section-empty {
    text-align: center;
    padding: 2rem 0;
    color: var(--ink-faint);
    font-style: italic;
    font-size: 0.92rem;
}

/* ============ CARDS ============ */
.cards {
    list-style: none;
    margin: 0;
    padding: 0;
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.25rem;
}

@media (min-width: 700px) {
    .cards {
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
    }
}

.card {
    display: flex;
    flex-direction: column;
    background: var(--panel);
    border: 1px solid var(--rule);
    border-radius: 16px;
    overflow: hidden;
    position: relative;
    transition: transform 380ms cubic-bezier(.2,.7,.2,1), border-color 380ms, box-shadow 380ms;
    animation: card-in 700ms cubic-bezier(.15,.7,.2,1) both;
    animation-delay: calc(min(var(--j, 0), 8) * 50ms + 200ms);
}

.card::before {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: inherit;
    padding: 1px;
    background: linear-gradient(180deg, color-mix(in oklch, var(--accent) 22%, transparent), transparent 40%);
    -webkit-mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
    mask: linear-gradient(#000 0 0) content-box, linear-gradient(#000 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
    pointer-events: none;
    opacity: 0;
    transition: opacity 380ms;
}

.card:hover {
    transform: translateY(-4px);
    border-color: color-mix(in oklch, var(--accent) 50%, var(--rule));
    box-shadow:
        0 24px 60px -24px oklch(0 0 0 / 0.55),
        0 0 0 1px color-mix(in oklch, var(--accent) 20%, transparent);
}

.card:hover::before { opacity: 1; }

.card-image {
    position: relative;
    width: 100%;
    aspect-ratio: 4 / 3;
    overflow: hidden;
    background: var(--panel-2);
}

.card-image::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, transparent 50%, oklch(0 0 0 / 0.25) 100%);
    pointer-events: none;
}

.card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    filter: saturate(0.95) contrast(1.02);
    transition: transform 600ms cubic-bezier(.2,.7,.2,1);
}

.card:hover .card-image img { transform: scale(1.04); }

.card-image-fallback {
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--panel-2), var(--panel));
}

.card-image-fallback span {
    font-family: var(--font-display);
    font-weight: 800;
    font-size: clamp(3rem, 8vw, 5rem);
    color: var(--accent);
    opacity: 0.85;
    text-transform: uppercase;
}

.card-body {
    padding: 1.5rem 1.5rem 1.6rem;
    display: flex;
    flex-direction: column;
    gap: 0.7rem;
    flex: 1;
}

.card-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
}

.card-name {
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 1.12rem;
    line-height: 1.25;
    margin: 0;
    letter-spacing: -0.015em;
    color: var(--ink);
    flex: 1;
}

.card-price {
    font-family: var(--font-display);
    font-weight: 800;
    font-size: 1.55rem;
    line-height: 0.95;
    letter-spacing: -0.025em;
    color: var(--accent);
    font-variant-numeric: tabular-nums;
    white-space: nowrap;
    flex-shrink: 0;
    text-shadow: 0 0 30px color-mix(in oklch, var(--accent) 40%, transparent);
}

.card-meta-top {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.4rem;
}

.card-kcal {
    font-size: 0.68rem;
    font-weight: 600;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    padding: 0.2rem 0.5rem;
    border-radius: 4px;
    background: var(--panel-2);
    color: var(--ink-soft);
    font-variant-numeric: tabular-nums;
}

.card-tag {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 1.35rem;
    height: 1.35rem;
    padding: 0 0.45rem;
    border-radius: 4px;
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.02em;
    text-transform: lowercase;
    color: oklch(0.12 0.01 270);
    background: var(--accent);
}

.card-desc {
    margin: 0;
    font-size: 0.88rem;
    line-height: 1.55;
    color: var(--ink-soft);
}

.card-allergens {
    display: flex;
    gap: 0.1rem;
    flex-wrap: wrap;
    margin-top: auto;
    padding-top: 0.5rem;
    border-top: 1px solid var(--rule);
    opacity: 0.8;
    font-size: 0.85em;
}

/* ============ INGREDIENTS ============ */
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
    font-size: 0.72rem;
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
    margin: 0.6rem 0 0;
    padding: 0.7rem 0.9rem;
    font-size: 0.83rem;
    line-height: 1.55;
    color: var(--ink);
    background: var(--panel-2);
    border-left: 2px solid var(--accent);
    border-radius: 0 6px 6px 0;
    animation: reveal 300ms cubic-bezier(.2,.7,.2,1);
}

/* ============ FOOTER ============ */
.foot {
    max-width: 1100px;
    margin: 0 auto;
    padding: clamp(4rem, 8vw, 6rem) clamp(1.25rem, 5vw, 3.5rem) clamp(3rem, 6vw, 4rem);
    text-align: center;
}

.foot-rule {
    width: 40px;
    height: 2px;
    background: var(--accent);
    margin: 0 auto 2rem;
    box-shadow: 0 0 14px var(--accent-glow);
}

.foot-name {
    font-family: var(--font-display);
    font-weight: 700;
    font-size: 1.4rem;
    letter-spacing: -0.01em;
    color: var(--ink);
    margin: 0 0 1rem;
    text-transform: uppercase;
}

.foot-info p {
    margin: 0.2rem 0;
    font-size: 0.82rem;
    color: var(--ink-soft);
}

.foot-branding {
    margin-top: 2.5rem;
    font-size: 0.68rem;
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

/* ============ ANIMATIONS ============ */
@keyframes hero-in {
    from { opacity: 0; transform: translateY(24px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes fade-up {
    from { opacity: 0; transform: translateY(18px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes card-in {
    from { opacity: 0; transform: translateY(20px) scale(0.98); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

@keyframes reveal {
    from { opacity: 0; transform: translateY(-4px); }
    to { opacity: 1; transform: translateY(0); }
}

@media (prefers-reduced-motion: reduce) {
    .hero-content, .section, .card, .ingredients-list {
        animation: none !important;
    }
}
</style>
