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
            href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,300;0,400;0,600;0,700;1,400&family=DM+Serif+Display:ital@0;1&display=swap"
            rel="stylesheet"
        />
    </Head>

    <div class="cafe">

        <!-- ─── TOPBAR ─── -->
        <nav class="cafe-nav">
            <div class="cafe-nav-inner">
                <div class="cafe-nav-brand">
                    <img v-if="primaryLocation?.logo_url" :src="primaryLocation.logo_url" :alt="primaryLocation.name" class="cafe-nav-logo" />
                    <span v-else class="cafe-nav-name">{{ primaryLocation?.name ?? tenant.name }}</span>
                </div>
                <div class="cafe-nav-links">
                    <a v-if="primaryLocation?.menus?.length" :href="`/menu/${primaryLocation.menus[0].id}`" class="cafe-nav-cta">
                        Ver carta
                    </a>
                </div>
            </div>
        </nav>

        <!-- ─── HERO ─── -->
        <header class="cafe-hero">
            <div class="cafe-hero-content">
                <div class="cafe-hero-image-wrap">
                    <img v-if="hero" :src="hero" :alt="primaryLocation?.name ?? ''" class="cafe-hero-img" />
                    <div v-else class="cafe-hero-img-placeholder">
                        <svg viewBox="0 0 80 80" fill="none" class="cafe-placeholder-icon">
                            <circle cx="40" cy="40" r="38" stroke="currentColor" stroke-width="1.5" />
                            <path d="M26 50 Q40 28 54 50" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round"/>
                            <circle cx="40" cy="36" r="6" stroke="currentColor" stroke-width="1.5" />
                        </svg>
                    </div>
                </div>

                <div class="cafe-hero-text">
                    <p class="cafe-eyebrow">{{ primaryLocation?.city ?? '' }}</p>
                    <h1 class="cafe-title">{{ primaryLocation?.name ?? tenant.name }}</h1>
                    <p v-if="primaryLocation?.description" class="cafe-sub">{{ primaryLocation.description }}</p>

                    <div class="cafe-hero-actions">
                        <a
                            v-for="menu in (primaryLocation?.menus ?? [])"
                            :key="menu.id"
                            :href="`/menu/${menu.id}`"
                            class="cafe-btn"
                        >
                            ☕ {{ menu.name }}
                        </a>
                    </div>

                    <SocialLinks
                        v-if="primaryLocation?.social_medias"
                        :social-medias="primaryLocation.social_medias"
                        size="md"
                        class="cafe-hero-socials"
                    />
                </div>
            </div>

            <!-- Decorative wave -->
            <div class="cafe-wave" aria-hidden="true">
                <svg viewBox="0 0 1440 120" preserveAspectRatio="none">
                    <path d="M0,64 C240,120 480,0 720,64 C960,120 1200,0 1440,64 L1440,120 L0,120 Z" fill="var(--cafe-bg)" />
                </svg>
            </div>
        </header>

        <!-- ─── MAIN ─── -->
        <main class="cafe-main">

            <template v-if="!isMultiLocation && primaryLocation">

                <!-- Menús -->
                <section v-if="primaryLocation.menus?.length" class="cafe-section">
                    <h2 class="cafe-section-title">
                        <span class="cafe-title-icon">☕</span>
                        Nuestros menús
                    </h2>
                    <div class="cafe-menu-list">
                        <a
                            v-for="menu in primaryLocation.menus"
                            :key="menu.id"
                            :href="`/menu/${menu.id}`"
                            class="cafe-menu-item"
                        >
                            <div class="cafe-menu-img-wrap">
                                <img v-if="menu.image_path" :src="menu.image_path" :alt="menu.name" class="cafe-menu-img" loading="lazy" />
                                <div v-else class="cafe-menu-img-placeholder">
                                    <span class="cafe-menu-icon">🍽️</span>
                                </div>
                            </div>
                            <div class="cafe-menu-info">
                                <strong class="cafe-menu-name">{{ menu.name }}</strong>
                                <span v-if="menu.description" class="cafe-menu-desc">{{ menu.description }}</span>
                            </div>
                            <span class="cafe-menu-arrow">→</span>
                        </a>
                    </div>
                </section>

                <!-- Horarios + Info -->
                <div class="cafe-bottom-grid">

                    <section v-if="primaryLocation.opening_hours?.length" class="cafe-card">
                        <h2 class="cafe-card-title">
                            <span>🕐</span> Horario
                        </h2>
                        <OpeningHoursDisplay :hours="primaryLocation.opening_hours" />
                    </section>

                    <section class="cafe-card">
                        <h2 class="cafe-card-title">
                            <span>📍</span> Encuéntranos
                        </h2>
                        <address class="cafe-address">
                            <p v-if="primaryLocation.address">{{ primaryLocation.address }}</p>
                            <p v-if="primaryLocation.city">{{ primaryLocation.city }}</p>
                        </address>
                        <a v-if="primaryLocation.phone" :href="`tel:${primaryLocation.phone}`" class="cafe-phone">
                            📞 {{ primaryLocation.phone }}
                        </a>
                        <a v-if="mapsUrl" :href="mapsUrl" target="_blank" rel="noopener" class="cafe-map-link">
                            Ver en el mapa →
                        </a>
                    </section>
                </div>
            </template>

            <!-- Multi-location -->
            <template v-else-if="isMultiLocation">
                <section class="cafe-section">
                    <h2 class="cafe-section-title">
                        <span class="cafe-title-icon">☕</span>
                        Nuestros locales
                    </h2>
                    <div class="cafe-locations-grid">
                        <LocationCard v-for="loc in locations" :key="loc.id" :location="loc" />
                    </div>
                </section>
            </template>
        </main>

        <!-- ─── FOOTER ─── -->
        <footer class="cafe-footer">
            <p class="cafe-footer-name">{{ primaryLocation?.name ?? tenant.name }}</p>
            <p v-if="primaryLocation?.address" class="cafe-footer-line">{{ primaryLocation.address }}, {{ primaryLocation.city }}</p>
            <SocialLinks
                v-if="primaryLocation?.social_medias"
                :social-medias="primaryLocation.social_medias"
                size="md"
                class="cafe-footer-socials"
            />
            <p class="cafe-footer-credit">
                Hecho con <a href="https://menulinker.com" target="_blank" rel="noopener">MenuLinker</a>
            </p>
        </footer>
    </div>
</template>

<style scoped>
/* ===== CAFÉ / SPECIALTY COFFEE TEMPLATE ===== */
.cafe {
    --cafe-bg: #fdf6ee;
    --cafe-surface: #fff9f3;
    --cafe-brown: #5c3a1e;
    --cafe-brown-light: #8b5e3c;
    --cafe-cream: #f0e6d2;
    --cafe-ink: #2c1e0f;
    --cafe-ink-soft: #7a5c3d;
    --cafe-ink-faint: #b09070;
    --cafe-accent: #c47e3f;
    --cafe-accent-soft: rgba(196,126,63,0.12);
    --cafe-border: #e8d8c4;
    --cafe-round: 1.5rem;
    --cafe-serif: 'DM Serif Display', 'Georgia', serif;
    --cafe-sans: 'Nunito', 'Segoe UI', system-ui, sans-serif;

    background: var(--cafe-bg);
    color: var(--cafe-ink);
    font-family: var(--cafe-sans);
    font-weight: 400;
    -webkit-font-smoothing: antialiased;
    min-height: 100vh;
    overflow-x: hidden;
}

@media (prefers-color-scheme: dark) {
    .cafe {
        --cafe-bg: #1e1409;
        --cafe-surface: #261b0e;
        --cafe-brown: #d4a478;
        --cafe-brown-light: #e8c09a;
        --cafe-cream: #2e1f0e;
        --cafe-ink: #f5ece0;
        --cafe-ink-soft: #c8a882;
        --cafe-ink-faint: #8a7060;
        --cafe-accent: #e8a060;
        --cafe-accent-soft: rgba(232,160,96,0.12);
        --cafe-border: #3a2510;
    }
}

/* ─── NAV ─── */
.cafe-nav {
    position: sticky;
    top: 0;
    z-index: 50;
    background: color-mix(in oklch, var(--cafe-bg) 88%, transparent);
    backdrop-filter: blur(16px);
    border-bottom: 1px solid var(--cafe-border);
}

.cafe-nav-inner {
    max-width: 1100px;
    margin: 0 auto;
    padding: 1rem clamp(1rem, 4vw, 2rem);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.cafe-nav-brand {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.cafe-nav-logo {
    height: 2.25rem;
    width: auto;
    border-radius: 50%;
    object-fit: cover;
}

.cafe-nav-name {
    font-family: var(--cafe-serif);
    font-size: 1.15rem;
    color: var(--cafe-brown);
}

.cafe-nav-cta {
    padding: 0.5rem 1.4rem;
    background: var(--cafe-accent);
    color: #fff;
    border-radius: 999px;
    font-weight: 700;
    font-size: 0.85rem;
    text-decoration: none;
    transition: background 200ms, transform 200ms;
}

.cafe-nav-cta:hover {
    background: var(--cafe-brown-light);
    transform: scale(1.04);
}

/* ─── HERO ─── */
.cafe-hero {
    position: relative;
    background: var(--cafe-cream);
    padding: clamp(2rem, 6vw, 4rem) clamp(1rem, 4vw, 2rem) 0;
    overflow: hidden;
}

.cafe-hero-content {
    max-width: 1100px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 1fr;
    gap: 2.5rem;
    align-items: center;
    padding-bottom: 3rem;
}

@media (min-width: 768px) {
    .cafe-hero-content {
        grid-template-columns: 1fr 1fr;
    }
}

.cafe-hero-image-wrap {
    position: relative;
    border-radius: 50%;
    aspect-ratio: 1;
    max-width: 400px;
    margin: 0 auto;
    overflow: hidden;
    box-shadow:
        0 0 0 8px var(--cafe-bg),
        0 0 0 16px var(--cafe-border),
        0 20px 60px -10px rgba(92,58,30,0.35);
    animation: cafe-float 6s ease-in-out infinite;
}

@keyframes cafe-float {
    0%, 100% { transform: translateY(0); }
    50%       { transform: translateY(-10px); }
}

@media (prefers-reduced-motion: reduce) {
    .cafe-hero-image-wrap { animation: none; }
}

.cafe-hero-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.cafe-hero-img-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--cafe-surface);
    color: var(--cafe-accent);
}

.cafe-placeholder-icon {
    width: 60%;
    height: 60%;
}

.cafe-hero-text {
    text-align: left;
    animation: cafe-slide-in 800ms cubic-bezier(.2,.65,.2,1) both;
}

@keyframes cafe-slide-in {
    from { opacity: 0; transform: translateX(20px); }
    to   { opacity: 1; transform: translateX(0); }
}

.cafe-eyebrow {
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.3em;
    text-transform: uppercase;
    color: var(--cafe-accent);
    margin: 0 0 0.75rem;
}

.cafe-title {
    font-family: var(--cafe-serif);
    font-style: italic;
    font-weight: 400;
    font-size: clamp(2.5rem, 8vw, 4rem);
    line-height: 1.1;
    color: var(--cafe-brown);
    margin: 0 0 1rem;
}

.cafe-sub {
    font-size: 1rem;
    line-height: 1.7;
    color: var(--cafe-ink-soft);
    max-width: 44ch;
    margin: 0 0 1.5rem;
}

.cafe-hero-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-bottom: 1.5rem;
}

.cafe-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.75rem 1.75rem;
    background: var(--cafe-brown);
    color: #fff;
    border-radius: 999px;
    font-weight: 700;
    font-size: 0.9rem;
    text-decoration: none;
    transition: background 200ms, transform 200ms, box-shadow 200ms;
    box-shadow: 0 4px 12px rgba(92,58,30,0.25);
}

.cafe-btn:hover {
    background: var(--cafe-accent);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(92,58,30,0.3);
}

.cafe-hero-socials {
    color: var(--cafe-ink-soft);
}

.cafe-wave {
    line-height: 0;
    overflow: hidden;
    margin-top: -2px;
}

.cafe-wave svg {
    width: 100%;
    display: block;
}

/* ─── MAIN ─── */
.cafe-main {
    max-width: 1100px;
    margin: 0 auto;
    padding: clamp(2.5rem, 6vw, 4rem) clamp(1rem, 4vw, 2rem);
}

/* ─── SECTION ─── */
.cafe-section {
    margin-bottom: clamp(2.5rem, 6vw, 4rem);
}

.cafe-section-title {
    font-family: var(--cafe-serif);
    font-style: italic;
    font-size: clamp(1.7rem, 4.5vw, 2.2rem);
    font-weight: 400;
    color: var(--cafe-brown);
    margin: 0 0 1.75rem;
    display: flex;
    align-items: center;
    gap: 0.6rem;
}

.cafe-title-icon {
    font-style: normal;
    font-size: 0.85em;
}

/* ─── MENU LIST ─── */
.cafe-menu-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.cafe-menu-item {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    padding: 1rem 1.25rem;
    background: var(--cafe-surface);
    border: 1px solid var(--cafe-border);
    border-radius: var(--cafe-round);
    text-decoration: none;
    color: inherit;
    transition: box-shadow 250ms, transform 250ms;
}

.cafe-menu-item:hover {
    box-shadow: 0 8px 24px -8px rgba(92,58,30,0.2);
    transform: translateX(4px);
}

.cafe-menu-img-wrap {
    width: 5rem;
    height: 5rem;
    border-radius: 1rem;
    overflow: hidden;
    flex-shrink: 0;
    background: var(--cafe-cream);
}

.cafe-menu-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.cafe-menu-img-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.cafe-menu-icon {
    font-size: 1.8rem;
}

.cafe-menu-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
}

.cafe-menu-name {
    font-family: var(--cafe-serif);
    font-size: 1.1rem;
    font-weight: 400;
    color: var(--cafe-ink);
}

.cafe-menu-desc {
    font-size: 0.83rem;
    color: var(--cafe-ink-soft);
    line-height: 1.5;
}

.cafe-menu-arrow {
    font-size: 1.25rem;
    color: var(--cafe-accent);
    flex-shrink: 0;
    transition: transform 200ms;
}

.cafe-menu-item:hover .cafe-menu-arrow {
    transform: translateX(4px);
}

/* ─── BOTTOM GRID ─── */
.cafe-bottom-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.5rem;
}

@media (min-width: 680px) {
    .cafe-bottom-grid {
        grid-template-columns: 1fr 1fr;
    }
}

.cafe-card {
    background: var(--cafe-surface);
    border: 1px solid var(--cafe-border);
    border-radius: var(--cafe-round);
    padding: 1.75rem;
}

.cafe-card-title {
    font-family: var(--cafe-serif);
    font-style: italic;
    font-weight: 400;
    font-size: 1.25rem;
    margin: 0 0 1.25rem;
    color: var(--cafe-brown);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.cafe-address {
    font-style: normal;
    font-size: 0.9rem;
    line-height: 1.7;
    color: var(--cafe-ink-soft);
}

.cafe-address p { margin: 0; }

.cafe-phone {
    display: block;
    margin-top: 0.75rem;
    font-weight: 600;
    color: var(--cafe-accent);
    text-decoration: none;
    font-size: 0.92rem;
}

.cafe-map-link {
    display: inline-block;
    margin-top: 0.75rem;
    font-size: 0.82rem;
    font-weight: 700;
    color: var(--cafe-brown-light);
    text-decoration: none;
    transition: color 200ms;
}

.cafe-map-link:hover {
    color: var(--cafe-accent);
}

/* ─── LOCATIONS GRID ─── */
.cafe-locations-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(min(100%, 300px), 1fr));
    gap: 1.5rem;
}

/* ─── FOOTER ─── */
.cafe-footer {
    background: var(--cafe-brown);
    color: rgba(255,249,243,0.85);
    padding: clamp(2.5rem, 6vw, 4rem) clamp(1rem, 4vw, 2rem);
    text-align: center;
}

.cafe-footer-name {
    font-family: var(--cafe-serif);
    font-style: italic;
    font-size: 1.6rem;
    font-weight: 400;
    color: #fff9f3;
    margin: 0 0 0.5rem;
}

.cafe-footer-line {
    font-size: 0.85rem;
    margin: 0.2rem 0;
    opacity: 0.75;
}

.cafe-footer-socials {
    justify-content: center;
    margin-top: 1.25rem;
    color: rgba(255,249,243,0.7);
}

.cafe-footer-credit {
    margin-top: 2rem;
    font-size: 0.72rem;
    opacity: 0.45;
    letter-spacing: 0.04em;
}

.cafe-footer-credit a {
    color: inherit;
    text-decoration: underline;
    text-underline-offset: 3px;
}
</style>
