<script setup lang="ts">
import type { OpeningHour } from '@/composables/useTenantHome';

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
    image_url?: string | null;
    logo_url?: string | null;
    menus?: Menu[];
    opening_hours?: OpeningHour[];
}

defineProps<{
    location: Location;
}>();
</script>

<template>
    <article class="lc-card">
        <div class="lc-image-wrap">
            <img
                v-if="location.image_url"
                :src="location.image_url"
                :alt="location.name"
                class="lc-image"
                loading="lazy"
            />
            <div v-else class="lc-image-placeholder">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" class="lc-placeholder-icon">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                    <polyline points="9 22 9 12 15 12 15 22" />
                </svg>
            </div>
        </div>

        <div class="lc-body">
            <h3 class="lc-name">{{ location.name }}</h3>
            <p v-if="location.address || location.city" class="lc-address">
                {{ [location.address, location.city].filter(Boolean).join(', ') }}
            </p>
            <p v-if="location.phone" class="lc-phone">{{ location.phone }}</p>

            <div v-if="location.menus && location.menus.length" class="lc-menus">
                <a
                    v-for="menu in location.menus"
                    :key="menu.id"
                    :href="`/menu/${menu.id}`"
                    class="lc-menu-link"
                >
                    {{ menu.name }}
                    <svg viewBox="0 0 16 16" width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M3 8h10M9 4l4 4-4 4" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </a>
            </div>
        </div>
    </article>
</template>

<style scoped>
.lc-card {
    background: var(--lc-card-bg, rgba(255,255,255,0.06));
    border: 1px solid var(--lc-card-border, rgba(255,255,255,0.1));
    border-radius: 1rem;
    overflow: hidden;
    transition: transform 300ms cubic-bezier(.2,.65,.2,1), box-shadow 300ms;
}

.lc-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 40px -12px rgba(0,0,0,0.3);
}

.lc-image-wrap {
    aspect-ratio: 16/9;
    overflow: hidden;
    background: var(--lc-placeholder-bg, rgba(0,0,0,0.15));
}

.lc-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 400ms cubic-bezier(.2,.65,.2,1);
}

.lc-card:hover .lc-image {
    transform: scale(1.04);
}

.lc-image-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0.3;
}

.lc-placeholder-icon {
    width: 3rem;
    height: 3rem;
}

.lc-body {
    padding: 1.25rem 1.5rem 1.5rem;
}

.lc-name {
    font-size: 1.2rem;
    font-weight: 600;
    margin: 0 0 0.4rem;
    color: var(--lc-name-color, inherit);
}

.lc-address, .lc-phone {
    font-size: 0.85rem;
    margin: 0.2rem 0;
    opacity: 0.7;
}

.lc-menus {
    margin-top: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}

.lc-menu-link {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.88rem;
    font-weight: 500;
    color: var(--lc-accent, currentColor);
    text-decoration: none;
    transition: gap 200ms;
}

.lc-menu-link:hover {
    gap: 0.7rem;
}
</style>
