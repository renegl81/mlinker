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
</script>

<template>
    <MenuSeoHead :meta="seo" :json-ld="(seo.jsonLd as any)" />
    <Head>
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
        <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Open+Sans:wght@300;400;600&display=swap" rel="stylesheet" />
    </Head>

    <div class="ff">
        <!-- ═══ HERO ═══ -->
        <header class="ff-hero">
            <div v-if="hero" class="ff-hero-img" :style="{ backgroundImage: `url(${hero})` }" />
            <div class="ff-hero-overlay" />
            <!-- Pattern geométrico de fondo (sólo sin imagen) -->
            <div class="ff-hero-pattern" aria-hidden="true" />
            <div class="ff-hero-inner">
                <img v-if="logo" :src="logo" :alt="loc?.name" class="ff-logo" />
                <p v-if="loc?.city" class="ff-kicker">{{ loc.city }}</p>
                <h1 class="ff-title">{{ loc?.name ?? tenant.name }}</h1>
                <p v-if="loc?.description" class="ff-sub">{{ loc.description }}</p>
                <div class="ff-hero-actions">
                    <a v-if="loc?.phone" :href="`tel:${loc.phone}`" class="ff-btn ff-btn-primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        Llamar
                    </a>
                    <a v-if="dirUrl" :href="dirUrl" target="_blank" rel="noopener" class="ff-btn ff-btn-outline">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        Cómo llegar
                    </a>
                </div>
            </div>
        </header>

        <template v-if="!isMultiLocation && loc">
            <!-- ═══ HORARIOS ═══ -->
            <section v-if="hasHours" class="ff-section ff-hours-section">
                <div class="ff-container">
                    <div class="ff-section-header">
                        <span class="ff-section-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        </span>
                        <h2 class="ff-section-title">Horario</h2>
                    </div>
                    <div class="ff-hours-compact">
                        <div v-for="(group, i) in hoursGrouped" :key="i" class="ff-hours-row" :class="{ 'is-closed': group.isClosed }">
                            <span class="ff-hours-range">{{ group.range }}</span>
                            <span class="ff-hours-value">{{ group.hours }}</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ═══ NUESTRAS CARTAS ═══ -->
            <section v-if="hasMenus" class="ff-section ff-menus-section">
                <div class="ff-container">
                    <div class="ff-section-header">
                        <span class="ff-section-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
                        </span>
                        <h2 class="ff-section-title">Nuestras Cartas</h2>
                    </div>
                    <p class="ff-section-subtitle">¡Elige y pide ya!</p>
                    <div class="ff-menu-cards">
                        <a v-for="menu in loc.menus" :key="menu.id" :href="`/menu/${menu.id}`" class="ff-menu-card">
                            <div v-if="menu.image_path" class="ff-menu-card-img-wrap">
                                <img :src="menu.image_path" :alt="menu.name" class="ff-menu-card-img" loading="lazy" />
                            </div>
                            <div v-else class="ff-menu-card-img-wrap ff-menu-card-placeholder">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
                            </div>
                            <div class="ff-menu-card-body">
                                <h3 class="ff-menu-card-name">{{ menu.name }}</h3>
                                <p v-if="menu.description" class="ff-menu-card-desc">{{ menu.description }}</p>
                                <span class="ff-menu-card-cta">Ver carta →</span>
                            </div>
                        </a>
                    </div>
                </div>
            </section>

            <!-- ═══ UBICACIÓN Y CONTACTO ═══ -->
            <section class="ff-section ff-location-section">
                <div class="ff-container">
                    <div class="ff-section-header">
                        <span class="ff-section-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        </span>
                        <h2 class="ff-section-title">Encuéntranos</h2>
                    </div>
                    <div class="ff-location-grid">
                        <div class="ff-map-wrap">
                            <iframe v-if="embedUrl" :src="embedUrl" class="ff-map" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen />
                            <div v-else class="ff-map-placeholder">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            </div>
                        </div>
                        <div class="ff-contact-info">
                            <address class="ff-address">
                                <div v-if="loc.address" class="ff-contact-row">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                    <div>
                                        <p>{{ loc.address }}</p>
                                        <p v-if="loc.city">{{ loc.city }}</p>
                                    </div>
                                </div>
                                <div v-if="loc.phone" class="ff-contact-row">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                    <a :href="`tel:${loc.phone}`">{{ loc.phone }}</a>
                                </div>
                            </address>
                            <a v-if="dirUrl" :href="dirUrl" target="_blank" rel="noopener" class="ff-btn ff-btn-primary ff-btn-full">Cómo llegar</a>
                            <div v-if="hasHours" class="ff-contact-hours">
                                <h3 class="ff-contact-hours-title">Horario completo</h3>
                                <OpeningHoursDisplay :hours="loc.opening_hours!" />
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ═══ REDES SOCIALES ═══ -->
            <section v-if="hasSocial" class="ff-section ff-social-section">
                <div class="ff-container">
                    <div class="ff-section-header"><h2 class="ff-section-title">Síguenos</h2></div>
                    <SocialLinks :social-medias="loc.social_medias" size="lg" class="ff-social-grid" />
                </div>
            </section>
        </template>

        <!-- ═══ MULTI-LOCATION ═══ -->
        <template v-else-if="isMultiLocation">
            <section class="ff-section ff-multi-section">
                <div class="ff-container">
                    <div class="ff-section-header"><h2 class="ff-section-title">Nuestros Locales</h2></div>
                    <p class="ff-section-subtitle">{{ locations.length }} establecimientos para servirte</p>
                    <div class="ff-locations-grid">
                        <LocationCard v-for="l in locations" :key="l.id" :location="l" />
                    </div>
                </div>
            </section>
        </template>

        <!-- ═══ FOOTER ═══ -->
        <footer class="ff-footer">
            <div class="ff-container ff-footer-inner">
                <div class="ff-footer-brand">
                    <img v-if="logo" :src="logo" :alt="loc?.name" class="ff-footer-logo" />
                    <div>
                        <p class="ff-footer-name">{{ loc?.name ?? tenant.name }}</p>
                        <p v-if="loc?.address" class="ff-footer-line">{{ loc.address }}{{ loc?.city ? `, ${loc.city}` : '' }}</p>
                        <p v-if="loc?.phone" class="ff-footer-line">{{ loc.phone }}</p>
                    </div>
                </div>
                <SocialLinks v-if="hasSocial" :social-medias="loc!.social_medias" size="sm" class="ff-footer-socials" />
                <p class="ff-footer-copy">Página creada con <a href="https://menulinker.com" target="_blank" rel="noopener">MenuLinker</a></p>
            </div>
        </footer>
    </div>
</template>

<style scoped>
/* ── TOKENS ── */
.ff {
    --bg: oklch(0.98 0.005 80);
    --surface: oklch(1 0 0);
    --surface-2: oklch(0.96 0.008 85);
    --ink: oklch(0.1 0.008 30);
    --ink-soft: oklch(0.32 0.010 28);
    --ink-faint: oklch(0.55 0.008 30);
    --rule: oklch(0.88 0.006 80);
    --yellow: oklch(0.90 0.16 95);
    --red: oklch(0.52 0.20 28);
    --red-dark: oklch(0.42 0.18 26);
    --accent: oklch(0.52 0.20 28);
    --accent-light: oklch(0.52 0.20 28 / 0.08);
    --serif: 'Oswald', 'Impact', 'Arial Black', sans-serif;
    --sans: 'Open Sans', ui-sans-serif, system-ui, sans-serif;
    --radius: 6px;
    --radius-sm: 4px;
    min-height: 100vh;
    background: var(--bg);
    color: var(--ink);
    font-family: var(--sans);
    font-weight: 400;
    -webkit-font-smoothing: antialiased;
}

@media (prefers-color-scheme: dark) {
    .ff {
        --bg: oklch(0.10 0.008 30);
        --surface: oklch(0.14 0.010 30);
        --surface-2: oklch(0.12 0.008 30);
        --ink: oklch(0.97 0.005 85);
        --ink-soft: oklch(0.76 0.008 80);
        --ink-faint: oklch(0.55 0.006 78);
        --rule: oklch(0.22 0.010 30);
        --yellow: oklch(0.88 0.16 95);
        --red: oklch(0.58 0.20 28);
        --red-dark: oklch(0.68 0.18 28);
        --accent: oklch(0.58 0.20 28);
        --accent-light: oklch(0.58 0.20 28 / 0.12);
    }
}

.ff-container {
    max-width: 1040px;
    margin: 0 auto;
    padding: 0 clamp(1.25rem, 5vw, 2.5rem);
}

/* ── HERO ── */
.ff-hero {
    position: relative;
    min-height: clamp(400px, 72svh, 680px);
    display: flex;
    align-items: center;
    padding: clamp(3rem, 8vw, 5rem) clamp(1.25rem, 5vw, 2.5rem);
    overflow: hidden;
}

.ff-hero-img {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center;
    filter: saturate(1.1) brightness(0.7);
}

.ff-hero-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(110deg, oklch(0 0 0 / 0.75) 0%, oklch(0 0 0 / 0.4) 60%, oklch(0 0 0 / 0.1) 100%);
}

/* Pattern geométrico retro: visible sólo sin imagen */
.ff-hero-pattern {
    position: absolute;
    inset: 0;
    background-image:
        linear-gradient(45deg, var(--yellow) 25%, transparent 25%),
        linear-gradient(-45deg, var(--yellow) 25%, transparent 25%),
        linear-gradient(45deg, transparent 75%, var(--red) 75%),
        linear-gradient(-45deg, transparent 75%, var(--red) 75%);
    background-size: 40px 40px;
    background-position: 0 0, 0 20px, 20px -20px, -20px 0;
    opacity: 0.08;
    pointer-events: none;
}

.ff-hero:has(.ff-hero-img) .ff-hero-pattern { display: none; }

.ff-hero:not(:has(.ff-hero-img)) {
    background: var(--yellow);
    min-height: clamp(360px, 65svh, 580px);
}

.ff-hero:not(:has(.ff-hero-img)) .ff-hero-overlay { display: none; }
.ff-hero:not(:has(.ff-hero-img)) .ff-hero-inner { color: var(--ink); }

.ff-hero-inner {
    position: relative;
    z-index: 2;
    max-width: 720px;
    color: oklch(0.99 0 0);
    animation: ff-slide-in 600ms cubic-bezier(0.2, 0.65, 0.2, 1) both;
}

.ff-logo {
    width: 72px;
    height: 72px;
    object-fit: contain;
    border-radius: var(--radius);
    margin-bottom: 1.25rem;
    border: 3px solid var(--yellow);
    padding: 6px;
    background: oklch(0 0 0 / 0.2);
}

.ff-kicker {
    font-family: var(--sans);
    font-weight: 600;
    font-size: 0.75rem;
    letter-spacing: 0.25em;
    text-transform: uppercase;
    margin: 0 0 0.6rem;
    color: var(--yellow);
}

.ff-title {
    font-family: var(--serif);
    font-weight: 700;
    font-size: clamp(3.5rem, 12vw, 7.5rem);
    line-height: 0.88;
    letter-spacing: 0.02em;
    text-transform: uppercase;
    margin: 0;
}

.ff-sub {
    margin: 1.25rem 0 0;
    font-size: clamp(0.95rem, 2vw, 1.05rem);
    line-height: 1.65;
    max-width: 50ch;
    opacity: 0.9;
    font-weight: 300;
}

.ff-hero-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-top: 2rem;
}

/* ── BUTTONS — offset shadow pop style ── */
.ff-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.55rem;
    padding: 0.85rem 1.75rem;
    border-radius: var(--radius);
    font-family: var(--serif);
    font-size: 0.95rem;
    font-weight: 600;
    letter-spacing: 0.04em;
    text-decoration: none;
    text-transform: uppercase;
    transition: transform 150ms, box-shadow 150ms;
    border: none;
    cursor: pointer;
}

.ff-btn svg { width: 18px; height: 18px; flex-shrink: 0; }

/* Offset solid shadow — estilo retro/pop */
.ff-btn-primary {
    background: var(--yellow);
    color: var(--ink);
    box-shadow: 4px 4px 0 var(--red);
}

.ff-btn-primary:hover {
    transform: translate(-2px, -2px);
    box-shadow: 6px 6px 0 var(--red);
}

.ff-btn-primary:active {
    transform: translate(2px, 2px);
    box-shadow: 2px 2px 0 var(--red);
}

.ff-btn-outline {
    background: transparent;
    color: var(--yellow);
    border: 3px solid var(--yellow);
    box-shadow: 4px 4px 0 oklch(0.90 0.16 95 / 0.4);
}

.ff-btn-outline:hover {
    transform: translate(-2px, -2px);
    box-shadow: 6px 6px 0 oklch(0.90 0.16 95 / 0.4);
}

.ff-btn-full { width: 100%; justify-content: center; }

/* ── SECTIONS ── */
.ff-section { padding: clamp(3rem, 7vw, 5.5rem) 0; }
.ff-section + .ff-section { border-top: 3px solid var(--ink); }

.ff-section-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.ff-section-icon {
    width: 42px;
    height: 42px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--yellow);
    border-radius: var(--radius-sm);
    color: var(--ink);
    border: 2px solid var(--ink);
    box-shadow: 3px 3px 0 var(--ink);
    flex-shrink: 0;
}

.ff-section-icon svg { width: 20px; height: 20px; }

.ff-section-title {
    font-family: var(--serif);
    font-weight: 700;
    font-size: clamp(1.75rem, 5vw, 2.75rem);
    line-height: 1.0;
    letter-spacing: 0.03em;
    text-transform: uppercase;
    margin: 0;
    color: var(--ink);
}

.ff-section-subtitle {
    margin: -0.25rem 0 2rem;
    padding-left: calc(42px + 1rem);
    font-size: 0.9rem;
    color: var(--ink-soft);
    line-height: 1.5;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

/* ── HOURS ── */
.ff-hours-section {
    background: var(--yellow);
    border-top: 3px solid var(--ink);
    border-bottom: 3px solid var(--ink);
}

.ff-hours-compact {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem 2.5rem;
    padding-left: calc(42px + 1rem);
}

.ff-hours-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 0.95rem;
}

.ff-hours-range { font-weight: 700; min-width: 5rem; color: var(--ink); font-family: var(--serif); letter-spacing: 0.03em; text-transform: uppercase; }
.ff-hours-value { color: var(--ink-soft); font-variant-numeric: tabular-nums; }
.ff-hours-row.is-closed .ff-hours-value { opacity: 0.5; font-style: italic; }

/* ── MENU CARDS ── */
.ff-menu-cards {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.25rem;
}

@media (min-width: 560px) { .ff-menu-cards { grid-template-columns: repeat(2, 1fr); } }
@media (min-width: 900px) { .ff-menu-cards { grid-template-columns: repeat(3, 1fr); } }

.ff-menu-card {
    background: var(--surface);
    border: 3px solid var(--ink);
    border-radius: var(--radius);
    overflow: hidden;
    text-decoration: none;
    color: inherit;
    transition: transform 150ms, box-shadow 150ms;
    box-shadow: 5px 5px 0 var(--ink);
}

.ff-menu-card:hover {
    transform: translate(-3px, -3px) rotate(-0.5deg);
    box-shadow: 8px 8px 0 var(--ink);
}

.ff-menu-card:active {
    transform: translate(2px, 2px) rotate(0.2deg);
    box-shadow: 3px 3px 0 var(--ink);
}

.ff-menu-card-img-wrap {
    aspect-ratio: 16/10;
    overflow: hidden;
    background: var(--surface-2);
    border-bottom: 3px solid var(--ink);
}

.ff-menu-card-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 400ms cubic-bezier(0.2, 0.65, 0.2, 1);
}

.ff-menu-card:hover .ff-menu-card-img { transform: scale(1.05); }

.ff-menu-card-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--ink-faint);
    opacity: 0.3;
}

.ff-menu-card-placeholder svg { width: 3rem; height: 3rem; }

.ff-menu-card-body { padding: 1.1rem 1.25rem 1.4rem; }

.ff-menu-card-name {
    font-family: var(--serif);
    font-weight: 700;
    font-size: 1.15rem;
    letter-spacing: 0.03em;
    text-transform: uppercase;
    margin: 0 0 0.4rem;
    color: var(--ink);
}

.ff-menu-card-desc {
    font-size: 0.87rem;
    color: var(--ink-soft);
    line-height: 1.55;
    margin: 0 0 0.9rem;
}

.ff-menu-card-cta {
    display: inline-block;
    font-family: var(--serif);
    font-size: 0.82rem;
    font-weight: 600;
    color: oklch(0.99 0 0);
    background: var(--red);
    padding: 0.25rem 0.65rem;
    border-radius: var(--radius-sm);
    letter-spacing: 0.04em;
    text-transform: uppercase;
}

/* ── LOCATION + MAP ── */
.ff-location-section { background: var(--surface-2); }

.ff-location-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 2rem;
}

@media (min-width: 768px) { .ff-location-grid { grid-template-columns: 1.3fr 1fr; } }

.ff-map-wrap {
    border-radius: var(--radius);
    overflow: hidden;
    aspect-ratio: 16/10;
    background: var(--surface);
    border: 3px solid var(--ink);
    box-shadow: 5px 5px 0 var(--ink);
}

.ff-map { width: 100%; height: 100%; border: 0; }

.ff-map-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--ink-faint);
    opacity: 0.2;
}

.ff-map-placeholder svg { width: 4rem; height: 4rem; }

.ff-contact-info { display: flex; flex-direction: column; gap: 1.5rem; }
.ff-address { font-style: normal; display: flex; flex-direction: column; gap: 1rem; }

.ff-contact-row {
    display: flex;
    align-items: flex-start;
    gap: 0.85rem;
    font-size: 0.95rem;
    color: var(--ink);
    line-height: 1.5;
}

.ff-contact-row svg { width: 20px; height: 20px; color: var(--red); flex-shrink: 0; margin-top: 2px; }
.ff-contact-row a { color: var(--red); text-decoration: none; font-weight: 700; }
.ff-contact-row a:hover { text-decoration: underline; }

.ff-contact-hours { margin-top: 0.5rem; }

.ff-contact-hours-title {
    font-family: var(--serif);
    font-weight: 700;
    font-size: 1rem;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    margin: 0 0 0.75rem;
    color: var(--ink);
}

/* ── SOCIAL ── */
.ff-social-section { background: var(--surface); text-align: center; }
.ff-social-section .ff-section-header { justify-content: center; }
.ff-social-grid { justify-content: center; }

/* ── MULTI-LOCATION ── */
.ff-locations-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.5rem;
}

@media (min-width: 640px) { .ff-locations-grid { grid-template-columns: repeat(2, 1fr); } }
@media (min-width: 1024px) { .ff-locations-grid { grid-template-columns: repeat(3, 1fr); } }

/* ── FOOTER ── */
.ff-footer {
    padding: clamp(2.5rem, 5vw, 3.5rem) 0;
    background: var(--ink);
    border-top: 4px solid var(--yellow);
}

.ff-footer-inner {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1.5rem;
    text-align: center;
}

.ff-footer-brand { display: flex; align-items: center; gap: 1rem; }

.ff-footer-logo {
    width: 48px;
    height: 48px;
    object-fit: contain;
    border-radius: var(--radius-sm);
    border: 2px solid var(--yellow);
}

.ff-footer-name {
    font-family: var(--serif);
    font-weight: 700;
    font-size: 1.2rem;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    margin: 0;
    color: var(--yellow);
}

.ff-footer-line { margin: 0.15rem 0; font-size: 0.82rem; color: oklch(0.7 0.005 80); }
.ff-footer-copy { font-size: 0.72rem; color: oklch(0.55 0.005 78); margin-top: 0.5rem; }
.ff-footer-copy a { color: var(--yellow); text-decoration: none; }
.ff-footer-copy a:hover { text-decoration: underline; }

/* ── ANIMATIONS ── */
@keyframes ff-slide-in {
    from { opacity: 0; transform: translateX(-20px); }
    to   { opacity: 1; transform: translateX(0); }
}

@media (prefers-reduced-motion: reduce) {
    .ff-hero-inner { animation: none !important; }
    .ff-menu-card,
    .ff-menu-card-img,
    .ff-btn { transition: none !important; }
}
</style>
