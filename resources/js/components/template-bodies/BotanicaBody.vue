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

const sections = (props.menu.sections ?? []).map((section) => ({
    ...section,
    products: section.products ?? [],
}));
</script>

<template>
    <div class="menu-botanica">
        <svg class="leaf leaf-tl" viewBox="0 0 100 100" aria-hidden="true">
            <path d="M50 5 C70 20 90 40 95 60 C80 55 60 50 45 40 C35 30 40 15 50 5 Z" fill="currentColor" opacity="0.12" />
            <path d="M50 5 Q50 45 60 70" stroke="currentColor" stroke-width="0.8" fill="none" opacity="0.3" />
        </svg>
        <svg class="leaf leaf-br" viewBox="0 0 100 100" aria-hidden="true">
            <path d="M50 95 C30 80 10 60 5 40 C20 45 40 50 55 60 C65 70 60 85 50 95 Z" fill="currentColor" opacity="0.12" />
            <path d="M50 95 Q50 55 40 30" stroke="currentColor" stroke-width="0.8" fill="none" opacity="0.3" />
        </svg>

        <header class="hero">
            <p class="hero-kicker">
                <span class="leaf-glyph">❊</span>
                {{ menu.location?.name }}
                <span class="leaf-glyph">❊</span>
            </p>
            <h1 class="hero-title">{{ menu.name }}</h1>
            <p v-if="menu.description" class="hero-sub">{{ menu.description }}</p>
        </header>

        <main class="main">
            <section
                v-for="(section, sIdx) in sections"
                :key="section.id"
                class="section"
                :style="{ '--i': sIdx } as Record<string, number>"
            >
                <header class="section-head">
                    <span class="section-glyph" aria-hidden="true">❊</span>
                    <h2 class="section-title">{{ section.name }}</h2>
                    <p v-if="section.description" class="section-desc">{{ section.description }}</p>
                </header>

                <ul class="dishes">
                    <li
                        v-for="(product, pIdx) in section.products"
                        :key="product.id"
                        class="dish"
                        :style="{ '--j': pIdx } as Record<string, number>"
                    >
                        <div class="dish-body">
                            <div class="dish-head">
                                <h3 class="dish-name">{{ product.name }}</h3>
                                <span v-if="menu.show_prices && product.price" class="dish-price">
                                    {{ formatPrice(product.price) }}
                                </span>
                            </div>
                            <p v-if="product.description" class="dish-desc">{{ product.description }}</p>
                        </div>
                    </li>
                </ul>
            </section>
        </main>

        <footer class="foot">
            <div class="foot-glyphs" aria-hidden="true">❊ ❊ ❊</div>
            <p class="foot-name">{{ menu.location?.name }}</p>
            <p v-if="menu.location?.address" class="foot-line">{{ menu.location.address }}</p>
        </footer>
    </div>
</template>

<style scoped>
.menu-botanica {
    --bg: oklch(0.955 0.022 88);
    --panel: oklch(0.98 0.015 88);
    --ink: oklch(0.22 0.015 120);
    --ink-soft: oklch(0.44 0.015 120);
    --ink-faint: oklch(0.60 0.015 120);
    --rule: oklch(0.82 0.03 120);
    --sage: oklch(0.56 0.07 140);
    --sage-dark: oklch(0.42 0.07 140);
    --terracotta: oklch(0.62 0.13 45);
    --font-display: 'Lora', Georgia, serif;
    --font-body: 'Nunito', ui-sans-serif, system-ui, sans-serif;

    position: relative;
    background: var(--bg);
    color: var(--ink);
    font-family: var(--font-body);
    -webkit-font-smoothing: antialiased;
    overflow: hidden;
    min-height: 100%;
}

.leaf {
    position: absolute;
    width: clamp(140px, 22vw, 240px);
    height: clamp(140px, 22vw, 240px);
    color: var(--sage);
    pointer-events: none;
    z-index: 1;
}

.leaf-tl { top: -30px; left: -30px; transform: rotate(-15deg); }
.leaf-br { bottom: -30px; right: -30px; transform: rotate(15deg); }

.hero {
    position: relative;
    z-index: 2;
    padding: clamp(2.5rem, 8vw, 5rem) clamp(1.5rem, 5vw, 3rem) clamp(1.5rem, 5vw, 3rem);
    max-width: 720px;
    margin: 0 auto;
    text-align: center;
}

.hero-kicker {
    display: inline-flex;
    align-items: center;
    gap: 0.9rem;
    font-family: var(--font-body);
    font-weight: 500;
    font-size: 0.72rem;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: var(--sage);
    margin: 0 0 1.1rem;
}

.leaf-glyph { font-size: 0.75rem; opacity: 0.8; }

.hero-title {
    font-family: var(--font-display);
    font-style: italic;
    font-weight: 500;
    font-size: clamp(2rem, 7vw, 3.8rem);
    line-height: 1;
    letter-spacing: -0.005em;
    margin: 0;
    color: var(--ink);
}

.hero-sub {
    margin: 1rem auto 0;
    max-width: 46ch;
    font-size: 0.9rem;
    line-height: 1.65;
    color: var(--ink-soft);
    font-weight: 300;
}

.main {
    position: relative;
    z-index: 2;
    max-width: 780px;
    margin: 0 auto;
    padding: 0 clamp(1.5rem, 5vw, 3rem);
}

.section {
    margin-bottom: clamp(2.5rem, 6vw, 4rem);
}

.section-head { text-align: center; margin-bottom: 1.75rem; }

.section-glyph {
    display: inline-block;
    font-size: 1.35rem;
    color: var(--sage);
    margin-bottom: 0.5rem;
    opacity: 0.75;
}

.section-title {
    font-family: var(--font-display);
    font-style: italic;
    font-weight: 500;
    font-size: clamp(1.5rem, 4vw, 2.2rem);
    line-height: 1;
    letter-spacing: -0.005em;
    margin: 0;
    color: var(--ink);
}

.section-desc {
    max-width: 48ch;
    margin: 0.75rem auto 0;
    font-size: 0.9rem;
    line-height: 1.6;
    color: var(--ink-soft);
    font-weight: 300;
}

.dishes {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.dish {
    display: flex;
    gap: 1.25rem;
    align-items: flex-start;
    padding: 1.1rem 1.25rem;
    background: var(--panel);
    border-radius: 14px;
    border: 1px solid color-mix(in oklch, var(--rule) 60%, transparent);
    transition: box-shadow 300ms;
}

.dish-body { flex: 1; min-width: 0; }

.dish-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 0.3rem;
}

.dish-name {
    font-family: var(--font-display);
    font-weight: 500;
    font-size: 1rem;
    line-height: 1.35;
    margin: 0;
    color: var(--ink);
    flex: 1;
}

.dish-price {
    font-family: var(--font-display);
    font-weight: 500;
    font-style: italic;
    font-size: 1rem;
    color: var(--terracotta);
    font-variant-numeric: tabular-nums;
    white-space: nowrap;
    flex-shrink: 0;
}

.dish-desc {
    margin: 0;
    font-size: 0.82rem;
    line-height: 1.6;
    color: var(--ink-soft);
    font-weight: 300;
}

.foot {
    position: relative;
    z-index: 2;
    max-width: 780px;
    margin: 0 auto;
    padding: clamp(2rem, 6vw, 4rem) clamp(1.5rem, 5vw, 3rem) clamp(1.5rem, 4vw, 2.5rem);
    text-align: center;
}

.foot-glyphs {
    color: var(--sage);
    font-size: 1rem;
    letter-spacing: 0.6em;
    margin-bottom: 1.1rem;
    opacity: 0.65;
}

.foot-name {
    font-family: var(--font-display);
    font-style: italic;
    font-weight: 500;
    font-size: 1.25rem;
    color: var(--ink);
    margin: 0 0 0.5rem;
}

.foot-line { margin: 0.15rem 0; font-size: 0.82rem; color: var(--ink-soft); font-weight: 300; }
</style>
