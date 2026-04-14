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
        <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet" />
    </Head>

    <div class="br">
        <!-- ═══ HERO ═══ -->
        <header class="br-hero">
            <div v-if="hero" class="br-hero-img" :style="{ backgroundImage: `url(${hero})` }" />
            <div class="br-hero-overlay" />
            <!-- Scanlines decorativas -->
            <div class="br-scanlines" aria-hidden="true" />
            <div class="br-hero-inner">
                <img v-if="logo" :src="logo" :alt="loc?.name" class="br-logo" />
                <p v-if="loc?.city" class="br-kicker">{{ loc.city }}</p>
                <h1 class="br-title">{{ loc?.name ?? tenant.name }}</h1>
                <p v-if="loc?.description" class="br-sub">{{ loc.description }}</p>
                <div class="br-hero-actions">
                    <a v-if="loc?.phone" :href="`tel:${loc.phone}`" class="br-btn br-btn-primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        Llamar
                    </a>
                    <a v-if="dirUrl" :href="dirUrl" target="_blank" rel="noopener" class="br-btn br-btn-outline">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        Cómo llegar
                    </a>
                </div>
            </div>
        </header>

        <template v-if="!isMultiLocation && loc">
            <!-- ═══ HORARIOS ═══ -->
            <section v-if="hasHours" class="br-section br-hours-section">
                <div class="br-container">
                    <div class="br-section-header">
                        <span class="br-section-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        </span>
                        <h2 class="br-section-title">Horario</h2>
                    </div>
                    <div class="br-hours-compact">
                        <div v-for="(group, i) in hoursGrouped" :key="i" class="br-hours-row" :class="{ 'is-closed': group.isClosed }">
                            <span class="br-hours-range">{{ group.range }}</span>
                            <span class="br-hours-value">{{ group.hours }}</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ═══ NUESTRAS CARTAS ═══ -->
            <section v-if="hasMenus" class="br-section br-menus-section">
                <div class="br-container">
                    <div class="br-section-header">
                        <span class="br-section-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
                        </span>
                        <h2 class="br-section-title">Nuestras Cartas</h2>
                    </div>
                    <p class="br-section-subtitle">Selecciona tu experiencia esta noche</p>
                    <div class="br-menu-cards">
                        <a v-for="menu in loc.menus" :key="menu.id" :href="`/menu/${menu.id}`" class="br-menu-card">
                            <div class="br-menu-card-neon-bar" aria-hidden="true" />
                            <div v-if="menu.image_path" class="br-menu-card-img-wrap">
                                <img :src="menu.image_path" :alt="menu.name" class="br-menu-card-img" loading="lazy" />
                            </div>
                            <div v-else class="br-menu-card-img-wrap br-menu-card-placeholder">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
                            </div>
                            <div class="br-menu-card-body">
                                <h3 class="br-menu-card-name">{{ menu.name }}</h3>
                                <p v-if="menu.description" class="br-menu-card-desc">{{ menu.description }}</p>
                                <span class="br-menu-card-cta">Ver carta →</span>
                            </div>
                        </a>
                    </div>
                </div>
            </section>

            <!-- ═══ UBICACIÓN Y CONTACTO ═══ -->
            <section class="br-section br-location-section">
                <div class="br-container">
                    <div class="br-section-header">
                        <span class="br-section-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        </span>
                        <h2 class="br-section-title">Encuéntranos</h2>
                    </div>
                    <div class="br-location-grid">
                        <div class="br-map-wrap">
                            <iframe v-if="embedUrl" :src="embedUrl" class="br-map" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen />
                            <div v-else class="br-map-placeholder">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            </div>
                        </div>
                        <div class="br-contact-info">
                            <address class="br-address">
                                <div v-if="loc.address" class="br-contact-row">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                    <div>
                                        <p>{{ loc.address }}</p>
                                        <p v-if="loc.city">{{ loc.city }}</p>
                                    </div>
                                </div>
                                <div v-if="loc.phone" class="br-contact-row">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                    <a :href="`tel:${loc.phone}`">{{ loc.phone }}</a>
                                </div>
                            </address>
                            <a v-if="dirUrl" :href="dirUrl" target="_blank" rel="noopener" class="br-btn br-btn-primary br-btn-full">Cómo llegar</a>
                            <div v-if="hasHours" class="br-contact-hours">
                                <h3 class="br-contact-hours-title">Horario completo</h3>
                                <OpeningHoursDisplay :hours="loc.opening_hours!" />
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ═══ REDES SOCIALES ═══ -->
            <section v-if="hasSocial" class="br-section br-social-section">
                <div class="br-container">
                    <div class="br-section-header"><h2 class="br-section-title">Síguenos</h2></div>
                    <SocialLinks :social-medias="loc.social_medias" size="lg" class="br-social-grid" />
                </div>
            </section>
        </template>

        <!-- ═══ MULTI-LOCATION ═══ -->
        <template v-else-if="isMultiLocation">
            <section class="br-section br-multi-section">
                <div class="br-container">
                    <div class="br-section-header"><h2 class="br-section-title">Nuestros Locales</h2></div>
                    <p class="br-section-subtitle">{{ locations.length }} establecimientos para servirte</p>
                    <div class="br-locations-grid">
                        <LocationCard v-for="l in locations" :key="l.id" :location="l" />
                    </div>
                </div>
            </section>
        </template>

        <!-- ═══ FOOTER ═══ -->
        <footer class="br-footer">
            <div class="br-container br-footer-inner">
                <div class="br-footer-brand">
                    <img v-if="logo" :src="logo" :alt="loc?.name" class="br-footer-logo" />
                    <div>
                        <p class="br-footer-name">{{ loc?.name ?? tenant.name }}</p>
                        <p v-if="loc?.address" class="br-footer-line">{{ loc.address }}{{ loc?.city ? `, ${loc.city}` : '' }}</p>
                        <p v-if="loc?.phone" class="br-footer-line">{{ loc.phone }}</p>
                    </div>
                </div>
                <SocialLinks v-if="hasSocial" :social-medias="loc!.social_medias" size="sm" class="br-footer-socials" />
                <p class="br-footer-copy">Página creada con <a href="https://menulinker.com" target="_blank" rel="noopener">MenuLinker</a></p>
            </div>
        </footer>
    </div>
</template>

<style scoped>
/* ── TOKENS ── */
.br {
    --bg: oklch(0.12 0.015 280);
    --surface: oklch(0.16 0.018 285);
    --surface-2: oklch(0.14 0.016 280);
    --ink: oklch(0.94 0.012 280);
    --ink-soft: oklch(0.72 0.015 278);
    --ink-faint: oklch(0.50 0.012 275);
    --rule: oklch(0.24 0.018 285);
    --neon: oklch(0.65 0.25 310);
    --cyan: oklch(0.78 0.15 200);
    --neon-glow: oklch(0.65 0.25 310 / 0.5);
    --cyan-glow: oklch(0.78 0.15 200 / 0.45);
    --accent: oklch(0.65 0.25 310);
    --accent-light: oklch(0.65 0.25 310 / 0.1);
    --serif: 'Bebas Neue', Impact, 'Arial Black', sans-serif;
    --sans: 'Inter', ui-sans-serif, system-ui, sans-serif;
    --radius: 8px;
    --radius-sm: 6px;
    min-height: 100vh;
    background: var(--bg);
    color: var(--ink);
    font-family: var(--sans);
    font-weight: 400;
    -webkit-font-smoothing: antialiased;
}

/* Dark es la paleta principal — light mode se aproxima */
@media (prefers-color-scheme: light) {
    .br {
        --bg: oklch(0.15 0.018 280);
        --surface: oklch(0.20 0.020 285);
        --surface-2: oklch(0.17 0.018 280);
        --rule: oklch(0.28 0.020 285);
    }
}

.br-container {
    max-width: 1040px;
    margin: 0 auto;
    padding: 0 clamp(1.25rem, 5vw, 2.5rem);
}

/* ── HERO ── */
.br-hero {
    position: relative;
    min-height: 100svh;
    display: flex;
    align-items: flex-end;
    padding: clamp(3rem, 8vw, 5rem) clamp(1.25rem, 5vw, 2.5rem);
    overflow: hidden;
}

.br-hero-img {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center;
    filter: saturate(0.5) brightness(0.4);
}

.br-hero-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(
        180deg,
        oklch(0 0 0 / 0.2) 0%,
        oklch(0 0 0 / 0.5) 50%,
        oklch(0.12 0.015 280 / 0.95) 100%
    );
}

.br-hero:not(:has(.br-hero-img)) {
    background:
        radial-gradient(ellipse at 20% 50%, oklch(0.65 0.25 310 / 0.08) 0%, transparent 60%),
        radial-gradient(ellipse at 80% 30%, oklch(0.78 0.15 200 / 0.06) 0%, transparent 50%),
        var(--bg);
    min-height: clamp(360px, 60svh, 600px);
    align-items: center;
}

.br-hero:not(:has(.br-hero-img)) .br-hero-overlay { display: none; }

/* Scanlines efecto nocturno */
.br-scanlines {
    position: absolute;
    inset: 0;
    background: repeating-linear-gradient(
        0deg,
        transparent,
        transparent 2px,
        oklch(0 0 0 / 0.04) 2px,
        oklch(0 0 0 / 0.04) 4px
    );
    pointer-events: none;
    z-index: 1;
}

.br-hero-inner {
    position: relative;
    z-index: 2;
    max-width: 800px;
    color: var(--ink);
    animation: br-fade-up 800ms cubic-bezier(0.2, 0.65, 0.2, 1) both;
}

.br-logo {
    width: 68px;
    height: 68px;
    object-fit: contain;
    border-radius: var(--radius);
    margin-bottom: 1.25rem;
    border: 1px solid var(--neon);
    box-shadow: 0 0 16px var(--neon-glow), 0 0 32px var(--neon-glow);
    padding: 8px;
    background: oklch(0 0 0 / 0.3);
    backdrop-filter: blur(8px);
}

.br-kicker {
    font-family: var(--sans);
    font-weight: 500;
    font-size: 0.75rem;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    margin: 0 0 0.75rem;
    color: var(--cyan);
}

.br-title {
    font-family: var(--serif);
    font-weight: 400;
    font-size: clamp(4rem, 14vw, 9rem);
    line-height: 0.9;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    margin: 0;
    color: var(--ink);
    text-shadow: 0 0 40px var(--neon-glow), 0 0 80px var(--neon-glow);
}

.br-sub {
    margin: 1.25rem 0 0;
    font-size: clamp(0.9rem, 2vw, 1rem);
    line-height: 1.65;
    max-width: 52ch;
    color: var(--ink-soft);
    font-weight: 300;
}

.br-hero-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-top: 2rem;
}

/* ── BUTTONS ── */
.br-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.55rem;
    padding: 0.85rem 1.75rem;
    border-radius: var(--radius);
    font-family: var(--sans);
    font-size: 0.85rem;
    font-weight: 600;
    text-decoration: none;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    transition: all 200ms;
    border: none;
    cursor: pointer;
}

.br-btn svg { width: 18px; height: 18px; flex-shrink: 0; }

.br-btn-primary {
    background: var(--neon);
    color: oklch(0.08 0.01 280);
    box-shadow: 0 0 20px var(--neon-glow), 0 0 40px var(--neon-glow);
}

.br-btn-primary:hover {
    box-shadow: 0 0 30px var(--neon-glow), 0 0 60px var(--neon-glow);
    transform: translateY(-1px);
    filter: brightness(1.1);
}

.br-btn-outline {
    background: transparent;
    color: var(--cyan);
    border: 1.5px solid var(--cyan);
    box-shadow: 0 0 12px var(--cyan-glow), inset 0 0 12px oklch(0.78 0.15 200 / 0.05);
}

.br-btn-outline:hover {
    background: oklch(0.78 0.15 200 / 0.1);
    box-shadow: 0 0 20px var(--cyan-glow), inset 0 0 20px oklch(0.78 0.15 200 / 0.08);
}

.br-btn-full { width: 100%; justify-content: center; }

/* ── SECTIONS ── */
.br-section { padding: clamp(3.5rem, 8vw, 6rem) 0; }
.br-section + .br-section { border-top: 1px solid var(--rule); }

.br-section-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.br-section-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--accent-light);
    border-radius: var(--radius-sm);
    color: var(--neon);
    border: 1px solid oklch(0.65 0.25 310 / 0.2);
    flex-shrink: 0;
    box-shadow: 0 0 8px var(--neon-glow);
}

.br-section-icon svg { width: 20px; height: 20px; }

.br-section-title {
    font-family: var(--serif);
    font-weight: 400;
    font-size: clamp(1.8rem, 5vw, 2.8rem);
    line-height: 1.0;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    margin: 0;
    color: var(--ink);
}

.br-section-subtitle {
    margin: -0.25rem 0 2rem;
    padding-left: calc(40px + 1rem);
    font-size: 0.9rem;
    color: var(--ink-soft);
    line-height: 1.5;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}

/* ── HOURS ── */
.br-hours-section { background: var(--surface); }

.br-hours-compact {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem 2.5rem;
    padding-left: calc(40px + 1rem);
}

.br-hours-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 0.9rem;
}

.br-hours-range { font-weight: 600; min-width: 5rem; color: var(--neon); letter-spacing: 0.03em; }
.br-hours-value { color: var(--ink-soft); font-variant-numeric: tabular-nums; }
.br-hours-row.is-closed .br-hours-value { opacity: 0.4; font-style: italic; }

/* ── MENU CARDS ── */
.br-menu-cards {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.25rem;
}

@media (min-width: 640px) { .br-menu-cards { grid-template-columns: repeat(2, 1fr); } }
@media (min-width: 900px) { .br-menu-cards { grid-template-columns: repeat(3, 1fr); } }

.br-menu-card {
    background: var(--surface);
    border: 1px solid var(--rule);
    border-radius: var(--radius);
    overflow: hidden;
    text-decoration: none;
    color: inherit;
    transition: transform 280ms cubic-bezier(0.2, 0.65, 0.2, 1), box-shadow 280ms, border-color 280ms;
    position: relative;
}

.br-menu-card:hover {
    transform: translateY(-3px);
    border-color: oklch(0.65 0.25 310 / 0.5);
    box-shadow: 0 0 20px var(--neon-glow), 0 12px 30px -10px oklch(0 0 0 / 0.5);
}

/* Barra neón lateral izquierda */
.br-menu-card-neon-bar {
    position: absolute;
    top: 0;
    left: 0;
    width: 3px;
    height: 100%;
    background: linear-gradient(180deg, var(--neon) 0%, var(--cyan) 100%);
    box-shadow: 0 0 8px var(--neon-glow);
    z-index: 1;
}

.br-menu-card-img-wrap {
    aspect-ratio: 16/10;
    overflow: hidden;
    background: var(--surface-2);
}

.br-menu-card-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 500ms cubic-bezier(0.2, 0.65, 0.2, 1);
    filter: saturate(0.7);
}

.br-menu-card:hover .br-menu-card-img {
    transform: scale(1.05);
    filter: saturate(1);
}

.br-menu-card-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--neon);
    opacity: 0.15;
}

.br-menu-card-placeholder svg { width: 3rem; height: 3rem; }

.br-menu-card-body { padding: 1.1rem 1.25rem 1.4rem 1.5rem; }

.br-menu-card-name {
    font-family: var(--serif);
    font-weight: 400;
    font-size: 1.25rem;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    margin: 0 0 0.4rem;
    color: var(--ink);
}

.br-menu-card-desc {
    font-size: 0.85rem;
    color: var(--ink-soft);
    line-height: 1.55;
    margin: 0 0 0.9rem;
}

.br-menu-card-cta {
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--neon);
    letter-spacing: 0.08em;
    text-transform: uppercase;
    text-shadow: 0 0 8px var(--neon-glow);
}

/* ── LOCATION + MAP ── */
.br-location-section { background: var(--surface-2); }

.br-location-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 2rem;
}

@media (min-width: 768px) { .br-location-grid { grid-template-columns: 1.3fr 1fr; } }

.br-map-wrap {
    border-radius: var(--radius);
    overflow: hidden;
    aspect-ratio: 16/10;
    background: var(--surface);
    border: 1px solid var(--rule);
    box-shadow: 0 0 20px oklch(0 0 0 / 0.4);
}

/* Invertir colores del mapa para look nocturno */
.br-map {
    width: 100%;
    height: 100%;
    border: 0;
    filter: invert(0.9) hue-rotate(180deg) saturate(0.7);
}

.br-map-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--neon);
    opacity: 0.15;
}

.br-map-placeholder svg { width: 4rem; height: 4rem; }

.br-contact-info { display: flex; flex-direction: column; gap: 1.5rem; }
.br-address { font-style: normal; display: flex; flex-direction: column; gap: 1rem; }

.br-contact-row {
    display: flex;
    align-items: flex-start;
    gap: 0.85rem;
    font-size: 0.95rem;
    color: var(--ink);
    line-height: 1.5;
}

.br-contact-row svg { width: 20px; height: 20px; color: var(--cyan); flex-shrink: 0; margin-top: 2px; }
.br-contact-row a { color: var(--neon); text-decoration: none; font-weight: 600; text-shadow: 0 0 8px var(--neon-glow); }
.br-contact-row a:hover { text-decoration: underline; }

.br-contact-hours { margin-top: 0.5rem; }

.br-contact-hours-title {
    font-family: var(--serif);
    font-weight: 400;
    font-size: 1rem;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    margin: 0 0 0.75rem;
    color: var(--ink);
}

/* ── SOCIAL ── */
.br-social-section { background: var(--surface); text-align: center; }
.br-social-section .br-section-header { justify-content: center; }
.br-social-grid { justify-content: center; }

/* ── MULTI-LOCATION ── */
.br-locations-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.5rem;
}

@media (min-width: 640px) { .br-locations-grid { grid-template-columns: repeat(2, 1fr); } }
@media (min-width: 1024px) { .br-locations-grid { grid-template-columns: repeat(3, 1fr); } }

/* ── FOOTER ── */
.br-footer {
    padding: clamp(2.5rem, 5vw, 3.5rem) 0;
    border-top: 1px solid var(--rule);
    background: var(--bg);
    position: relative;
    overflow: hidden;
}

.br-footer::before {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent, var(--neon), var(--cyan), transparent);
    opacity: 0.4;
}

.br-footer-inner {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1.5rem;
    text-align: center;
}

.br-footer-brand { display: flex; align-items: center; gap: 1rem; }

.br-footer-logo {
    width: 48px;
    height: 48px;
    object-fit: contain;
    border-radius: var(--radius-sm);
    border: 1px solid oklch(0.65 0.25 310 / 0.3);
    box-shadow: 0 0 10px var(--neon-glow);
}

.br-footer-name {
    font-family: var(--serif);
    font-weight: 400;
    font-size: 1.2rem;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    margin: 0;
    color: var(--ink);
}

.br-footer-line { margin: 0.15rem 0; font-size: 0.82rem; color: var(--ink-soft); }
.br-footer-copy { font-size: 0.72rem; color: var(--ink-faint); margin-top: 0.5rem; }
.br-footer-copy a { color: var(--neon); text-decoration: none; }
.br-footer-copy a:hover { text-decoration: underline; }

/* ── ANIMATIONS ── */
@keyframes br-fade-up {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
}

@media (prefers-reduced-motion: reduce) {
    .br-hero-inner { animation: none !important; }
    .br-menu-card,
    .br-menu-card-img,
    .br-btn { transition: none !important; }
}
</style>
