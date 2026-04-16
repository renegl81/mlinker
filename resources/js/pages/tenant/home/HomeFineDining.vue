<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import MenuSeoHead from '@/components/public/MenuSeoHead.vue';
import OpeningHoursDisplay from './components/OpeningHoursDisplay.vue';
import SocialLinks from './components/SocialLinks.vue';
import LocationCard from './components/LocationCard.vue';
import { mapsDirectionsUrl, mapsEmbedUrl, groupOpeningHours, type OpeningHour } from '@/composables/useTenantHome';

interface Menu {
    id: number;
    name: string;
    description?: string | null;
    image_path?: string | null;
}

interface Location {
    id: number;
    name: string;
    address?: string | null;
    city?: string | null;
    phone?: string | null;
    description?: string | null;
    image_url?: string | null;
    logo_url?: string | null;
    social_medias?: unknown;
    latitude?: number | null;
    longitude?: number | null;
    menus?: Menu[];
    opening_hours?: OpeningHour[];
}

interface SeoData {
    title: string;
    description: string;
    image: string | null;
    url: string;
    jsonLd: Record<string, unknown>;
}

const props = defineProps<{
    tenant: { id: string; name: string; businessType: string };
    locations: Location[];
    primaryLocation: Location | null;
    isMultiLocation: boolean;
    seo: SeoData;
}>();

const loc = computed(() => props.primaryLocation);
const hero = computed(() => loc.value?.image_url ?? null);
const logo = computed(() => loc.value?.logo_url ?? null);
const dirUrl = computed(() => loc.value ? mapsDirectionsUrl({ lat: loc.value.latitude, lng: loc.value.longitude, address: loc.value.address }) : null);
const embedUrl = computed(() => loc.value ? mapsEmbedUrl({ lat: loc.value.latitude, lng: loc.value.longitude, address: loc.value.address, name: loc.value.name }) : null);
const hoursGrouped = computed(() => loc.value?.opening_hours ? groupOpeningHours(loc.value.opening_hours) : []);
const hasHours = computed(() => loc.value?.opening_hours && loc.value.opening_hours.length > 0);
const hasMenus = computed(() => loc.value?.menus && loc.value.menus.length > 0);
const hasSocial = computed(() => !!loc.value?.social_medias && Object.keys(loc.value.social_medias as object).length > 0);

/* Números de orden para los menús en estilo fine-dining */
const menuOrdinals = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X'];
</script>

<template>
    <MenuSeoHead :meta="seo" :json-ld="(seo.jsonLd as any)" />
    <Head>
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
        <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;1,300;1,400&family=Jost:wght@200;300;400;500&display=swap" rel="stylesheet" />
    </Head>

    <div class="fd">
        <!-- ═══ HERO ═══ -->
        <header class="fd-hero">
            <div v-if="hero" class="fd-hero-img" :style="{ backgroundImage: `url(${hero})` }" />
            <div class="fd-hero-overlay" />
            <div class="fd-hero-inner">
                <img v-if="logo" :src="logo" :alt="loc?.name" class="fd-logo" />
                <p v-if="loc?.city" class="fd-kicker">{{ loc.city }}</p>
                <!-- Linea ornamental -->
                <div class="fd-ornament" aria-hidden="true"><span /><span class="fd-ornament-diamond" /><span /></div>
                <h1 class="fd-title">{{ loc?.name ?? tenant.name }}</h1>
                <p v-if="loc?.description" class="fd-sub">{{ loc.description }}</p>
                <div class="fd-hero-actions">
                    <a v-if="loc?.phone" :href="`tel:${loc.phone}`" class="fd-btn fd-btn-primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        Reservas
                    </a>
                    <a v-if="dirUrl" :href="dirUrl" target="_blank" rel="noopener" class="fd-btn fd-btn-outline">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        Cómo llegar
                    </a>
                </div>
            </div>
        </header>

        <template v-if="!isMultiLocation && loc">
            <!-- ═══ HORARIOS ═══ -->
            <section v-if="hasHours" class="fd-section fd-hours-section">
                <div class="fd-container">
                    <div class="fd-section-header">
                        <h2 class="fd-section-title">Horario</h2>
                        <div class="fd-rule" aria-hidden="true" />
                    </div>
                    <div class="fd-hours-compact">
                        <div v-for="(group, i) in hoursGrouped" :key="i" class="fd-hours-row" :class="{ 'is-closed': group.isClosed }">
                            <span class="fd-hours-range">{{ group.range }}</span>
                            <span class="fd-hours-dots" aria-hidden="true" />
                            <span class="fd-hours-value">{{ group.hours }}</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ═══ NUESTRAS CARTAS — lista numerada en estilo fine-dining ═══ -->
            <section v-if="hasMenus" class="fd-section fd-menus-section">
                <div class="fd-container">
                    <div class="fd-section-header">
                        <h2 class="fd-section-title">Menús</h2>
                        <div class="fd-rule" aria-hidden="true" />
                    </div>
                    <p class="fd-section-subtitle">Una experiencia gastronómica de autor</p>
                    <nav class="fd-menu-list" aria-label="Cartas">
                        <a v-for="(menu, idx) in loc.menus" :key="menu.id" :href="`/menu/${menu.id}`" class="fd-menu-item">
                            <span class="fd-menu-ordinal">{{ menuOrdinals[idx] ?? (idx + 1) }}</span>
                            <span class="fd-menu-item-dots" aria-hidden="true" />
                            <span class="fd-menu-item-content">
                                <span class="fd-menu-item-name">{{ menu.name }}</span>
                                <span v-if="menu.description" class="fd-menu-item-desc">{{ menu.description }}</span>
                            </span>
                            <span class="fd-menu-item-arrow" aria-hidden="true">→</span>
                        </a>
                    </nav>
                </div>
            </section>

            <!-- ═══ UBICACIÓN Y CONTACTO ═══ -->
            <section class="fd-section fd-location-section">
                <div class="fd-container">
                    <div class="fd-section-header">
                        <h2 class="fd-section-title">Encuéntranos</h2>
                        <div class="fd-rule" aria-hidden="true" />
                    </div>
                    <div class="fd-location-grid">
                        <div class="fd-map-wrap">
                            <iframe v-if="embedUrl" :src="embedUrl" class="fd-map" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen />
                            <div v-else class="fd-map-placeholder">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            </div>
                        </div>
                        <div class="fd-contact-info">
                            <address class="fd-address">
                                <div v-if="loc.address" class="fd-contact-row">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                    <div>
                                        <p>{{ loc.address }}</p>
                                        <p v-if="loc.city">{{ loc.city }}</p>
                                    </div>
                                </div>
                                <div v-if="loc.phone" class="fd-contact-row">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                    <a :href="`tel:${loc.phone}`">{{ loc.phone }}</a>
                                </div>
                            </address>
                            <a v-if="dirUrl" :href="dirUrl" target="_blank" rel="noopener" class="fd-btn fd-btn-primary fd-btn-full">Cómo llegar</a>
                            <div v-if="hasHours" class="fd-contact-hours">
                                <h3 class="fd-contact-hours-title">Horario completo</h3>
                                <OpeningHoursDisplay :hours="loc.opening_hours!" />
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ═══ REDES SOCIALES ═══ -->
            <section v-if="hasSocial" class="fd-section fd-social-section">
                <div class="fd-container">
                    <div class="fd-section-header"><h2 class="fd-section-title">Síguenos</h2><div class="fd-rule" aria-hidden="true" /></div>
                    <SocialLinks :social-medias="loc.social_medias" size="lg" class="fd-social-grid" />
                </div>
            </section>
        </template>

        <!-- ═══ MULTI-LOCATION ═══ -->
        <template v-else-if="isMultiLocation">
            <section class="fd-section fd-multi-section">
                <div class="fd-container">
                    <div class="fd-section-header"><h2 class="fd-section-title">Nuestros Locales</h2><div class="fd-rule" aria-hidden="true" /></div>
                    <p class="fd-section-subtitle">{{ locations.length }} establecimientos para servirte</p>
                    <div class="fd-locations-grid">
                        <LocationCard v-for="l in locations" :key="l.id" :location="l" />
                    </div>
                </div>
            </section>
        </template>

        <!-- ═══ FOOTER ═══ -->
        <footer class="fd-footer">
            <div class="fd-container fd-footer-inner">
                <div class="fd-ornament fd-ornament-sm" aria-hidden="true"><span /><span class="fd-ornament-diamond" /><span /></div>
                <div class="fd-footer-brand">
                    <img v-if="logo" :src="logo" :alt="loc?.name" class="fd-footer-logo" />
                    <div>
                        <p class="fd-footer-name">{{ loc?.name ?? tenant.name }}</p>
                        <p v-if="loc?.address" class="fd-footer-line">{{ loc.address }}{{ loc?.city ? `, ${loc.city}` : '' }}</p>
                        <p v-if="loc?.phone" class="fd-footer-line">{{ loc.phone }}</p>
                    </div>
                </div>
                <SocialLinks v-if="hasSocial" :social-medias="loc!.social_medias" size="sm" class="fd-footer-socials" />
                <p class="fd-footer-copy">Página creada con <a href="https://menulinker.com" target="_blank" rel="noopener">MenuLinker</a></p>
            </div>
        </footer>
    </div>
</template>

<style scoped>
/* ── TOKENS ── */
.fd {
    --bg: oklch(0.975 0.008 85);
    --surface: oklch(1 0.004 82);
    --surface-2: oklch(0.965 0.010 82);
    --ink: oklch(0.18 0.008 260);
    --ink-soft: oklch(0.38 0.008 255);
    --ink-faint: oklch(0.58 0.006 255);
    --rule: oklch(0.86 0.010 80);
    --gold: oklch(0.62 0.12 75);
    --gold-light: oklch(0.78 0.10 78);
    --gold-faint: oklch(0.62 0.12 75 / 0.12);
    --accent: oklch(0.62 0.12 75);
    --accent-light: oklch(0.62 0.12 75 / 0.08);
    --serif: 'Cormorant Garamond', 'Garamond', Georgia, serif;
    --sans: 'Jost', ui-sans-serif, system-ui, sans-serif;
    --radius: 0px;
    min-height: 100vh;
    background: var(--bg);
    color: var(--ink);
    font-family: var(--sans);
    font-weight: 300;
    -webkit-font-smoothing: antialiased;
    letter-spacing: 0.01em;
}

@media (prefers-color-scheme: dark) {
    .fd {
        --bg: oklch(0.14 0.008 265);
        --surface: oklch(0.17 0.009 263);
        --surface-2: oklch(0.15 0.008 265);
        --ink: oklch(0.96 0.006 82);
        --ink-soft: oklch(0.74 0.008 78);
        --ink-faint: oklch(0.54 0.006 75);
        --rule: oklch(0.26 0.008 265);
        --gold: oklch(0.70 0.12 78);
        --gold-light: oklch(0.80 0.10 80);
        --gold-faint: oklch(0.70 0.12 78 / 0.14);
        --accent: oklch(0.70 0.12 78);
        --accent-light: oklch(0.70 0.12 78 / 0.08);
    }
}

.fd-container {
    max-width: 880px;
    margin: 0 auto;
    padding: 0 clamp(1.5rem, 6vw, 3rem);
}

/* ── HERO ── */
.fd-hero {
    position: relative;
    min-height: 100svh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: clamp(4rem, 10vw, 6rem) clamp(1.5rem, 6vw, 3rem);
    overflow: hidden;
    text-align: center;
}

.fd-hero-img {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center;
    filter: saturate(0.65) brightness(0.55);
    /* Zoom suave continuo */
    animation: fd-hero-zoom 20s ease-in-out infinite alternate;
}

.fd-hero-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, oklch(0 0 0 / 0.2) 0%, oklch(0 0 0 / 0.45) 60%, oklch(0 0 0 / 0.65) 100%);
}

.fd-hero:not(:has(.fd-hero-img)) {
    background: var(--surface-2);
    min-height: clamp(400px, 65svh, 640px);
}

.fd-hero:not(:has(.fd-hero-img)) .fd-hero-overlay { display: none; }
.fd-hero:not(:has(.fd-hero-img)) .fd-hero-img { display: none; }
.fd-hero:not(:has(.fd-hero-img)) .fd-hero-inner { color: var(--ink); }

.fd-hero-inner {
    position: relative;
    z-index: 2;
    max-width: 680px;
    color: oklch(0.97 0.005 82);
    animation: fd-fade-in 1200ms cubic-bezier(0.4, 0, 0.2, 1) both;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.fd-logo {
    width: 64px;
    height: 64px;
    object-fit: contain;
    margin-bottom: 2rem;
    /* Sin border-radius — estética austera */
    background: oklch(1 0 0 / 0.08);
    padding: 8px;
}

.fd-kicker {
    font-family: var(--sans);
    font-weight: 200;
    font-size: 0.72rem;
    letter-spacing: 0.35em;
    text-transform: uppercase;
    margin: 0 0 1.5rem;
    opacity: 0.7;
}

/* ── Ornamento dorado ── */
.fd-ornament {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
    width: 100%;
    justify-content: center;
}

.fd-ornament span:not(.fd-ornament-diamond) {
    flex: 1;
    height: 1px;
    background: currentColor;
    opacity: 0.3;
    max-width: 80px;
}

.fd-ornament-diamond {
    width: 6px;
    height: 6px;
    background: currentColor;
    transform: rotate(45deg);
    opacity: 0.6;
    flex: none !important;
}

.fd-ornament-sm {
    color: var(--gold);
    margin-bottom: 1.5rem;
}

.fd-ornament-sm span:not(.fd-ornament-diamond) { max-width: 48px; }

.fd-title {
    font-family: var(--serif);
    font-weight: 300;
    font-size: clamp(2.8rem, 9vw, 5.5rem);
    line-height: 1.0;
    letter-spacing: 0.03em;
    margin: 0;
    font-style: italic;
}

.fd-sub {
    margin: 1.75rem 0 0;
    font-family: var(--sans);
    font-size: clamp(0.85rem, 1.8vw, 0.95rem);
    line-height: 1.8;
    max-width: 44ch;
    opacity: 0.75;
    font-weight: 200;
    letter-spacing: 0.04em;
}

.fd-hero-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-top: 2.5rem;
    justify-content: center;
}

/* ── BUTTONS — austeros, sin bordes redondeados ── */
.fd-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.9rem 2rem;
    border-radius: 0;
    font-family: var(--sans);
    font-size: 0.78rem;
    font-weight: 400;
    letter-spacing: 0.18em;
    text-decoration: none;
    text-transform: uppercase;
    transition: background 250ms, color 250ms, border-color 250ms;
    border: none;
    cursor: pointer;
}

.fd-btn svg { width: 16px; height: 16px; flex-shrink: 0; }

.fd-btn-primary {
    background: var(--gold);
    color: oklch(0.99 0 0);
}

.fd-btn-primary:hover { background: var(--gold-light); }

.fd-btn-outline {
    background: transparent;
    color: oklch(0.97 0 0);
    border: 1px solid oklch(1 0 0 / 0.35);
}

.fd-btn-outline:hover {
    background: oklch(1 0 0 / 0.08);
    border-color: oklch(1 0 0 / 0.6);
}

.fd-btn-full { width: 100%; justify-content: center; }

/* ── SECTIONS ── */
.fd-section { padding: clamp(4rem, 9vw, 7rem) 0; }
.fd-section + .fd-section { border-top: 1px solid var(--rule); }

.fd-section-header {
    display: flex;
    align-items: baseline;
    gap: 1.5rem;
    margin-bottom: 2.5rem;
}

/* Línea que se extiende a la derecha del título */
.fd-rule {
    flex: 1;
    height: 1px;
    background: var(--gold);
    opacity: 0.35;
    align-self: center;
}

.fd-section-title {
    font-family: var(--serif);
    font-weight: 300;
    font-size: clamp(1.5rem, 3.5vw, 2rem);
    line-height: 1.1;
    letter-spacing: 0.05em;
    margin: 0;
    color: var(--ink);
    white-space: nowrap;
}

.fd-section-subtitle {
    margin: -1.5rem 0 2.5rem;
    font-size: 0.82rem;
    color: var(--ink-faint);
    line-height: 1.6;
    font-style: italic;
    letter-spacing: 0.06em;
}

/* ── HOURS ── */
.fd-hours-section { background: var(--surface); }

.fd-hours-compact {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
    max-width: 480px;
}

.fd-hours-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 0.88rem;
    padding-bottom: 0.6rem;
    border-bottom: 1px solid var(--rule);
}

.fd-hours-row:last-child { border-bottom: none; }

.fd-hours-range {
    font-family: var(--sans);
    font-weight: 300;
    min-width: 6rem;
    color: var(--ink);
    letter-spacing: 0.04em;
}

.fd-hours-dots {
    flex: 1;
    border-bottom: 1px dotted var(--rule);
    margin-bottom: 2px;
}

.fd-hours-value {
    color: var(--ink-soft);
    font-variant-numeric: tabular-nums;
    font-weight: 300;
    text-align: right;
}

.fd-hours-row.is-closed .fd-hours-value { opacity: 0.4; font-style: italic; }

/* ── MENU LIST — estilo lista numerada fine-dining ── */
.fd-menu-list {
    display: flex;
    flex-direction: column;
    gap: 0;
    border-top: 1px solid var(--rule);
}

.fd-menu-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.5rem 0;
    border-bottom: 1px solid var(--rule);
    text-decoration: none;
    color: inherit;
    transition: background 200ms;
    position: relative;
}

.fd-menu-item:hover { background: var(--gold-faint); }

.fd-menu-ordinal {
    font-family: var(--serif);
    font-size: 0.75rem;
    font-weight: 300;
    color: var(--gold);
    letter-spacing: 0.1em;
    min-width: 1.5rem;
    text-align: center;
}

.fd-menu-item-dots {
    flex: 1;
    border-bottom: 1px dotted var(--rule);
    height: 1px;
    align-self: center;
}

.fd-menu-item-content {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
    text-align: right;
    flex: 3;
}

.fd-menu-item-name {
    font-family: var(--serif);
    font-weight: 400;
    font-size: 1.2rem;
    color: var(--ink);
    letter-spacing: 0.02em;
}

.fd-menu-item-desc {
    font-size: 0.78rem;
    color: var(--ink-faint);
    font-style: italic;
    letter-spacing: 0.04em;
    line-height: 1.5;
}

.fd-menu-item-arrow {
    font-family: var(--sans);
    font-weight: 200;
    font-size: 0.85rem;
    color: var(--gold);
    opacity: 0;
    transition: opacity 200ms, transform 200ms;
    margin-left: 0.5rem;
}

.fd-menu-item:hover .fd-menu-item-arrow {
    opacity: 1;
    transform: translateX(4px);
}

/* ── LOCATION + MAP ── */
.fd-location-section { background: var(--surface-2); }

.fd-location-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 3rem;
}

@media (min-width: 768px) { .fd-location-grid { grid-template-columns: 1.2fr 1fr; } }

/* Mapa mínimo, discreto */
.fd-map-wrap {
    aspect-ratio: 4/3;
    overflow: hidden;
    background: var(--surface);
    border: 1px solid var(--rule);
}

.fd-map { width: 100%; height: 100%; border: 0; filter: saturate(0.5); }

.fd-map-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--ink-faint);
    opacity: 0.15;
}

.fd-map-placeholder svg { width: 3rem; height: 3rem; }

.fd-contact-info { display: flex; flex-direction: column; gap: 2rem; }

.fd-address { font-style: normal; display: flex; flex-direction: column; gap: 1.25rem; }

.fd-contact-row {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    font-size: 0.88rem;
    color: var(--ink);
    line-height: 1.7;
    font-weight: 300;
    letter-spacing: 0.03em;
}

.fd-contact-row svg { width: 16px; height: 16px; color: var(--gold); flex-shrink: 0; margin-top: 3px; }
.fd-contact-row a { color: var(--gold); text-decoration: none; font-weight: 400; }
.fd-contact-row a:hover { text-decoration: underline; }

.fd-contact-hours { margin-top: 0.5rem; }

.fd-contact-hours-title {
    font-family: var(--serif);
    font-weight: 300;
    font-size: 0.9rem;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    margin: 0 0 1rem;
    color: var(--gold);
}

/* ── SOCIAL ── */
.fd-social-section { background: var(--surface); text-align: center; }
.fd-social-section .fd-section-header { justify-content: center; }
.fd-social-section .fd-rule { display: none; }
.fd-social-grid { justify-content: center; }

/* ── MULTI-LOCATION ── */
.fd-locations-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.5rem;
}

@media (min-width: 640px) { .fd-locations-grid { grid-template-columns: repeat(2, 1fr); } }
@media (min-width: 1024px) { .fd-locations-grid { grid-template-columns: repeat(3, 1fr); } }

/* ── FOOTER ── */
.fd-footer {
    padding: clamp(3.5rem, 7vw, 5rem) 0;
    border-top: 1px solid var(--rule);
    background: var(--surface);
}

.fd-footer-inner {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1.25rem;
    text-align: center;
}

.fd-footer-brand { display: flex; align-items: center; gap: 1.25rem; }

.fd-footer-logo {
    width: 44px;
    height: 44px;
    object-fit: contain;
    /* Sin border-radius */
}

.fd-footer-name {
    font-family: var(--serif);
    font-weight: 300;
    font-size: 1.15rem;
    letter-spacing: 0.06em;
    font-style: italic;
    margin: 0;
    color: var(--ink);
}

.fd-footer-line { margin: 0.1rem 0; font-size: 0.78rem; color: var(--ink-faint); letter-spacing: 0.05em; font-weight: 200; }
.fd-footer-copy { font-size: 0.68rem; color: var(--ink-faint); margin-top: 0.5rem; letter-spacing: 0.06em; }
.fd-footer-copy a { color: var(--gold); text-decoration: none; }
.fd-footer-copy a:hover { text-decoration: underline; }

/* ── ANIMATIONS ── */
@keyframes fd-hero-zoom {
    from { transform: scale(1); }
    to   { transform: scale(1.03); }
}

@keyframes fd-fade-in {
    from { opacity: 0; }
    to   { opacity: 1; }
}

@media (prefers-reduced-motion: reduce) {
    .fd-hero-img { animation: none !important; }
    .fd-hero-inner { animation: none !important; }
    .fd-menu-item,
    .fd-menu-item-arrow,
    .fd-btn { transition: none !important; }
}
</style>
