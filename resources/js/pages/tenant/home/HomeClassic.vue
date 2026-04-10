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
        <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Lato:wght@300;400;700&display=swap" rel="stylesheet" />
    </Head>

    <div class="hc">
        <!-- ═══ HERO ═══ -->
        <header class="hc-hero">
            <div v-if="hero" class="hc-hero-img" :style="{ backgroundImage: `url(${hero})` }" />
            <div class="hc-hero-overlay" />
            <div class="hc-hero-inner">
                <img v-if="logo" :src="logo" :alt="loc?.name" class="hc-logo" />
                <p v-if="loc?.city" class="hc-kicker">{{ loc.city }}</p>
                <h1 class="hc-title">{{ loc?.name ?? tenant.name }}</h1>
                <p v-if="loc?.description" class="hc-sub">{{ loc.description }}</p>
                <div class="hc-hero-actions">
                    <a v-if="loc?.phone" :href="`tel:${loc.phone}`" class="hc-btn hc-btn-primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        Llamar
                    </a>
                    <a v-if="dirUrl" :href="dirUrl" target="_blank" rel="noopener" class="hc-btn hc-btn-outline">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        Cómo llegar
                    </a>
                </div>
            </div>
        </header>

        <template v-if="!isMultiLocation && loc">
            <!-- ═══ HORARIOS ═══ -->
            <section v-if="hasHours" class="hc-section hc-hours-section">
                <div class="hc-container">
                    <div class="hc-section-header">
                        <span class="hc-section-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        </span>
                        <h2 class="hc-section-title">Horario</h2>
                    </div>
                    <div class="hc-hours-compact">
                        <div v-for="(group, i) in hoursGrouped" :key="i" class="hc-hours-row" :class="{ 'is-closed': group.isClosed }">
                            <span class="hc-hours-range">{{ group.range }}</span>
                            <span class="hc-hours-value">{{ group.hours }}</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ═══ NUESTRAS CARTAS ═══ -->
            <section v-if="hasMenus" class="hc-section hc-menus-section">
                <div class="hc-container">
                    <div class="hc-section-header">
                        <span class="hc-section-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
                        </span>
                        <h2 class="hc-section-title">Nuestras Cartas</h2>
                    </div>
                    <p class="hc-section-subtitle">Consulta nuestra oferta gastronómica</p>
                    <div class="hc-menu-cards">
                        <a v-for="menu in loc.menus" :key="menu.id" :href="`/menu/${menu.id}`" class="hc-menu-card">
                            <div v-if="menu.image_path" class="hc-menu-card-img-wrap">
                                <img :src="menu.image_path" :alt="menu.name" class="hc-menu-card-img" loading="lazy" />
                            </div>
                            <div v-else class="hc-menu-card-img-wrap hc-menu-card-placeholder">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
                            </div>
                            <div class="hc-menu-card-body">
                                <h3 class="hc-menu-card-name">{{ menu.name }}</h3>
                                <p v-if="menu.description" class="hc-menu-card-desc">{{ menu.description }}</p>
                                <span class="hc-menu-card-cta">Ver carta completa →</span>
                            </div>
                        </a>
                    </div>
                </div>
            </section>

            <!-- ═══ UBICACIÓN Y CONTACTO ═══ -->
            <section class="hc-section hc-location-section">
                <div class="hc-container">
                    <div class="hc-section-header">
                        <span class="hc-section-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        </span>
                        <h2 class="hc-section-title">Encuéntranos</h2>
                    </div>
                    <div class="hc-location-grid">
                        <div class="hc-map-wrap">
                            <iframe v-if="embedUrl" :src="embedUrl" class="hc-map" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen />
                            <div v-else class="hc-map-placeholder">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            </div>
                        </div>
                        <div class="hc-contact-info">
                            <address class="hc-address">
                                <div v-if="loc.address" class="hc-contact-row">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                    <div>
                                        <p>{{ loc.address }}</p>
                                        <p v-if="loc.city">{{ loc.city }}</p>
                                    </div>
                                </div>
                                <div v-if="loc.phone" class="hc-contact-row">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                    <a :href="`tel:${loc.phone}`">{{ loc.phone }}</a>
                                </div>
                            </address>
                            <a v-if="dirUrl" :href="dirUrl" target="_blank" rel="noopener" class="hc-btn hc-btn-primary hc-btn-full">Cómo llegar</a>
                            <div v-if="hasHours" class="hc-contact-hours">
                                <h3 class="hc-contact-hours-title">Horario completo</h3>
                                <OpeningHoursDisplay :hours="loc.opening_hours!" />
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ═══ REDES SOCIALES ═══ -->
            <section v-if="hasSocial" class="hc-section hc-social-section">
                <div class="hc-container">
                    <div class="hc-section-header"><h2 class="hc-section-title">Síguenos</h2></div>
                    <SocialLinks :social-medias="loc.social_medias" size="lg" class="hc-social-grid" />
                </div>
            </section>
        </template>

        <!-- ═══ MULTI-LOCATION ═══ -->
        <template v-else-if="isMultiLocation">
            <section class="hc-section hc-multi-section">
                <div class="hc-container">
                    <div class="hc-section-header"><h2 class="hc-section-title">Nuestros Locales</h2></div>
                    <p class="hc-section-subtitle">{{ locations.length }} establecimientos para servirte</p>
                    <div class="hc-locations-grid">
                        <LocationCard v-for="l in locations" :key="l.id" :location="l" />
                    </div>
                </div>
            </section>
        </template>

        <!-- ═══ FOOTER ═══ -->
        <footer class="hc-footer">
            <div class="hc-container hc-footer-inner">
                <div class="hc-footer-brand">
                    <img v-if="logo" :src="logo" :alt="loc?.name" class="hc-footer-logo" />
                    <div>
                        <p class="hc-footer-name">{{ loc?.name ?? tenant.name }}</p>
                        <p v-if="loc?.address" class="hc-footer-line">{{ loc.address }}{{ loc?.city ? `, ${loc.city}` : '' }}</p>
                        <p v-if="loc?.phone" class="hc-footer-line">{{ loc.phone }}</p>
                    </div>
                </div>
                <SocialLinks v-if="hasSocial" :social-medias="loc!.social_medias" size="sm" class="hc-footer-socials" />
                <p class="hc-footer-copy">Página creada con <a href="https://menulinker.com" target="_blank" rel="noopener">MenuLinker</a></p>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.hc {
    --bg: oklch(0.985 0.008 75);
    --surface: oklch(1 0 0);
    --surface-2: oklch(0.97 0.006 75);
    --ink: oklch(0.18 0.02 40);
    --ink-soft: oklch(0.42 0.015 40);
    --ink-faint: oklch(0.62 0.012 40);
    --rule: oklch(0.88 0.012 75);
    --accent: oklch(0.42 0.08 30);
    --accent-light: oklch(0.42 0.08 30 / 0.08);
    --serif: 'Playfair Display', Georgia, serif;
    --sans: 'Lato', ui-sans-serif, system-ui, sans-serif;
    --radius: 14px;
    min-height: 100vh;
    background: var(--bg);
    color: var(--ink);
    font-family: var(--sans);
    font-weight: 400;
    -webkit-font-smoothing: antialiased;
}
@media (prefers-color-scheme: dark) {
    .hc {
        --bg: oklch(0.14 0.01 40);
        --surface: oklch(0.19 0.012 40);
        --surface-2: oklch(0.16 0.01 40);
        --ink: oklch(0.95 0.008 75);
        --ink-soft: oklch(0.72 0.01 75);
        --ink-faint: oklch(0.55 0.01 75);
        --rule: oklch(0.28 0.01 40);
        --accent: oklch(0.78 0.1 35);
        --accent-light: oklch(0.78 0.1 35 / 0.12);
    }
}
.hc-container { max-width: 1040px; margin: 0 auto; padding: 0 clamp(1.25rem, 5vw, 2.5rem); }

/* HERO */
.hc-hero { position: relative; min-height: clamp(480px, 75svh, 700px); display: flex; align-items: flex-end; padding: clamp(2rem, 8vw, 5rem) clamp(1.25rem, 5vw, 2.5rem); overflow: hidden; }
.hc-hero-img { position: absolute; inset: 0; background-size: cover; background-position: center; filter: saturate(0.9) contrast(1.04); }
.hc-hero-overlay { position: absolute; inset: 0; background: linear-gradient(180deg, oklch(0 0 0 / 0.15) 0%, oklch(0 0 0 / 0.3) 50%, oklch(0 0 0 / 0.75) 100%); }
.hc-hero:not(:has(.hc-hero-img)) { background: linear-gradient(135deg, var(--surface-2), var(--bg)); min-height: auto; padding-top: clamp(5rem, 12vw, 8rem); padding-bottom: clamp(3rem, 8vw, 5rem); }
.hc-hero:not(:has(.hc-hero-img)) .hc-hero-overlay { display: none; }
.hc-hero:not(:has(.hc-hero-img)) .hc-hero-inner { color: var(--ink); }
.hc-hero-inner { position: relative; z-index: 2; max-width: 720px; color: oklch(0.99 0 0); animation: fade-up 900ms cubic-bezier(.2,.65,.2,1) both; }
.hc-logo { width: 72px; height: 72px; object-fit: contain; border-radius: 16px; margin-bottom: 1.25rem; background: oklch(1 0 0 / 0.1); backdrop-filter: blur(8px); padding: 8px; }
.hc-kicker { font-family: var(--sans); font-weight: 300; font-size: 0.82rem; letter-spacing: 0.2em; text-transform: uppercase; margin: 0 0 0.75rem; opacity: 0.85; }
.hc-title { font-family: var(--serif); font-weight: 700; font-size: clamp(2.75rem, 9vw, 5rem); line-height: 0.95; letter-spacing: -0.015em; margin: 0; }
.hc-sub { margin: 1.25rem 0 0; font-size: clamp(1rem, 2vw, 1.15rem); line-height: 1.65; max-width: 52ch; opacity: 0.9; font-weight: 300; }
.hc-hero-actions { display: flex; flex-wrap: wrap; gap: 0.75rem; margin-top: 2rem; }
.hc-btn { display: inline-flex; align-items: center; gap: 0.6rem; padding: 0.85rem 1.5rem; border-radius: 999px; font-family: var(--sans); font-size: 0.88rem; font-weight: 700; text-decoration: none; transition: all 200ms; border: none; cursor: pointer; }
.hc-btn svg { width: 18px; height: 18px; flex-shrink: 0; }
.hc-btn-primary { background: var(--accent); color: oklch(1 0 0); }
.hc-btn-primary:hover { filter: brightness(1.1); transform: translateY(-1px); }
.hc-btn-outline { background: transparent; color: oklch(1 0 0); backdrop-filter: blur(8px); border: 1.5px solid oklch(1 0 0 / 0.4); }
.hc-btn-outline:hover { background: oklch(1 0 0 / 0.15); }
/* When hero has no image, buttons need dark text */
.hc-hero:not(:has(.hc-hero-img)) .hc-btn-primary { background: var(--accent); color: oklch(1 0 0); }
.hc-hero:not(:has(.hc-hero-img)) .hc-btn-outline { color: var(--ink); border-color: var(--rule); }
.hc-hero:not(:has(.hc-hero-img)) .hc-btn-outline:hover { background: var(--accent-light); }
.hc-btn-full { width: 100%; justify-content: center; }

/* SECTIONS */
.hc-section { padding: clamp(3.5rem, 8vw, 6rem) 0; }
.hc-section + .hc-section { border-top: 1px solid var(--rule); }
.hc-section-header { display: flex; align-items: center; gap: 0.9rem; margin-bottom: 1.5rem; }
.hc-section-icon { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; background: var(--accent-light); border-radius: 12px; color: var(--accent); flex-shrink: 0; }
.hc-section-icon svg { width: 20px; height: 20px; }
.hc-section-title { font-family: var(--serif); font-weight: 600; font-size: clamp(1.6rem, 4vw, 2.25rem); line-height: 1.1; margin: 0; color: var(--ink); }
.hc-section-subtitle { margin: -0.5rem 0 2rem; padding-left: calc(40px + 0.9rem); font-size: 0.95rem; color: var(--ink-soft); line-height: 1.5; }

/* HOURS */
.hc-hours-section { background: var(--surface); }
.hc-hours-compact { display: flex; flex-wrap: wrap; gap: 0.75rem 2rem; padding-left: calc(40px + 0.9rem); }
.hc-hours-row { display: flex; align-items: center; gap: 0.75rem; font-size: 0.95rem; }
.hc-hours-range { font-weight: 700; min-width: 5rem; color: var(--ink); }
.hc-hours-value { color: var(--ink-soft); font-variant-numeric: tabular-nums; }
.hc-hours-row.is-closed .hc-hours-value { opacity: 0.5; font-style: italic; }

/* MENU CARDS */
.hc-menu-cards { display: grid; grid-template-columns: 1fr; gap: 1.25rem; }
@media (min-width: 640px) { .hc-menu-cards { grid-template-columns: repeat(2, 1fr); } }
@media (min-width: 900px) { .hc-menu-cards { grid-template-columns: repeat(3, 1fr); } }
.hc-menu-card { background: var(--surface); border: 1px solid var(--rule); border-radius: var(--radius); overflow: hidden; text-decoration: none; color: inherit; transition: transform 300ms cubic-bezier(.2,.65,.2,1), box-shadow 300ms; }
.hc-menu-card:hover { transform: translateY(-4px); box-shadow: 0 20px 40px -16px oklch(0 0 0 / 0.15); }
.hc-menu-card-img-wrap { aspect-ratio: 16/10; overflow: hidden; background: var(--surface-2); }
.hc-menu-card-img { width: 100%; height: 100%; object-fit: cover; transition: transform 500ms cubic-bezier(.2,.65,.2,1); }
.hc-menu-card:hover .hc-menu-card-img { transform: scale(1.05); }
.hc-menu-card-placeholder { display: flex; align-items: center; justify-content: center; color: var(--ink-faint); opacity: 0.3; }
.hc-menu-card-placeholder svg { width: 3rem; height: 3rem; }
.hc-menu-card-body { padding: 1.25rem 1.5rem 1.5rem; }
.hc-menu-card-name { font-family: var(--serif); font-weight: 600; font-size: 1.15rem; margin: 0 0 0.4rem; color: var(--ink); }
.hc-menu-card-desc { font-size: 0.88rem; color: var(--ink-soft); line-height: 1.55; margin: 0 0 0.9rem; }
.hc-menu-card-cta { font-size: 0.82rem; font-weight: 700; color: var(--accent); letter-spacing: 0.02em; }

/* LOCATION + MAP */
.hc-location-section { background: var(--surface); }
.hc-location-grid { display: grid; grid-template-columns: 1fr; gap: 2rem; }
@media (min-width: 768px) { .hc-location-grid { grid-template-columns: 1.3fr 1fr; } }
.hc-map-wrap { border-radius: var(--radius); overflow: hidden; aspect-ratio: 16/10; background: var(--surface-2); }
.hc-map { width: 100%; height: 100%; border: 0; }
.hc-map-placeholder { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: var(--ink-faint); opacity: 0.2; }
.hc-map-placeholder svg { width: 4rem; height: 4rem; }
.hc-contact-info { display: flex; flex-direction: column; gap: 1.5rem; }
.hc-address { font-style: normal; display: flex; flex-direction: column; gap: 1rem; }
.hc-contact-row { display: flex; align-items: flex-start; gap: 0.85rem; font-size: 0.95rem; color: var(--ink); line-height: 1.5; }
.hc-contact-row svg { width: 20px; height: 20px; color: var(--accent); flex-shrink: 0; margin-top: 2px; }
.hc-contact-row a { color: var(--accent); text-decoration: none; font-weight: 600; }
.hc-contact-row a:hover { text-decoration: underline; }
.hc-contact-hours { margin-top: 0.5rem; }
.hc-contact-hours-title { font-family: var(--serif); font-weight: 600; font-size: 1rem; margin: 0 0 0.75rem; color: var(--ink); }

/* SOCIAL */
.hc-social-section { background: var(--surface-2); text-align: center; }
.hc-social-section .hc-section-header { justify-content: center; }
.hc-social-grid { justify-content: center; }

/* MULTI */
.hc-locations-grid { display: grid; grid-template-columns: 1fr; gap: 1.5rem; }
@media (min-width: 640px) { .hc-locations-grid { grid-template-columns: repeat(2, 1fr); } }
@media (min-width: 1024px) { .hc-locations-grid { grid-template-columns: repeat(3, 1fr); } }

/* FOOTER */
.hc-footer { padding: clamp(3rem, 6vw, 4rem) 0; border-top: 1px solid var(--rule); background: var(--surface); }
.hc-footer-inner { display: flex; flex-direction: column; align-items: center; gap: 1.5rem; text-align: center; }
.hc-footer-brand { display: flex; align-items: center; gap: 1rem; }
.hc-footer-logo { width: 48px; height: 48px; object-fit: contain; border-radius: 12px; }
.hc-footer-name { font-family: var(--serif); font-weight: 600; font-size: 1.15rem; margin: 0; color: var(--ink); }
.hc-footer-line { margin: 0.15rem 0; font-size: 0.82rem; color: var(--ink-soft); }
.hc-footer-copy { font-size: 0.72rem; color: var(--ink-faint); margin-top: 0.5rem; }
.hc-footer-copy a { color: var(--accent); text-decoration: none; }
.hc-footer-copy a:hover { text-decoration: underline; }

@keyframes fade-up { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: translateY(0); } }
@media (prefers-reduced-motion: reduce) { .hc-hero-inner { animation: none !important; } }
</style>
