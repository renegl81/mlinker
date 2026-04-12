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
            href="https://fonts.googleapis.com/css2?family=Yeseva+One&family=DM+Sans:wght@300;400;500;600&display=swap"
            rel="stylesheet"
        />
        <link v-for="url in fontLinks" :key="url" rel="stylesheet" :href="url" />
    </Head>

    <div class="menu-riviera" :style="cssVars">
        <!-- Wave SVG decoration -->
        <svg class="wave-top" viewBox="0 0 1200 120" preserveAspectRatio="none" aria-hidden="true">
            <path d="M0 60 Q 150 20 300 60 T 600 60 T 900 60 T 1200 60 L 1200 120 L 0 120 Z" fill="currentColor" />
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
            <div
                v-if="menu.image_path"
                class="hero-image"
                :style="{ backgroundImage: `url(${menu.image_path})` }"
            />
            <div class="hero-gradient" aria-hidden="true" />
            <div class="hero-inner">
                <div class="hero-sun" aria-hidden="true">
                    <svg viewBox="0 0 80 80" width="72" height="72">
                        <circle cx="40" cy="40" r="14" fill="currentColor" />
                        <g stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <line x1="40" y1="6" x2="40" y2="18" />
                            <line x1="40" y1="62" x2="40" y2="74" />
                            <line x1="6" y1="40" x2="18" y2="40" />
                            <line x1="62" y1="40" x2="74" y2="40" />
                            <line x1="16" y1="16" x2="24" y2="24" />
                            <line x1="56" y1="56" x2="64" y2="64" />
                            <line x1="16" y1="64" x2="24" y2="56" />
                            <line x1="56" y1="24" x2="64" y2="16" />
                        </g>
                    </svg>
                </div>
                <p class="hero-kicker">{{ menu.location?.name }}</p>
                <h1 class="hero-title">{{ menu.name }}</h1>
                <p v-if="menu.description" class="hero-sub">{{ menu.description }}</p>
            </div>
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
                    <svg class="section-wave" viewBox="0 0 120 20" aria-hidden="true">
                        <path d="M0 10 Q 15 0 30 10 T 60 10 T 90 10 T 120 10" stroke="currentColor" stroke-width="1.5" fill="none" />
                    </svg>
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
                        <div class="dish-body">
                            <div class="dish-row">
                                <h3 class="dish-name">{{ product.name }}</h3>
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

                            <div class="dish-meta" v-if="menu.show_calories && product.calories || tagsFor(product.tags).length || (layout.showAllergens && product.allergens?.length)">
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

                            <div
                                v-if="layout.showIngredients && product.ingredients && product.ingredients.length > 0"
                                class="dish-ing"
                            >
                                <button
                                    type="button"
                                    class="ing-toggle"
                                    @click="toggleIngredients(product.id)"
                                >
                                    {{ expandedProducts.has(product.id) ? t('public_menu.hide_ingredients') : t('public_menu.show_ingredients') }} →
                                </button>
                                <p v-show="expandedProducts.has(product.id)" class="ing-list">
                                    {{ product.ingredients.map((i) => i.name).join(' · ') }}
                                </p>
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
            <svg class="foot-wave" viewBox="0 0 120 20" aria-hidden="true">
                <path d="M0 10 Q 15 0 30 10 T 60 10 T 90 10 T 120 10" stroke="currentColor" stroke-width="1.5" fill="none" />
            </svg>
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
.menu-riviera {
    /* Canonical ML variables (overridable by customization) */
    --ml-bg: oklch(0.985 0.015 220);
    --ml-ink: oklch(0.22 0.04 240);
    --ml-ink-soft: oklch(0.45 0.035 240);
    --ml-accent: oklch(0.76 0.14 80);
    --ml-rule: oklch(0.84 0.02 220);
    --ml-font-display: 'Yeseva One', Georgia, serif;
    --ml-font-body: 'DM Sans', ui-sans-serif, system-ui, sans-serif;
    --ml-spacing: 1;

    /* Template internal variables now read from ML canonical */
    --bg: var(--ml-bg);
    --panel: oklch(0.995 0.008 70);
    --ink: var(--ml-ink);
    --ink-soft: var(--ml-ink-soft);
    --ink-faint: oklch(0.62 0.03 240);
    --rule: var(--ml-rule);
    --sea: oklch(0.48 0.12 230);
    --sea-deep: oklch(0.34 0.11 240);
    --gold: var(--ml-accent);
    --gold-dark: oklch(0.60 0.14 70);
    --menu-paper: var(--panel);
    --menu-ink: var(--ink);
    --menu-rule: var(--rule);
    --menu-accent: var(--gold);

    --font-display: var(--ml-font-display);
    --font-body: var(--ml-font-body);

    position: relative;
    min-height: 100vh;
    background: linear-gradient(180deg, oklch(0.96 0.03 220) 0%, var(--bg) 30%, var(--bg) 100%);
    color: var(--ink);
    font-family: var(--font-body);
    -webkit-font-smoothing: antialiased;
    overflow-x: hidden;
}

.wave-top {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    width: 100%;
    height: 80px;
    color: oklch(0.92 0.035 220);
    z-index: 0;
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
    min-height: clamp(480px, 72svh, 660px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: clamp(4rem, 10vw, 6rem) clamp(1.5rem, 5vw, 3rem);
    overflow: hidden;
}

.hero-image {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center;
    filter: saturate(0.9) brightness(1.05);
}

.hero-gradient {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, oklch(0 0 0 / 0.15) 0%, oklch(0 0 0 / 0.3) 60%, var(--bg) 100%);
    pointer-events: none;
}

.hero:not(:has(.hero-image)) .hero-gradient { display: none; }

.hero-inner {
    position: relative;
    z-index: 2;
    text-align: center;
    max-width: 720px;
    animation: drift-in 1000ms cubic-bezier(.2,.65,.2,1) both;
}

.hero:has(.hero-image) .hero-inner {
    color: oklch(0.99 0 0);
}

.hero-sun {
    color: var(--gold);
    margin: 0 auto 1.25rem;
    animation: spin 60s linear infinite;
    filter: drop-shadow(0 0 20px color-mix(in oklch, var(--gold) 40%, transparent));
}

.hero:has(.hero-image) .hero-sun { color: oklch(0.99 0.14 80); }

.hero-kicker {
    font-family: var(--font-body);
    font-weight: 500;
    font-size: 0.78rem;
    letter-spacing: 0.32em;
    text-transform: uppercase;
    margin: 0 0 1rem;
    opacity: 0.85;
}

.hero-title {
    font-family: var(--font-display);
    font-weight: 400;
    font-size: clamp(3rem, 10vw, 6rem);
    line-height: 0.95;
    letter-spacing: -0.01em;
    margin: 0;
}

.hero:not(:has(.hero-image)) .hero-title {
    color: var(--sea-deep);
}

.hero-sub {
    margin: 1.5rem auto 0;
    max-width: 50ch;
    font-size: clamp(0.98rem, 1.8vw, 1.12rem);
    line-height: 1.6;
    font-weight: 300;
    opacity: 0.92;
}

/* ============ MAIN ============ */
.main {
    position: relative;
    z-index: 2;
    max-width: 760px;
    margin: 0 auto;
    padding: clamp(3rem, 8vw, 5rem) clamp(1.5rem, 5vw, 3rem) 0;
}

.empty {
    text-align: center;
    padding: 4rem 0;
    color: var(--ink-faint);
    font-family: var(--font-display);
}

.section {
    margin-bottom: clamp(4rem, 9vw, 6rem);
    animation: fade-up 800ms cubic-bezier(.2,.65,.2,1) both;
    animation-delay: calc(var(--i, 0) * 90ms + 150ms);
}

.section-head {
    text-align: center;
    margin-bottom: 2.5rem;
}

.section-wave {
    width: 60px;
    height: 12px;
    color: var(--gold);
    margin: 0 auto 1rem;
    opacity: 0.85;
}

.section-title {
    font-family: var(--font-display);
    font-weight: 400;
    font-size: clamp(1.9rem, 5vw, 2.8rem);
    line-height: 1;
    letter-spacing: -0.005em;
    margin: 0;
    color: var(--sea-deep);
}

.section-desc {
    max-width: 50ch;
    margin: 1rem auto 0;
    font-size: 0.95rem;
    line-height: 1.6;
    color: var(--ink-soft);
    font-weight: 300;
}

.section-empty {
    text-align: center;
    color: var(--ink-faint);
    font-style: italic;
    padding: 2rem 0;
}

/* ============ DISHES ============ */
.dishes {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.dish {
    display: flex;
    gap: 1.3rem;
    align-items: flex-start;
    padding: 1.25rem;
    background: var(--panel);
    border-radius: 18px;
    box-shadow:
        0 1px 2px oklch(0 0 0 / 0.04),
        0 16px 40px -20px oklch(0.34 0.11 240 / 0.2);
    border: 1px solid color-mix(in oklch, var(--sea) 8%, transparent);
    animation: fade-up 650ms cubic-bezier(.2,.65,.2,1) both;
    animation-delay: calc(min(var(--j, 0), 8) * 50ms + 250ms);
    transition: transform 300ms, box-shadow 300ms;
}

.dish:hover {
    transform: translateY(-3px);
    box-shadow:
        0 1px 2px oklch(0 0 0 / 0.04),
        0 28px 56px -20px oklch(0.34 0.11 240 / 0.28);
}

.dish-body { flex: 1; min-width: 0; }

.dish-row {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 0.4rem;
}

.dish-name {
    font-family: var(--font-display);
    font-weight: 400;
    font-size: 1.1rem;
    line-height: 1.3;
    margin: 0;
    color: var(--sea-deep);
    flex: 1;
}

.dish-price {
    font-family: var(--font-display);
    font-weight: 400;
    font-size: 1.15rem;
    color: var(--gold-dark);
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

.dish-meta {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.45rem 0.55rem;
    margin-top: 0.65rem;
}

.dish-kcal {
    font-size: 0.72rem;
    font-weight: 500;
    letter-spacing: 0.04em;
    color: var(--ink-faint);
    padding: 0.15rem 0.55rem;
    background: color-mix(in oklch, var(--sea) 8%, transparent);
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
    font-size: 0.7rem;
    color: var(--gold-dark);
    background: color-mix(in oklch, var(--gold) 18%, transparent);
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

.dish-ing { margin-top: 0.6rem; }

.ing-toggle {
    padding: 0;
    background: transparent;
    border: none;
    font-family: var(--font-body);
    font-size: 0.78rem;
    font-weight: 500;
    color: var(--sea);
    cursor: pointer;
    transition: color 200ms;
}

.ing-toggle:hover { color: var(--sea-deep); }

.ing-list {
    margin: 0.55rem 0 0;
    padding: 0.6rem 0.85rem;
    font-size: 0.83rem;
    line-height: 1.6;
    color: var(--ink);
    background: color-mix(in oklch, var(--sea) 8%, transparent);
    border-radius: 10px;
    animation: fade-in 300ms ease-out;
}

.dish-image {
    flex: none;
    width: 96px;
    height: 96px;
    object-fit: cover;
    border-radius: 18px;
    filter: saturate(0.95);
    box-shadow: 0 12px 28px -10px oklch(0.34 0.11 240 / 0.3);
}

@media (min-width: 640px) {
    .dish-image { width: 120px; height: 120px; }
}

/* ============ FOOTER ============ */
.foot {
    position: relative;
    z-index: 2;
    max-width: 760px;
    margin: 0 auto;
    padding: clamp(4rem, 9vw, 6rem) clamp(1.5rem, 5vw, 3rem) clamp(3rem, 6vw, 4rem);
    text-align: center;
}

.foot-wave {
    width: 80px;
    height: 14px;
    color: var(--gold);
    margin: 0 auto 1.5rem;
    opacity: 0.85;
}

.foot-name {
    font-family: var(--font-display);
    font-weight: 400;
    font-size: 1.4rem;
    color: var(--sea-deep);
    margin: 0 0 0.6rem;
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
    letter-spacing: 0.04em;
}

.foot-branding a {
    color: inherit;
    text-decoration: none;
    border-bottom: 1px solid var(--gold);
    padding-bottom: 1px;
}

.foot-branding a:hover { color: var(--gold-dark); }

@keyframes drift-in {
    from { opacity: 0; transform: translateY(20px) scale(0.98); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

@keyframes fade-up {
    from { opacity: 0; transform: translateY(14px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes fade-in {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

@media (prefers-reduced-motion: reduce) {
    .hero-inner, .section, .dish, .hero-sun { animation: none !important; }
}
</style>
