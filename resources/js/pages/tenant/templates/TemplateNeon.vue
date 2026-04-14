<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import MenuSeoHead from '@/components/public/MenuSeoHead.vue';
import MenuLanguageSwitcher from '@/components/public/MenuLanguageSwitcher.vue';
import ShareMenu from '@/components/public/ShareMenu.vue';
import AllergenIcon from '@/components/ui/AllergenIcon.vue';
import AddToCartButton from '@/components/public/AddToCartButton.vue';
import CartFab from '@/components/public/CartFab.vue';
import CartOnboarding from '@/components/public/CartOnboarding.vue';
import CartDrawer from '@/components/public/CartDrawer.vue';
import { useMenuFormatter } from '@/composables/useMenuFormatter';
import { useMenuCustomization } from '@/composables/useMenuCustomization';
import { useCart } from '@/composables/useCart';
import type { Menu } from '@/types';
import { computed, onMounted, ref } from 'vue';
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
    hasCart: boolean;
    availableLocales: string[];
    supportedLocales: Record<string, LocaleMeta>;
    customization?: Record<string, any> | null;
}

const props = defineProps<Props>();
const { formatPrice, tagsFor, productImage } = useMenuFormatter(props.menu);
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
    const top = el.getBoundingClientRect().top + window.scrollY - 80;
    window.scrollTo({ top, behavior: 'smooth' });
}

onMounted(() => {
    if (!('IntersectionObserver' in window)) return;
    const observer = new IntersectionObserver(
        (entries) => {
            for (const entry of entries) if (entry.isIntersecting) activeAnchor.value = entry.target.id;
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
            href="https://fonts.googleapis.com/css2?family=Big+Shoulders+Display:wght@500;700;900&family=JetBrains+Mono:wght@400;500;700&family=Manrope:wght@300;400;500;600&display=swap"
            rel="stylesheet"
        />
        <link v-for="url in fontLinks" :key="url" rel="stylesheet" :href="url" />
    </Head>

    <div class="menu-neon" :style="cssVars">
        <div class="scanlines" aria-hidden="true" />

        <div class="topbar">
            <div class="brand">
                <span class="brand-bar" />
                <span class="brand-text">{{ menu.location?.name }}</span>
                <span class="brand-bar" />
            </div>
            <MenuLanguageSwitcher
                v-if="hasMultilang && availableLocales.length > 1"
                :current="locale"
                :available="availableLocales"
                :locales-meta="supportedLocales"
            />
        </div>

        <header class="hero">
            <div
                v-if="menu.image_path"
                class="hero-image"
                :style="{ backgroundImage: `url(${menu.image_path})` }"
            />
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

        <nav v-if="sections.length > 1" class="section-nav">
            <div class="section-nav-inner">
                <button
                    v-for="s in sections"
                    :key="s.id"
                    type="button"
                    class="chip"
                    :class="{ 'is-active': activeAnchor === s.anchor }"
                    @click="scrollToSection(s.anchor)"
                >
                    <span class="chip-num">/{{ s.number }}</span>
                    {{ s.name }}
                </button>
            </div>
        </nav>

        <main class="main">
            <div v-if="sections.length === 0" class="empty">
                &gt;_ NO_DISHES_FOUND
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
                    <p v-if="layout.showSectionDescriptions && section.description" class="section-desc">{{ section.description }}</p>
                </header>

                <ul v-if="section.products.length > 0" class="dishes">
                    <li
                        v-for="(product, pIdx) in section.products"
                        :key="product.id"
                        class="dish"
                        :style="{ '--j': pIdx } as Record<string, number>"
                    >
                        <div v-if="layout.showProductImages && productImage(product)" class="dish-image">
                            <img :src="productImage(product)!" :alt="product.name" loading="lazy" />
                            <div class="dish-scanlines" aria-hidden="true" />
                        </div>

                        <div class="dish-body">
                            <div class="dish-head">
                                <h3 class="dish-name">{{ product.name }}</h3>
                                <span
                                    v-if="menu.show_prices && product.price"
                                    class="dish-price"
                                >¥ {{ formatPrice(product.price).replace(/[^\d.,]/g, '') }}</span>
                                <AddToCartButton
                                    v-if="hasCart && menu.show_prices && product.price"
                                    :quantity="cart?.getQuantity(product.id) ?? 0"
                                    @add="cart?.addItem(product, formatPrice(product.price))"
                                    @remove="cart?.removeItem(product.id)"
                                />
                            </div>

                            <div class="dish-meta">
                                <span
                                    v-if="menu.show_calories && product.calories"
                                    class="dish-kcal"
                                >{{ product.calories }}{{ t('public_menu.kcal') }}</span>
                                <span
                                    v-for="tag in tagsFor(product.tags)"
                                    :key="tag.code"
                                    class="dish-tag"
                                    :title="t(`public_menu.tags.${tag.code}`)"
                                >[{{ tag.glyph }}]</span>
                            </div>

                            <p v-if="product.description" class="dish-desc">
                                {{ product.description }}
                            </p>

                            <div
                                v-if="layout.showIngredients && product.ingredients && product.ingredients.length > 0"
                                class="dish-ingredients"
                            >
                                <button
                                    type="button"
                                    class="ing-toggle"
                                    :aria-expanded="expandedProducts.has(product.id)"
                                    @click="toggleIngredients(product.id)"
                                >
                                    &gt; {{ expandedProducts.has(product.id) ? t('public_menu.hide_ingredients') : t('public_menu.show_ingredients') }}
                                </button>
                                <p v-show="expandedProducts.has(product.id)" class="ing-list">
                                    {{ product.ingredients.map((i) => i.name).join(' / ') }}
                                </p>
                            </div>

                            <div
                                v-if="layout.showAllergens && product.allergens && product.allergens.length > 0"
                                class="dish-allergens"
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

                <p v-else class="section-empty">&gt;_ NO_ITEMS</p>
            </section>
        </main>

        <footer class="foot">
            <div class="foot-bar" aria-hidden="true" />
            <p class="foot-name">{{ menu.location?.name }}</p>
            <p v-if="menu.location?.address" class="foot-line">◆ {{ menu.location.address }}</p>
            <p v-if="menu.location?.phone" class="foot-line">◆ {{ menu.location.phone }}</p>
            <div v-if="showBranding" class="foot-branding">
                <a :href="`https://menulinker.com?ref=${tenantSlug}`" target="_blank" rel="noopener">
                    {{ t('public_menu.branding') }}
                </a>
            </div>
        </footer>

        <ShareMenu :url="shareUrl" :menu-name="menu.name" :location-name="menu.location?.name ?? ''" />
        <CartOnboarding v-if="hasCart && menu.show_prices" :menu-id="menu.id" />
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
.menu-neon {
    /* Canonical ML variables (overridable by customization) */
    --ml-bg: oklch(0.10 0.012 300);
    --ml-ink: oklch(0.97 0.005 300);
    --ml-ink-soft: oklch(0.75 0.012 300);
    --ml-accent: oklch(0.70 0.32 0);
    --ml-rule: oklch(0.25 0.02 300);
    --ml-font-display: 'Big Shoulders Display', 'Impact', sans-serif;
    --ml-font-body: 'Manrope', ui-sans-serif, system-ui, sans-serif;
    --ml-spacing: 1;

    /* Template internal variables now read from ML canonical */
    --bg: var(--ml-bg);
    --panel: oklch(0.15 0.015 300);
    --ink: var(--ml-ink);
    --ink-soft: var(--ml-ink-soft);
    --ink-faint: oklch(0.50 0.015 300);
    --rule: var(--ml-rule);
    --neon: var(--ml-accent);
    --neon-glow: oklch(0.70 0.32 0 / 0.55);
    --neon-cyan: oklch(0.82 0.17 200);
    --menu-paper: var(--panel);
    --menu-ink: var(--ink);
    --menu-rule: var(--rule);
    --menu-accent: var(--neon);

    --font-display: var(--ml-font-display);
    --font-body: var(--ml-font-body);
    --font-mono: 'JetBrains Mono', ui-monospace, monospace;

    min-height: 100vh;
    background: var(--bg);
    color: var(--ink);
    font-family: var(--font-body);
    -webkit-font-smoothing: antialiased;
    overflow-x: hidden;
}

.scanlines {
    position: fixed;
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

/* ============ TOPBAR ============ */
.topbar {
    position: sticky;
    top: 0;
    z-index: 30;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 0.85rem clamp(1rem, 4vw, 2rem);
    background: color-mix(in oklch, var(--bg) 90%, transparent);
    backdrop-filter: blur(12px);
    border-bottom: 1px solid var(--rule);
}

.brand {
    display: flex;
    align-items: center;
    gap: 0.7rem;
    font-family: var(--font-mono);
    font-size: 0.68rem;
    font-weight: 500;
    letter-spacing: 0.25em;
    text-transform: uppercase;
    color: var(--ink-soft);
}

.brand-bar {
    width: 20px;
    height: 2px;
    background: var(--neon);
    box-shadow: 0 0 12px var(--neon-glow);
}

/* ============ HERO ============ */
.hero {
    position: relative;
    z-index: 2;
    min-height: clamp(480px, 75svh, 680px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: clamp(3rem, 8vw, 5rem) clamp(1rem, 4vw, 2rem);
    overflow: hidden;
}

.hero-image {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center;
    filter: brightness(0.55) contrast(1.15) saturate(1.1);
}

.hero-overlay {
    position: absolute;
    inset: 0;
    background:
        linear-gradient(180deg, oklch(0 0 0 / 0.4) 0%, oklch(0 0 0 / 0.5) 60%, var(--bg) 100%),
        radial-gradient(ellipse at center, transparent 30%, oklch(0 0 0 / 0.5) 100%);
    pointer-events: none;
}

.hero-inner {
    position: relative;
    z-index: 2;
    text-align: center;
    max-width: 900px;
    animation: glitch-in 800ms cubic-bezier(.2,.7,.2,1) both;
}

.hero-marks {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.9rem;
    color: var(--neon);
    font-family: var(--font-mono);
    font-size: 0.72rem;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    text-shadow: 0 0 12px var(--neon-glow);
    margin-bottom: 1.5rem;
}

.hero-code { font-weight: 700; }

.hero-title {
    font-family: var(--font-display);
    font-weight: 900;
    font-size: clamp(3rem, 13vw, 8rem);
    line-height: 0.9;
    letter-spacing: -0.02em;
    text-transform: uppercase;
    color: var(--ink);
    margin: 0;
    text-shadow:
        0 0 20px var(--neon-glow),
        0 0 40px var(--neon-glow);
}

.hero-sub {
    margin: 1.5rem auto 0;
    max-width: 52ch;
    font-family: var(--font-body);
    font-size: clamp(0.95rem, 1.8vw, 1.1rem);
    line-height: 1.6;
    color: var(--ink-soft);
}

.hero-cta {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.8rem;
    margin-top: 2rem;
}

.cta-line {
    width: 40px;
    height: 2px;
    background: var(--neon);
    box-shadow: 0 0 12px var(--neon-glow);
}

.cta-dot {
    width: 8px;
    height: 8px;
    background: var(--neon);
    border-radius: 999px;
    box-shadow: 0 0 16px var(--neon), 0 0 32px var(--neon-glow);
    animation: pulse 2s ease-in-out infinite;
}

/* ============ SECTION NAV ============ */
.section-nav {
    position: sticky;
    top: 48px;
    z-index: 25;
    background: color-mix(in oklch, var(--bg) 90%, transparent);
    backdrop-filter: blur(14px);
    border-bottom: 1px solid var(--rule);
}

.section-nav-inner {
    display: flex;
    gap: 0.4rem;
    overflow-x: auto;
    padding: 0.75rem clamp(1rem, 4vw, 2rem);
    scrollbar-width: none;
}

.section-nav-inner::-webkit-scrollbar { display: none; }

.chip {
    flex: none;
    display: flex;
    align-items: center;
    gap: 0.55rem;
    padding: 0.5rem 0.95rem;
    background: transparent;
    border: 1px solid var(--rule);
    color: var(--ink-soft);
    font-family: var(--font-body);
    font-size: 0.74rem;
    font-weight: 600;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    cursor: pointer;
    white-space: nowrap;
    transition: all 200ms;
}

.chip:hover {
    color: var(--neon);
    border-color: var(--neon);
    box-shadow: 0 0 12px color-mix(in oklch, var(--neon) 40%, transparent);
}

.chip:focus-visible {
    outline: 2px solid var(--neon);
    outline-offset: 2px;
}

.chip.is-active {
    background: var(--neon);
    color: var(--bg);
    border-color: var(--neon);
    box-shadow: 0 0 18px var(--neon-glow);
}

.chip-num {
    font-family: var(--font-mono);
    font-weight: 700;
    font-size: 0.68rem;
    opacity: 0.7;
}

.chip.is-active .chip-num { opacity: 0.85; }

/* ============ MAIN ============ */
.main {
    position: relative;
    z-index: 2;
    max-width: 1000px;
    margin: 0 auto;
    padding: clamp(3rem, 8vw, 5rem) clamp(1.25rem, 4vw, 2.5rem) 0;
}

.empty {
    text-align: center;
    padding: 5rem 0;
    font-family: var(--font-mono);
    color: var(--ink-faint);
    font-size: 0.9rem;
    letter-spacing: 0.1em;
}

.section {
    margin-bottom: clamp(4rem, 9vw, 6rem);
    animation: fade-up 700ms cubic-bezier(.2,.7,.2,1) both;
    animation-delay: calc(var(--i, 0) * 90ms + 120ms);
}

.section-head { margin-bottom: 2.5rem; }

.section-label {
    display: flex;
    align-items: center;
    gap: 0.9rem;
    margin-bottom: 0.8rem;
}

.section-num {
    font-family: var(--font-display);
    font-weight: 900;
    font-size: 1.6rem;
    line-height: 1;
    color: var(--neon);
    text-shadow: 0 0 14px var(--neon-glow);
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
    font-size: clamp(2rem, 5vw, 3rem);
    line-height: 0.95;
    letter-spacing: -0.02em;
    text-transform: uppercase;
    margin: 0;
    color: var(--ink);
}

.section-desc {
    margin-top: 0.7rem;
    font-size: 0.9rem;
    color: var(--ink-soft);
    max-width: 60ch;
    line-height: 1.55;
}

.section-empty {
    text-align: center;
    color: var(--ink-faint);
    font-family: var(--font-mono);
    font-size: 0.85rem;
    padding: 2rem 0;
}

/* ============ DISHES ============ */
.dishes {
    list-style: none;
    padding: 0;
    margin: 0;
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.25rem;
}

@media (min-width: 720px) {
    .dishes { grid-template-columns: 1fr 1fr; gap: 1.5rem; }
}

.dish {
    display: flex;
    flex-direction: column;
    background: var(--panel);
    border: 1px solid var(--rule);
    overflow: hidden;
    position: relative;
    transition: border-color 300ms, transform 300ms;
    animation: fade-up 650ms cubic-bezier(.2,.7,.2,1) both;
    animation-delay: calc(min(var(--j, 0), 8) * 50ms + 220ms);
}

.dish:hover {
    border-color: var(--neon);
    transform: translateY(-2px);
}

.dish::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 16px;
    height: 16px;
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
    width: 16px;
    height: 16px;
    border-bottom: 2px solid var(--neon);
    border-right: 2px solid var(--neon);
    pointer-events: none;
    opacity: 0.7;
}

.dish-image {
    position: relative;
    aspect-ratio: 16 / 10;
    overflow: hidden;
    background: oklch(0 0 0);
}

.dish-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    filter: saturate(1.15) contrast(1.08);
}

.dish-scanlines {
    position: absolute;
    inset: 0;
    pointer-events: none;
    background-image: repeating-linear-gradient(
        0deg,
        transparent 0,
        transparent 3px,
        oklch(0 0 0 / 0.15) 3px,
        oklch(0 0 0 / 0.15) 4px
    );
    mix-blend-mode: multiply;
}

.dish-body {
    padding: 1.25rem 1.3rem 1.35rem;
    display: flex;
    flex-direction: column;
    gap: 0.55rem;
    flex: 1;
}

.dish-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
}

.dish-name {
    font-family: var(--font-display);
    font-weight: 700;
    font-size: 1.2rem;
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
    font-size: 1rem;
    color: var(--neon);
    font-variant-numeric: tabular-nums;
    white-space: nowrap;
    text-shadow: 0 0 10px var(--neon-glow);
}

.dish-meta {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.4rem;
    font-family: var(--font-mono);
    font-size: 0.68rem;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    color: var(--neon-cyan);
}

.dish-kcal { opacity: 0.8; }

.dish-tag {
    padding: 0 0.25rem;
    color: var(--neon);
    font-weight: 600;
}

.dish-desc {
    margin: 0.2rem 0 0;
    font-size: 0.86rem;
    line-height: 1.55;
    color: var(--ink-soft);
}

.dish-ingredients { margin-top: 0.3rem; }

.ing-toggle {
    padding: 0;
    background: transparent;
    border: none;
    font-family: var(--font-mono);
    font-size: 0.7rem;
    font-weight: 600;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: var(--neon);
    cursor: pointer;
    transition: opacity 200ms;
}

.ing-toggle:hover,
.ing-toggle:focus-visible {
    opacity: 0.75;
    outline: none;
    text-shadow: 0 0 10px var(--neon-glow);
}

.ing-list {
    margin: 0.55rem 0 0;
    padding: 0.55rem 0.8rem;
    font-family: var(--font-mono);
    font-size: 0.78rem;
    line-height: 1.55;
    color: var(--ink);
    background: oklch(0 0 0 / 0.4);
    border-left: 2px solid var(--neon);
    animation: fade-up 300ms ease-out;
}

.dish-allergens {
    display: flex;
    gap: 0.1rem;
    flex-wrap: wrap;
    margin-top: auto;
    padding-top: 0.5rem;
    border-top: 1px solid var(--rule);
    opacity: 0.8;
    font-size: 0.82em;
}

/* ============ FOOTER ============ */
.foot {
    position: relative;
    z-index: 2;
    text-align: center;
    max-width: 1000px;
    margin: 0 auto;
    padding: clamp(4rem, 8vw, 6rem) clamp(1.5rem, 4vw, 3rem) clamp(3rem, 6vw, 4rem);
}

.foot-bar {
    width: 60px;
    height: 2px;
    background: var(--neon);
    margin: 0 auto 2rem;
    box-shadow: 0 0 18px var(--neon-glow);
}

.foot-name {
    font-family: var(--font-display);
    font-weight: 900;
    font-size: 1.5rem;
    text-transform: uppercase;
    letter-spacing: -0.01em;
    color: var(--ink);
    margin: 0 0 1rem;
    text-shadow: 0 0 14px var(--neon-glow);
}

.foot-line {
    margin: 0.2rem 0;
    font-family: var(--font-mono);
    font-size: 0.78rem;
    color: var(--ink-soft);
    letter-spacing: 0.03em;
}

.foot-branding {
    margin-top: 2.5rem;
    font-family: var(--font-mono);
    font-size: 0.7rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: var(--ink-faint);
}

.foot-branding a {
    color: inherit;
    text-decoration: none;
}

.foot-branding a:hover { color: var(--neon); }

@keyframes glitch-in {
    0% { opacity: 0; transform: translateY(12px) skewX(-2deg); }
    60% { opacity: 1; transform: translateY(0) skewX(1deg); }
    100% { opacity: 1; transform: translateY(0) skewX(0); }
}

@keyframes fade-up {
    from { opacity: 0; transform: translateY(16px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.65; transform: scale(1.15); }
}

@media (prefers-reduced-motion: reduce) {
    .hero-inner, .section, .dish, .cta-dot, .ing-list { animation: none !important; }
}
</style>
