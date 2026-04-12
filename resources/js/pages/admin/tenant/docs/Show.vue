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
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

interface Section {
    heading: string;
    body: string;
    tip?: string;
}

interface Topic {
    slug: string;
    icon: string;
    sections: Section[];
}

const props = defineProps<{
    topic: Topic;
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

const topicIcon = computed(() => iconMap[props.topic.icon] ?? BookOpen);

const sidebarTopics = computed(() =>
    props.topics.map((tp) => ({
        slug: tp.slug,
        icon: iconMap[tp.icon] ?? BookOpen,
        title: t(`docs.topics.${tp.slug}.title`),
        href: `/panel/docs/${tp.slug}`,
        active: tp.slug === props.topic.slug,
    })),
);

const topicTitle = computed(() => t(`docs.topics.${props.topic.slug}.title`));

// Mobile: sidebar accordion open state
const sidebarOpen = ref(false);
</script>

<template>
    <AppLayout>
        <Head :title="`${topicTitle} — ${t('docs.title')}`" />

        <div class="mx-auto max-w-6xl px-4 py-8">
            <!-- Breadcrumb -->
            <nav class="mb-6 flex items-center gap-1.5 text-sm text-muted-foreground">
                <Link href="/panel/docs" class="transition-colors hover:text-foreground">
                    {{ t('docs.title') }}
                </Link>
                <ChevronRight class="size-3.5" />
                <span class="text-foreground">{{ topicTitle }}</span>
            </nav>

            <div class="flex flex-col gap-8 lg:flex-row lg:gap-10">
                <!-- Sidebar (desktop) -->
                <aside class="hidden w-56 shrink-0 lg:block">
                    <nav class="sticky top-20 flex flex-col gap-0.5">
                        <Link
                            v-for="item in sidebarTopics"
                            :key="item.slug"
                            :href="item.href"
                            class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium transition-colors"
                            :class="
                                item.active
                                    ? 'bg-primary/10 text-primary'
                                    : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground'
                            "
                        >
                            <component :is="item.icon" class="size-4 shrink-0" />
                            <span class="truncate">{{ item.title }}</span>
                        </Link>
                    </nav>
                </aside>

                <!-- Mobile sidebar toggle -->
                <div class="lg:hidden">
                    <button
                        type="button"
                        class="flex w-full items-center justify-between rounded-xl border border-border bg-card px-4 py-3 text-sm font-medium text-card-foreground"
                        @click="sidebarOpen = !sidebarOpen"
                    >
                        <span class="flex items-center gap-2">
                            <component :is="topicIcon" class="size-4" />
                            {{ topicTitle }}
                        </span>
                        <ChevronRight
                            class="size-4 text-muted-foreground transition-transform"
                            :class="sidebarOpen ? 'rotate-90' : ''"
                        />
                    </button>
                    <nav
                        v-if="sidebarOpen"
                        class="mt-1 flex flex-col gap-0.5 rounded-xl border border-border bg-card p-2"
                    >
                        <Link
                            v-for="item in sidebarTopics"
                            :key="item.slug"
                            :href="item.href"
                            class="flex items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium transition-colors"
                            :class="
                                item.active
                                    ? 'bg-primary/10 text-primary'
                                    : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground'
                            "
                            @click="sidebarOpen = false"
                        >
                            <component :is="item.icon" class="size-4 shrink-0" />
                            <span>{{ item.title }}</span>
                        </Link>
                    </nav>
                </div>

                <!-- Main content -->
                <main class="min-w-0 flex-1">
                    <!-- Topic header -->
                    <div class="mb-8 flex items-center gap-4">
                        <div
                            class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary"
                        >
                            <component :is="topicIcon" class="size-6" />
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold tracking-tight text-foreground">
                                {{ topicTitle }}
                            </h1>
                            <p class="text-sm text-muted-foreground">
                                {{ t(`docs.topics.${topic.slug}.description`) }}
                            </p>
                        </div>
                    </div>

                    <!-- Sections -->
                    <div class="flex flex-col gap-8">
                        <template v-for="(section, idx) in topic.sections" :key="idx">
                            <!-- Tip callout (when heading is empty, it's a tip-only block) -->
                            <div
                                v-if="section.tip"
                                class="flex gap-3 rounded-xl border-l-4 border-teal-500 bg-teal-50 px-4 py-3 dark:bg-teal-950/30"
                            >
                                <span class="mt-0.5 text-base">💡</span>
                                <p class="text-sm leading-relaxed text-teal-800 dark:text-teal-300">
                                    {{ t(section.tip) }}
                                </p>
                            </div>

                            <!-- Regular section -->
                            <div v-else class="flex flex-col gap-2">
                                <h2
                                    v-if="section.heading"
                                    class="text-lg font-semibold text-foreground"
                                >
                                    {{ t(section.heading) }}
                                </h2>
                                <p
                                    v-if="section.body"
                                    class="text-sm leading-relaxed text-muted-foreground"
                                >
                                    {{ t(section.body) }}
                                </p>
                            </div>
                        </template>
                    </div>

                    <!-- Navigation footer -->
                    <div class="mt-12 border-t border-border pt-6">
                        <Link
                            href="/panel/docs"
                            class="inline-flex items-center gap-1.5 text-sm font-medium text-primary hover:underline"
                        >
                            ← {{ t('docs.back') }}
                        </Link>
                    </div>
                </main>
            </div>
        </div>
    </AppLayout>
</template>
