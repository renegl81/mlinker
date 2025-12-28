<script setup lang="ts">
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import AppContent from '@/components/AppContent.vue'
import AppShell from '@/components/AppShell.vue'
import FrontHeader from '@/components/FrontHeader.vue'
import type { BreadcrumbItemType } from '@/types'

interface Props {
    breadcrumbs?: BreadcrumbItemType[]
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
})

const page = usePage()

const shouldUseDefaultTheme = computed(() => {
    const url = page.url
    return !url.startsWith('/admin') && !url.startsWith('/panel')
})
</script>

<template>
    <AppShell :class="{ 'default': shouldUseDefaultTheme }" class="flex-col">
        <FrontHeader :breadcrumbs="breadcrumbs" />
        <AppContent>
            <slot />
        </AppContent>
    </AppShell>
</template>
