<script setup lang="ts">
import { type FakeMenu, formatFakePrice } from './fakeMenu';

defineProps<{ menu: FakeMenu }>();
</script>

<template>
    <div class="mp-root">
        <!-- topbar -->
        <div class="mp-topbar">
            <div class="mp-brand">
                <span class="mp-dot" />
                <span class="mp-brand-text">{{ menu.location_name }}</span>
            </div>
        </div>

        <!-- hero -->
        <header class="mp-hero">
            <div class="mp-hero-overlay" aria-hidden="true" />
            <div class="mp-hero-content">
                <div class="mp-kicker">
                    <span class="mp-dash" />
                    <span>MENU · ES</span>
                </div>
                <h1 class="mp-title">{{ menu.name }}</h1>
                <p class="mp-sub">{{ menu.description }}</p>
            </div>
        </header>

        <!-- section nav chips (numbered) -->
        <nav class="mp-nav">
            <button v-for="(s, i) in menu.sections" :key="s.id" class="mp-chip" :class="{ 'mp-chip-active': i === 0 }">
                <span class="mp-chip-num">{{ String(i + 1).padStart(2, '0') }}</span>
                <span class="mp-chip-name">{{ s.name }}</span>
            </button>
        </nav>

        <!-- cards grid -->
        <main class="mp-main">
            <div v-for="(section, sIdx) in menu.sections.slice(0, 2)" :key="section.id" class="mp-section">
                <div class="mp-sec-head">
                    <span class="mp-sec-num">{{ String(sIdx + 1).padStart(2, '0') }}</span>
                    <h2 class="mp-sec-title">{{ section.name }}</h2>
                </div>
                <div class="mp-cards">
                    <div v-for="product in section.products.slice(0, 2)" :key="product.id" class="mp-card">
                        <div class="mp-card-top">
                            <span class="mp-card-name">{{ product.name }}</span>
                            <span class="mp-card-price">{{ formatFakePrice(product.price, menu.currency) }}</span>
                        </div>
                        <p class="mp-card-desc">{{ product.description }}</p>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<style scoped>
/* -- Modern preview -------------------------------------------------------- */
/* Matches TemplateModern.vue: fondo slate-950 azulado, acento violeta/azul  */
/* Syne display (bold, uppercase), Manrope body                               */
.mp-root {
    width: 600px;
    height: 800px;
    overflow: hidden;
    background: #0f0f1a;
    color: #e8e8f0;
    font-family: ui-sans-serif, system-ui, sans-serif;
    font-size: 12px;
    line-height: 1.45;
    box-sizing: border-box;
}

/* topbar */
.mp-topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 28px;
    background: rgba(15,15,26,0.9);
    border-bottom: 1px solid #2a2a42;
}

.mp-brand {
    display: flex;
    align-items: center;
    gap: 7px;
    font-size: 9px;
    font-weight: 600;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: #8888aa;
}

.mp-dot {
    width: 6px;
    height: 6px;
    border-radius: 999px;
    background: #6366f1;
    box-shadow: 0 0 10px rgba(99,102,241,0.6);
}

.mp-brand-text { color: #8888aa; }

/* hero */
.mp-hero {
    position: relative;
    height: 200px;
    display: flex;
    align-items: flex-end;
    padding: 20px 28px;
    overflow: hidden;
    background: linear-gradient(180deg, #1a1a2e 0%, #0f0f1a 100%);
}

.mp-hero-overlay {
    position: absolute;
    inset: 0;
    background: radial-gradient(ellipse at 30% 30%, transparent 0%, rgba(0,0,0,0.4) 100%);
    pointer-events: none;
}

.mp-hero-content {
    position: relative;
    z-index: 2;
    width: 100%;
}

.mp-kicker {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 8.5px;
    font-weight: 600;
    letter-spacing: 0.28em;
    text-transform: uppercase;
    color: #6366f1;
    margin-bottom: 8px;
}

.mp-dash {
    width: 24px;
    height: 2px;
    background: #6366f1;
    display: inline-block;
    box-shadow: 0 0 8px rgba(99,102,241,0.5);
}

.mp-title {
    font-family: ui-sans-serif, system-ui, sans-serif;
    font-weight: 800;
    font-size: 38px;
    line-height: 0.92;
    letter-spacing: -0.03em;
    text-transform: uppercase;
    margin: 0;
    color: #f0f0ff;
}

.mp-sub {
    margin: 6px 0 0;
    font-size: 11px;
    color: #8888aa;
    max-width: 52ch;
}

/* nav */
.mp-nav {
    display: flex;
    gap: 6px;
    padding: 8px 28px;
    border-bottom: 1px solid #2a2a42;
    overflow: hidden;
    background: rgba(15,15,26,0.92);
}

.mp-chip {
    flex: none;
    display: flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    border-radius: 999px;
    border: 1px solid #2a2a42;
    background: transparent;
    color: #8888aa;
    font-size: 8.5px;
    font-weight: 600;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    white-space: nowrap;
    cursor: default;
}

.mp-chip-active {
    background: #6366f1;
    color: #0f0f1a;
    border-color: #6366f1;
}

.mp-chip-num {
    font-weight: 700;
    font-size: 8px;
    opacity: 0.65;
}
.mp-chip-active .mp-chip-num { opacity: 0.85; }
.mp-chip-name { font-weight: 600; }

/* main */
.mp-main {
    padding: 16px 28px;
    overflow: hidden;
}

.mp-section {
    margin-bottom: 16px;
}

.mp-sec-head {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-bottom: 10px;
    padding-bottom: 8px;
    border-bottom: 1px solid #2a2a42;
}

.mp-sec-num {
    font-weight: 800;
    font-size: 20px;
    line-height: 0.9;
    color: #6366f1;
    letter-spacing: -0.02em;
    padding-top: 2px;
}

.mp-sec-title {
    font-weight: 700;
    font-size: 16px;
    line-height: 1;
    letter-spacing: -0.02em;
    text-transform: uppercase;
    margin: 0;
    color: #f0f0ff;
}

/* cards */
.mp-cards {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
}

.mp-card {
    background: #1a1a2e;
    border: 1px solid #2a2a42;
    border-radius: 10px;
    padding: 10px 12px 11px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.mp-card-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 8px;
}

.mp-card-name {
    font-weight: 600;
    font-size: 11px;
    line-height: 1.25;
    letter-spacing: -0.01em;
    color: #f0f0ff;
    flex: 1;
}

.mp-card-price {
    font-weight: 800;
    font-size: 14px;
    line-height: 0.95;
    letter-spacing: -0.02em;
    color: #6366f1;
    font-variant-numeric: tabular-nums;
    white-space: nowrap;
    flex-shrink: 0;
    text-shadow: 0 0 20px rgba(99,102,241,0.4);
}

.mp-card-desc {
    margin: 0;
    font-size: 9.5px;
    line-height: 1.4;
    color: #8888aa;
}
</style>
