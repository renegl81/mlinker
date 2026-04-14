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
import { computed, ref } from 'vue';
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
const { formatPrice, tagsFor, toRoman } = useMenuFormatter(props.menu);
const { cssVars, fontLinks, layout } = useMenuCustomization(props.customization ?? null);
const { t } = useI18n();

const cart = props.hasCart ? useCart(props.menu.id) : null;
const cartOpen = ref(false);

const sections = computed(() => {
    if (!props.menu.sections) return [];
    return props.menu.sections.map((section, index) => ({
        ...section,
        products: section.products || [],
        numeral: toRoman(index + 1),
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
            href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400;1,500&display=swap"
            rel="stylesheet"
        />
        <link v-for="url in fontLinks" :key="url" rel="stylesheet" :href="url" />
    </Head>

    <div class="menu-minimal" :style="cssVars">
        <!-- Language switcher discreto -->
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
            <p class="hero-kicker">{{ menu.location?.name }}</p>
            <div class="hero-divider" aria-hidden="true" />
            <h1 class="hero-title">{{ menu.name }}</h1>
            <p v-if="menu.description" class="hero-sub">{{ menu.description }}</p>
        </header>

        <!-- Sections -->
        <main class="main">
            <p v-if="sections.length === 0" class="empty">
                {{ t('public_menu.empty') }}
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

                <p v-if="layout.showSectionDescriptions && section.description" class="section-desc">
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
                                    :title="t(`public_menu.tags.${tag.code}`)"
                                >{{ tag.glyph }}</span>
                            </h3>
                            <span
                                v-if="menu.show_prices && product.price"
                                class="product-price"
                            >{{ formatPrice(product.price) }}</span>
                            <AddToCartButton
                                v-if="hasCart && menu.show_prices && product.price"
                                :quantity="cart?.getQuantity(product.id) ?? 0"
                                @add="cart?.addItem(product, formatPrice(product.price))"
                                @remove="cart?.removeItem(product.id)"
                            />
                        </div>

                        <p v-if="product.description" class="product-desc">
                            {{ product.description }}
                        </p>

                        <p
                            v-if="menu.show_calories && product.calories"
                            class="product-kcal"
                        >
                            {{ product.calories }} {{ t('public_menu.kcal') }}
                        </p>

                        <div
                            v-if="layout.showIngredients && product.ingredients && product.ingredients.length > 0"
                            class="ingredients-wrap"
                        >
                            <button
                                type="button"
                                class="ingredients-toggle"
                                :aria-expanded="expandedProducts.has(product.id)"
                                @click="toggleIngredients(product.id)"
                            >
                                {{ expandedProducts.has(product.id) ? '—' : '+' }}
                                <span>
                                    {{ expandedProducts.has(product.id) ? t('public_menu.hide_ingredients') : t('public_menu.show_ingredients') }}
                                </span>
                            </button>
                            <p v-show="expandedProducts.has(product.id)" class="ingredients-list">
                                {{ product.ingredients.map((i) => i.name).join(', ') }}
                            </p>
                        </div>

                        <div
                            v-if="layout.showAllergens && product.allergens && product.allergens.length > 0"
                            class="product-allergens"
                        >
                            <AllergenIcon
                                v-for="allergen in product.allergens"
                                :key="allergen.id"
                                :code="allergen.code"
                                size="sm"
                                :title="allergen.name"
                            />
                        </div>
                    </li>
                </ul>

                <p v-else class="section-empty">{{ t('public_menu.section_empty') }}</p>
            </section>
        </main>

        <footer class="foot">
            <div class="foot-divider" aria-hidden="true" />
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
.menu-minimal {
    /* Canonical ML variables (overridable by customization) */
    --ml-bg: oklch(0.985 0 0);
    --ml-ink: oklch(0.14 0.003 260);
    --ml-ink-soft: oklch(0.42 0.004 260);
    --ml-accent: oklch(0.14 0.003 260);
    --ml-rule: oklch(0.84 0.002 260);
    --ml-font-display: 'Cormorant Garamond', 'Garamond', 'Times New Roman', serif;
    --ml-font-body: 'Cormorant Garamond', 'Garamond', 'Times New Roman', serif;
    --ml-spacing: 1;

    /* Template internal variables now read from ML canonical */
    --bg: var(--ml-bg);
    --ink: var(--ml-ink);
    --ink-soft: var(--ml-ink-soft);
    --ink-faint: oklch(0.62 0.004 260);
    --rule: var(--ml-rule);
    --menu-paper: var(--bg);
    --menu-ink: var(--ink);
    --menu-rule: var(--rule);
    --menu-accent: var(--ml-accent);

    --font-serif: var(--ml-font-display);

    min-height: 100vh;
    background: var(--bg);
    color: var(--ink);
    font-family: var(--font-serif);
    font-weight: 400;
    -webkit-font-smoothing: antialiased;
    overflow-x: hidden;
}

/* ============ TOPBAR ============ */
.topbar {
    position: absolute;
    top: clamp(1rem, 3vw, 1.5rem);
    right: clamp(1rem, 3vw, 1.5rem);
    z-index: 20;
}

/* ============ HERO ============ */
.hero {
    padding: clamp(5rem, 14vw, 9rem) clamp(1.5rem, 5vw, 3rem) clamp(3rem, 8vw, 5rem);
    text-align: center;
    max-width: 620px;
    margin: 0 auto;
    animation: fade 900ms ease-out both;
}

.hero-kicker {
    font-size: 0.78rem;
    font-weight: 400;
    letter-spacing: 0.35em;
    text-transform: uppercase;
    color: var(--ink-soft);
    margin: 0 0 1.75rem;
}

.hero-divider {
    width: 1px;
    height: 48px;
    margin: 0 auto 1.75rem;
    background: var(--rule);
}

.hero-title {
    font-family: var(--font-serif);
    font-style: italic;
    font-weight: 400;
    font-size: clamp(2.5rem, 8vw, 4.5rem);
    line-height: 1.05;
    letter-spacing: -0.01em;
    margin: 0;
}

.hero-sub {
    margin-top: 1.5rem;
    font-size: clamp(0.98rem, 1.8vw, 1.1rem);
    line-height: 1.65;
    color: var(--ink-soft);
    font-style: italic;
    max-width: 46ch;
    margin-left: auto;
    margin-right: auto;
}

/* ============ MAIN ============ */
.main {
    max-width: 560px;
    margin: 0 auto;
    padding: 0 clamp(1.5rem, 5vw, 3rem);
}

.empty {
    text-align: center;
    padding: 5rem 0;
    color: var(--ink-faint);
    font-style: italic;
    font-size: 1.05rem;
}

.section {
    margin-bottom: clamp(4rem, 10vw, 6rem);
}

.section-head {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    margin: 0 0 2.25rem;
}

.section-divider {
    flex: 1;
    height: 1px;
    background: var(--rule);
}

.section-title-block {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.35rem;
}

.section-numeral {
    font-family: var(--font-serif);
    font-style: italic;
    font-weight: 400;
    font-size: 0.72rem;
    letter-spacing: 0.16em;
    color: var(--ink-faint);
    text-transform: none;
}

.section-title {
    font-family: var(--font-serif);
    font-weight: 500;
    font-size: 0.9rem;
    letter-spacing: 0.32em;
    text-transform: uppercase;
    margin: 0;
    color: var(--ink);
    white-space: nowrap;
}

.section-desc {
    text-align: center;
    max-width: 44ch;
    margin: -0.75rem auto 2.5rem;
    font-style: italic;
    font-size: 0.95rem;
    line-height: 1.6;
    color: var(--ink-soft);
}

.section-empty {
    text-align: center;
    color: var(--ink-faint);
    font-style: italic;
    font-size: 0.95rem;
    padding: 1.5rem 0;
}

/* ============ PRODUCTS ============ */
.products {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 2.25rem;
}

.product {
    text-align: center;
}

.product-head {
    display: flex;
    align-items: baseline;
    justify-content: center;
    gap: 0.9rem;
    flex-wrap: wrap;
    margin-bottom: 0.3rem;
}

.product-name {
    font-family: var(--font-serif);
    font-weight: 500;
    font-size: 1.3rem;
    line-height: 1.2;
    letter-spacing: 0.01em;
    margin: 0;
    color: var(--ink);
}

.product-tag {
    display: inline-block;
    margin-left: 0.25rem;
    font-family: var(--font-serif);
    font-style: italic;
    font-weight: 400;
    font-size: 0.78rem;
    color: var(--ink-faint);
    letter-spacing: 0.02em;
}

.product-tag::before { content: '('; }
.product-tag::after { content: ')'; }

.product-price {
    font-family: var(--font-serif);
    font-weight: 500;
    font-size: 1.15rem;
    color: var(--ink);
    font-variant-numeric: tabular-nums;
    white-space: nowrap;
}

.product-desc {
    margin: 0.35rem auto 0;
    max-width: 42ch;
    font-style: italic;
    font-weight: 400;
    font-size: 0.95rem;
    line-height: 1.6;
    color: var(--ink-soft);
}

.product-kcal {
    margin: 0.45rem 0 0;
    font-size: 0.72rem;
    font-weight: 400;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: var(--ink-faint);
    font-variant-numeric: tabular-nums;
}

/* ============ INGREDIENTS ============ */
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
    animation: reveal 320ms ease-out;
}

.product-allergens {
    display: flex;
    justify-content: center;
    gap: 0.15rem;
    margin-top: 0.6rem;
    opacity: 0.75;
    font-size: 0.82em;
}

/* ============ FOOTER ============ */
.foot {
    max-width: 560px;
    margin: 0 auto;
    padding: clamp(5rem, 10vw, 7rem) clamp(1.5rem, 5vw, 3rem) clamp(3rem, 6vw, 4rem);
    text-align: center;
}

.foot-divider {
    width: 1px;
    height: 40px;
    margin: 0 auto 2rem;
    background: var(--rule);
}

.foot-name {
    font-family: var(--font-serif);
    font-weight: 500;
    font-size: 1.05rem;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: var(--ink);
    margin: 0 0 1rem;
}

.foot-line {
    margin: 0.25rem 0;
    font-size: 0.85rem;
    font-style: italic;
    color: var(--ink-soft);
}

.foot-branding {
    margin-top: 2.5rem;
    font-size: 0.72rem;
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

@keyframes fade {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes reveal {
    from { opacity: 0; transform: translateY(-4px); }
    to { opacity: 1; transform: translateY(0); }
}

@media (prefers-reduced-motion: reduce) {
    .hero, .ingredients-list { animation: none !important; }
}
</style>
