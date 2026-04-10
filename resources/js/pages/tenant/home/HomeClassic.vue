<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import MenuSeoHead from '@/components/public/MenuSeoHead.vue';
import OpeningHoursDisplay from './components/OpeningHoursDisplay.vue';
import SocialLinks from './components/SocialLinks.vue';
import LocationCard from './components/LocationCard.vue';
import { mapsDirectionsUrl, type OpeningHour } from '@/composables/useTenantHome';

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

const hero = computed(() => props.primaryLocation?.image_url ?? props.primaryLocation?.logo_url ?? null);
const mapsUrl = computed(() =>
    props.primaryLocation
        ? mapsDirectionsUrl({
              lat: props.primaryLocation.latitude,
              lng: props.primaryLocation.longitude,
              address: props.primaryLocation.address,
          })
        : null,
);
</script>

<template>
    <MenuSeoHead :meta="seo" :json-ld="(seo.jsonLd as any)" />
    <Head>
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
        <link
            href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;1,400;1,500&family=Lato:wght@300;400;700&display=swap"
            rel="stylesheet"
        />
    </Head>

    <div class="hc">
        <!-- ─── HERO ─── -->
        <header class="hc-hero">
            <div v-if="hero" class="hc-hero-img" :style="{ backgroundImage: `url(${hero})` }" />
            <div class="hc-hero-overlay" />
            <div class="hc-hero-inner">
                <p v-if="primaryLocation?.city" class="hc-kicker">{{ primaryLocation.city }}</p>
                <h1 class="hc-title">
                    {{ primaryLocation?.name ?? tenant.name }}
                </h1>
                <p v-if="primaryLocation?.description" class="hc-sub">{{ primaryLocation.description }}</p>
                <div class="hc-ornament" aria-hidden="true">
                    <span class="hc-rule" /><span class="hc-diamond">◆</span><span class="hc-rule" />
                </div>
                <div v-if="primaryLocation?.menus?.length && !isMultiLocation" class="hc-hero-ctas">
                    <a
                        v-for="menu in primaryLocation.menus"
                        :key="menu.id"
                        :href="`/menu/${menu.id}`"
                        class="hc-btn-primary"
                    >
                        Ver {{ menu.name }}
                    </a>
                </div>
            </div>
        </header>

        <!-- ─── MAIN ─── -->
        <main class="hc-main">

            <!-- Single-location content -->
            <template v-if="!isMultiLocation && primaryLocation">

                <!-- Menus cards -->
                <section v-if="primaryLocation.menus?.length" class="hc-section">
                    <div class="hc-section-head">
                        <h2 class="hc-section-title">Nuestra Carta</h2>
                        <div class="hc-section-line" />
                    </div>
                    <div class="hc-cards">
                        <a
                            v-for="menu in primaryLocation.menus"
                            :key="menu.id"
                            :href="`/menu/${menu.id}`"
                            class="hc-card"
                        >
                            <div v-if="menu.image_path" class="hc-card-img-wrap">
                                <img :src="menu.image_path" :alt="menu.name" class="hc-card-img" loading="lazy" />
                            </div>
                            <div class="hc-card-body">
                                <h3 class="hc-card-title">{{ menu.name }}</h3>
                                <p v-if="menu.description" class="hc-card-desc">{{ menu.description }}</p>
                                <span class="hc-card-link">Ver carta →</span>
                            </div>
                        </a>
                    </div>
                </section>

                <!-- Horarios + Contacto -->
                <div class="hc-info-grid">

                    <!-- Horarios -->
                    <section v-if="primaryLocation.opening_hours?.length" class="hc-info-block">
                        <h2 class="hc-info-title">Horario</h2>
                        <OpeningHoursDisplay :hours="primaryLocation.opening_hours" />
                    </section>

                    <!-- Contacto -->
                    <section class="hc-info-block">
                        <h2 class="hc-info-title">Contacto</h2>
                        <address class="hc-address">
                            <p v-if="primaryLocation.address">{{ primaryLocation.address }}</p>
                            <p v-if="primaryLocation.city">{{ primaryLocation.city }}</p>
                            <a v-if="primaryLocation.phone" :href="`tel:${primaryLocation.phone}`" class="hc-phone">
                                {{ primaryLocation.phone }}
                            </a>
                        </address>

                        <div v-if="mapsUrl" class="hc-map-cta">
                            <a :href="mapsUrl" target="_blank" rel="noopener" class="hc-btn-outline">
                                Cómo llegar
                            </a>
                        </div>

                        <SocialLinks
                            v-if="primaryLocation.social_medias"
                            :social-medias="primaryLocation.social_medias"
                            size="md"
                            class="hc-socials"
                        />
                    </section>
                </div>
            </template>

            <!-- Multi-location grid -->
            <template v-else-if="isMultiLocation">
                <section class="hc-section">
                    <div class="hc-section-head">
                        <h2 class="hc-section-title">Nuestros Locales</h2>
                        <div class="hc-section-line" />
                    </div>
                    <div class="hc-locations-grid">
                        <LocationCard
                            v-for="loc in locations"
                            :key="loc.id"
                            :location="loc"
                        />
                    </div>
                </section>
            </template>
        </main>

        <!-- ─── FOOTER ─── -->
        <footer class="hc-footer">
            <div class="hc-footer-inner">
                <p class="hc-footer-name">{{ primaryLocation?.name ?? tenant.name }}</p>
                <p v-if="primaryLocation?.address" class="hc-footer-line">{{ primaryLocation.address }}</p>
                <p v-if="primaryLocation?.city" class="hc-footer-line">{{ primaryLocation.city }}</p>
                <SocialLinks
                    v-if="primaryLocation?.social_medias"
                    :social-medias="primaryLocation.social_medias"
                    size="sm"
                    class="hc-footer-socials"
                />
                <p class="hc-footer-copy">
                    Menú digital con
                    <a href="https://menulinker.com" target="_blank" rel="noopener">MenuLinker</a>
                </p>
            </div>
        </footer>
    </div>
</template>

<style scoped>
/* ===== CLASSIC RESTAURANT TEMPLATE ===== */
.hc {
    --hc-bg: #faf8f4;
    --hc-paper: #ffffff;
    --hc-ink: #1c1a17;
    --hc-ink-soft: #5a5347;
    --hc-ink-faint: #9a9087;
    --hc-accent: #8b4513;
    --hc-accent-light: #c8702e;
    --hc-rule: #e8e0d4;
    --hc-serif: 'Playfair Display', 'Georgia', serif;
    --hc-sans: 'Lato', 'Helvetica Neue', system-ui, sans-serif;
    --hc-radius: 0.75rem;

    position: relative;
    min-height: 100vh;
    background: var(--hc-bg);
    color: var(--hc-ink);
    font-family: var(--hc-sans);
    font-weight: 400;
    -webkit-font-smoothing: antialiased;
    overflow-x: hidden;
}

@media (prefers-color-scheme: dark) {
    .hc {
        --hc-bg: #1a1714;
        --hc-paper: #211e1a;
        --hc-ink: #f0ebe2;
        --hc-ink-soft: #b5aa9a;
        --hc-ink-faint: #7a7168;
        --hc-accent: #c8702e;
        --hc-accent-light: #e8913d;
        --hc-rule: #332e28;
    }
}

/* ─── HERO ─── */
.hc-hero {
    position: relative;
    min-height: clamp(480px, 75svh, 680px);
    display: flex;
    align-items: flex-end;
    overflow: hidden;
}

.hc-hero-img {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center 30%;
    filter: saturate(0.85) contrast(1.05);
    transform: scale(1.04);
    transition: transform 12s ease;
}

.hc-hero:hover .hc-hero-img {
    transform: scale(1.0);
}

.hc-hero-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(
        170deg,
        rgba(10, 8, 5, 0.15) 0%,
        rgba(10, 8, 5, 0.35) 45%,
        rgba(10, 8, 5, 0.82) 100%
    );
}

.hc-hero-inner {
    position: relative;
    z-index: 2;
    width: 100%;
    max-width: 760px;
    margin: 0 auto;
    padding: clamp(1.5rem, 4vw, 3rem);
    padding-top: clamp(4rem, 12vw, 7rem);
    text-align: center;
    color: #faf6f0;
    animation: hc-fade-up 900ms cubic-bezier(.2,.65,.2,1) both;
}

.hc-kicker {
    font-family: var(--hc-sans);
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.38em;
    text-transform: uppercase;
    opacity: 0.75;
    margin-bottom: 1rem;
}

.hc-title {
    font-family: var(--hc-serif);
    font-weight: 400;
    font-style: italic;
    font-size: clamp(2.8rem, 10vw, 5.5rem);
    line-height: 1.05;
    letter-spacing: -0.01em;
    margin: 0 0 1.2rem;
    text-shadow: 0 2px 20px rgba(0,0,0,0.4);
}

.hc-sub {
    font-family: var(--hc-sans);
    font-weight: 300;
    font-size: clamp(0.95rem, 2.4vw, 1.15rem);
    line-height: 1.65;
    max-width: 52ch;
    margin: 0 auto 1.5rem;
    opacity: 0.88;
}

.hc-ornament {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    margin-bottom: 1.75rem;
    opacity: 0.6;
}

.hc-rule {
    display: block;
    height: 1px;
    width: clamp(40px, 10vw, 80px);
    background: currentColor;
}

.hc-diamond {
    font-size: 0.65rem;
    letter-spacing: 0;
}

.hc-hero-ctas {
    display: flex;
    gap: 0.75rem;
    justify-content: center;
    flex-wrap: wrap;
}

.hc-btn-primary {
    display: inline-block;
    padding: 0.75rem 2rem;
    border: 1.5px solid rgba(250,246,240,0.75);
    border-radius: 0;
    font-family: var(--hc-sans);
    font-size: 0.8rem;
    font-weight: 700;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: #faf6f0;
    text-decoration: none;
    transition: background 250ms, color 250ms;
}

.hc-btn-primary:hover {
    background: #faf6f0;
    color: var(--hc-ink);
}

/* ─── MAIN ─── */
.hc-main {
    max-width: 1120px;
    margin: 0 auto;
    padding: clamp(3rem, 8vw, 6rem) clamp(1.25rem, 4vw, 2.5rem);
}

/* ─── SECTION HEAD ─── */
.hc-section {
    margin-bottom: clamp(3rem, 8vw, 5rem);
}

.hc-section-head {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    margin-bottom: 2.5rem;
}

.hc-section-title {
    font-family: var(--hc-serif);
    font-style: italic;
    font-weight: 400;
    font-size: clamp(1.8rem, 5vw, 2.6rem);
    margin: 0;
    color: var(--hc-ink);
    white-space: nowrap;
}

.hc-section-line {
    flex: 1;
    height: 1px;
    background: var(--hc-rule);
}

/* ─── MENU CARDS ─── */
.hc-cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(min(100%, 300px), 1fr));
    gap: 1.5rem;
}

.hc-card {
    background: var(--hc-paper);
    border: 1px solid var(--hc-rule);
    border-radius: var(--hc-radius);
    overflow: hidden;
    text-decoration: none;
    color: inherit;
    transition: box-shadow 300ms, transform 300ms;
    display: flex;
    flex-direction: column;
}

.hc-card:hover {
    box-shadow: 0 12px 32px -8px rgba(0,0,0,0.18);
    transform: translateY(-3px);
}

.hc-card-img-wrap {
    aspect-ratio: 16/9;
    overflow: hidden;
}

.hc-card-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    filter: saturate(0.9);
    transition: transform 400ms cubic-bezier(.2,.65,.2,1);
}

.hc-card:hover .hc-card-img {
    transform: scale(1.06);
}

.hc-card-body {
    padding: 1.25rem 1.5rem 1.5rem;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.hc-card-title {
    font-family: var(--hc-serif);
    font-style: italic;
    font-weight: 400;
    font-size: 1.35rem;
    margin: 0 0 0.5rem;
    color: var(--hc-ink);
}

.hc-card-desc {
    font-size: 0.88rem;
    line-height: 1.6;
    color: var(--hc-ink-soft);
    margin: 0 0 1rem;
    flex: 1;
}

.hc-card-link {
    font-family: var(--hc-sans);
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--hc-accent);
    margin-top: auto;
}

/* ─── INFO GRID ─── */
.hc-info-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 2.5rem;
    background: var(--hc-paper);
    border: 1px solid var(--hc-rule);
    border-radius: var(--hc-radius);
    padding: 2.5rem;
}

@media (min-width: 680px) {
    .hc-info-grid {
        grid-template-columns: 1fr 1fr;
    }
}

.hc-info-block {}

.hc-info-title {
    font-family: var(--hc-serif);
    font-style: italic;
    font-weight: 400;
    font-size: 1.4rem;
    margin: 0 0 1.25rem;
    color: var(--hc-ink);
    padding-bottom: 0.75rem;
    border-bottom: 1px solid var(--hc-rule);
    position: relative;
}

.hc-info-title::after {
    content: '';
    position: absolute;
    bottom: -1px;
    left: 0;
    width: 3rem;
    height: 2px;
    background: var(--hc-accent);
}

.hc-address {
    font-style: normal;
    font-size: 0.9rem;
    line-height: 1.7;
    color: var(--hc-ink-soft);
}

.hc-address p { margin: 0; }

.hc-phone {
    display: inline-block;
    margin-top: 0.5rem;
    color: var(--hc-accent);
    font-weight: 700;
    text-decoration: none;
    font-size: 0.92rem;
    letter-spacing: 0.02em;
}

.hc-phone:hover {
    color: var(--hc-accent-light);
}

.hc-map-cta {
    margin-top: 1.25rem;
}

.hc-btn-outline {
    display: inline-block;
    padding: 0.55rem 1.4rem;
    border: 1.5px solid var(--hc-accent);
    border-radius: 0;
    font-family: var(--hc-sans);
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    color: var(--hc-accent);
    text-decoration: none;
    transition: background 250ms, color 250ms;
}

.hc-btn-outline:hover {
    background: var(--hc-accent);
    color: var(--hc-bg);
}

.hc-socials {
    margin-top: 1.5rem;
    color: var(--hc-ink-soft);
}

/* ─── LOCATIONS GRID ─── */
.hc-locations-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(min(100%, 320px), 1fr));
    gap: 1.5rem;
}

/* ─── FOOTER ─── */
.hc-footer {
    background: var(--hc-ink);
    color: rgba(250,246,240,0.7);
    padding: clamp(2.5rem, 6vw, 4rem) clamp(1.25rem, 4vw, 2.5rem);
}

.hc-footer-inner {
    max-width: 760px;
    margin: 0 auto;
    text-align: center;
}

.hc-footer-name {
    font-family: var(--hc-serif);
    font-style: italic;
    font-size: 1.5rem;
    font-weight: 400;
    color: rgba(250,246,240,0.95);
    margin: 0 0 0.5rem;
}

.hc-footer-line {
    margin: 0.15rem 0;
    font-size: 0.83rem;
    letter-spacing: 0.01em;
}

.hc-footer-socials {
    justify-content: center;
    margin-top: 1.25rem;
    color: rgba(250,246,240,0.65);
}

.hc-footer-copy {
    margin-top: 2rem;
    font-size: 0.72rem;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    opacity: 0.45;
}

.hc-footer-copy a {
    color: inherit;
    text-decoration: underline;
    text-underline-offset: 3px;
}

/* ─── ANIMATIONS ─── */
@keyframes hc-fade-up {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
}

@media (prefers-reduced-motion: reduce) {
    .hc-hero-inner { animation: none; }
    .hc-hero-img { transition: none; }
}
</style>
