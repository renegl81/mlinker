<script setup lang="ts">
import type { BodyMenu } from './fakeMenu';

const props = defineProps<{
    menu: BodyMenu;
    interactive?: boolean;
}>();

function formatPrice(price: number | string | null | undefined): string {
    if (price === null || price === undefined) return '';
    const numeric = typeof price === 'number' ? price : Number(price);
    if (Number.isNaN(numeric)) return '';
    const currency = props.menu.show_currency ? `${props.menu.location?.currency ?? '€'} ` : '';
    return `${currency}${numeric.toFixed(2)}`;
}

function toRoman(n: number): string {
    const ROMAN: Array<[number, string]> = [[10, 'X'], [9, 'IX'], [5, 'V'], [4, 'IV'], [1, 'I']];
    let num = n;
    let result = '';
    for (const [value, symbol] of ROMAN) {
        while (num >= value) { result += symbol; num -= value; }
    }
    return result;
}

const sections = (props.menu.sections ?? []).map((section, index) => ({
    ...section,
    products: section.products ?? [],
    numeral: toRoman(index + 1),
}));
</script>

<template>
    <div class="menu-minimal">
        <header class="hero">
            <p class="hero-kicker">{{ menu.location?.name }}</p>
            <div class="hero-divider" aria-hidden="true" />
            <h1 class="hero-title">{{ menu.name }}</h1>
            <p v-if="menu.description" class="hero-sub">{{ menu.description }}</p>
        </header>

        <main class="main">
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

                <p v-if="section.description" class="section-desc">{{ section.description }}</p>

                <ul class="products">
                    <li
                        v-for="product in section.products"
                        :key="product.id"
                        class="product"
                    >
                        <div class="product-head">
                            <h3 class="product-name">{{ product.name }}</h3>
                            <span v-if="menu.show_prices && product.price" class="product-price">
                                {{ formatPrice(product.price) }}
                            </span>
                        </div>
                        <p v-if="product.description" class="product-desc">{{ product.description }}</p>
                    </li>
                </ul>
            </section>
        </main>

        <footer class="foot">
            <div class="foot-divider" aria-hidden="true" />
            <p class="foot-name">{{ menu.location?.name }}</p>
            <p v-if="menu.location?.address" class="foot-line">{{ menu.location.address }}</p>
        </footer>
    </div>
</template>

<style scoped>
.menu-minimal {
    --bg: oklch(0.985 0 0);
    --ink: oklch(0.14 0.003 260);
    --ink-soft: oklch(0.42 0.004 260);
    --ink-faint: oklch(0.62 0.004 260);
    --rule: oklch(0.84 0.002 260);
    --font-serif: 'Cormorant Garamond', 'Garamond', 'Times New Roman', serif;

    background: var(--bg);
    color: var(--ink);
    font-family: var(--font-serif);
    font-weight: 400;
    -webkit-font-smoothing: antialiased;
    overflow: hidden;
    min-height: 100%;
}

.hero {
    padding: clamp(2.5rem, 8vw, 5rem) clamp(1.5rem, 5vw, 3rem) clamp(1.5rem, 5vw, 3rem);
    text-align: center;
    max-width: 620px;
    margin: 0 auto;
}

.hero-kicker {
    font-size: 0.75rem;
    font-weight: 400;
    letter-spacing: 0.35em;
    text-transform: uppercase;
    color: var(--ink-soft);
    margin: 0 0 1.25rem;
}

.hero-divider {
    width: 1px;
    height: 36px;
    margin: 0 auto 1.25rem;
    background: var(--rule);
}

.hero-title {
    font-family: var(--font-serif);
    font-style: italic;
    font-weight: 400;
    font-size: clamp(2rem, 7vw, 3.5rem);
    line-height: 1.05;
    letter-spacing: -0.01em;
    margin: 0;
}

.hero-sub {
    margin-top: 1rem;
    font-size: 0.92rem;
    line-height: 1.65;
    color: var(--ink-soft);
    font-style: italic;
    max-width: 46ch;
    margin-left: auto;
    margin-right: auto;
}

.main {
    max-width: 560px;
    margin: 0 auto;
    padding: 0 clamp(1.5rem, 5vw, 3rem);
}

.section { margin-bottom: clamp(2.5rem, 7vw, 4rem); }

.section-head {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin: 0 0 1.5rem;
}

.section-divider { flex: 1; height: 1px; background: var(--rule); }

.section-title-block {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.25rem;
}

.section-numeral {
    font-family: var(--font-serif);
    font-style: italic;
    font-weight: 400;
    font-size: 0.7rem;
    letter-spacing: 0.16em;
    color: var(--ink-faint);
}

.section-title {
    font-family: var(--font-serif);
    font-weight: 500;
    font-size: 0.88rem;
    letter-spacing: 0.32em;
    text-transform: uppercase;
    margin: 0;
    color: var(--ink);
    white-space: nowrap;
}

.section-desc {
    text-align: center;
    max-width: 44ch;
    margin: -0.5rem auto 1.5rem;
    font-style: italic;
    font-size: 0.9rem;
    line-height: 1.6;
    color: var(--ink-soft);
}

.products {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 1.75rem;
}

.product { text-align: center; }

.product-head {
    display: flex;
    align-items: baseline;
    justify-content: center;
    gap: 0.9rem;
    flex-wrap: wrap;
    margin-bottom: 0.25rem;
}

.product-name {
    font-family: var(--font-serif);
    font-weight: 500;
    font-size: 1.2rem;
    line-height: 1.2;
    margin: 0;
    color: var(--ink);
}

.product-price {
    font-family: var(--font-serif);
    font-weight: 500;
    font-size: 1.05rem;
    color: var(--ink);
    font-variant-numeric: tabular-nums;
    white-space: nowrap;
}

.product-desc {
    margin: 0.25rem auto 0;
    max-width: 42ch;
    font-style: italic;
    font-weight: 400;
    font-size: 0.88rem;
    line-height: 1.6;
    color: var(--ink-soft);
}

.foot {
    max-width: 560px;
    margin: 0 auto;
    padding: clamp(2.5rem, 7vw, 4.5rem) clamp(1.5rem, 5vw, 3rem) clamp(1.5rem, 4vw, 2.5rem);
    text-align: center;
}

.foot-divider {
    width: 1px;
    height: 30px;
    margin: 0 auto 1.5rem;
    background: var(--rule);
}

.foot-name {
    font-family: var(--font-serif);
    font-weight: 500;
    font-size: 0.98rem;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: var(--ink);
    margin: 0 0 0.75rem;
}

.foot-line {
    margin: 0.2rem 0;
    font-size: 0.82rem;
    font-style: italic;
    color: var(--ink-soft);
}
</style>
