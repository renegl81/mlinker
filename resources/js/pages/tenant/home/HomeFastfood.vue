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
            href="https://fonts.googleapis.com/css2?family=Oswald:wght@400;500;600;700&family=Open+Sans:wght@400;600&display=swap"
            rel="stylesheet"
        />
    </Head>

    <div class="ff">

        <!-- ─── HERO ─── -->
        <header class="ff-hero">
            <!-- Geometric pattern background -->
            <div class="ff-pattern" aria-hidden="true" />

            <div v-if="hero" class="ff-hero-img-wrap">
                <img :src="hero" :alt="primaryLocation?.name ?? ''" class="ff-hero-img" />
            </div>

            <div class="ff-hero-content">
                <div class="ff-hero-text">
                    <div class="ff-badge" v-if="primaryLocation?.city">📍 {{ primaryLocation.city }}</div>
                    <h1 class="ff-title">{{ primaryLocation?.name ?? tenant.name }}</h1>
                    <p v-if="primaryLocation?.description" class="ff-sub">{{ primaryLocation.description }}</p>

                    <div class="ff-ctas">
                        <a
                            v-for="menu in (primaryLocation?.menus ?? [])"
                            :key="menu.id"
                            :href="`/menu/${menu.id}`"
                            class="ff-btn-primary"
                        >
                            🍔 Ver Carta
                        </a>
                        <a v-if="mapsUrl" :href="mapsUrl" target="_blank" rel="noopener" class="ff-btn-secondary">
                            📍 Cómo llegar
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- ─── MAIN ─── -->
        <main class="ff-main">

            <template v-if="!isMultiLocation && primaryLocation">

                <!-- Menús -->
                <section v-if="primaryLocation.menus?.length" class="ff-section">
                    <div class="ff-section-header">
                        <h2 class="ff-section-title">NUESTRA CARTA</h2>
                        <div class="ff-section-badge">{{ primaryLocation.menus.length }} menú{{ primaryLocation.menus.length > 1 ? 's' : '' }}</div>
                    </div>
                    <div class="ff-menu-grid">
                        <a
                            v-for="menu in primaryLocation.menus"
                            :key="menu.id"
                            :href="`/menu/${menu.id}`"
                            class="ff-menu-tile"
                        >
                            <div v-if="menu.image_path" class="ff-menu-tile-img-wrap">
                                <img :src="menu.image_path" :alt="menu.name" class="ff-menu-tile-img" loading="lazy" />
                            </div>
                            <div class="ff-menu-tile-body">
                                <span class="ff-menu-tile-emoji">🍽️</span>
                                <strong class="ff-menu-tile-name">{{ menu.name }}</strong>
                                <span v-if="menu.description" class="ff-menu-tile-desc">{{ menu.description }}</span>
                                <span class="ff-menu-tile-cta">VER CARTA →</span>
                            </div>
                        </a>
                    </div>
                </section>

                <!-- Info strip -->
                <div class="ff-info-strip">

                    <div v-if="primaryLocation.opening_hours?.length" class="ff-info-block">
                        <div class="ff-info-icon">🕐</div>
                        <div class="ff-info-content">
                            <h3 class="ff-info-title">HORARIO</h3>
                            <OpeningHoursDisplay :hours="primaryLocation.opening_hours" compact />
                        </div>
                    </div>

                    <div class="ff-info-block">
                        <div class="ff-info-icon">📍</div>
                        <div class="ff-info-content">
                            <h3 class="ff-info-title">DIRECCIÓN</h3>
                            <address class="ff-address">
                                <p v-if="primaryLocation.address">{{ primaryLocation.address }}</p>
                                <p v-if="primaryLocation.city">{{ primaryLocation.city }}</p>
                            </address>
                            <a v-if="primaryLocation.phone" :href="`tel:${primaryLocation.phone}`" class="ff-phone">
                                📞 {{ primaryLocation.phone }}
                            </a>
                        </div>
                    </div>

                    <div v-if="primaryLocation.social_medias" class="ff-info-block">
                        <div class="ff-info-icon">📲</div>
                        <div class="ff-info-content">
                            <h3 class="ff-info-title">SÍGUENOS</h3>
                            <SocialLinks :social-medias="primaryLocation.social_medias" size="lg" class="ff-socials" />
                        </div>
                    </div>
                </div>

            </template>

            <!-- Multi-location -->
            <template v-else-if="isMultiLocation">
                <section class="ff-section">
                    <div class="ff-section-header">
                        <h2 class="ff-section-title">NUESTROS LOCALES</h2>
                    </div>
                    <div class="ff-locations-grid">
                        <LocationCard v-for="loc in locations" :key="loc.id" :location="loc" />
                    </div>
                </section>
            </template>

        </main>

        <!-- ─── FOOTER ─── -->
        <footer class="ff-footer">
            <div class="ff-footer-inner">
                <p class="ff-footer-name">{{ primaryLocation?.name ?? tenant.name }}</p>
                <p v-if="primaryLocation?.address" class="ff-footer-address">
                    {{ [primaryLocation.address, primaryLocation.city].filter(Boolean).join(', ') }}
                </p>
                <div v-if="primaryLocation?.menus?.length" class="ff-footer-cta">
                    <a :href="`/menu/${primaryLocation.menus[0].id}`" class="ff-footer-btn">VER CARTA COMPLETA →</a>
                </div>
                <p class="ff-footer-credit">
                    Menú digital con <a href="https://menulinker.com" target="_blank" rel="noopener">MenuLinker</a>
                </p>
            </div>
        </footer>
    </div>
</template>

<style scoped>
/* ===== FAST FOOD / STREET FOOD TEMPLATE ===== */
.ff {
    --ff-yellow: #ffd000;
    --ff-red: #e63329;
    --ff-red-dark: #b52920;
    --ff-bg: #1a1208;
    --ff-surface: #231809;
    --ff-ink: #fff8f0;
    --ff-ink-soft: rgba(255,248,240,0.7);
    --ff-ink-faint: rgba(255,248,240,0.4);
    --ff-display: 'Oswald', 'Impact', 'Arial Black', sans-serif;
    --ff-body: 'Open Sans', system-ui, sans-serif;

    background: var(--ff-bg);
    color: var(--ff-ink);
    font-family: var(--ff-body);
    -webkit-font-smoothing: antialiased;
    min-height: 100vh;
    overflow-x: hidden;
}

/* ─── HERO ─── */
.ff-hero {
    position: relative;
    min-height: clamp(420px, 70svh, 640px);
    display: flex;
    align-items: center;
    overflow: hidden;
}

.ff-pattern {
    position: absolute;
    inset: 0;
    background-color: var(--ff-red);
    background-image:
        repeating-linear-gradient(
            45deg,
            rgba(0,0,0,0.08) 0px,
            rgba(0,0,0,0.08) 20px,
            transparent 20px,
            transparent 40px
        ),
        repeating-linear-gradient(
            -45deg,
            rgba(0,0,0,0.08) 0px,
            rgba(0,0,0,0.08) 20px,
            transparent 20px,
            transparent 40px
        );
}

.ff-hero-img-wrap {
    position: absolute;
    inset: 0;
    overflow: hidden;
}

.ff-hero-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    mix-blend-mode: multiply;
    filter: saturate(1.3) brightness(0.65);
}

.ff-hero-content {
    position: relative;
    z-index: 5;
    width: 100%;
    padding: clamp(2rem, 5vw, 4rem);
}

.ff-hero-text {
    max-width: 700px;
    animation: ff-smash-in 600ms cubic-bezier(.2,.65,.2,1) both;
}

@keyframes ff-smash-in {
    from { opacity: 0; transform: translateY(-30px) scale(0.96); }
    to   { opacity: 1; transform: none; }
}

.ff-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    background: var(--ff-yellow);
    color: #1a1208;
    font-family: var(--ff-display);
    font-size: 0.85rem;
    font-weight: 600;
    letter-spacing: 0.05em;
    padding: 0.4rem 1rem;
    margin-bottom: 1rem;
}

.ff-title {
    font-family: var(--ff-display);
    font-weight: 700;
    font-size: clamp(3.5rem, 13vw, 8rem);
    line-height: 0.92;
    letter-spacing: 0.02em;
    text-transform: uppercase;
    color: var(--ff-ink);
    margin: 0 0 1rem;
    text-shadow:
        4px 4px 0 rgba(0,0,0,0.35),
        -1px -1px 0 rgba(0,0,0,0.2);
}

.ff-sub {
    font-size: clamp(0.9rem, 2.2vw, 1.05rem);
    font-weight: 400;
    line-height: 1.65;
    color: var(--ff-ink-soft);
    max-width: 48ch;
    margin: 0 0 2rem;
}

.ff-ctas {
    display: flex;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.ff-btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem 2.25rem;
    background: var(--ff-yellow);
    color: #1a1208;
    font-family: var(--ff-display);
    font-size: 1.1rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    text-decoration: none;
    transition: transform 150ms, box-shadow 150ms;
    box-shadow: 4px 4px 0 rgba(0,0,0,0.4);
}

.ff-btn-primary:hover {
    transform: translate(-2px, -2px);
    box-shadow: 6px 6px 0 rgba(0,0,0,0.4);
}

.ff-btn-primary:active {
    transform: translate(2px, 2px);
    box-shadow: 2px 2px 0 rgba(0,0,0,0.4);
}

.ff-btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem 2rem;
    background: transparent;
    color: var(--ff-ink);
    font-family: var(--ff-display);
    font-size: 1rem;
    font-weight: 500;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    text-decoration: none;
    border: 2px solid rgba(255,248,240,0.5);
    transition: border-color 200ms, color 200ms;
}

.ff-btn-secondary:hover {
    border-color: var(--ff-yellow);
    color: var(--ff-yellow);
}

/* ─── MAIN ─── */
.ff-main {
    max-width: 1200px;
    margin: 0 auto;
    padding: clamp(2.5rem, 6vw, 5rem) clamp(1rem, 4vw, 2.5rem);
}

/* ─── SECTION ─── */
.ff-section {
    margin-bottom: clamp(3rem, 8vw, 5rem);
}

.ff-section-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 2rem;
}

.ff-section-title {
    font-family: var(--ff-display);
    font-size: clamp(1.8rem, 6vw, 3.5rem);
    font-weight: 700;
    letter-spacing: 0.06em;
    color: var(--ff-yellow);
    margin: 0;
    text-transform: uppercase;
}

.ff-section-badge {
    background: var(--ff-red);
    color: #fff;
    font-family: var(--ff-display);
    font-size: 0.8rem;
    font-weight: 600;
    letter-spacing: 0.1em;
    padding: 0.3rem 0.8rem;
}

/* ─── MENU GRID ─── */
.ff-menu-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(min(100%, 280px), 1fr));
    gap: 1.25rem;
}

.ff-menu-tile {
    position: relative;
    display: flex;
    flex-direction: column;
    background: var(--ff-surface);
    border: 2px solid transparent;
    text-decoration: none;
    color: inherit;
    overflow: hidden;
    transition: border-color 200ms, transform 200ms;
    min-height: 240px;
}

.ff-menu-tile:hover {
    border-color: var(--ff-yellow);
    transform: translateY(-4px);
}

.ff-menu-tile-img-wrap {
    position: absolute;
    inset: 0;
}

.ff-menu-tile-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    filter: brightness(0.45) saturate(0.7);
    transition: filter 300ms;
}

.ff-menu-tile:hover .ff-menu-tile-img {
    filter: brightness(0.55) saturate(0.85);
}

.ff-menu-tile-body {
    position: relative;
    z-index: 2;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    flex: 1;
    padding: 1.5rem;
    background: linear-gradient(to top, rgba(26,18,8,0.98) 0%, transparent 60%);
}

.ff-menu-tile-emoji {
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
    line-height: 1;
}

.ff-menu-tile-name {
    font-family: var(--ff-display);
    font-size: 1.6rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    color: var(--ff-ink);
    line-height: 1.1;
    margin-bottom: 0.35rem;
}

.ff-menu-tile-desc {
    font-size: 0.82rem;
    color: var(--ff-ink-soft);
    line-height: 1.5;
    margin-bottom: 0.75rem;
    display: block;
}

.ff-menu-tile-cta {
    font-family: var(--ff-display);
    font-size: 0.82rem;
    font-weight: 600;
    letter-spacing: 0.2em;
    color: var(--ff-yellow);
}

/* ─── INFO STRIP ─── */
.ff-info-strip {
    display: grid;
    grid-template-columns: 1fr;
    gap: 0;
    border: 2px solid rgba(255,208,0,0.2);
}

@media (min-width: 680px) {
    .ff-info-strip { grid-template-columns: repeat(3, 1fr); }
}

.ff-info-block {
    display: flex;
    gap: 1.25rem;
    padding: 2rem;
    border-bottom: 2px solid rgba(255,208,0,0.12);
}

@media (min-width: 680px) {
    .ff-info-block {
        border-bottom: none;
        border-right: 2px solid rgba(255,208,0,0.12);
    }
    .ff-info-block:last-child { border-right: none; }
}

.ff-info-icon {
    font-size: 2rem;
    flex-shrink: 0;
    line-height: 1;
}

.ff-info-content {
    flex: 1;
}

.ff-info-title {
    font-family: var(--ff-display);
    font-size: 0.85rem;
    font-weight: 600;
    letter-spacing: 0.2em;
    color: var(--ff-yellow);
    margin: 0 0 0.75rem;
}

.ff-address {
    font-style: normal;
    font-size: 0.88rem;
    line-height: 1.6;
    color: var(--ff-ink-soft);
}

.ff-address p { margin: 0; }

.ff-phone {
    display: block;
    margin-top: 0.5rem;
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--ff-yellow);
    text-decoration: none;
}

.ff-socials {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.6rem;
    color: var(--ff-ink-soft);
}

/* ─── LOCATIONS GRID ─── */
.ff-locations-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(min(100%, 280px), 1fr));
    gap: 1.25rem;
    --lc-card-bg: var(--ff-surface);
    --lc-card-border: rgba(255,208,0,0.15);
    --lc-accent: var(--ff-yellow);
    --lc-name-color: var(--ff-ink);
}

/* ─── FOOTER ─── */
.ff-footer {
    background: var(--ff-red-dark);
    padding: clamp(2.5rem, 6vw, 4rem) clamp(1rem, 4vw, 2.5rem);
}

.ff-footer-inner {
    max-width: 800px;
    margin: 0 auto;
    text-align: center;
}

.ff-footer-name {
    font-family: var(--ff-display);
    font-size: clamp(2rem, 7vw, 3.5rem);
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: var(--ff-yellow);
    margin: 0 0 0.5rem;
}

.ff-footer-address {
    font-size: 0.9rem;
    color: rgba(255,248,240,0.7);
    margin: 0 0 1.5rem;
}

.ff-footer-cta {
    margin-bottom: 2rem;
}

.ff-footer-btn {
    display: inline-block;
    padding: 1rem 2.5rem;
    background: var(--ff-yellow);
    color: #1a1208;
    font-family: var(--ff-display);
    font-size: 1rem;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    text-decoration: none;
    transition: transform 150ms;
}

.ff-footer-btn:hover {
    transform: translateY(-2px);
}

.ff-footer-credit {
    font-size: 0.72rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: rgba(255,248,240,0.45);
}

.ff-footer-credit a {
    color: inherit;
    text-decoration: underline;
    text-underline-offset: 3px;
}

@media (prefers-reduced-motion: reduce) {
    .ff-hero-text { animation: none; }
    .ff-btn-primary:hover { transform: none; }
}
</style>
