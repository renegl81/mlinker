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
        <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Nunito:wght@300;400;600;700&display=swap" rel="stylesheet" />
    </Head>

    <div class="cf">
        <!-- ═══ HERO ═══ -->
        <header class="cf-hero">
            <div v-if="hero" class="cf-hero-img" :style="{ backgroundImage: `url(${hero})` }" />
            <div class="cf-hero-overlay" />
            <div class="cf-hero-inner">
                <img v-if="logo" :src="logo" :alt="loc?.name" class="cf-logo" />
                <p v-if="loc?.city" class="cf-kicker">{{ loc.city }}</p>
                <h1 class="cf-title">{{ loc?.name ?? tenant.name }}</h1>
                <p v-if="loc?.description" class="cf-sub">{{ loc.description }}</p>
                <div class="cf-hero-actions">
                    <a v-if="loc?.phone" :href="`tel:${loc.phone}`" class="cf-btn cf-btn-primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        Llamar
                    </a>
                    <a v-if="dirUrl" :href="dirUrl" target="_blank" rel="noopener" class="cf-btn cf-btn-outline">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        Cómo llegar
                    </a>
                </div>
            </div>
            <div class="cf-hero-wave" aria-hidden="true">
                <svg viewBox="0 0 1440 60" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg"><path d="M0,30 C360,60 1080,0 1440,30 L1440,60 L0,60 Z" fill="var(--bg)"/></svg>
            </div>
        </header>

        <template v-if="!isMultiLocation && loc">
            <!-- ═══ HORARIOS ═══ -->
            <section v-if="hasHours" class="cf-section cf-hours-section">
                <div class="cf-container">
                    <div class="cf-section-header">
                        <span class="cf-section-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        </span>
                        <h2 class="cf-section-title">Horario</h2>
                    </div>
                    <div class="cf-hours-compact">
                        <div v-for="(group, i) in hoursGrouped" :key="i" class="cf-hours-row" :class="{ 'is-closed': group.isClosed }">
                            <span class="cf-hours-range">{{ group.range }}</span>
                            <span class="cf-hours-value">{{ group.hours }}</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ═══ NUESTRAS CARTAS ═══ -->
            <section v-if="hasMenus" class="cf-section cf-menus-section">
                <div class="cf-container">
                    <div class="cf-section-header">
                        <span class="cf-section-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
                        </span>
                        <h2 class="cf-section-title">Nuestras Cartas</h2>
                    </div>
                    <p class="cf-section-subtitle">Hecho con cariño, servido con calor</p>
                    <div class="cf-menu-cards">
                        <a v-for="menu in loc.menus" :key="menu.id" :href="`/menu/${menu.id}`" class="cf-menu-card">
                            <div v-if="menu.image_path" class="cf-menu-card-img-wrap">
                                <img :src="menu.image_path" :alt="menu.name" class="cf-menu-card-img" loading="lazy" />
                            </div>
                            <div v-else class="cf-menu-card-img-wrap cf-menu-card-placeholder">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
                            </div>
                            <div class="cf-menu-card-body">
                                <h3 class="cf-menu-card-name">{{ menu.name }}</h3>
                                <p v-if="menu.description" class="cf-menu-card-desc">{{ menu.description }}</p>
                                <span class="cf-menu-card-cta">Ver carta →</span>
                            </div>
                        </a>
                    </div>
                </div>
            </section>

            <!-- ═══ UBICACIÓN Y CONTACTO ═══ -->
            <section class="cf-section cf-location-section">
                <div class="cf-container">
                    <div class="cf-section-header">
                        <span class="cf-section-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        </span>
                        <h2 class="cf-section-title">Encuéntranos</h2>
                    </div>
                    <div class="cf-location-grid">
                        <div class="cf-map-wrap">
                            <iframe v-if="embedUrl" :src="embedUrl" class="cf-map" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen />
                            <div v-else class="cf-map-placeholder">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            </div>
                        </div>
                        <div class="cf-contact-info">
                            <address class="cf-address">
                                <div v-if="loc.address" class="cf-contact-row">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                    <div>
                                        <p>{{ loc.address }}</p>
                                        <p v-if="loc.city">{{ loc.city }}</p>
                                    </div>
                                </div>
                                <div v-if="loc.phone" class="cf-contact-row">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                    <a :href="`tel:${loc.phone}`">{{ loc.phone }}</a>
                                </div>
                            </address>
                            <a v-if="dirUrl" :href="dirUrl" target="_blank" rel="noopener" class="cf-btn cf-btn-primary cf-btn-full">Cómo llegar</a>
                            <div v-if="hasHours" class="cf-contact-hours">
                                <h3 class="cf-contact-hours-title">Horario completo</h3>
                                <OpeningHoursDisplay :hours="loc.opening_hours!" />
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ═══ REDES SOCIALES ═══ -->
            <section v-if="hasSocial" class="cf-section cf-social-section">
                <div class="cf-container">
                    <div class="cf-section-header"><h2 class="cf-section-title">Síguenos</h2></div>
                    <SocialLinks :social-medias="loc.social_medias" size="lg" class="cf-social-grid" />
                </div>
            </section>
        </template>

        <!-- ═══ MULTI-LOCATION ═══ -->
        <template v-else-if="isMultiLocation">
            <section class="cf-section cf-multi-section">
                <div class="cf-container">
                    <div class="cf-section-header"><h2 class="cf-section-title">Nuestros Locales</h2></div>
                    <p class="cf-section-subtitle">{{ locations.length }} establecimientos para servirte</p>
                    <div class="cf-locations-grid">
                        <LocationCard v-for="l in locations" :key="l.id" :location="l" />
                    </div>
                </div>
            </section>
        </template>

        <!-- ═══ FOOTER ═══ -->
        <footer class="cf-footer">
            <div class="cf-container cf-footer-inner">
                <div class="cf-footer-brand">
                    <img v-if="logo" :src="logo" :alt="loc?.name" class="cf-footer-logo" />
                    <div>
                        <p class="cf-footer-name">{{ loc?.name ?? tenant.name }}</p>
                        <p v-if="loc?.address" class="cf-footer-line">{{ loc.address }}{{ loc?.city ? `, ${loc.city}` : '' }}</p>
                        <p v-if="loc?.phone" class="cf-footer-line">{{ loc.phone }}</p>
                    </div>
                </div>
                <SocialLinks v-if="hasSocial" :social-medias="loc!.social_medias" size="sm" class="cf-footer-socials" />
                <p class="cf-footer-copy">Página creada con <a href="https://menulinker.com" target="_blank" rel="noopener">MenuLinker</a></p>
            </div>
        </footer>
    </div>
</template>

<style scoped>
/* ── TOKENS ── */
.cf {
    --bg: oklch(0.97 0.02 80);
    --surface: oklch(1 0.01 75);
    --surface-2: oklch(0.94 0.025 75);
    --ink: oklch(0.35 0.08 55);
    --ink-soft: oklch(0.48 0.06 52);
    --ink-faint: oklch(0.62 0.04 50);
    --rule: oklch(0.88 0.025 70);
    --accent: oklch(0.55 0.12 45);
    --accent-light: oklch(0.55 0.12 45 / 0.1);
    --accent-warm: oklch(0.63 0.14 48);
    --serif: 'DM Serif Display', Georgia, serif;
    --sans: 'Nunito', ui-sans-serif, system-ui, sans-serif;
    --radius: 20px;
    --radius-sm: 14px;
    min-height: 100vh;
    background: var(--bg);
    color: var(--ink);
    font-family: var(--sans);
    font-weight: 400;
    -webkit-font-smoothing: antialiased;
}

@media (prefers-color-scheme: dark) {
    .cf {
        --bg: oklch(0.18 0.025 50);
        --surface: oklch(0.22 0.028 52);
        --surface-2: oklch(0.20 0.022 50);
        --ink: oklch(0.96 0.015 78);
        --ink-soft: oklch(0.78 0.018 72);
        --ink-faint: oklch(0.58 0.012 68);
        --rule: oklch(0.30 0.022 52);
        --accent: oklch(0.72 0.13 50);
        --accent-light: oklch(0.72 0.13 50 / 0.12);
        --accent-warm: oklch(0.78 0.14 55);
    }
}

.cf-container {
    max-width: 1040px;
    margin: 0 auto;
    padding: 0 clamp(1.25rem, 5vw, 2.5rem);
}

/* ── HERO ── */
.cf-hero {
    position: relative;
    min-height: clamp(340px, 60svh, 560px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: clamp(3rem, 8vw, 5rem) clamp(1.25rem, 5vw, 2.5rem) clamp(4rem, 10vw, 6rem);
    overflow: hidden;
    text-align: center;
}

.cf-hero-img {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center;
    filter: saturate(0.85) brightness(0.88);
}

.cf-hero-overlay {
    position: absolute;
    inset: 0;
    background: radial-gradient(ellipse at center bottom, oklch(0 0 0 / 0.58) 0%, oklch(0 0 0 / 0.28) 55%, oklch(0 0 0 / 0.12) 100%);
}

.cf-hero:not(:has(.cf-hero-img)) {
    background: linear-gradient(150deg, var(--surface-2) 0%, var(--bg) 60%, oklch(0.90 0.04 55 / 0.4) 100%);
    min-height: auto;
    padding-top: clamp(5rem, 12vw, 8rem);
    padding-bottom: clamp(3rem, 8vw, 5rem);
}

.cf-hero:not(:has(.cf-hero-img)) .cf-hero-overlay { display: none; }
.cf-hero:not(:has(.cf-hero-img)) .cf-hero-inner { color: var(--ink); }
.cf-hero:not(:has(.cf-hero-img)) .cf-hero-wave { display: none; }

.cf-hero-inner {
    position: relative;
    z-index: 2;
    max-width: 640px;
    color: oklch(0.99 0 0);
    animation: cf-fade-up 900ms cubic-bezier(0.2, 0.65, 0.2, 1) both;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.cf-logo {
    width: 80px;
    height: 80px;
    object-fit: contain;
    border-radius: var(--radius);
    margin-bottom: 1.25rem;
    background: oklch(1 0 0 / 0.15);
    backdrop-filter: blur(10px);
    padding: 10px;
    box-shadow: 0 4px 20px oklch(0 0 0 / 0.2);
}

.cf-kicker {
    font-family: var(--sans);
    font-weight: 400;
    font-size: 0.78rem;
    letter-spacing: 0.22em;
    text-transform: uppercase;
    margin: 0 0 0.75rem;
    opacity: 0.8;
}

.cf-title {
    font-family: var(--serif);
    font-weight: 400;
    font-size: clamp(2.4rem, 8vw, 4.2rem);
    line-height: 1.05;
    letter-spacing: -0.01em;
    margin: 0;
}

.cf-sub {
    margin: 1.1rem 0 0;
    font-size: clamp(0.95rem, 2vw, 1.08rem);
    line-height: 1.7;
    max-width: 46ch;
    opacity: 0.88;
    font-weight: 300;
}

.cf-hero-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-top: 2rem;
    justify-content: center;
}

/* ── BUTTONS ── */
.cf-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.55rem;
    padding: 0.85rem 1.6rem;
    border-radius: 999px;
    font-family: var(--sans);
    font-size: 0.88rem;
    font-weight: 700;
    text-decoration: none;
    transition: all 220ms cubic-bezier(0.2, 0.65, 0.2, 1);
    border: none;
    cursor: pointer;
}

.cf-btn svg { width: 18px; height: 18px; flex-shrink: 0; }

.cf-btn-primary {
    background: var(--accent);
    color: oklch(0.99 0 0);
    box-shadow: 0 4px 18px oklch(0.55 0.12 45 / 0.35);
}

.cf-btn-primary:hover {
    background: var(--accent-warm);
    transform: translateY(-2px);
    box-shadow: 0 8px 26px oklch(0.55 0.12 45 / 0.42);
}

.cf-btn-outline {
    background: oklch(1 0 0 / 0.15);
    color: oklch(1 0 0);
    backdrop-filter: blur(8px);
    border: 1.5px solid oklch(1 0 0 / 0.3);
}

.cf-btn-outline:hover { background: oklch(1 0 0 / 0.25); }
.cf-btn-full { width: 100%; justify-content: center; }

/* ── WAVE ── */
.cf-hero-wave {
    position: absolute;
    bottom: -1px;
    left: 0;
    right: 0;
    height: 60px;
    z-index: 3;
    pointer-events: none;
}

.cf-hero-wave svg { width: 100%; height: 100%; display: block; }

/* ── SECTIONS ── */
.cf-section { padding: clamp(3rem, 7vw, 5.5rem) 0; }
.cf-section + .cf-section { border-top: 1px solid var(--rule); }

.cf-section-header {
    display: flex;
    align-items: center;
    gap: 0.9rem;
    margin-bottom: 1.5rem;
}

.cf-section-icon {
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--accent-light);
    border-radius: var(--radius-sm);
    color: var(--accent);
    flex-shrink: 0;
}

.cf-section-icon svg { width: 22px; height: 22px; }

.cf-section-title {
    font-family: var(--serif);
    font-weight: 400;
    font-size: clamp(1.6rem, 4vw, 2.2rem);
    line-height: 1.1;
    margin: 0;
    color: var(--ink);
}

.cf-section-subtitle {
    margin: -0.5rem 0 2rem;
    padding-left: calc(44px + 0.9rem);
    font-size: 0.95rem;
    color: var(--ink-soft);
    line-height: 1.55;
    font-style: italic;
}

/* ── HOURS ── */
.cf-hours-section { background: var(--surface); }

.cf-hours-compact {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem 2.5rem;
    padding-left: calc(44px + 0.9rem);
}

.cf-hours-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 0.95rem;
}

.cf-hours-range { font-weight: 700; min-width: 5rem; color: var(--ink); }
.cf-hours-value { color: var(--ink-soft); font-variant-numeric: tabular-nums; }
.cf-hours-row.is-closed .cf-hours-value { opacity: 0.45; font-style: italic; }

/* ── MENU CARDS ── */
.cf-menu-cards {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.5rem;
}

@media (min-width: 560px) { .cf-menu-cards { grid-template-columns: repeat(2, 1fr); } }
@media (min-width: 900px) { .cf-menu-cards { grid-template-columns: repeat(3, 1fr); } }

.cf-menu-card {
    background: var(--surface);
    border: 1.5px solid var(--rule);
    border-radius: var(--radius);
    overflow: hidden;
    text-decoration: none;
    color: inherit;
    transition: transform 300ms cubic-bezier(0.2, 0.65, 0.2, 1), box-shadow 300ms;
    box-shadow: 0 2px 12px oklch(0.55 0.12 45 / 0.06);
}

.cf-menu-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 16px 36px -10px oklch(0.55 0.12 45 / 0.2);
}

.cf-menu-card-img-wrap {
    aspect-ratio: 4/3;
    overflow: hidden;
    background: var(--surface-2);
}

.cf-menu-card-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 500ms cubic-bezier(0.2, 0.65, 0.2, 1);
}

.cf-menu-card:hover .cf-menu-card-img { transform: scale(1.06); }

.cf-menu-card-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--ink-faint);
    opacity: 0.3;
}

.cf-menu-card-placeholder svg { width: 3rem; height: 3rem; }

.cf-menu-card-body { padding: 1.25rem 1.4rem 1.5rem; }

.cf-menu-card-name {
    font-family: var(--serif);
    font-weight: 400;
    font-size: 1.2rem;
    margin: 0 0 0.4rem;
    color: var(--ink);
}

.cf-menu-card-desc {
    font-size: 0.87rem;
    color: var(--ink-soft);
    line-height: 1.6;
    margin: 0 0 0.9rem;
}

.cf-menu-card-cta {
    font-size: 0.82rem;
    font-weight: 700;
    color: var(--accent);
    letter-spacing: 0.02em;
}

/* ── LOCATION + MAP ── */
.cf-location-section { background: var(--surface-2); }

.cf-location-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 2rem;
}

@media (min-width: 768px) { .cf-location-grid { grid-template-columns: 1.3fr 1fr; } }

.cf-map-wrap {
    border-radius: var(--radius);
    overflow: hidden;
    aspect-ratio: 4/3;
    background: var(--surface);
    box-shadow: 0 4px 20px oklch(0 0 0 / 0.07);
}

.cf-map { width: 100%; height: 100%; border: 0; }

.cf-map-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--ink-faint);
    opacity: 0.2;
}

.cf-map-placeholder svg { width: 4rem; height: 4rem; }

.cf-contact-info { display: flex; flex-direction: column; gap: 1.5rem; }

.cf-address { font-style: normal; display: flex; flex-direction: column; gap: 1rem; }

.cf-contact-row {
    display: flex;
    align-items: flex-start;
    gap: 0.85rem;
    font-size: 0.95rem;
    color: var(--ink);
    line-height: 1.55;
}

.cf-contact-row svg { width: 20px; height: 20px; color: var(--accent); flex-shrink: 0; margin-top: 2px; }
.cf-contact-row a { color: var(--accent); text-decoration: none; font-weight: 700; }
.cf-contact-row a:hover { text-decoration: underline; }

.cf-contact-hours { margin-top: 0.5rem; }

.cf-contact-hours-title {
    font-family: var(--serif);
    font-weight: 400;
    font-size: 1rem;
    margin: 0 0 0.75rem;
    color: var(--ink);
}

/* ── SOCIAL ── */
.cf-social-section { background: var(--surface); text-align: center; }
.cf-social-section .cf-section-header { justify-content: center; }
.cf-social-grid { justify-content: center; }

/* ── MULTI-LOCATION ── */
.cf-locations-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.5rem;
}

@media (min-width: 640px) { .cf-locations-grid { grid-template-columns: repeat(2, 1fr); } }
@media (min-width: 1024px) { .cf-locations-grid { grid-template-columns: repeat(3, 1fr); } }

/* ── FOOTER ── */
.cf-footer {
    padding: clamp(3rem, 6vw, 4rem) 0;
    border-top: 1px solid var(--rule);
    background: var(--surface);
    position: relative;
    overflow: hidden;
}

.cf-footer::before {
    content: '';
    position: absolute;
    top: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 220px;
    height: 220px;
    background: radial-gradient(circle, var(--accent-light) 0%, transparent 70%);
    opacity: 0.5;
    pointer-events: none;
}

.cf-footer-inner {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1.5rem;
    text-align: center;
    position: relative;
    z-index: 1;
}

.cf-footer-brand { display: flex; align-items: center; gap: 1rem; }

.cf-footer-logo {
    width: 52px;
    height: 52px;
    object-fit: contain;
    border-radius: var(--radius-sm);
    box-shadow: 0 2px 10px oklch(0 0 0 / 0.1);
}

.cf-footer-name {
    font-family: var(--serif);
    font-weight: 400;
    font-size: 1.2rem;
    margin: 0;
    color: var(--ink);
}

.cf-footer-line { margin: 0.15rem 0; font-size: 0.82rem; color: var(--ink-soft); }
.cf-footer-copy { font-size: 0.72rem; color: var(--ink-faint); margin-top: 0.5rem; }
.cf-footer-copy a { color: var(--accent); text-decoration: none; }
.cf-footer-copy a:hover { text-decoration: underline; }

/* ── ANIMATIONS ── */
@keyframes cf-fade-up {
    from { opacity: 0; transform: translateY(18px); }
    to   { opacity: 1; transform: translateY(0); }
}

@media (prefers-reduced-motion: reduce) {
    .cf-hero-inner { animation: none !important; }
    .cf-menu-card,
    .cf-menu-card-img,
    .cf-btn { transition: none !important; }
}
</style>
