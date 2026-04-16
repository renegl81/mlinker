<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import MenuSeoHead from '@/components/public/MenuSeoHead.vue';
import MenuLanguageSwitcher from '@/components/public/MenuLanguageSwitcher.vue';
import ShareMenu from '@/components/public/ShareMenu.vue';
import RivieraBody from '@/components/template-bodies/RivieraBody.vue';
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
const { formatPrice } = useMenuFormatter(props.menu);
const { cssVars, fontLinks, layout } = useMenuCustomization(props.customization ?? null);
const { t } = useI18n();

const cart = props.hasCart ? useCart(props.menu.id) : null;
const cartOpen = ref(false);

const labels = computed(() => ({
    empty: t('public_menu.empty'),
    section_empty: t('public_menu.section_empty'),
    kcal: t('public_menu.kcal'),
    show_ingredients: t('public_menu.show_ingredients'),
    hide_ingredients: t('public_menu.hide_ingredients'),
    branding: t('public_menu.branding'),
}));

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

    <div :style="cssVars">
        <div
            v-if="hasMultilang && availableLocales.length > 1"
            style="position: absolute; top: clamp(1rem, 3vw, 1.5rem); right: clamp(1rem, 3vw, 1.5rem); z-index: 50;"
        >
            <MenuLanguageSwitcher
                :current="locale"
                :available="availableLocales"
                :locales-meta="supportedLocales"
            />
        </div>

        <RivieraBody
            :menu="menu"
            :interactive="true"
            :has-cart="hasCart && menu.show_prices"
            :cart-get-quantity="(id) => cart?.getQuantity(id) ?? 0"
            :cart-add-item="(id) => { const p = menu.sections?.flatMap(s => s.products ?? []).find(pr => pr.id === id); if (p && cart) cart.addItem(p, formatPrice(p.price)); }"
            :cart-remove-item="(id) => cart?.removeItem(id)"
            :layout="layout"
            :labels="labels"
            :show-branding="showBranding"
            :tenant-slug="tenantSlug"
        />

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
