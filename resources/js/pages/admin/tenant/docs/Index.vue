<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    BarChart2,
    BookOpen,
    Building2,
    ChevronRight,
    CreditCard,
    FileSpreadsheet,
    Globe,
    Languages,
    Palette,
    QrCode,
    Rocket,
    Settings,
    ShoppingBag,
    Users,
    Utensils,
} from 'lucide-vue-next';
import type { Component } from 'vue';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

interface Topic {
    slug: string;
    icon: string;
    sections: Array<{ heading: string; body: string; tip?: string }>;
}

const props = defineProps<{
    topics: Topic[];
}>();

const iconMap: Record<string, Component> = {
    Rocket,
    Building2,
    BookOpen,
    Utensils,
    Languages,
    QrCode,
    Users,
    CreditCard,
    Globe,
    Palette,
    BarChart2,
    ShoppingBag,
    FileSpreadsheet,
    Settings,
};

const topicCards = computed(() =>
    props.topics.map((topic) => ({
        slug: topic.slug,
        icon: iconMap[topic.icon] ?? BookOpen,
        title: t(`docs.topics.${topic.slug}.title`),
        description: t(`docs.topics.${topic.slug}.description`),
        href: `/panel/docs/${topic.slug}`,
    })),
);
</script>

<template>
    <AppLayout>
        <Head :title="t('docs.title')" />

        <div class="mx-auto max-w-5xl px-4 py-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-2xl font-bold tracking-tight text-foreground">
                    {{ t('docs.title') }}
                </h1>
                <p class="mt-1 text-muted-foreground">
                    {{ t('docs.subtitle') }}
                </p>
            </div>

            <!-- Topic grid -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <Link
                    v-for="card in topicCards"
                    :key="card.slug"
                    :href="card.href"
                    class="group flex flex-col gap-3 rounded-xl border border-border bg-card p-5 text-card-foreground shadow-sm transition-all hover:border-primary/40 hover:shadow-md"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary transition-colors group-hover:bg-primary/15"
                        >
                            <component :is="card.icon" class="size-5" />
                        </div>
                        <span class="font-semibold leading-tight">{{ card.title }}</span>
                    </div>
                    <p class="text-sm leading-relaxed text-muted-foreground">
                        {{ card.description }}
                    </p>
                    <div
                        class="mt-auto flex items-center gap-1 text-xs font-medium text-primary opacity-0 transition-opacity group-hover:opacity-100"
                    >
                        {{ t('docs.read_more') }}
                        <ChevronRight class="size-3" />
                    </div>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
