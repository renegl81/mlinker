<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { show, edit } from '@/routes/tenant/locations';
import type { Location } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { Building2, MapPin, Pencil, Phone, Trash2, Utensils } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    location: Location;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    delete: [id: number];
}>();

const page = usePage();
const messages = computed(() => page.props.messages as any);

const menuCount = computed(() => props.location.menus?.length ?? 0);
const locationImage = computed(() => (props.location as any).image_path ?? props.location.image_url ?? null);
const initial = computed(() => props.location.name?.charAt(0)?.toUpperCase() ?? 'L');

function handleDelete() {
    emit('delete', props.location.id);
}
</script>

<template>
    <div class="loc-card">
        <!-- Image / fallback -->
        <Link :href="show(location.id)" class="loc-image-wrap">
            <img
                v-if="locationImage"
                :src="locationImage"
                :alt="location.name"
                class="loc-image"
                loading="lazy"
            />
            <div v-else class="loc-image-fallback">
                <span class="loc-initial">{{ initial }}</span>
            </div>
            <div v-if="menuCount > 0" class="loc-menu-badge">
                <Utensils class="loc-badge-icon" />
                {{ menuCount }} {{ menuCount === 1 ? 'carta' : 'cartas' }}
            </div>
        </Link>

        <!-- Body -->
        <div class="loc-body">
            <Link :href="show(location.id)" class="loc-name">
                {{ location.name }}
            </Link>
            <p v-if="location.description" class="loc-desc">
                {{ location.description }}
            </p>
            <div class="loc-meta">
                <div v-if="location.address" class="loc-meta-row">
                    <MapPin class="loc-meta-icon" />
                    <span>{{ location.address }}{{ location.city ? `, ${location.city}` : '' }}</span>
                </div>
                <div v-if="location.phone" class="loc-meta-row">
                    <Phone class="loc-meta-icon" />
                    <span>{{ location.phone }}</span>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="loc-actions">
            <Button variant="outline" size="sm" as-child class="loc-action-btn">
                <Link :href="show(location.id)">
                    <Building2 class="loc-action-icon" />
                    {{ messages.actions.show_details }}
                </Link>
            </Button>
            <div class="loc-action-icons">
                <Button variant="outline" size="icon" as-child>
                    <Link :href="edit(location.id)">
                        <Pencil class="loc-action-icon" />
                    </Link>
                </Button>
                <Button variant="destructive" size="icon" @click="handleDelete">
                    <Trash2 class="loc-action-icon" />
                </Button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.loc-card {
    display: flex;
    flex-direction: column;
    border-radius: 16px;
    border: 1px solid var(--color-border);
    background: var(--color-card);
    color: var(--color-card-foreground);
    overflow: hidden;
    transition: box-shadow 300ms, transform 300ms;
}

.loc-card:hover {
    box-shadow: 0 12px 32px -12px oklch(0 0 0 / 0.12);
    transform: translateY(-2px);
}

.loc-image-wrap {
    position: relative;
    aspect-ratio: 16/9;
    overflow: hidden;
    display: block;
    text-decoration: none;
}

.loc-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 500ms cubic-bezier(.2,.65,.2,1);
}

.loc-card:hover .loc-image {
    transform: scale(1.04);
}

.loc-image-fallback {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, var(--color-primary), oklch(0.65 0.15 200));
}

.loc-initial {
    font-size: 3rem;
    font-weight: 700;
    color: oklch(1 0 0);
    opacity: 0.9;
}

.loc-menu-badge {
    position: absolute;
    bottom: 0.75rem;
    right: 0.75rem;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.3rem 0.65rem;
    border-radius: 999px;
    background: oklch(0 0 0 / 0.6);
    backdrop-filter: blur(8px);
    color: oklch(1 0 0);
    font-size: 0.72rem;
    font-weight: 600;
}

.loc-badge-icon {
    width: 12px;
    height: 12px;
}

.loc-body {
    padding: 1.25rem 1.25rem 0.75rem;
    flex: 1;
}

.loc-name {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--color-card-foreground);
    text-decoration: none;
    display: block;
    line-height: 1.3;
}

.loc-name:hover {
    color: var(--color-primary);
}

.loc-desc {
    margin: 0.4rem 0 0;
    font-size: 0.82rem;
    color: var(--color-muted-foreground);
    line-height: 1.5;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.loc-meta {
    margin-top: 0.85rem;
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}

.loc-meta-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.8rem;
    color: var(--color-muted-foreground);
}

.loc-meta-icon {
    width: 14px;
    height: 14px;
    flex-shrink: 0;
    opacity: 0.6;
}

.loc-actions {
    padding: 0.75rem 1.25rem 1.25rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    border-top: 1px solid var(--color-border);
    margin-top: 0.75rem;
}

.loc-action-btn {
    flex: 1;
}

.loc-action-icons {
    display: flex;
    gap: 0.35rem;
}

.loc-action-icon {
    width: 14px;
    height: 14px;
}
</style>
