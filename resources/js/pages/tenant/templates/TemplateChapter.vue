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
        chapter: index + 1,
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
            href="https://fonts.googleapis.com/css2?family=Bodoni+Moda:ital,opsz,wght@0,6..96,400;0,6..96,500;1,6..96,400;1,6..96,500&family=Lora:ital,wght@0,400;0,500;1,400&display=swap"
            rel="stylesheet"
        />
        <link v-for="url in fontLinks" :key="url" rel="stylesheet" :href="url" />
    </Head>

    <div class="menu-chapter" :style="cssVars">
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
            <p class="hero-kicker">— {{ menu.location?.name }} —</p>
            <div class="hero-rule" aria-hidden="true" />
            <h1 class="hero-title">{{ menu.name }}</h1>
            <div class="hero-rule" aria-hidden="true" />
            <p v-if="menu.description" class="hero-sub">{{ menu.description }}</p>
            <p class="hero-meta">Degustación · {{ sections.length }} {{ sections.length === 1 ? 'capítulo' : 'capítulos' }}</p>
        </header>

        <main class="main">
            <div v-if="sections.length === 0" class="empty">
                {{ t('public_menu.empty') }}
            </div>

            <section
                v-for="(section, sIdx) in sections"
                :key="section.id"
                class="chapter"
                :style="{ '--i': sIdx } as Record<string, number>"
            >
                <header class="chapter-head">
                    <div class="chapter-label">
                        <span class="chapter-word">Capítulo</span>
                        <span class="chapter-num">{{ section.numeral }}</span>
                    </div>
                    <h2 class="chapter-title">{{ section.name }}</h2>
                    <p v-if="layout.showSectionDescriptions && section.description" class="chapter-desc">{{ section.description }}</p>
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

                            <p v-if="product.description" class="dish-desc">
                                {{ product.description }}
                            </p>

                            <div
                                v-if="layout.showIngredients && product.ingredients && product.ingredients.length > 0"
                                class="dish-ing"
                            >
                                <button
                                    type="button"
                                    class="ing-toggle"
                                    @click="toggleIngredients(product.id)"
                                >
                                    {{ expandedProducts.has(product.id) ? t('public_menu.hide_ingredients') : t('public_menu.show_ingredients') }}
                                </button>
                                <p v-show="expandedProducts.has(product.id)" class="ing-list">
                                    <em>pairing notes:</em>
                                    {{ product.ingredients.map((i) => i.name).join(', ') }}
                                </p>
                            </div>

                            <div v-if="tagsFor(product.tags).length || (layout.showAllergens && product.allergens?.length)" class="dish-meta">
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
                        <div class="dish-price-wrap">
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
                    </li>
                </ol>

                <p v-else class="section-empty">{{ t('public_menu.section_empty') }}</p>

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
.menu-chapter {
    /* Canonical ML variables (overridable by customization) */
    --ml-bg: oklch(0.962 0.012 85);
    --ml-ink: oklch(0.16 0.01 40);
    --ml-ink-soft: oklch(0.42 0.012 40);
    --ml-accent: oklch(0.62 0.12 75);
    --ml-rule: oklch(0.82 0.01 60);
    --ml-font-display: 'Bodoni Moda', 'Playfair Display', Georgia, serif;
    --ml-font-body: 'Lora', Georgia, serif;
    --ml-spacing: 1;

    /* Template internal variables now read from ML canonical */
    --bg: var(--ml-bg);
    --ink: var(--ml-ink);
    --ink-soft: var(--ml-ink-soft);
    --ink-faint: oklch(0.58 0.01 40);
    --rule: var(--ml-rule);
    --gold: var(--ml-accent);
    --gold-dark: oklch(0.48 0.11 75);
    --menu-paper: var(--bg);
    --menu-ink: var(--ink);
    --menu-rule: var(--rule);
    --menu-accent: var(--gold);

    --font-display: var(--ml-font-display);
    --font-body: var(--ml-font-body);

    position: relative;
    min-height: 100vh;
    background: var(--bg);
    color: var(--ink);
    font-family: var(--font-body);
    font-weight: 400;
    -webkit-font-smoothing: antialiased;
    overflow-x: hidden;
}

.topbar {
    position: absolute;
    top: clamp(1rem, 3vw, 1.5rem);
    right: clamp(1rem, 3vw, 1.5rem);
    z-index: 20;
}

/* ============ HERO ============ */
.hero {
    max-width: 620px;
    margin: 0 auto;
    padding: clamp(6rem, 14vw, 10rem) clamp(1.5rem, 5vw, 3rem) clamp(4rem, 10vw, 7rem);
    text-align: center;
    animation: fade 1000ms ease-out both;
}

.hero-kicker {
    font-family: var(--font-body);
    font-style: italic;
    font-weight: 400;
    font-size: 0.78rem;
    letter-spacing: 0.24em;
    text-transform: uppercase;
    color: var(--gold-dark);
    margin: 0 0 1.5rem;
}

.hero-rule {
    width: 50px;
    height: 1px;
    background: var(--ink);
    margin: 1.5rem auto;
    opacity: 0.5;
}

.hero-title {
    font-family: var(--font-display);
    font-weight: 400;
    font-style: italic;
    font-size: clamp(2.8rem, 8vw, 5rem);
    line-height: 1;
    letter-spacing: -0.01em;
    margin: 0;
    color: var(--ink);
    font-variation-settings: 'opsz' 96;
}

.hero-sub {
    margin: 1.5rem auto 1rem;
    max-width: 42ch;
    font-size: clamp(0.98rem, 1.8vw, 1.1rem);
    line-height: 1.7;
    font-style: italic;
    color: var(--ink-soft);
}

.hero-meta {
    font-family: var(--font-body);
    font-style: italic;
    font-size: 0.78rem;
    letter-spacing: 0.1em;
    color: var(--ink-faint);
    margin: 2rem 0 0;
}

/* ============ MAIN ============ */
.main {
    max-width: 620px;
    margin: 0 auto;
    padding: 0 clamp(1.5rem, 5vw, 3rem);
}

.empty {
    text-align: center;
    padding: 5rem 0;
    font-style: italic;
    color: var(--ink-faint);
    font-family: var(--font-display);
}

.chapter {
    margin-bottom: clamp(5rem, 12vw, 8rem);
    animation: fade 900ms cubic-bezier(.2,.65,.2,1) both;
    animation-delay: calc(var(--i, 0) * 120ms + 200ms);
}

.chapter-head {
    text-align: center;
    margin-bottom: 3rem;
}

.chapter-label {
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    gap: 0.15rem;
    margin-bottom: 1rem;
}

.chapter-word {
    font-family: var(--font-body);
    font-style: italic;
    font-size: 0.7rem;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    color: var(--gold-dark);
}

.chapter-num {
    font-family: var(--font-display);
    font-weight: 500;
    font-size: 1.6rem;
    line-height: 1;
    color: var(--gold);
    letter-spacing: 0.04em;
    font-variation-settings: 'opsz' 96;
}

.chapter-title {
    font-family: var(--font-display);
    font-style: italic;
    font-weight: 400;
    font-size: clamp(1.9rem, 5.5vw, 2.8rem);
    line-height: 1;
    letter-spacing: -0.005em;
    margin: 0 0 0.9rem;
    color: var(--ink);
    font-variation-settings: 'opsz' 96;
}

.chapter-desc {
    max-width: 42ch;
    margin: 0 auto;
    font-style: italic;
    font-size: 0.92rem;
    line-height: 1.65;
    color: var(--ink-soft);
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
    gap: 2.2rem;
    counter-reset: dish;
}

.dish {
    display: grid;
    grid-template-columns: auto 1fr auto;
    gap: 1.25rem;
    align-items: flex-start;
    animation: fade 700ms ease-out both;
    animation-delay: calc(min(var(--j, 0), 8) * 60ms + 300ms);
}

.dish-index {
    font-family: var(--font-display);
    font-style: italic;
    font-weight: 400;
    font-size: 0.78rem;
    color: var(--gold);
    letter-spacing: 0.02em;
    padding-top: 0.15rem;
    font-variant-numeric: tabular-nums;
}

.dish-body { min-width: 0; }

.dish-name {
    font-family: var(--font-display);
    font-weight: 500;
    font-size: 1.15rem;
    line-height: 1.3;
    letter-spacing: -0.005em;
    margin: 0 0 0.4rem;
    color: var(--ink);
    font-variation-settings: 'opsz' 20;
}

.dish-desc {
    margin: 0;
    font-style: italic;
    font-size: 0.92rem;
    line-height: 1.65;
    color: var(--ink-soft);
    max-width: 42ch;
}

.dish-ing { margin-top: 0.6rem; }

.ing-toggle {
    padding: 0;
    background: transparent;
    border: none;
    font-family: var(--font-body);
    font-style: italic;
    font-size: 0.78rem;
    color: var(--gold-dark);
    cursor: pointer;
    letter-spacing: 0.02em;
    text-decoration: underline;
    text-decoration-style: dotted;
    text-underline-offset: 3px;
}

.ing-toggle:hover { color: var(--ink); }

.ing-list {
    margin: 0.5rem 0 0;
    font-family: var(--font-body);
    font-style: italic;
    font-size: 0.86rem;
    line-height: 1.6;
    color: var(--ink-soft);
    animation: fade 300ms ease-out;
    max-width: 44ch;
}

.ing-list em {
    color: var(--gold-dark);
    font-style: italic;
    font-weight: 500;
    margin-right: 0.35rem;
}

.dish-meta {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.4rem 0.55rem;
    margin-top: 0.65rem;
    font-size: 0.82rem;
}

.dish-kcal {
    font-family: var(--font-body);
    font-style: italic;
    font-size: 0.74rem;
    color: var(--ink-faint);
    font-variant-numeric: tabular-nums;
}

.dish-tag {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 1.3rem;
    padding: 0 0.4rem;
    font-family: var(--font-display);
    font-style: italic;
    font-size: 0.7rem;
    color: var(--gold-dark);
    border: 1px solid color-mix(in oklch, var(--gold) 35%, transparent);
    border-radius: 2px;
}

.dish-allergens {
    display: flex;
    align-items: center;
    gap: 0.1rem;
    flex-wrap: wrap;
    padding-left: 0.4rem;
    margin-left: 0.15rem;
    border-left: 1px solid var(--rule);
    opacity: 0.75;
    font-size: 0.78em;
}

.dish-price-wrap {
    padding-top: 0.15rem;
}

.dish-price {
    font-family: var(--font-display);
    font-weight: 400;
    font-size: 1rem;
    color: var(--ink);
    font-variant-numeric: tabular-nums;
    white-space: nowrap;
    font-variation-settings: 'opsz' 20;
}

.chapter-end {
    text-align: center;
    margin-top: 3rem;
    color: var(--gold);
    opacity: 0.55;
    font-size: 0.85rem;
}

/* ============ FOOTER ============ */
.foot {
    max-width: 620px;
    margin: 0 auto;
    padding: clamp(5rem, 10vw, 7rem) clamp(1.5rem, 5vw, 3rem) clamp(3rem, 6vw, 4rem);
    text-align: center;
}

.foot-rule {
    width: 40px;
    height: 1px;
    background: var(--gold);
    margin: 0 auto 2rem;
    opacity: 0.7;
}

.foot-name {
    font-family: var(--font-display);
    font-style: italic;
    font-weight: 400;
    font-size: 1.35rem;
    color: var(--ink);
    margin: 0 0 0.6rem;
    font-variation-settings: 'opsz' 96;
}

.foot-line {
    margin: 0.2rem 0;
    font-size: 0.85rem;
    font-style: italic;
    color: var(--ink-soft);
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
    border-bottom: 1px solid var(--gold);
    padding-bottom: 1px;
}

.foot-branding a:hover { color: var(--gold-dark); }

@keyframes fade {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

@media (prefers-reduced-motion: reduce) {
    .hero, .chapter, .dish, .ing-list { animation: none !important; }
}
</style>
