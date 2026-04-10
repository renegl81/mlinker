<script setup lang="ts">
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import LanguageSelector from '@/components/ui/LanguageSelector.vue';
import { Link } from '@inertiajs/vue3';
import { Menu } from 'lucide-vue-next';
import type { Component } from 'vue';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';

interface NavItem {
    title: string;
    href: string;
    icon?: Component;
    external?: boolean;
}

defineProps<{
    items: NavItem[];
    class?: string;
}>();

const { t } = useI18n();
const open = ref(false);
</script>

<template>
    <Sheet v-model:open="open">
        <SheetTrigger
            :class="$props.class"
            class="flex size-9 items-center justify-center rounded-lg text-muted-foreground hover:bg-accent hover:text-foreground"
            :aria-label="t('nav.more')"
        >
            <Menu class="size-5" />
        </SheetTrigger>
        <SheetContent
            side="bottom"
            class="rounded-t-2xl"
            style="padding-bottom: env(safe-area-inset-bottom, 1rem)"
        >
            <SheetHeader class="mb-4">
                <SheetTitle class="text-left">{{ t('nav.more') }}</SheetTitle>
            </SheetHeader>

            <nav class="flex flex-col gap-1">
                <template v-for="item in items" :key="item.href">
                    <a
                        v-if="item.external"
                        :href="item.href"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="flex min-h-[44px] items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-foreground hover:bg-accent"
                        @click="open = false"
                    >
                        <component :is="item.icon" class="size-5 text-muted-foreground" />
                        {{ item.title }}
                    </a>
                    <Link
                        v-else
                        :href="item.href"
                        class="flex min-h-[44px] items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-foreground hover:bg-accent"
                        @click="open = false"
                    >
                        <component :is="item.icon" class="size-5 text-muted-foreground" />
                        {{ item.title }}
                    </Link>
                </template>
            </nav>

            <div class="mt-4 border-t border-border pt-4">
                <LanguageSelector />
            </div>
        </SheetContent>
    </Sheet>
</template>
