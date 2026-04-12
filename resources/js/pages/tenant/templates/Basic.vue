<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import MenuSeoHead from '@/components/public/MenuSeoHead.vue';
import MenuLanguageSwitcher from '@/components/public/MenuLanguageSwitcher.vue';
import ShareMenu from '@/components/public/ShareMenu.vue';
import AllergenIcon from '@/components/ui/AllergenIcon.vue';
import AddToCartButton from '@/components/public/AddToCartButton.vue';
import CartFab from '@/components/public/CartFab.vue';
import CartDrawer from '@/components/public/CartDrawer.vue';
import { useMenuFormatter } from '@/composables/useMenuFormatter';
import { useMenuCustomization } from '@/composables/useMenuCustomization';
import { useCart } from '@/composables/useCart';
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
    hasCart: boolean;
    availableLocales: string[];
    supportedLocales: Record<string, LocaleMeta>;
    customization?: Record<string, any> | null;
}

const props = defineProps<Props>();
const { formatPrice, tagsFor, toRoman, productImage } = useMenuFormatter(props.menu);
const { cssVars, fontLinks, layout } = useMenuCustomization(props.customization ?? null);
const { t } = useI18n();

const cart = props.hasCart ? useCart(props.menu.id) : null;
const cartOpen = ref(false);

const sections = computed(() => {
    if (!props.menu.sections) return [];
    return props.menu.sections.map((section, index) => ({
        ...section,
        products: section.products || [],
        anchor: `section-${section.id}`,
        numeral: toRoman(index + 1),
    }));
});

const activeAnchor = ref<string | null>(null);
const expandedProducts = ref<Set<number>>(new Set());

function toggleIngredients(productId: number) {
    const next = new Set(expandedProducts.value);
    if (next.has(productId)) {
        next.delete(productId);
    } else {
        next.add(productId);
    }
    expandedProducts.value = next;
}

function scrollToSection(anchor: string) {
    const el = document.getElementById(anchor);
    if (!el) return;
    const top = el.getBoundingClientRect().top + window.scrollY - 88;
    window.scrollTo({ top, behavior: 'smooth' });
}

onMounted(() => {
    if (!('IntersectionObserver' in window)) return;
    const observer = new IntersectionObserver(
        (entries) => {
            for (const entry of entries) {
                if (entry.isIntersecting) {
                    activeAnchor.value = entry.target.id;
                }
            }
        },
        { rootMargin: '-35% 0px -55% 0px', threshold: 0 },
    );
    sections.value.forEach((s) => {
        const el = document.getElementById(s.anchor);
        if (el) observer.observe(el);
    });
});

function buildOrderText(): string {
    if (!cart) return '';
    const name = cart.customerName.value;
    const lines: string[] = [];
    if (name) lines.push(`📋 ${name}`);
    lines.push(`🍽️ ${props.menu.name}`);
    lines.push('');
    for (const item of cart.items.value) {
        lines.push(`${item.quantity}x ${item.name} — ${item.priceDisplay}`);
    }
    lines.push('');
    lines.push(`💰 Total: ${formatPrice(cart.totalPrice.value)}`);
    return lines.join('\n');
}

function sendViaWhatsApp() {
    const phone = props.menu.location?.order_whatsapp;
    if (!phone) return;
    const cleanPhone = phone.replace(/[^0-9+]/g, '');
    const text = encodeURIComponent(buildOrderText());
    window.open(`https://wa.me/${cleanPhone}?text=${text}`, '_blank');
}

function sendViaEmail() {
    const email = props.menu.location?.order_email;
    if (!email) return;
    const subject = encodeURIComponent(`${cart?.customerName.value ? cart.customerName.value + ' — ' : ''}${props.menu.name}`);
    const body = encodeURIComponent(buildOrderText());
    window.location.href = `mailto:${email}?subject=${subject}&body=${body}`;
}
</script>

<template>
    <MenuSeoHead :meta="meta" :jsonLd="jsonLd" />
    <Head>
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
        <link
            href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,500;1,9..144,300;1,9..144,500&display=swap"
            rel="stylesheet"
        />
        <link v-for="url in fontLinks" :key="url" rel="stylesheet" :href="url" />
    </Head>

    <div class="menu-editorial" :style="cssVars">
        <!-- Grain texture overlay -->
        <svg class="grain" aria-hidden="true">
            <filter id="menu-grain">
                <feTurbulence type="fractalNoise" baseFrequency="0.9" numOctaves="2" stitchTiles="stitch" />
                <feColorMatrix type="saturate" values="0" />
            </filter>
            <rect width="100%" height="100%" filter="url(#menu-grain)" />
        </svg>

        <!-- Top bar: language switcher (only if plan has multilang AND >1 locale available) -->
        <div v-if="hasMultilang && availableLocales.length > 1" class="topbar">
            <MenuLanguageSwitcher
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

        <!-- Nav sticky de secciones (solo si hay más de una) -->
        <nav v-if="sections.length > 1" class="section-nav">
            <div class="section-nav-inner">
                <button
                    v-for="s in sections"
                    :key="s.id"
                    type="button"
                    class="section-chip"
                    :class="{ 'is-active': activeAnchor === s.anchor }"
                    @click="scrollToSection(s.anchor)"
                >
                    {{ s.name }}
                </button>
            </div>
        </nav>

        <!-- Secciones -->
        <main class="main">
            <div v-if="sections.length === 0" class="empty">
                <p>{{ t('public_menu.empty') }}</p>
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
                        <p v-if="layout.showSectionDescriptions && section.description" class="section-desc">{{ section.description }}</p>
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
                                        · {{ product.calories }} {{ t('public_menu.kcal') }}
                                    </span>
                                </h3>
                                <template v-if="menu.show_prices && product.price">
                                    <span class="product-dots" aria-hidden="true" />
                                    <span class="product-price">{{ formatPrice(product.price) }}</span>
                                    <AddToCartButton
                                        v-if="hasCart"
                                        :quantity="cart?.getQuantity(product.id) ?? 0"
                                        @add="cart?.addItem(product, formatPrice(product.price))"
                                        @remove="cart?.removeItem(product.id)"
                                    />
                                </template>
                            </div>

                            <p v-if="product.description" class="product-desc">
                                {{ product.description }}
                            </p>

                            <div
                                v-if="layout.showIngredients && product.ingredients && product.ingredients.length > 0"
                                class="product-ingredients-wrap"
                            >
                                <button
                                    type="button"
                                    class="product-ingredients-toggle"
                                    :aria-expanded="expandedProducts.has(product.id)"
                                    :aria-controls="`ingredients-${product.id}`"
                                    @click="toggleIngredients(product.id)"
                                >
                                    <span class="tog-line" aria-hidden="true" />
                                    <span class="tog-label">
                                        {{ expandedProducts.has(product.id) ? t('public_menu.hide_ingredients') : t('public_menu.show_ingredients') }}
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
                            </div>

                            <div
                                v-if="tagsFor(product.tags).length > 0 || (layout.showAllergens && product.allergens && product.allergens.length > 0)"
                                class="product-meta"
                            >
                                <span
                                    v-for="tag in tagsFor(product.tags)"
                                    :key="tag.code"
                                    class="product-tag"
                                    :title="t(`public_menu.tags.${tag.code}`)"
                                >{{ tag.glyph }}</span>
                                <div v-if="layout.showAllergens && product.allergens && product.allergens.length > 0" class="product-allergens">
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

                        <img
                            v-if="layout.showProductImages && productImage(product)"
                            :src="productImage(product)!"
                            :alt="product.name"
                            class="product-image"
                            loading="lazy"
                        />
                    </li>
                </ul>

                <p v-else class="section-empty">{{ t('public_menu.section_empty') }}</p>
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
        <CartFab
            v-if="hasCart && menu.show_prices"
            :item-count="cart?.totalItems.value ?? 0"
            @click="cartOpen = true"
        />
        <CartDrawer
            v-if="hasCart && menu.show_prices"
            :open="cartOpen"
            :items="cart?.items.value ?? []"
            :total-price="cart?.totalPrice.value ?? 0"
            :format-price="(n) => formatPrice(n)"
            :customer-name="cart?.customerName.value ?? ''"
            :order-email="menu.location?.order_email ?? null"
            :order-whatsapp="menu.location?.order_whatsapp ?? null"
            @close="cartOpen = false"
            @increment="(id) => cart?.incrementItem(id)"
            @decrement="(id) => cart?.removeItem(id)"
            @delete="(id) => cart?.deleteItem(id)"
            @clear="cart?.clearCart()"
            @update:customer-name="(name) => { if (cart) cart.customerName.value = name; }"
            @send-whatsapp="sendViaWhatsApp"
            @send-email="sendViaEmail"
        />
    </div>
</template>

<style scoped>
.menu-editorial {
    /* Canonical ML variables (overridable by customization) */
    --ml-bg: oklch(0.972 0.015 82);
    --ml-ink: oklch(0.22 0.025 40);
    --ml-ink-soft: oklch(0.46 0.02 40);
    --ml-accent: oklch(0.56 0.15 35);
    --ml-rule: oklch(0.85 0.02 40);
    --ml-font-display: 'Fraunces', 'Instrument Serif', Georgia, serif;
    --ml-font-body: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
    --ml-spacing: 1;

    /* Template internal variables now read from ML canonical */
    --menu-bg: var(--ml-bg);
    --menu-paper: oklch(0.99 0.008 82);
    --menu-ink: var(--ml-ink);
    --menu-ink-soft: var(--ml-ink-soft);
    --menu-ink-faint: oklch(0.66 0.015 40);
    --menu-accent: var(--ml-accent);
    --menu-accent-soft: oklch(0.56 0.15 35 / 0.12);
    --menu-rule: var(--ml-rule);
    --menu-serif: var(--ml-font-display);
    --menu-sans: var(--ml-font-body);

    position: relative;
    min-height: 100vh;
    background: var(--menu-bg);
    color: var(--menu-ink);
    font-family: var(--menu-sans);
    font-feature-settings: 'ss01', 'cv01';
    -webkit-font-smoothing: antialiased;
    overflow-x: hidden;
}

.grain {
    position: fixed;
    inset: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    opacity: 0.11;
    mix-blend-mode: multiply;
    z-index: 1;
}

/* ============ TOP BAR ============ */
.topbar {
    position: absolute;
    top: clamp(0.9rem, 3vw, 1.4rem);
    right: clamp(0.9rem, 3vw, 1.4rem);
    z-index: 30;
}

/* ============ HERO ============ */
.hero {
    position: relative;
    z-index: 2;
    min-height: clamp(420px, 70svh, 620px);
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
    animation: fade-up 900ms cubic-bezier(.2,.65,.2,1) both;
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
    font-size: clamp(2.75rem, 11vw, 5.5rem);
    line-height: 0.95;
    letter-spacing: -0.02em;
    font-variation-settings: 'opsz' 144, 'SOFT' 50;
    margin: 0;
}

.hero-sub {
    margin-top: 1.25rem;
    font-size: clamp(0.95rem, 2.2vw, 1.1rem);
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
    margin-top: 1.75rem;
}

.hero-ornament .rule {
    height: 1px;
    width: clamp(40px, 12vw, 88px);
    background: currentColor;
    opacity: 0.4;
}

.hero-ornament .mark {
    font-family: var(--menu-serif);
    font-size: 0.85rem;
    opacity: 0.7;
}

/* Fallback (no hero image): ink on paper */
.hero:not(:has(.hero-image)) {
    background: var(--menu-bg);
    color: var(--menu-ink);
    min-height: auto;
    padding-top: clamp(3.5rem, 10vw, 6rem);
    padding-bottom: clamp(2rem, 6vw, 4rem);
}

.hero:not(:has(.hero-image)) .hero-inner {
    color: var(--menu-ink);
}

.hero:not(:has(.hero-image)) .hero-kicker {
    color: var(--menu-ink-soft);
}

/* ============ SECTION NAV ============ */
.section-nav {
    position: sticky;
    top: 0;
    z-index: 20;
    backdrop-filter: blur(16px) saturate(1.1);
    background: color-mix(in oklch, var(--menu-bg) 82%, transparent);
    border-bottom: 1px solid color-mix(in oklch, var(--menu-rule) 50%, transparent);
}

.section-nav-inner {
    display: flex;
    gap: 0.25rem;
    overflow-x: auto;
    padding: 0.75rem clamp(1rem, 4vw, 2rem);
    scroll-snap-type: x proximity;
    scrollbar-width: none;
}

.section-nav-inner::-webkit-scrollbar { display: none; }

.section-chip {
    flex: none;
    font-family: var(--menu-sans);
    font-size: 0.78rem;
    font-weight: 500;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    padding: 0.55rem 1rem;
    border-radius: 999px;
    color: var(--menu-ink-soft);
    background: transparent;
    border: none;
    cursor: pointer;
    scroll-snap-align: start;
    transition: color 200ms, background 200ms;
    white-space: nowrap;
}

.section-chip:hover { color: var(--menu-ink); }

.section-chip:focus-visible {
    outline: 2px solid var(--menu-accent);
    outline-offset: 2px;
    color: var(--menu-ink);
}

.section-chip.is-active {
    color: var(--menu-accent);
    background: var(--menu-accent-soft);
}

/* ============ MAIN ============ */
.main {
    position: relative;
    z-index: 2;
    max-width: 720px;
    margin: 0 auto;
    padding: clamp(2.5rem, 7vw, 5rem) clamp(1.25rem, 4vw, 2rem) 0;
}

.empty {
    text-align: center;
    padding: 4rem 0;
    color: var(--menu-ink-soft);
    font-style: italic;
    font-family: var(--menu-serif);
}

.section {
    margin-bottom: clamp(3rem, 8vw, 5rem);
    animation: fade-up 700ms cubic-bezier(.2,.65,.2,1) both;
    animation-delay: calc(var(--i, 0) * 80ms + 120ms);
}

.section-head {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    margin-bottom: 2.25rem;
    padding-bottom: 1.5rem;
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
    font-size: 1.4rem;
    line-height: 1;
    color: var(--menu-accent);
    font-variation-settings: 'opsz' 144;
    padding-top: 0.6rem;
    min-width: 2ch;
}

.section-title-wrap { flex: 1; }

.section-title {
    font-family: var(--menu-serif);
    font-style: italic;
    font-weight: 400;
    font-size: clamp(1.75rem, 5.5vw, 2.75rem);
    line-height: 1;
    letter-spacing: -0.015em;
    font-variation-settings: 'opsz' 144, 'SOFT' 30;
    margin: 0;
}

.section-desc {
    margin-top: 0.5rem;
    font-size: 0.88rem;
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
    gap: 1.75rem;
}

.product {
    display: flex;
    gap: 1rem;
    align-items: flex-start;
    animation: fade-up 600ms cubic-bezier(.2,.65,.2,1) both;
    /* Cap the stagger so long menus don't animate for seconds */
    animation-delay: calc(min(var(--j, 0), 10) * 35ms + 240ms);
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
    font-size: 1.02rem;
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
    font-size: 0.82rem;
    color: var(--menu-ink-faint);
    margin-left: 0.35rem;
}

.product-dots {
    flex: 1 1 auto;
    min-width: 1.5rem;
    height: 0;
    align-self: flex-end;
    margin-bottom: 0.32em;
    border-bottom: 1.5px dotted var(--menu-ink-faint);
    opacity: 0.55;
}

.product-price {
    font-family: var(--menu-serif);
    font-weight: 500;
    font-size: 1.08rem;
    color: var(--menu-accent);
    font-variant-numeric: tabular-nums;
    font-variation-settings: 'opsz' 144;
    white-space: nowrap;
    flex: none;
}

.product-desc {
    margin: 0.4rem 0 0;
    font-size: 0.85rem;
    line-height: 1.55;
    color: var(--menu-ink-soft);
    max-width: 56ch;
}

.product-ingredients-wrap {
    margin-top: 0.65rem;
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
    font-size: 0.78rem;
    color: var(--menu-ink-soft);
    letter-spacing: 0.01em;
    transition: color 200ms;
    font-variation-settings: 'opsz' 14;
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
    margin: 0.55rem 0 0;
    padding: 0.65rem 0.85rem 0.7rem;
    border-left: 2px solid var(--menu-accent);
    background: color-mix(in oklch, var(--menu-accent) 7%, transparent);
    border-radius: 0 6px 6px 0;
    font-family: var(--menu-serif);
    font-style: italic;
    font-weight: 300;
    font-size: 0.83rem;
    line-height: 1.55;
    color: var(--menu-ink);
    font-variation-settings: 'opsz' 14;
    animation: ingredients-reveal 320ms cubic-bezier(.2,.65,.2,1);
}

@keyframes ingredients-reveal {
    from {
        opacity: 0;
        transform: translateY(-4px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.product-meta {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.45rem 0.55rem;
    margin-top: 0.7rem;
}

.product-tag {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 1.35rem;
    height: 1.35rem;
    padding: 0 0.42rem;
    border-radius: 999px;
    font-family: var(--menu-serif);
    font-style: italic;
    font-weight: 500;
    font-size: 0.72rem;
    letter-spacing: 0.02em;
    color: var(--menu-accent);
    background: var(--menu-accent-soft);
    border: 1px solid color-mix(in oklch, var(--menu-accent) 18%, transparent);
    line-height: 1;
}

.product-allergens {
    display: flex;
    align-items: center;
    gap: 0.1rem;
    flex-wrap: wrap;
    padding-left: 0.4rem;
    margin-left: 0.15rem;
    border-left: 1px solid var(--menu-rule);
    opacity: 0.78;
    font-size: 0.78em;
}

.product-image {
    flex: none;
    width: 96px;
    height: 96px;
    object-fit: cover;
    border-radius: 10px;
    filter: saturate(0.95);
    box-shadow:
        0 1px 2px oklch(0 0 0 / 0.08),
        0 18px 36px -14px oklch(0 0 0 / 0.28);
    transition: transform 400ms cubic-bezier(.2,.65,.2,1);
}

.product:hover .product-image {
    transform: translateY(-2px) scale(1.02);
}

@media (min-width: 640px) {
    .product-image { width: 118px; height: 118px; }
}

/* ============ FOOT ============ */
.foot {
    position: relative;
    z-index: 2;
    max-width: 720px;
    margin: 0 auto;
    padding: clamp(4rem, 10vw, 6rem) clamp(1.25rem, 4vw, 2rem) clamp(3rem, 6vw, 4rem);
    text-align: center;
    color: var(--menu-ink-soft);
}

.foot-ornament {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.85rem;
    margin-bottom: 2rem;
    color: var(--menu-accent);
}

.foot-ornament .rule {
    height: 1px;
    width: 60px;
    background: currentColor;
    opacity: 0.4;
}

.foot-ornament .mark {
    font-family: var(--menu-serif);
    font-size: 0.85rem;
}

.foot-name {
    font-family: var(--menu-serif);
    font-style: italic;
    font-weight: 400;
    font-size: 1.4rem;
    color: var(--menu-ink);
    margin: 0 0 0.5rem;
    font-variation-settings: 'opsz' 144;
}

.foot-line {
    margin: 0.15rem 0;
    font-size: 0.82rem;
    letter-spacing: 0.01em;
}

.foot-branding {
    margin-top: 2rem;
    font-size: 0.72rem;
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

/* ============ ANIMATIONS ============ */
@keyframes fade-up {
    from {
        opacity: 0;
        transform: translateY(16px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (prefers-reduced-motion: reduce) {
    .hero-inner,
    .section,
    .product {
        animation: none !important;
    }
    * { scroll-behavior: auto !important; }
}
</style>
