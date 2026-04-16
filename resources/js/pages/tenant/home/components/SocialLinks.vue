<script setup lang="ts">
import { computed } from 'vue';
import { parseSocialLinks } from '@/composables/useTenantHome';

const props = defineProps<{
    socialMedias: unknown;
    size?: 'sm' | 'md' | 'lg';
}>();

const links = computed(() => parseSocialLinks(props.socialMedias));
const sz = computed(() => props.size ?? 'md');
</script>

<template>
    <div v-if="links.length" class="social-links" :class="`social-${sz}`">
        <a
            v-for="link in links"
            :key="link.platform"
            :href="link.url"
            target="_blank"
            rel="noopener noreferrer"
            :aria-label="link.label"
            class="social-link"
            :title="link.label"
            v-html="link.icon"
        />
    </div>
</template>

<style scoped>
.social-links {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.social-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: inherit;
    opacity: 0.7;
    transition: opacity 200ms, transform 200ms;
    flex-shrink: 0;
}

.social-link:hover {
    opacity: 1;
    transform: translateY(-1px);
}

.social-sm .social-link { width: 1.25rem; height: 1.25rem; }
.social-md .social-link { width: 1.5rem; height: 1.5rem; }
.social-lg .social-link { width: 1.875rem; height: 1.875rem; }

.social-link :deep(svg) {
    width: 100%;
    height: 100%;
}
</style>
