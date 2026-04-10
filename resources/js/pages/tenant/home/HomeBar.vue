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

const hero = computed(() => props.primaryLocation?.image_url ?? null);
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
            href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@300;400;500;600;700&display=swap"
            rel="stylesheet"
        />
    </Head>

    <div class="bar">

        <!-- ─── HERO ─── -->
        <header class="bar-hero">
            <div v-if="hero" class="bar-hero-bg" :style="{ backgroundImage: `url(${hero})` }" />
            <div class="bar-hero-gradient" />

            <!-- Scanline texture -->
            <div class="bar-scanlines" aria-hidden="true" />

            <div class="bar-hero-inner">
                <div class="bar-tag" v-if="primaryLocation?.city">{{ primaryLocation.city }}</div>
                <h1 class="bar-title">{{ primaryLocation?.name ?? tenant.name }}</h1>
                <p v-if="primaryLocation?.description" class="bar-sub">{{ primaryLocation.description }}</p>

                <div class="bar-ctas" v-if="primaryLocation?.menus?.length && !isMultiLocation">
                    <a
                        v-for="menu in primaryLocation.menus"
                        :key="menu.id"
                        :href="`/menu/${menu.id}`"
                        class="bar-btn"
                    >
                        <span class="bar-btn-inner">{{ menu.name }}</span>
                    </a>
                </div>

                <SocialLinks
                    v-if="primaryLocation?.social_medias"
                    :social-medias="primaryLocation.social_medias"
                    size="lg"
                    class="bar-hero-socials"
                />
            </div>

            <!-- Scroll indicator -->
            <div class="bar-scroll-hint" aria-hidden="true">
                <span class="bar-scroll-line" />
                <span class="bar-scroll-label">Scroll</span>
            </div>
        </header>

        <!-- ─── MAIN ─── -->
        <main class="bar-main">

            <template v-if="!isMultiLocation && primaryLocation">

                <!-- Menus -->
                <section v-if="primaryLocation.menus?.length" class="bar-section">
                    <div class="bar-section-label">— Menús —</div>
                    <h2 class="bar-section-title">NUESTRA CARTA</h2>
                    <div class="bar-menu-grid">
                        <a
                            v-for="menu in primaryLocation.menus"
                            :key="menu.id"
                            :href="`/menu/${menu.id}`"
                            class="bar-menu-card"
                        >
                            <div v-if="menu.image_path" class="bar-menu-img-wrap">
                                <img :src="menu.image_path" :alt="menu.name" class="bar-menu-img" loading="lazy" />
                            </div>
                            <div v-else class="bar-menu-no-img">
                                <span class="bar-menu-glyph">🍹</span>
                            </div>
                            <div class="bar-menu-overlay">
                                <span class="bar-menu-card-name">{{ menu.name }}</span>
                                <span class="bar-menu-card-cta">Ver carta →</span>
                            </div>
                        </a>
                    </div>
                </section>

                <!-- Horarios + Contacto en cols -->
                <section class="bar-info-section">
                    <div class="bar-info-grid">

                        <div v-if="primaryLocation.opening_hours?.length" class="bar-info-col">
                            <h3 class="bar-info-heading">HORARIO</h3>
                            <OpeningHoursDisplay :hours="primaryLocation.opening_hours" class="bar-oh" />
                        </div>

                        <div class="bar-info-col">
                            <h3 class="bar-info-heading">ENCUÉNTRANOS</h3>
                            <address class="bar-address">
                                <p v-if="primaryLocation.address">{{ primaryLocation.address }}</p>
                                <p v-if="primaryLocation.city">{{ primaryLocation.city }}</p>
                            </address>
                            <a v-if="primaryLocation.phone" :href="`tel:${primaryLocation.phone}`" class="bar-phone">
                                {{ primaryLocation.phone }}
                            </a>
                            <a v-if="mapsUrl" :href="mapsUrl" target="_blank" rel="noopener" class="bar-map-btn">
                                CÓMO LLEGAR →
                            </a>
                        </div>

                        <div class="bar-info-col">
                            <h3 class="bar-info-heading">SÍGUENOS</h3>
                            <SocialLinks
                                v-if="primaryLocation.social_medias"
                                :social-medias="primaryLocation.social_medias"
                                size="lg"
                                class="bar-socials"
                            />
                            <p v-else class="bar-no-socials">Pronto en redes</p>
                        </div>
                    </div>
                </section>

            </template>

            <!-- Multi-location -->
            <template v-else-if="isMultiLocation">
                <section class="bar-section">
                    <div class="bar-section-label">— Locales —</div>
                    <h2 class="bar-section-title">NUESTROS BARES</h2>
                    <div class="bar-locations-grid">
                        <LocationCard v-for="loc in locations" :key="loc.id" :location="loc" />
                    </div>
                </section>
            </template>

        </main>

        <!-- ─── FOOTER ─── -->
        <footer class="bar-footer">
            <div class="bar-footer-neon" aria-hidden="true">✦</div>
            <p class="bar-footer-name">{{ primaryLocation?.name ?? tenant.name }}</p>
            <p v-if="primaryLocation?.address" class="bar-footer-line">
                {{ [primaryLocation.address, primaryLocation.city].filter(Boolean).join(' · ') }}
            </p>
            <p class="bar-footer-credit">
                <a href="https://menulinker.com" target="_blank" rel="noopener">MenuLinker</a>
            </p>
        </footer>
    </div>
</template>

<style scoped>
/* ===== BAR / COCKTAIL BAR TEMPLATE ===== */
.bar {
    --bar-bg: #0c0c0e;
    --bar-surface: #13131a;
    --bar-surface2: #1a1a24;
    --bar-ink: #e8e4f0;
    --bar-ink-soft: rgba(232,228,240,0.55);
    --bar-ink-faint: rgba(232,228,240,0.3);
    --bar-neon: #b48aff;
    --bar-neon2: #ff6eb4;
    --bar-neon-glow: rgba(180,138,255,0.35);
    --bar-border: rgba(180,138,255,0.15);
    --bar-display: 'Bebas Neue', 'Impact', 'Arial Black', sans-serif;
    --bar-sans: 'Inter', system-ui, sans-serif;

    background: var(--bar-bg);
    color: var(--bar-ink);
    font-family: var(--bar-sans);
    -webkit-font-smoothing: antialiased;
    min-height: 100vh;
    overflow-x: hidden;
}

/* ─── HERO ─── */
.bar-hero {
    position: relative;
    min-height: 100svh;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.bar-hero-bg {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center;
    filter: saturate(0.4) brightness(0.35);
}

.bar-hero-gradient {
    position: absolute;
    inset: 0;
    background:
        radial-gradient(ellipse 80% 60% at 50% 120%, rgba(180,138,255,0.18) 0%, transparent 70%),
        linear-gradient(to bottom, rgba(12,12,14,0.3) 0%, rgba(12,12,14,0.92) 100%);
}

.bar-scanlines {
    position: absolute;
    inset: 0;
    background-image: repeating-linear-gradient(
        0deg,
        rgba(255,255,255,0.016) 0px,
        rgba(255,255,255,0.016) 1px,
        transparent 1px,
        transparent 3px
    );
    pointer-events: none;
    z-index: 1;
}

.bar-hero-inner {
    position: relative;
    z-index: 5;
    text-align: center;
    padding: 2rem;
    max-width: 900px;
    width: 100%;
    animation: bar-fade 1s ease both;
}

@keyframes bar-fade {
    from { opacity: 0; transform: translateY(30px); }
    to   { opacity: 1; transform: translateY(0); }
}

.bar-tag {
    display: inline-block;
    border: 1px solid var(--bar-border);
    padding: 0.35rem 1.25rem;
    font-size: 0.68rem;
    font-weight: 600;
    letter-spacing: 0.4em;
    text-transform: uppercase;
    color: var(--bar-neon);
    margin-bottom: 1.5rem;
    border-radius: 999px;
    background: rgba(180,138,255,0.06);
}

.bar-title {
    font-family: var(--bar-display);
    font-size: clamp(4rem, 18vw, 12rem);
    line-height: 0.92;
    letter-spacing: 0.04em;
    color: var(--bar-ink);
    margin: 0 0 1.25rem;
    text-shadow:
        0 0 40px rgba(180,138,255,0.25),
        0 0 80px rgba(180,138,255,0.12);
}

.bar-sub {
    font-size: clamp(0.9rem, 2.2vw, 1.05rem);
    font-weight: 300;
    line-height: 1.7;
    color: var(--bar-ink-soft);
    max-width: 50ch;
    margin: 0 auto 2rem;
}

.bar-ctas {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
    margin-bottom: 2rem;
}

.bar-btn {
    display: inline-block;
    text-decoration: none;
    position: relative;
}

.bar-btn-inner {
    display: inline-block;
    padding: 0.8rem 2.5rem;
    border: 1.5px solid var(--bar-neon);
    font-family: var(--bar-sans);
    font-size: 0.8rem;
    font-weight: 600;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: var(--bar-neon);
    background: transparent;
    transition: background 250ms, color 250ms, box-shadow 250ms;
    cursor: pointer;
}

.bar-btn:hover .bar-btn-inner {
    background: var(--bar-neon);
    color: #0c0c0e;
    box-shadow: 0 0 24px var(--bar-neon-glow);
}

.bar-hero-socials {
    justify-content: center;
    color: var(--bar-ink-soft);
}

.bar-scroll-hint {
    position: absolute;
    bottom: 2rem;
    left: 50%;
    transform: translateX(-50%);
    z-index: 5;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.6rem;
    color: var(--bar-ink-faint);
    animation: bar-bounce 2s ease-in-out infinite;
}

@keyframes bar-bounce {
    0%, 100% { transform: translateX(-50%) translateY(0); }
    50%       { transform: translateX(-50%) translateY(8px); }
}

.bar-scroll-line {
    display: block;
    width: 1px;
    height: 3rem;
    background: linear-gradient(to bottom, transparent, currentColor);
}

.bar-scroll-label {
    font-size: 0.6rem;
    letter-spacing: 0.3em;
    text-transform: uppercase;
}

/* ─── MAIN ─── */
.bar-main {
    max-width: 1100px;
    margin: 0 auto;
    padding: clamp(3rem, 8vw, 6rem) clamp(1rem, 4vw, 2.5rem);
}

/* ─── SECTION ─── */
.bar-section {
    margin-bottom: clamp(3rem, 8vw, 5rem);
}

.bar-section-label {
    font-size: 0.7rem;
    letter-spacing: 0.4em;
    text-transform: uppercase;
    color: var(--bar-neon);
    margin-bottom: 0.6rem;
    text-align: center;
}

.bar-section-title {
    font-family: var(--bar-display);
    font-size: clamp(2.5rem, 8vw, 5rem);
    letter-spacing: 0.06em;
    color: var(--bar-ink);
    margin: 0 0 2.5rem;
    text-align: center;
    text-shadow: 0 0 40px rgba(180,138,255,0.15);
}

/* ─── MENU GRID ─── */
.bar-menu-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(min(100%, 280px), 1fr));
    gap: 1.5rem;
}

.bar-menu-card {
    position: relative;
    aspect-ratio: 3/4;
    border: 1px solid var(--bar-border);
    overflow: hidden;
    background: var(--bar-surface);
    text-decoration: none;
    display: block;
    transition: box-shadow 300ms;
}

.bar-menu-card:hover {
    box-shadow: 0 0 40px var(--bar-neon-glow);
}

.bar-menu-img-wrap {
    position: absolute;
    inset: 0;
}

.bar-menu-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    filter: saturate(0.6) brightness(0.55);
    transition: filter 300ms, transform 400ms;
}

.bar-menu-card:hover .bar-menu-img {
    filter: saturate(0.8) brightness(0.65);
    transform: scale(1.05);
}

.bar-menu-no-img {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--bar-surface2);
}

.bar-menu-glyph {
    font-size: 4rem;
    opacity: 0.5;
}

.bar-menu-overlay {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    padding: 1.75rem 1.5rem;
    background: linear-gradient(to top, rgba(12,12,14,0.95) 0%, transparent 60%);
}

.bar-menu-card-name {
    font-family: var(--bar-display);
    font-size: 2rem;
    letter-spacing: 0.06em;
    color: var(--bar-ink);
    line-height: 1.1;
    margin-bottom: 0.5rem;
}

.bar-menu-card-cta {
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: var(--bar-neon);
}

/* ─── INFO SECTION ─── */
.bar-info-section {
    border-top: 1px solid var(--bar-border);
    padding-top: clamp(2rem, 6vw, 4rem);
}

.bar-info-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 2.5rem;
}

@media (min-width: 680px) {
    .bar-info-grid { grid-template-columns: 1fr 1fr 1fr; }
}

.bar-info-col {}

.bar-info-heading {
    font-family: var(--bar-display);
    font-size: 1.25rem;
    letter-spacing: 0.22em;
    color: var(--bar-neon);
    margin: 0 0 1.25rem;
    padding-bottom: 0.6rem;
    border-bottom: 1px solid var(--bar-border);
}

.bar-oh {
    color: var(--bar-ink-soft);
    font-size: 0.87rem;
}

.bar-address {
    font-style: normal;
    font-size: 0.9rem;
    line-height: 1.7;
    color: var(--bar-ink-soft);
}

.bar-address p { margin: 0; }

.bar-phone {
    display: block;
    margin-top: 0.75rem;
    font-size: 1rem;
    font-weight: 500;
    color: var(--bar-neon);
    text-decoration: none;
    letter-spacing: 0.04em;
}

.bar-phone:hover {
    color: var(--bar-neon2);
}

.bar-map-btn {
    display: inline-block;
    margin-top: 1rem;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: var(--bar-ink-faint);
    text-decoration: none;
    border-bottom: 1px solid var(--bar-border);
    padding-bottom: 2px;
    transition: color 200ms, border-color 200ms;
}

.bar-map-btn:hover {
    color: var(--bar-neon);
    border-color: var(--bar-neon);
}

.bar-socials {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.85rem;
    color: var(--bar-ink-soft);
}

.bar-no-socials {
    color: var(--bar-ink-faint);
    font-size: 0.85rem;
    font-style: italic;
}

/* ─── LOCATIONS GRID ─── */
.bar-locations-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(min(100%, 280px), 1fr));
    gap: 1.5rem;
    --lc-card-bg: var(--bar-surface);
    --lc-card-border: var(--bar-border);
    --lc-accent: var(--bar-neon);
    --lc-name-color: var(--bar-ink);
}

/* ─── FOOTER ─── */
.bar-footer {
    background: var(--bar-surface);
    border-top: 1px solid var(--bar-border);
    text-align: center;
    padding: clamp(2.5rem, 6vw, 4rem) 2rem;
}

.bar-footer-neon {
    font-size: 2rem;
    color: var(--bar-neon);
    margin-bottom: 1rem;
    display: block;
    text-shadow: 0 0 20px var(--bar-neon-glow);
    animation: bar-pulse 3s ease-in-out infinite;
}

@keyframes bar-pulse {
    0%, 100% { opacity: 1; }
    50%       { opacity: 0.5; }
}

.bar-footer-name {
    font-family: var(--bar-display);
    font-size: 2rem;
    letter-spacing: 0.12em;
    margin: 0 0 0.5rem;
    color: var(--bar-ink);
}

.bar-footer-line {
    font-size: 0.85rem;
    color: var(--bar-ink-soft);
    margin: 0.25rem 0;
    letter-spacing: 0.04em;
}

.bar-footer-credit {
    margin-top: 2rem;
    font-size: 0.7rem;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--bar-ink-faint);
}

.bar-footer-credit a {
    color: inherit;
    text-decoration: none;
}

.bar-footer-credit a:hover {
    color: var(--bar-neon);
}

@media (prefers-reduced-motion: reduce) {
    .bar-hero-inner { animation: none; }
    .bar-scroll-hint, .bar-footer-neon { animation: none; }
    .bar-menu-card:hover .bar-menu-img { transform: none; }
}
</style>
