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
    chapter: index + 1,
}));
</script>

<template>
    <div class="menu-chapter">
        <header class="hero">
            <p class="hero-kicker">— {{ menu.location?.name }} —</p>
            <div class="hero-rule" aria-hidden="true" />
            <h1 class="hero-title">{{ menu.name }}</h1>
            <div class="hero-rule" aria-hidden="true" />
            <p v-if="menu.description" class="hero-sub">{{ menu.description }}</p>
            <p class="hero-meta">Degustación · {{ sections.length }} {{ sections.length === 1 ? 'capítulo' : 'capítulos' }}</p>
        </header>

        <main class="main">
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
                    <p v-if="section.description" class="chapter-desc">{{ section.description }}</p>
                </header>

                <ol class="dishes">
                    <li
                        v-for="(product, pIdx) in section.products"
                        :key="product.id"
                        class="dish"
                        :style="{ '--j': pIdx } as Record<string, number>"
                    >
                        <div class="dish-index">{{ String(pIdx + 1).padStart(2, '0') }}</div>
                        <div class="dish-body">
                            <h3 class="dish-name">{{ product.name }}</h3>
                            <p v-if="product.description" class="dish-desc">{{ product.description }}</p>
                        </div>
                        <div class="dish-price-wrap">
                            <span v-if="menu.show_prices && product.price" class="dish-price">
                                {{ formatPrice(product.price) }}
                            </span>
                        </div>
                    </li>
                </ol>

                <div class="chapter-end" aria-hidden="true">
                    <span>✦</span>
                </div>
            </section>
        </main>

        <footer class="foot">
            <div class="foot-rule" aria-hidden="true" />
            <p class="foot-name">{{ menu.location?.name }}</p>
            <p v-if="menu.location?.address" class="foot-line">{{ menu.location.address }}</p>
        </footer>
    </div>
</template>

<style scoped>
.menu-chapter {
    --bg: oklch(0.962 0.012 85);
    --ink: oklch(0.16 0.01 40);
    --ink-soft: oklch(0.42 0.012 40);
    --ink-faint: oklch(0.58 0.01 40);
    --rule: oklch(0.82 0.01 60);
    --gold: oklch(0.62 0.12 75);
    --gold-dark: oklch(0.48 0.11 75);
    --font-display: 'Bodoni Moda', 'Playfair Display', Georgia, serif;
    --font-body: 'Lora', Georgia, serif;

    position: relative;
    background: var(--bg);
    color: var(--ink);
    font-family: var(--font-body);
    font-weight: 400;
    -webkit-font-smoothing: antialiased;
    overflow: hidden;
    min-height: 100%;
}

.hero {
    max-width: 620px;
    margin: 0 auto;
    padding: clamp(2.5rem, 8vw, 5.5rem) clamp(1.5rem, 5vw, 3rem) clamp(2rem, 6vw, 4rem);
    text-align: center;
}

.hero-kicker {
    font-family: var(--font-body);
    font-style: italic;
    font-weight: 400;
    font-size: 0.72rem;
    letter-spacing: 0.24em;
    text-transform: uppercase;
    color: var(--gold-dark);
    margin: 0 0 1.1rem;
}

.hero-rule {
    width: 40px;
    height: 1px;
    background: var(--ink);
    margin: 1.1rem auto;
    opacity: 0.5;
}

.hero-title {
    font-family: var(--font-display);
    font-weight: 400;
    font-style: italic;
    font-size: clamp(2.2rem, 7vw, 4rem);
    line-height: 1;
    letter-spacing: -0.01em;
    margin: 0;
    color: var(--ink);
}

.hero-sub {
    margin: 1.1rem auto 0.75rem;
    max-width: 42ch;
    font-size: 0.92rem;
    line-height: 1.7;
    font-style: italic;
    color: var(--ink-soft);
}

.hero-meta {
    font-family: var(--font-body);
    font-style: italic;
    font-size: 0.72rem;
    letter-spacing: 0.1em;
    color: var(--ink-faint);
    margin: 1.5rem 0 0;
}

.main {
    max-width: 620px;
    margin: 0 auto;
    padding: 0 clamp(1.5rem, 5vw, 3rem);
}

.chapter {
    margin-bottom: clamp(3rem, 8vw, 5.5rem);
}

.chapter-head { text-align: center; margin-bottom: 2rem; }

.chapter-label {
    display: inline-flex;
    flex-direction: column;
    align-items: center;
    gap: 0.15rem;
    margin-bottom: 0.75rem;
}

.chapter-word {
    font-family: var(--font-body);
    font-style: italic;
    font-size: 0.65rem;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    color: var(--gold-dark);
}

.chapter-num {
    font-family: var(--font-display);
    font-weight: 500;
    font-size: 1.4rem;
    line-height: 1;
    color: var(--gold);
    letter-spacing: 0.04em;
}

.chapter-title {
    font-family: var(--font-display);
    font-style: italic;
    font-weight: 400;
    font-size: clamp(1.5rem, 4.5vw, 2.3rem);
    line-height: 1;
    letter-spacing: -0.005em;
    margin: 0 0 0.7rem;
    color: var(--ink);
}

.chapter-desc {
    max-width: 42ch;
    margin: 0 auto;
    font-style: italic;
    font-size: 0.88rem;
    line-height: 1.65;
    color: var(--ink-soft);
}

.dishes {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 1.75rem;
}

.dish {
    display: grid;
    grid-template-columns: auto 1fr auto;
    gap: 1.1rem;
    align-items: flex-start;
}

.dish-index {
    font-family: var(--font-display);
    font-style: italic;
    font-weight: 400;
    font-size: 0.72rem;
    color: var(--gold);
    letter-spacing: 0.02em;
    padding-top: 0.15rem;
    font-variant-numeric: tabular-nums;
}

.dish-body { min-width: 0; }

.dish-name {
    font-family: var(--font-display);
    font-weight: 500;
    font-size: 1.05rem;
    line-height: 1.3;
    letter-spacing: -0.005em;
    margin: 0 0 0.35rem;
    color: var(--ink);
}

.dish-desc {
    margin: 0;
    font-style: italic;
    font-size: 0.86rem;
    line-height: 1.65;
    color: var(--ink-soft);
}

.dish-price-wrap { padding-top: 0.15rem; }

.dish-price {
    font-family: var(--font-display);
    font-weight: 400;
    font-size: 0.95rem;
    color: var(--ink);
    font-variant-numeric: tabular-nums;
    white-space: nowrap;
}

.chapter-end {
    text-align: center;
    margin-top: 2rem;
    color: var(--gold);
    opacity: 0.55;
    font-size: 0.82rem;
}

.foot {
    max-width: 620px;
    margin: 0 auto;
    padding: clamp(2.5rem, 7vw, 5rem) clamp(1.5rem, 5vw, 3rem) clamp(1.5rem, 4vw, 2.5rem);
    text-align: center;
}

.foot-rule {
    width: 35px;
    height: 1px;
    background: var(--gold);
    margin: 0 auto 1.5rem;
    opacity: 0.7;
}

.foot-name {
    font-family: var(--font-display);
    font-style: italic;
    font-weight: 400;
    font-size: 1.2rem;
    color: var(--ink);
    margin: 0 0 0.5rem;
}

.foot-line { margin: 0.15rem 0; font-size: 0.82rem; font-style: italic; color: var(--ink-soft); }
</style>
