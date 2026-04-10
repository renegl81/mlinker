<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, ref, onMounted } from 'vue';
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

const loaded = ref(false);
onMounted(() => {
    requestAnimationFrame(() => {
        setTimeout(() => { loaded.value = true; }, 80);
    });
});
</script>

<template>
    <MenuSeoHead :meta="seo" :json-ld="(seo.jsonLd as any)" />
    <Head>
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous" />
        <link
            href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300;1,400;1,500&family=Cormorant+Upright:wght@300;400;500&family=Jost:wght@300;400;500&display=swap"
            rel="stylesheet"
        />
    </Head>

    <div class="fd" :class="{ 'fd-loaded': loaded }">

        <!-- ─── HERO fullscreen ─── -->
        <header class="fd-hero">
            <!-- Image fills entire viewport -->
            <div v-if="hero" class="fd-hero-img" :style="{ backgroundImage: `url(${hero})` }" />
            <div class="fd-hero-veil" />

            <!-- Centered content -->
            <div class="fd-hero-center">
                <div class="fd-hero-eyebrow">
                    <span class="fd-line" />
                    <span class="fd-eyebrow-text">{{ primaryLocation?.city ?? '' }}</span>
                    <span class="fd-line" />
                </div>

                <h1 class="fd-name">{{ primaryLocation?.name ?? tenant.name }}</h1>

                <p v-if="primaryLocation?.description" class="fd-tagline">
                    {{ primaryLocation.description }}
                </p>

                <a
                    v-if="primaryLocation?.menus?.length"
                    :href="`/menu/${primaryLocation.menus[0].id}`"
                    class="fd-hero-link"
                >
                    Descubrir la carta
                </a>
            </div>

            <!-- Scroll indicator -->
            <div class="fd-scroll" aria-hidden="true">
                <span class="fd-scroll-tick" />
                <span class="fd-scroll-tick" />
                <span class="fd-scroll-tick" />
            </div>
        </header>

        <!-- ─── INTRO ─── -->
        <section v-if="primaryLocation?.description" class="fd-intro">
            <div class="fd-intro-inner">
                <div class="fd-rule-wrap" aria-hidden="true">
                    <span class="fd-rule-thin" />
                    <span class="fd-mark">✦</span>
                    <span class="fd-rule-thin" />
                </div>
                <p class="fd-intro-text">{{ primaryLocation.description }}</p>
            </div>
        </section>

        <!-- ─── MAIN ─── -->
        <main class="fd-main">

            <template v-if="!isMultiLocation && primaryLocation">

                <!-- Menus -->
                <section v-if="primaryLocation.menus?.length" class="fd-section">
                    <div class="fd-section-cap">La Carta</div>
                    <div class="fd-menu-list">
                        <a
                            v-for="(menu, i) in primaryLocation.menus"
                            :key="menu.id"
                            :href="`/menu/${menu.id}`"
                            class="fd-menu-entry"
                            :style="{ '--i': i } as Record<string, number>"
                        >
                            <div v-if="menu.image_path" class="fd-menu-img-wrap">
                                <img :src="menu.image_path" :alt="menu.name" class="fd-menu-img" loading="lazy" />
                            </div>
                            <div class="fd-menu-text">
                                <span class="fd-menu-numeral">{{ String(i + 1).padStart(2, '0') }}</span>
                                <div class="fd-menu-info">
                                    <h3 class="fd-menu-name">{{ menu.name }}</h3>
                                    <p v-if="menu.description" class="fd-menu-desc">{{ menu.description }}</p>
                                </div>
                                <span class="fd-menu-arrow" aria-hidden="true">→</span>
                            </div>
                            <div class="fd-menu-divider" />
                        </a>
                    </div>
                </section>

                <!-- Horarios -->
                <section v-if="primaryLocation.opening_hours?.length" class="fd-section fd-section-alt">
                    <div class="fd-section-cap">Reservas</div>
                    <div class="fd-two-col">
                        <div>
                            <p class="fd-contact-label">Horario</p>
                            <OpeningHoursDisplay :hours="primaryLocation.opening_hours" class="fd-oh" />
                        </div>
                        <div>
                            <p class="fd-contact-label">Contacto</p>
                            <address class="fd-address">
                                <p v-if="primaryLocation.address">{{ primaryLocation.address }}</p>
                                <p v-if="primaryLocation.city">{{ primaryLocation.city }}</p>
                            </address>
                            <a v-if="primaryLocation.phone" :href="`tel:${primaryLocation.phone}`" class="fd-phone">
                                {{ primaryLocation.phone }}
                            </a>
                            <a v-if="mapsUrl" :href="mapsUrl" target="_blank" rel="noopener" class="fd-directions">
                                Obtener indicaciones
                            </a>
                            <SocialLinks
                                v-if="primaryLocation.social_medias"
                                :social-medias="primaryLocation.social_medias"
                                size="md"
                                class="fd-socials"
                            />
                        </div>
                    </div>
                </section>

            </template>

            <!-- Multi-location -->
            <template v-else-if="isMultiLocation">
                <section class="fd-section">
                    <div class="fd-section-cap">Nuestras casas</div>
                    <div class="fd-locations-grid">
                        <LocationCard v-for="loc in locations" :key="loc.id" :location="loc" />
                    </div>
                </section>
            </template>

        </main>

        <!-- ─── FOOTER ─── -->
        <footer class="fd-footer">
            <div class="fd-rule-wrap" aria-hidden="true">
                <span class="fd-rule-thin" />
                <span class="fd-mark">◆</span>
                <span class="fd-rule-thin" />
            </div>
            <p class="fd-footer-name">{{ primaryLocation?.name ?? tenant.name }}</p>
            <address class="fd-footer-address">
                <span v-if="primaryLocation?.address">{{ primaryLocation.address }}</span>
                <span v-if="primaryLocation?.city"> · {{ primaryLocation.city }}</span>
            </address>
            <SocialLinks
                v-if="primaryLocation?.social_medias"
                :social-medias="primaryLocation.social_medias"
                size="sm"
                class="fd-footer-socials"
            />
            <p class="fd-footer-credit">
                <a href="https://menulinker.com" target="_blank" rel="noopener">MenuLinker</a>
            </p>
        </footer>
    </div>
</template>

<style scoped>
/* ===== FINE DINING / ALTA COCINA TEMPLATE ===== */
.fd {
    --fd-bg: #f9f7f4;
    --fd-ink: #1a1814;
    --fd-ink-soft: #6a6258;
    --fd-ink-faint: #a89e90;
    --fd-accent: #2a2620;
    --fd-rule: #e0d8cc;
    --fd-serif: 'Cormorant Garamond', 'IM Fell English', Georgia, serif;
    --fd-serif-up: 'Cormorant Upright', 'Cormorant Garamond', serif;
    --fd-sans: 'Jost', 'Helvetica Neue', system-ui, sans-serif;

    background: var(--fd-bg);
    color: var(--fd-ink);
    font-family: var(--fd-sans);
    font-weight: 300;
    -webkit-font-smoothing: antialiased;
    min-height: 100vh;
    overflow-x: hidden;
}

@media (prefers-color-scheme: dark) {
    .fd {
        --fd-bg: #110f0c;
        --fd-ink: #f2ede5;
        --fd-ink-soft: #9a9080;
        --fd-ink-faint: #5a5248;
        --fd-accent: #d4c8b8;
        --fd-rule: #2a2620;
    }
}

/* ─── LOAD ANIMATION ─── */
.fd {
    opacity: 0;
    transition: opacity 1.2s ease;
}
.fd.fd-loaded {
    opacity: 1;
}

/* ─── HERO ─── */
.fd-hero {
    position: relative;
    height: 100svh;
    min-height: 600px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.fd-hero-img {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center 35%;
    transform: scale(1.08);
    transition: transform 10s cubic-bezier(.15,.55,.15,1);
}

.fd-loaded .fd-hero-img {
    transform: scale(1.0);
}

.fd-hero-veil {
    position: absolute;
    inset: 0;
    background: linear-gradient(
        to bottom,
        rgba(17,15,12,0.35) 0%,
        rgba(17,15,12,0.55) 50%,
        rgba(17,15,12,0.78) 100%
    );
}

.fd-hero-center {
    position: relative;
    z-index: 5;
    text-align: center;
    padding: 2rem;
    color: #f5f0e8;
    animation: fd-reveal 1.6s cubic-bezier(.2,.65,.2,1) 0.3s both;
}

@keyframes fd-reveal {
    from { opacity: 0; transform: translateY(24px); }
    to   { opacity: 1; transform: translateY(0); }
}

.fd-hero-eyebrow {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1.25rem;
    margin-bottom: 2.5rem;
}

.fd-line {
    display: block;
    height: 1px;
    width: clamp(40px, 8vw, 70px);
    background: rgba(245,240,232,0.45);
}

.fd-eyebrow-text {
    font-family: var(--fd-sans);
    font-size: 0.62rem;
    font-weight: 500;
    letter-spacing: 0.45em;
    text-transform: uppercase;
    color: rgba(245,240,232,0.7);
    white-space: nowrap;
}

.fd-name {
    font-family: var(--fd-serif);
    font-style: italic;
    font-weight: 300;
    font-size: clamp(3rem, 12vw, 7.5rem);
    line-height: 1.05;
    letter-spacing: -0.01em;
    margin: 0 0 1.75rem;
    color: #f5f0e8;
}

.fd-tagline {
    font-family: var(--fd-sans);
    font-weight: 300;
    font-size: clamp(0.9rem, 2vw, 1.05rem);
    line-height: 1.8;
    letter-spacing: 0.04em;
    max-width: 46ch;
    margin: 0 auto 2.5rem;
    color: rgba(245,240,232,0.75);
}

.fd-hero-link {
    font-family: var(--fd-sans);
    font-size: 0.7rem;
    font-weight: 500;
    letter-spacing: 0.35em;
    text-transform: uppercase;
    color: rgba(245,240,232,0.85);
    text-decoration: none;
    border-bottom: 1px solid rgba(245,240,232,0.4);
    padding-bottom: 3px;
    transition: color 300ms, border-color 300ms;
}

.fd-hero-link:hover {
    color: #f5f0e8;
    border-color: rgba(245,240,232,0.8);
}

.fd-scroll {
    position: absolute;
    bottom: 2.5rem;
    left: 50%;
    transform: translateX(-50%);
    z-index: 5;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
}

.fd-scroll-tick {
    display: block;
    width: 1px;
    height: 8px;
    background: rgba(245,240,232,0.4);
    animation: fd-tick 2s ease-in-out infinite;
}

.fd-scroll-tick:nth-child(2) { animation-delay: 0.3s; }
.fd-scroll-tick:nth-child(3) { animation-delay: 0.6s; }

@keyframes fd-tick {
    0%, 100% { opacity: 0.25; }
    50%       { opacity: 1; }
}

/* ─── INTRO ─── */
.fd-intro {
    padding: clamp(4rem, 10vw, 7rem) clamp(1.25rem, 6vw, 4rem);
}

.fd-intro-inner {
    max-width: 620px;
    margin: 0 auto;
    text-align: center;
}

.fd-rule-wrap {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1.25rem;
    margin-bottom: 2.5rem;
    color: var(--fd-ink-faint);
}

.fd-rule-thin {
    display: block;
    height: 1px;
    width: 60px;
    background: currentColor;
}

.fd-mark {
    font-size: 0.6rem;
    letter-spacing: 0;
}

.fd-intro-text {
    font-family: var(--fd-serif);
    font-style: italic;
    font-weight: 300;
    font-size: clamp(1.35rem, 3.5vw, 1.8rem);
    line-height: 1.65;
    letter-spacing: 0.01em;
    color: var(--fd-ink-soft);
    margin: 0;
}

/* ─── MAIN ─── */
.fd-main {
    max-width: 1000px;
    margin: 0 auto;
    padding: 0 clamp(1.25rem, 4vw, 3rem);
}

/* ─── SECTION ─── */
.fd-section {
    padding: clamp(3rem, 8vw, 6rem) 0;
    border-top: 1px solid var(--fd-rule);
}

.fd-section-alt {
    background: transparent;
}

.fd-section-cap {
    font-family: var(--fd-sans);
    font-size: 0.62rem;
    font-weight: 500;
    letter-spacing: 0.45em;
    text-transform: uppercase;
    color: var(--fd-ink-faint);
    margin-bottom: 3rem;
}

/* ─── MENU LIST ─── */
.fd-menu-list {
    display: flex;
    flex-direction: column;
}

.fd-menu-entry {
    text-decoration: none;
    color: inherit;
    display: block;
    animation: fd-entry-in 700ms cubic-bezier(.2,.65,.2,1) both;
    animation-delay: calc(var(--i, 0) * 120ms);
}

@keyframes fd-entry-in {
    from { opacity: 0; transform: translateX(-12px); }
    to   { opacity: 1; transform: translateX(0); }
}

.fd-menu-text {
    display: flex;
    align-items: center;
    gap: 2rem;
    padding: 1.75rem 0;
    transition: padding-left 300ms;
}

.fd-menu-entry:hover .fd-menu-text {
    padding-left: 1.5rem;
}

.fd-menu-numeral {
    font-family: var(--fd-serif);
    font-style: italic;
    font-weight: 300;
    font-size: 0.88rem;
    color: var(--fd-ink-faint);
    flex-shrink: 0;
    letter-spacing: 0.04em;
    min-width: 2.2rem;
}

.fd-menu-info {
    flex: 1;
}

.fd-menu-name {
    font-family: var(--fd-serif);
    font-style: italic;
    font-weight: 400;
    font-size: clamp(1.4rem, 4vw, 1.9rem);
    line-height: 1.2;
    margin: 0;
    color: var(--fd-ink);
    transition: color 250ms;
}

.fd-menu-entry:hover .fd-menu-name {
    color: var(--fd-ink-soft);
}

.fd-menu-desc {
    font-size: 0.85rem;
    color: var(--fd-ink-faint);
    margin: 0.35rem 0 0;
    line-height: 1.6;
    max-width: 50ch;
}

.fd-menu-img-wrap {
    width: 5rem;
    height: 5rem;
    overflow: hidden;
    flex-shrink: 0;
}

.fd-menu-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    filter: saturate(0.7) brightness(0.95);
    transition: filter 300ms;
}

.fd-menu-entry:hover .fd-menu-img {
    filter: saturate(0.9) brightness(1);
}

.fd-menu-arrow {
    font-size: 1rem;
    color: var(--fd-ink-faint);
    flex-shrink: 0;
    transition: transform 250ms;
}

.fd-menu-entry:hover .fd-menu-arrow {
    transform: translateX(6px);
    color: var(--fd-ink-soft);
}

.fd-menu-divider {
    height: 1px;
    background: var(--fd-rule);
    transition: margin 300ms;
}

/* ─── TWO COL ─── */
.fd-two-col {
    display: grid;
    grid-template-columns: 1fr;
    gap: 3rem;
}

@media (min-width: 680px) {
    .fd-two-col { grid-template-columns: 1fr 1fr; }
}

.fd-contact-label {
    font-family: var(--fd-sans);
    font-size: 0.62rem;
    font-weight: 500;
    letter-spacing: 0.45em;
    text-transform: uppercase;
    color: var(--fd-ink-faint);
    margin: 0 0 1.5rem;
}

.fd-oh {
    color: var(--fd-ink-soft);
    font-size: 0.88rem;
}

.fd-address {
    font-style: normal;
    font-size: 0.9rem;
    line-height: 1.75;
    color: var(--fd-ink-soft);
}

.fd-address p { margin: 0; }

.fd-phone {
    display: block;
    margin-top: 0.75rem;
    font-size: 0.9rem;
    color: var(--fd-ink);
    text-decoration: none;
    border-bottom: 1px solid transparent;
    display: inline-block;
    padding-bottom: 2px;
    transition: border-color 200ms;
}

.fd-phone:hover {
    border-color: var(--fd-ink-faint);
}

.fd-directions {
    display: block;
    margin-top: 0.75rem;
    font-size: 0.78rem;
    font-weight: 400;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--fd-ink-faint);
    text-decoration: none;
    transition: color 200ms;
}

.fd-directions:hover {
    color: var(--fd-ink);
}

.fd-socials {
    margin-top: 1.5rem;
    color: var(--fd-ink-faint);
}

/* ─── LOCATIONS ─── */
.fd-locations-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(min(100%, 300px), 1fr));
    gap: 2rem;
}

/* ─── FOOTER ─── */
.fd-footer {
    border-top: 1px solid var(--fd-rule);
    padding: clamp(3rem, 8vw, 5rem) clamp(1.25rem, 4vw, 3rem);
    text-align: center;
    color: var(--fd-ink-faint);
}

.fd-footer-name {
    font-family: var(--fd-serif);
    font-style: italic;
    font-weight: 300;
    font-size: 1.6rem;
    letter-spacing: 0.01em;
    color: var(--fd-ink-soft);
    margin: 1.5rem 0 0.5rem;
}

.fd-footer-address {
    font-style: normal;
    font-size: 0.82rem;
    letter-spacing: 0.04em;
    color: var(--fd-ink-faint);
    display: block;
    margin: 0.25rem 0;
}

.fd-footer-socials {
    justify-content: center;
    margin-top: 1.25rem;
    color: var(--fd-ink-faint);
}

.fd-footer-credit {
    margin-top: 2.5rem;
    font-size: 0.65rem;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    opacity: 0.5;
}

.fd-footer-credit a {
    color: inherit;
    text-decoration: none;
}

@media (prefers-reduced-motion: reduce) {
    .fd { transition: none; opacity: 1; }
    .fd-hero-center, .fd-menu-entry { animation: none; }
    .fd-hero-img { transition: none; }
    .fd-scroll-tick { animation: none; }
}
</style>
