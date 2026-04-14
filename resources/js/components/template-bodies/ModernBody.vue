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
    number: String(index + 1).padStart(2, '0'),
}));
</script>

<template>
    <div class="menu-modern">
        <div class="topbar">
            <div class="topbar-brand">
                <span class="brand-dot" />
                <span class="brand-text">{{ menu.location?.name }}</span>
            </div>
        </div>

        <header class="hero">
            <div class="hero-overlay" />
            <div class="hero-content">
                <div class="hero-kicker">
                    <span class="hero-dash" />
                    <span>MENÚ · ES</span>
                </div>
                <h1 class="hero-title">{{ menu.name }}</h1>
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
                <div class="section-head">
                    <span class="section-num">{{ section.number }}</span>
                    <div class="section-meta">
                        <h2 class="section-title">{{ section.name }}</h2>
                        <p v-if="section.description" class="section-desc">{{ section.description }}</p>
                    </div>
                </div>

                <ul class="cards">
                    <li
                        v-for="(product, pIdx) in section.products"
                        :key="product.id"
                        class="card"
                        :style="{ '--j': pIdx } as Record<string, number>"
                    >
                        <div class="card-body">
                            <div class="card-top">
                                <h3 class="card-name">{{ product.name }}</h3>
                                <span v-if="menu.show_prices && product.price" class="card-price">
                                    {{ formatPrice(product.price) }}
                                </span>
                            </div>
                            <p v-if="product.description" class="card-desc">{{ product.description }}</p>
                        </div>
                    </li>
                </ul>
            </section>
        </main>

        <footer class="foot">
            <div class="foot-rule" />
            <p class="foot-name">{{ menu.location?.name }}</p>
            <div class="foot-info">
                <p v-if="menu.location?.address">{{ menu.location.address }}</p>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.menu-modern {
    --bg: oklch(0.975 0.006 270);
    --panel: oklch(0.99 0.004 270);
    --panel-2: oklch(0.96 0.008 270);
    --ink: oklch(0.16 0.02 270);
    --ink-soft: oklch(0.45 0.015 270);
    --ink-faint: oklch(0.60 0.01 270);
    --rule: oklch(0.90 0.008 270);
    --accent: oklch(0.50 0.18 270);
    --accent-glow: oklch(0.50 0.18 270 / 0.15);
    --font-display: 'Syne', ui-sans-serif, system-ui, sans-serif;
    --font-body: 'Manrope', ui-sans-serif, system-ui, sans-serif;

    background: var(--bg);
    color: var(--ink);
    font-family: var(--font-body);
    -webkit-font-smoothing: antialiased;
    overflow: hidden;
    min-height: 100%;
}

.topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 0.75rem clamp(1rem, 4vw, 2rem);
    background: color-mix(in oklch, var(--bg) 80%, transparent);
    border-bottom: 1px solid var(--rule);
}

.topbar-brand {
    display: flex;
    align-items: center;
    gap: 0.55rem;
    font-size: 0.65rem;
    font-weight: 600;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: var(--ink-soft);
}

.brand-dot {
    width: 6px;
    height: 6px;
    border-radius: 999px;
    background: var(--accent);
    box-shadow: 0 0 12px var(--accent-glow);
}

.hero {
    position: relative;
    min-height: clamp(200px, 40svh, 340px);
    display: flex;
    align-items: flex-end;
    padding: clamp(1.5rem, 5vw, 3rem) clamp(1.25rem, 5vw, 3.5rem) clamp(1.5rem, 5vw, 3rem);
    overflow: hidden;
    background: color-mix(in oklch, var(--bg) 50%, oklch(0.10 0.02 270));
}

.hero-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, oklch(0 0 0 / 0.1) 0%, oklch(0 0 0 / 0.2) 40%, var(--bg) 100%);
    pointer-events: none;
}

.hero-content {
    position: relative;
    z-index: 2;
    max-width: 1100px;
    width: 100%;
    margin: 0 auto;
    color: oklch(0.97 0.005 270);
}

.hero-kicker {
    display: flex;
    align-items: center;
    gap: 0.9rem;
    font-size: 0.65rem;
    font-weight: 600;
    letter-spacing: 0.28em;
    text-transform: uppercase;
    color: var(--accent);
    margin-bottom: 0.75rem;
}

.hero-dash {
    display: inline-block;
    width: 30px;
    height: 2px;
    background: var(--accent);
}

.hero-title {
    font-family: var(--font-display);
    font-weight: 800;
    font-size: clamp(2rem, 8vw, 4.5rem);
    line-height: 0.92;
    letter-spacing: -0.035em;
    margin: 0;
    text-transform: uppercase;
}

.hero-sub {
    margin-top: 0.85rem;
    max-width: 56ch;
    font-size: 0.88rem;
    line-height: 1.55;
    color: var(--ink-soft);
}

.main {
    max-width: 1100px;
    margin: 0 auto;
    padding: clamp(1.5rem, 5vw, 3.5rem) clamp(1.25rem, 5vw, 3.5rem) 0;
}

.section { margin-bottom: clamp(2.5rem, 6vw, 4rem); }

.section-head {
    display: flex;
    align-items: flex-start;
    gap: clamp(1rem, 3vw, 2rem);
    margin-bottom: clamp(1.25rem, 3vw, 2rem);
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--rule);
}

.section-num {
    font-family: var(--font-display);
    font-weight: 800;
    font-size: clamp(1.5rem, 4vw, 2.2rem);
    line-height: 0.9;
    color: var(--accent);
    letter-spacing: -0.02em;
    padding-top: 0.2rem;
}

.section-meta { flex: 1; min-width: 0; }

.section-title {
    font-family: var(--font-display);
    font-weight: 700;
    font-size: clamp(1.2rem, 3.5vw, 2rem);
    line-height: 1;
    letter-spacing: -0.02em;
    text-transform: uppercase;
    margin: 0;
}

.section-desc {
    margin-top: 0.5rem;
    font-size: 0.85rem;
    color: var(--ink-soft);
    line-height: 1.5;
}

.cards {
    list-style: none;
    margin: 0;
    padding: 0;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.85rem;
}

.card {
    display: flex;
    flex-direction: column;
    background: var(--panel);
    border: 1px solid var(--rule);
    border-radius: 12px;
    overflow: hidden;
    position: relative;
}

.card-body {
    padding: 1rem 1rem 1.1rem;
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
    flex: 1;
}

.card-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.75rem;
}

.card-name {
    font-family: var(--font-display);
    font-weight: 600;
    font-size: 0.92rem;
    line-height: 1.25;
    margin: 0;
    letter-spacing: -0.01em;
    color: var(--ink);
    flex: 1;
}

.card-price {
    font-family: var(--font-display);
    font-weight: 800;
    font-size: 1.15rem;
    line-height: 0.95;
    letter-spacing: -0.025em;
    color: var(--accent);
    font-variant-numeric: tabular-nums;
    white-space: nowrap;
    flex-shrink: 0;
}

.card-desc {
    margin: 0;
    font-size: 0.78rem;
    line-height: 1.5;
    color: var(--ink-soft);
}

.foot {
    max-width: 1100px;
    margin: 0 auto;
    padding: clamp(2rem, 5vw, 4rem) clamp(1.25rem, 5vw, 3.5rem) clamp(1.5rem, 4vw, 3rem);
    text-align: center;
}

.foot-rule {
    width: 40px;
    height: 2px;
    background: var(--accent);
    margin: 0 auto 1.5rem;
    box-shadow: 0 0 14px var(--accent-glow);
}

.foot-name {
    font-family: var(--font-display);
    font-weight: 700;
    font-size: 1.2rem;
    letter-spacing: -0.01em;
    color: var(--ink);
    margin: 0 0 0.6rem;
    text-transform: uppercase;
}

.foot-info p {
    margin: 0.15rem 0;
    font-size: 0.78rem;
    color: var(--ink-soft);
}
</style>
