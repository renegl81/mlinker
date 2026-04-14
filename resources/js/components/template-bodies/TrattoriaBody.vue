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

const sections = (props.menu.sections ?? []).map((section, index) => ({
    ...section,
    products: section.products ?? [],
    index: index + 1,
}));
</script>

<template>
    <div class="menu-trattoria">
        <div class="paper-grain" aria-hidden="true" />

        <header class="hero">
            <div class="hero-solo">
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

        <main class="main">
            <section
                v-for="(section, sIdx) in sections"
                :key="section.id"
                class="section"
                :style="{ '--i': sIdx } as Record<string, number>"
            >
                <header class="section-head">
                    <p class="section-kicker">— {{ String(section.index).padStart(2, '0') }} —</p>
                    <h2 class="section-title">{{ section.name }}</h2>
                    <p v-if="section.description" class="section-desc">{{ section.description }}</p>
                    <div class="section-ornament" aria-hidden="true">
                        <span class="wheat">✦ ✦ ✦</span>
                    </div>
                </header>

                <ul class="dishes">
                    <li
                        v-for="(product, pIdx) in section.products"
                        :key="product.id"
                        class="dish"
                        :style="{ '--j': pIdx } as Record<string, number>"
                    >
                        <div class="dish-body">
                            <div class="dish-top">
                                <h3 class="dish-name">{{ product.name }}</h3>
                                <span class="dish-line" aria-hidden="true" />
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
            <div class="foot-ornament" aria-hidden="true">
                <span class="glyph">✦</span>
            </div>
            <p class="foot-name">{{ menu.location?.name }}</p>
            <p v-if="menu.location?.address" class="foot-line">{{ menu.location.address }}</p>
        </footer>
    </div>
</template>

<style scoped>
.menu-trattoria {
    --bg: oklch(0.955 0.022 75);
    --paper: oklch(0.97 0.018 75);
    --ink: oklch(0.20 0.02 30);
    --ink-soft: oklch(0.42 0.018 30);
    --ink-faint: oklch(0.58 0.016 30);
    --rule: oklch(0.78 0.02 50);
    --wine: oklch(0.38 0.12 22);
    --wine-dark: oklch(0.28 0.09 22);
    --font-display: 'Libre Bodoni', 'Playfair Display', Georgia, serif;
    --font-body: 'Lora', Georgia, serif;

    position: relative;
    background: var(--bg);
    color: var(--ink);
    font-family: var(--font-body);
    -webkit-font-smoothing: antialiased;
    overflow: hidden;
    min-height: 100%;
}

.paper-grain {
    position: absolute;
    inset: 0;
    pointer-events: none;
    background-image:
        radial-gradient(ellipse at 20% 30%, oklch(0 0 0 / 0.03) 0%, transparent 40%),
        radial-gradient(ellipse at 80% 70%, oklch(0.3 0.1 22 / 0.04) 0%, transparent 40%);
    z-index: 1;
}

.hero {
    position: relative;
    z-index: 2;
    padding: clamp(2rem, 7vw, 4.5rem) clamp(1.5rem, 5vw, 3.5rem) clamp(1.5rem, 5vw, 3rem);
    max-width: 1200px;
    margin: 0 auto;
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
    font-size: 0.82rem;
    letter-spacing: 0.18em;
    color: var(--wine);
    margin: 0 0 1rem;
    text-transform: uppercase;
}

.hero-title {
    font-family: var(--font-display);
    font-weight: 400;
    font-size: clamp(2.2rem, 7vw, 4rem);
    line-height: 0.95;
    letter-spacing: -0.01em;
    margin: 0;
    color: var(--ink);
}

.hero-ornament {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin: 1.1rem 0;
    justify-content: center;
    color: var(--wine);
}

.hero-ornament .rule { height: 1px; width: 50px; background: currentColor; opacity: 0.6; }
.hero-ornament .glyph { font-size: 0.88rem; opacity: 0.75; }

.hero-sub {
    margin: 0.75rem 0 0;
    max-width: 48ch;
    margin-left: auto;
    margin-right: auto;
    font-style: italic;
    font-size: 0.92rem;
    line-height: 1.7;
    color: var(--ink-soft);
}

.main {
    position: relative;
    z-index: 2;
    max-width: 760px;
    margin: 0 auto;
    padding: 0 clamp(1.5rem, 5vw, 3rem);
}

.section {
    margin-bottom: clamp(2.5rem, 7vw, 4.5rem);
}

.section-head { text-align: center; margin-bottom: 1.75rem; }

.section-kicker {
    font-family: var(--font-body);
    font-style: italic;
    font-size: 0.72rem;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    color: var(--wine);
    margin: 0 0 0.5rem;
    opacity: 0.85;
}

.section-title {
    font-family: var(--font-display);
    font-weight: 400;
    font-style: italic;
    font-size: clamp(1.6rem, 4vw, 2.4rem);
    line-height: 1;
    margin: 0;
    color: var(--ink);
}

.section-desc {
    max-width: 46ch;
    margin: 0.75rem auto 0;
    font-style: italic;
    font-size: 0.9rem;
    line-height: 1.6;
    color: var(--ink-soft);
}

.section-ornament {
    margin-top: 0.9rem;
    color: var(--wine);
    opacity: 0.5;
    letter-spacing: 0.4em;
    font-size: 0.65rem;
}

.dishes {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.dish { display: flex; gap: 1.2rem; align-items: flex-start; }
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
    font-size: 1.1rem;
    line-height: 1.25;
    margin: 0;
    color: var(--ink);
    flex: 0 1 auto;
}

.dish-line {
    flex: 1 1 auto;
    min-width: 1.5rem;
    height: 0;
    align-self: flex-end;
    margin-bottom: 0.38em;
    border-bottom: 1px dotted var(--ink-faint);
    opacity: 0.5;
}

.dish-price {
    font-family: var(--font-display);
    font-weight: 500;
    font-size: 1.08rem;
    color: var(--wine);
    font-variant-numeric: tabular-nums;
    white-space: nowrap;
    flex-shrink: 0;
}

.dish-desc {
    margin: 0.35rem 0 0;
    font-style: italic;
    font-size: 0.88rem;
    line-height: 1.6;
    color: var(--ink-soft);
}

.foot {
    position: relative;
    z-index: 2;
    max-width: 760px;
    margin: 0 auto;
    padding: clamp(2.5rem, 7vw, 4.5rem) clamp(1.5rem, 5vw, 3rem) clamp(1.5rem, 4vw, 2.5rem);
    text-align: center;
    color: var(--ink-soft);
}

.foot-ornament { color: var(--wine); opacity: 0.6; font-size: 1.1rem; margin-bottom: 1.25rem; }

.foot-name {
    font-family: var(--font-display);
    font-style: italic;
    font-weight: 500;
    font-size: 1.3rem;
    color: var(--ink);
    margin: 0 0 0.5rem;
}

.foot-line { margin: 0.15rem 0; font-size: 0.82rem; font-style: italic; }
</style>
