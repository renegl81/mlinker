<script setup lang="ts">
import TopBar from '@/components/layout/TopBar.vue';
import BottomNav from '@/components/layout/BottomNav.vue';
import FlashToast from '@/components/layout/FlashToast.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import type { BreadcrumbItemType } from '@/types';
import { useWindowSize } from '@vueuse/core';
import { computed } from 'vue';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const { width } = useWindowSize();
const isMobile = computed(() => width.value < 768);
</script>

<template>
    <div class="flex min-h-screen flex-col bg-background">
        <!-- Fixed topbar — always visible -->
        <TopBar />

        <!-- Flash messages -->
        <FlashToast />

        <!-- Main content area -->
        <!-- pt-[52px] mobile to clear topbar; md:pt-14 desktop; pb-[60px] mobile for bottom nav -->
        <main
            class="mx-auto w-full max-w-7xl flex-1 px-4 pt-[52px] pb-[72px] md:px-6 md:pt-14 md:pb-6"
        >
            <!-- Breadcrumbs: only desktop, only if >1 item -->
            <div
                v-if="breadcrumbs && breadcrumbs.length > 1"
                class="hidden border-b border-border pb-3 pt-4 md:flex"
            >
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </div>

            <div
                class="py-4 md:py-6"
                :class="breadcrumbs && breadcrumbs.length > 1 ? '' : 'md:pt-6'"
            >
                <slot />
            </div>
        </main>

        <!-- Bottom nav — only mobile -->
        <BottomNav v-if="isMobile" />
    </div>
</template>
