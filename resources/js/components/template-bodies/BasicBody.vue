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
    <div class="menu-editorial">
        <svg class="grain" aria-hidden="true">
            <filter id="menu-grain-b">
                <feTurbulence type="fractalNoise" baseFrequency="0.9" numOctaves="2" stitchTiles="stitch" />
                <feColorMatrix type="saturate" values="0" />
            </filter>
            <rect width="100%" height="100%" filter="url(#menu-grain-b)" />
        </svg>

        <header class="hero">
            <div class="hero-inner">
                <div class="hero-kicker"><span>{{ menu.location?.name }}</span></div>
                <h1 class="hero-title">{{ menu.name }}</h1>
                <p v-if="menu.description" class="hero-sub">{{ menu.description }}</p>
                <div class="hero-ornament" aria-hidden="true">
                    <span class="rule" />
                    <span class="mark">✦</span>
                    <span class="rule" />
                </div>
            </div>
        </header>

        <main class="main">
            <section
                v-for="(section, sIdx) in sections"
                :key="section.id"
                class="section"
                :style="{ '--i': sIdx } as Record<string, number>"
            >
                <div class="section-head">
                    <span class="section-numeral" aria-hidden="true">{{ section.numeral }}</span>
                    <div class="section-title-wrap">
                        <h2 class="section-title">{{ section.name }}</h2>
                        <p v-if="section.description" class="section-desc">{{ section.description }}</p>
                    </div>
                </div>

                <ul class="product-list">
                    <li
                        v-for="(product, pIdx) in section.products"
                        :key="product.id"
                        class="product"
                        :style="{ '--j': pIdx } as Record<string, number>"
                    >
                        <div class="product-main">
                            <div class="product-row">
                                <h3 class="product-name">{{ product.name }}</h3>
                                <template v-if="menu.show_prices && product.price">
                                    <span class="product-dots" aria-hidden="true" />
                                    <span class="product-price">{{ formatPrice(product.price) }}</span>
                                </template>
                            </div>
                            <p v-if="product.description" class="product-desc">{{ product.description }}</p>
                        </div>
                    </li>
                </ul>
            </section>
        </main>

        <footer class="foot">
            <div class="foot-ornament" aria-hidden="true">
                <span class="rule" />
                <span class="mark">◆</span>
                <span class="rule" />
            </div>
            <p class="foot-name">{{ menu.location?.name }}</p>
            <p v-if="menu.location?.address" class="foot-line">{{ menu.location.address }}</p>
        </footer>
    </div>
</template>

<style scoped>
.menu-editorial {
    --menu-bg: oklch(0.972 0.015 82);
    --menu-paper: oklch(0.99 0.008 82);
    --menu-ink: oklch(0.22 0.025 40);
    --menu-ink-soft: oklch(0.46 0.02 40);
    --menu-ink-faint: oklch(0.66 0.015 40);
    --menu-accent: oklch(0.56 0.15 35);
    --menu-accent-soft: oklch(0.56 0.15 35 / 0.12);
    --menu-rule: oklch(0.85 0.02 40);
    --menu-serif: 'Fraunces', 'Instrument Serif', Georgia, serif;
    --menu-sans: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;

    position: relative;
    background: var(--menu-bg);
    color: var(--menu-ink);
    font-family: var(--menu-sans);
    -webkit-font-smoothing: antialiased;
    overflow: hidden;
    min-height: 100%;
}

.grain {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    opacity: 0.11;
    mix-blend-mode: multiply;
    z-index: 1;
}

.hero {
    position: relative;
    z-index: 2;
    padding: clamp(2rem, 8vw, 4rem) clamp(1.25rem, 4vw, 2rem) clamp(1.5rem, 5vw, 3rem);
    overflow: hidden;
}

.hero-inner {
    position: relative;
    z-index: 2;
    max-width: 720px;
    margin: 0 auto;
    text-align: center;
}

.hero-kicker {
    font-family: var(--menu-sans);
    font-size: 0.68rem;
    font-weight: 500;
    letter-spacing: 0.32em;
    text-transform: uppercase;
    opacity: 0.85;
    margin-bottom: 0.75rem;
}

.hero-title {
    font-family: var(--menu-serif);
    font-style: italic;
    font-weight: 300;
    font-size: clamp(2rem, 8vw, 4rem);
    line-height: 0.95;
    letter-spacing: -0.02em;
    margin: 0;
}

.hero-sub {
    margin-top: 0.85rem;
    font-size: 0.9rem;
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
    margin-top: 1.25rem;
}

.hero-ornament .rule { height: 1px; width: 60px; background: currentColor; opacity: 0.4; }
.hero-ornament .mark { font-family: var(--menu-serif); font-size: 0.85rem; opacity: 0.7; }

.main {
    position: relative;
    z-index: 2;
    max-width: 720px;
    margin: 0 auto;
    padding: 1.5rem clamp(1.25rem, 4vw, 2rem) 0;
}

.section { margin-bottom: clamp(2rem, 6vw, 3.5rem); }

.section-head {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
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
    font-size: 1.2rem;
    line-height: 1;
    color: var(--menu-accent);
    padding-top: 0.5rem;
    min-width: 2ch;
}

.section-title-wrap { flex: 1; }

.section-title {
    font-family: var(--menu-serif);
    font-style: italic;
    font-weight: 400;
    font-size: clamp(1.4rem, 4vw, 2rem);
    line-height: 1;
    letter-spacing: -0.015em;
    margin: 0;
}

.section-desc {
    margin-top: 0.35rem;
    font-size: 0.82rem;
    color: var(--menu-ink-soft);
    line-height: 1.5;
}

.product-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.product { display: flex; gap: 1rem; align-items: flex-start; }
.product-main { flex: 1; min-width: 0; }

.product-row {
    display: flex;
    align-items: baseline;
    gap: 0.5rem;
    width: 100%;
}

.product-name {
    font-family: var(--menu-sans);
    font-weight: 500;
    font-size: 0.95rem;
    line-height: 1.35;
    margin: 0;
    color: var(--menu-ink);
    flex: 0 1 auto;
}

.product-dots {
    flex: 1 1 auto;
    min-width: 1.5rem;
    height: 0;
    align-self: flex-end;
    margin-bottom: 0.28em;
    border-bottom: 1.5px dotted var(--menu-ink-faint);
    opacity: 0.55;
}

.product-price {
    font-family: var(--menu-serif);
    font-weight: 500;
    font-size: 1rem;
    color: var(--menu-accent);
    font-variant-numeric: tabular-nums;
    white-space: nowrap;
    flex: none;
}

.product-desc {
    margin: 0.3rem 0 0;
    font-size: 0.8rem;
    line-height: 1.5;
    color: var(--menu-ink-soft);
}

.foot {
    position: relative;
    z-index: 2;
    max-width: 720px;
    margin: 0 auto;
    padding: clamp(2rem, 6vw, 4rem) clamp(1.25rem, 4vw, 2rem) clamp(1.5rem, 4vw, 2.5rem);
    text-align: center;
    color: var(--menu-ink-soft);
}

.foot-ornament {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.85rem;
    margin-bottom: 1.25rem;
    color: var(--menu-accent);
}

.foot-ornament .rule { height: 1px; width: 60px; background: currentColor; opacity: 0.4; }
.foot-ornament .mark { font-family: var(--menu-serif); font-size: 0.85rem; }

.foot-name {
    font-family: var(--menu-serif);
    font-style: italic;
    font-weight: 400;
    font-size: 1.2rem;
    color: var(--menu-ink);
    margin: 0 0 0.4rem;
}

.foot-line { margin: 0.1rem 0; font-size: 0.78rem; letter-spacing: 0.01em; }
</style>
