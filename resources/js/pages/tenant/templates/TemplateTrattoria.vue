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
        index: index + 1,
    }));
});

const expandedProducts = ref<Set<number>>(new Set());

function toggleIngredients(productId: number) {
    const next = new Set(expandedProducts.value);
    if (next.has(productId)) next.delete(productId);
    else next.add(productId);
    expandedProducts.value = next;
}

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
            href="https://fonts.googleapis.com/css2?family=Libre+Bodoni:ital,wght@0,400;0,500;0,700;1,400;1,500&family=Lora:ital,wght@0,400;0,500;1,400;1,500&display=swap"
            rel="stylesheet"
        />
        <link v-for="url in fontLinks" :key="url" rel="stylesheet" :href="url" />
    </Head>

    <div class="menu-trattoria" :style="cssVars">
        <div class="paper-grain" aria-hidden="true" />

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

        <!-- HERO -->
        <header class="hero">
            <div v-if="menu.image_path" class="hero-split">
                <div class="hero-image-wrap">
                    <img :src="menu.image_path" :alt="menu.name" class="hero-image" />
                    <div class="hero-frame" aria-hidden="true" />
                </div>
                <div class="hero-text">
                    <p class="hero-kicker">— {{ menu.location?.name }} —</p>
                    <h1 class="hero-title">{{ menu.name }}</h1>
                    <div class="hero-ornament" aria-hidden="true">
                        <span class="rule" />
                        <span class="glyph">✻</span>
                        <span class="rule" />
                    </div>
                    <p v-if="menu.description" class="hero-sub">{{ menu.description }}</p>
                </div>
            </div>
            <div v-else class="hero-solo">
                <p class="hero-kicker">— {{ menu.location?.name }} —</p>
                <h1 class="hero-title">{{ menu.name }}</h1>
                <div class="hero-ornament" aria-hidden="true">
                    <span class="rule" />
                    <span class="glyph">✻</span>
                    <span class="rule" />
                </div>
                <p v-if="menu.description" class="hero-sub">{{ menu.description }}</p>
            </div>
        </header>

        <!-- MAIN -->
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
                    <p class="section-kicker">— {{ String(section.index).padStart(2, '0') }} —</p>
                    <h2 class="section-title">{{ section.name }}</h2>
                    <p v-if="layout.showSectionDescriptions && section.description" class="section-desc">{{ section.description }}</p>
                    <div class="section-ornament" aria-hidden="true">
                        <span class="wheat">✦ ✦ ✦</span>
                    </div>
                </header>

                <ul v-if="section.products.length > 0" class="dishes">
                    <li
                        v-for="(product, pIdx) in section.products"
                        :key="product.id"
                        class="dish"
                        :style="{ '--j': pIdx } as Record<string, number>"
                    >
                        <div class="dish-body">
                            <div class="dish-top">
                                <h3 class="dish-name">
                                    {{ product.name }}
                                    <span
                                        v-if="menu.show_calories && product.calories"
                                        class="dish-kcal"
                                    >· {{ product.calories }} {{ t('public_menu.kcal') }}</span>
                                </h3>
                                <span class="dish-line" aria-hidden="true" />
                                <span
                                    v-if="menu.show_prices && product.price"
                                    class="dish-price"
                                >{{ formatPrice(product.price) }}</span>
                                <AddToCartButton
                                    v-if="hasCart && menu.show_prices && product.price"
                                    :quantity="cart?.getQuantity(product.id) ?? 0"
                                    @add="cart?.addItem(product, formatPrice(product.price))"
                                    @remove="cart?.removeItem(product.id)"
                                />
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
                                    {{ expandedProducts.has(product.id) ? t('public_menu.hide_ingredients') : t('public_menu.show_ingredients') }}
                                </button>
                                <p v-show="expandedProducts.has(product.id)" class="ing-list">
                                    {{ product.ingredients.map((i) => i.name).join(' · ') }}
                                </p>
                            </div>

                            <div class="dish-meta" v-if="tagsFor(product.tags).length > 0 || (layout.showAllergens && product.allergens && product.allergens.length > 0)">
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

                        <img
                            v-if="layout.showProductImages && productImage(product)"
                            :src="productImage(product)!"
                            :alt="product.name"
                            class="dish-image"
                            loading="lazy"
                        />
                    </li>
                </ul>

                <p v-else class="section-empty">{{ t('public_menu.section_empty') }}</p>
            </section>
        </main>

        <footer class="foot">
            <div class="foot-ornament" aria-hidden="true">
                <span class="glyph">✦</span>
            </div>
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
.menu-trattoria {
    /* Canonical ML variables (overridable by customization) */
    --ml-bg: oklch(0.955 0.022 75);
    --ml-ink: oklch(0.20 0.02 30);
    --ml-ink-soft: oklch(0.42 0.018 30);
    --ml-accent: oklch(0.38 0.12 22);
    --ml-rule: oklch(0.78 0.02 50);
    --ml-font-display: 'Libre Bodoni', 'Playfair Display', Georgia, serif;
    --ml-font-body: 'Lora', Georgia, serif;
    --ml-spacing: 1;

    /* Template internal variables now read from ML canonical */
    --bg: var(--ml-bg);
    --paper: oklch(0.97 0.018 75);
    --ink: var(--ml-ink);
    --ink-soft: var(--ml-ink-soft);
    --ink-faint: oklch(0.58 0.016 30);
    --rule: var(--ml-rule);
    --wine: var(--ml-accent);
    --wine-dark: oklch(0.28 0.09 22);
    --olive: oklch(0.52 0.06 120);
    --menu-paper: var(--paper);
    --menu-ink: var(--ink);
    --menu-rule: var(--rule);
    --menu-accent: var(--wine);

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

.paper-grain {
    position: fixed;
    inset: 0;
    pointer-events: none;
    background-image:
        radial-gradient(ellipse at 20% 30%, oklch(0 0 0 / 0.03) 0%, transparent 40%),
        radial-gradient(ellipse at 80% 70%, oklch(0.3 0.1 22 / 0.04) 0%, transparent 40%);
    z-index: 1;
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
    padding: clamp(4rem, 10vw, 7rem) clamp(1.5rem, 5vw, 3.5rem) clamp(3rem, 7vw, 5rem);
    max-width: 1200px;
    margin: 0 auto;
    animation: fade-in 900ms ease-out both;
}

.hero-split {
    display: grid;
    grid-template-columns: 1fr;
    gap: clamp(2rem, 6vw, 4rem);
    align-items: center;
}

@media (min-width: 780px) {
    .hero-split { grid-template-columns: 0.85fr 1fr; }
}

.hero-image-wrap {
    position: relative;
    aspect-ratio: 4 / 5;
    max-width: 440px;
    margin: 0 auto;
}

.hero-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    filter: sepia(0.12) saturate(0.92) contrast(1.03);
    box-shadow: 0 40px 80px -30px oklch(0 0 0 / 0.45);
}

.hero-frame {
    position: absolute;
    inset: 12px;
    border: 1px solid color-mix(in oklch, var(--paper) 70%, transparent);
    pointer-events: none;
}

.hero-text {
    text-align: center;
}

@media (min-width: 780px) {
    .hero-text { text-align: left; }
}

.hero-solo {
    text-align: center;
    max-width: 680px;
    margin: 0 auto;
}

.hero-kicker {
    font-family: var(--font-body);
    font-style: italic;
    font-weight: 400;
    font-size: 0.85rem;
    letter-spacing: 0.18em;
    color: var(--wine);
    margin: 0 0 1.25rem;
    text-transform: uppercase;
}

.hero-title {
    font-family: var(--font-display);
    font-weight: 400;
    font-size: clamp(3rem, 9vw, 5.5rem);
    line-height: 0.95;
    letter-spacing: -0.01em;
    margin: 0;
    color: var(--ink);
}

.hero-ornament {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin: 1.5rem 0;
    justify-content: center;
    color: var(--wine);
}

.hero-text .hero-ornament,
.hero-text .hero-ornament { justify-content: flex-start; }

@media (min-width: 780px) {
    .hero-split .hero-ornament { justify-content: flex-start; }
}

.hero-ornament .rule {
    height: 1px;
    width: 50px;
    background: currentColor;
    opacity: 0.6;
}

.hero-ornament .glyph {
    font-size: 0.9rem;
    opacity: 0.75;
}

.hero-sub {
    margin: 1rem 0 0;
    max-width: 48ch;
    font-style: italic;
    font-size: clamp(0.98rem, 1.8vw, 1.12rem);
    line-height: 1.7;
    color: var(--ink-soft);
}

@media (min-width: 780px) {
    .hero-split .hero-sub { margin: 1rem 0 0; }
}

/* ============ MAIN ============ */
.main {
    position: relative;
    z-index: 2;
    max-width: 760px;
    margin: 0 auto;
    padding: 0 clamp(1.5rem, 5vw, 3rem);
}

.empty {
    text-align: center;
    padding: 4rem 0;
    font-style: italic;
    color: var(--ink-faint);
    font-family: var(--font-display);
}

.section {
    margin-bottom: clamp(4rem, 9vw, 6rem);
    animation: fade-up 800ms cubic-bezier(.2,.65,.2,1) both;
    animation-delay: calc(var(--i, 0) * 90ms + 120ms);
}

.section-head {
    text-align: center;
    margin-bottom: 2.5rem;
}

.section-kicker {
    font-family: var(--font-body);
    font-style: italic;
    font-size: 0.75rem;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: var(--wine);
    margin: 0 0 0.7rem;
    opacity: 0.85;
}

.section-title {
    font-family: var(--font-display);
    font-weight: 400;
    font-style: italic;
    font-size: clamp(2rem, 5vw, 3rem);
    line-height: 1;
    letter-spacing: -0.005em;
    margin: 0;
    color: var(--ink);
}

.section-desc {
    max-width: 46ch;
    margin: 1rem auto 0;
    font-style: italic;
    font-size: 0.95rem;
    line-height: 1.6;
    color: var(--ink-soft);
}

.section-ornament {
    margin-top: 1.25rem;
    color: var(--wine);
    opacity: 0.5;
    letter-spacing: 0.4em;
    font-size: 0.7rem;
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
    gap: 2rem;
}

.dish {
    display: flex;
    gap: 1.2rem;
    align-items: flex-start;
    animation: fade-up 600ms cubic-bezier(.2,.65,.2,1) both;
    animation-delay: calc(min(var(--j, 0), 8) * 45ms + 260ms);
}

.dish-body { flex: 1; min-width: 0; }

.dish-top {
    display: flex;
    align-items: baseline;
    gap: 0.75rem;
    width: 100%;
}

.dish-name {
    font-family: var(--font-display);
    font-weight: 500;
    font-size: 1.2rem;
    line-height: 1.25;
    margin: 0;
    color: var(--ink);
    flex: 0 1 auto;
}

.dish-kcal {
    font-family: var(--font-body);
    font-style: italic;
    font-weight: 400;
    font-size: 0.78rem;
    color: var(--ink-faint);
    margin-left: 0.3rem;
}

.dish-line {
    flex: 1 1 auto;
    min-width: 1.5rem;
    height: 0;
    align-self: flex-end;
    margin-bottom: 0.42em;
    border-bottom: 1px dotted var(--ink-faint);
    opacity: 0.5;
}

.dish-price {
    font-family: var(--font-display);
    font-weight: 500;
    font-size: 1.15rem;
    color: var(--wine);
    font-variant-numeric: tabular-nums;
    white-space: nowrap;
    flex-shrink: 0;
}

.dish-desc {
    margin: 0.45rem 0 0;
    font-style: italic;
    font-size: 0.92rem;
    line-height: 1.6;
    color: var(--ink-soft);
    max-width: 56ch;
}

.dish-ingredients { margin-top: 0.7rem; }

.ing-toggle {
    padding: 0;
    border: none;
    background: transparent;
    font-family: var(--font-body);
    font-style: italic;
    font-size: 0.82rem;
    color: var(--wine);
    cursor: pointer;
    text-decoration: underline;
    text-decoration-style: dotted;
    text-underline-offset: 3px;
    transition: color 200ms;
}

.ing-toggle:hover { color: var(--wine-dark); }

.ing-list {
    margin: 0.6rem 0 0;
    padding: 0.7rem 0.9rem;
    font-style: italic;
    font-size: 0.88rem;
    color: var(--ink);
    background: color-mix(in oklch, var(--wine) 6%, transparent);
    border-left: 2px solid var(--wine);
    line-height: 1.6;
    animation: slide-down 300ms ease-out;
}

.dish-meta {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.45rem 0.6rem;
    margin-top: 0.7rem;
}

.dish-tag {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 1.4rem;
    height: 1.4rem;
    padding: 0 0.45rem;
    border-radius: 2px;
    font-family: var(--font-display);
    font-style: italic;
    font-weight: 500;
    font-size: 0.72rem;
    color: var(--wine);
    background: color-mix(in oklch, var(--wine) 10%, transparent);
    border: 1px solid color-mix(in oklch, var(--wine) 25%, transparent);
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

.dish-image {
    flex: none;
    width: 96px;
    height: 120px;
    object-fit: cover;
    filter: sepia(0.1) saturate(0.92);
    box-shadow: 0 2px 4px oklch(0 0 0 / 0.08), 0 20px 40px -18px oklch(0 0 0 / 0.3);
}

@media (min-width: 640px) {
    .dish-image { width: 112px; height: 140px; }
}

/* ============ FOOTER ============ */
.foot {
    position: relative;
    z-index: 2;
    max-width: 760px;
    margin: 0 auto;
    padding: clamp(4rem, 9vw, 6rem) clamp(1.5rem, 5vw, 3rem) clamp(3rem, 6vw, 4rem);
    text-align: center;
    color: var(--ink-soft);
}

.foot-ornament {
    color: var(--wine);
    opacity: 0.6;
    font-size: 1.2rem;
    margin-bottom: 1.5rem;
}

.foot-name {
    font-family: var(--font-display);
    font-style: italic;
    font-weight: 500;
    font-size: 1.4rem;
    color: var(--ink);
    margin: 0 0 0.6rem;
}

.foot-line {
    margin: 0.2rem 0;
    font-size: 0.85rem;
    font-style: italic;
}

.foot-branding {
    margin-top: 2rem;
    font-size: 0.72rem;
    font-style: italic;
    color: var(--ink-faint);
}

.foot-branding a {
    color: inherit;
    text-decoration: none;
    border-bottom: 1px solid var(--wine);
    padding-bottom: 1px;
}

.foot-branding a:hover { color: var(--wine); }

@keyframes fade-in {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes fade-up {
    from { opacity: 0; transform: translateY(16px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes slide-down {
    from { opacity: 0; transform: translateY(-6px); }
    to { opacity: 1; transform: translateY(0); }
}

@media (prefers-reduced-motion: reduce) {
    .hero, .section, .dish, .ing-list { animation: none !important; }
}
</style>
