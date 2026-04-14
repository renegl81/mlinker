<script setup lang="ts">
import { type FakeMenu, formatFakePrice, toRomanNumeral } from './fakeMenu';

defineProps<{ menu: FakeMenu }>();
</script>

<template>
    <div class="bp-root">
        <!-- hero -->
        <header class="bp-hero">
            <div class="bp-kicker">{{ menu.location_name }}</div>
            <h1 class="bp-title">{{ menu.name }}</h1>
            <p class="bp-sub">{{ menu.description }}</p>
            <div class="bp-ornament" aria-hidden="true">
                <span class="bp-rule" />
                <span class="bp-mark">✦</span>
                <span class="bp-rule" />
            </div>
        </header>

        <!-- sections nav pills -->
        <nav class="bp-nav">
            <span v-for="(s, i) in menu.sections" :key="s.id" class="bp-chip">
                {{ s.name }}
            </span>
        </nav>

        <!-- content -->
        <main class="bp-main">
            <section v-for="(section, sIdx) in menu.sections" :key="section.id" class="bp-section">
                <div class="bp-sec-head">
                    <span class="bp-numeral">{{ toRomanNumeral(sIdx + 1) }}</span>
                    <h2 class="bp-sec-title">{{ section.name }}</h2>
                </div>
                <ul class="bp-products">
                    <li v-for="product in section.products" :key="product.id" class="bp-product">
                        <div class="bp-prod-row">
                            <span class="bp-prod-name">{{ product.name }}</span>
                            <span class="bp-dots" aria-hidden="true" />
                            <span class="bp-price">{{ formatFakePrice(product.price, menu.currency) }}</span>
                        </div>
                        <p class="bp-prod-desc">{{ product.description }}</p>
                    </li>
                </ul>
            </section>
        </main>

        <footer class="bp-foot">
            <div class="bp-foot-ornament" aria-hidden="true">
                <span class="bp-rule" />
                <span class="bp-mark">◆</span>
                <span class="bp-rule" />
            </div>
            <p class="bp-foot-name">{{ menu.location_name }}</p>
        </footer>
    </div>
</template>

<style scoped>
/* -- Basic / Editorial preview ------------------------------------------ */
/* Matches Basic.vue: fondo crema-amarillo, tinta oscura, acento terracota   */
/* Fraunces serif itálica para títulos, sans-serif para cuerpo               */
.bp-root {
    width: 600px;
    height: 800px;
    overflow: hidden;
    background: #f5f0e8;
    color: #2d2318;
    font-family: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
    font-size: 13px;
    line-height: 1.45;
    box-sizing: border-box;
}

.bp-hero {
    background: #f5f0e8;
    padding: 36px 40px 22px;
    text-align: center;
    border-bottom: 1px solid #d4c9b5;
}

.bp-kicker {
    font-size: 9px;
    font-weight: 500;
    letter-spacing: 0.32em;
    text-transform: uppercase;
    color: #7a6a55;
    margin-bottom: 8px;
}

.bp-title {
    font-family: Georgia, 'Times New Roman', serif;
    font-style: italic;
    font-weight: 400;
    font-size: 32px;
    line-height: 1;
    letter-spacing: -0.01em;
    margin: 0 0 6px;
    color: #2d2318;
}

.bp-sub {
    font-size: 11px;
    color: #7a6a55;
    margin: 0 0 12px;
    max-width: 52ch;
    margin-left: auto;
    margin-right: auto;
}

.bp-ornament {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    color: #8b5e3c;
}

.bp-rule {
    height: 1px;
    width: 50px;
    background: currentColor;
    opacity: 0.4;
    display: inline-block;
}

.bp-mark {
    font-size: 10px;
    opacity: 0.7;
}

/* nav */
.bp-nav {
    display: flex;
    gap: 4px;
    padding: 7px 20px;
    background: rgba(245,240,232,0.9);
    border-bottom: 1px solid #d4c9b5;
    overflow: hidden;
    flex-wrap: nowrap;
}

.bp-chip {
    flex: none;
    font-size: 8.5px;
    font-weight: 500;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    padding: 3px 9px;
    border-radius: 999px;
    color: #7a6a55;
    background: transparent;
    border: 1px solid transparent;
    white-space: nowrap;
}
.bp-chip:first-child {
    color: #8b5e3c;
    background: rgba(139,94,60,0.1);
}

/* main */
.bp-main {
    padding: 16px 40px 0;
    overflow: hidden;
    max-height: 560px;
}

.bp-section {
    margin-bottom: 18px;
}

.bp-sec-head {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    margin-bottom: 10px;
    padding-bottom: 7px;
    border-bottom: 1px solid #d4c9b5;
    position: relative;
}

.bp-sec-head::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 32px;
    height: 2px;
    background: #8b5e3c;
}

.bp-numeral {
    font-family: Georgia, serif;
    font-style: italic;
    font-weight: 300;
    font-size: 13px;
    line-height: 1;
    color: #8b5e3c;
    padding-top: 4px;
    min-width: 18px;
}

.bp-sec-title {
    font-family: Georgia, 'Times New Roman', serif;
    font-style: italic;
    font-weight: 400;
    font-size: 17px;
    line-height: 1;
    letter-spacing: -0.01em;
    margin: 0;
    color: #2d2318;
}

.bp-products {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.bp-product { }

.bp-prod-row {
    display: flex;
    align-items: baseline;
    gap: 4px;
    width: 100%;
}

.bp-prod-name {
    font-weight: 500;
    font-size: 11.5px;
    color: #2d2318;
    flex: 0 1 auto;
    max-width: 100%;
}

.bp-dots {
    flex: 1 1 auto;
    min-width: 12px;
    height: 0;
    align-self: flex-end;
    margin-bottom: 3px;
    border-bottom: 1.5px dotted rgba(45,35,24,0.3);
}

.bp-price {
    font-family: Georgia, serif;
    font-weight: 500;
    font-size: 11.5px;
    color: #8b5e3c;
    font-variant-numeric: tabular-nums;
    white-space: nowrap;
    flex: none;
}

.bp-prod-desc {
    margin: 2px 0 0;
    font-size: 10px;
    line-height: 1.4;
    color: #7a6a55;
}

/* footer */
.bp-foot {
    padding: 14px 40px 12px;
    text-align: center;
    border-top: 1px solid #d4c9b5;
    margin-top: 4px;
}

.bp-foot-ornament {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    color: #8b5e3c;
    margin-bottom: 6px;
}

.bp-foot-name {
    font-family: Georgia, serif;
    font-style: italic;
    font-size: 12px;
    color: #2d2318;
    margin: 0;
}
</style>
