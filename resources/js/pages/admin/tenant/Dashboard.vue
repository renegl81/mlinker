<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes/tenant';
import type { BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { BarChart2, Eye, Star, TrendingUp, Lock } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

interface DayView {
    date: string;
    count: number;
}

interface TopMenu {
    menu_id: number;
    name: string;
    count: number;
}

interface ViewsBySource {
    QR: number;
    WhatsApp: number;
    Google: number;
    Social: number;
    Directo: number;
}

interface CurrentPeriod {
    start: string;
    end: string;
    days: number;
}

const props = defineProps<{
    hasAnalytics: boolean;
    currentPlan: string;
    total_views: number;
    views_by_day: DayView[];
    top_menus: TopMenu[];
    views_by_source: ViewsBySource;
    current_period: CurrentPeriod;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

const topMenu = computed(() => props.top_menus?.[0] ?? null);

const mainSource = computed(() => {
    if (!props.views_by_source) return '—';
    const sources = props.views_by_source as Record<string, number>;
    const sorted = Object.entries(sources).sort((a, b) => b[1] - a[1]);
    return sorted[0]?.[0] ?? '—';
});

const chartMax = computed(() => {
    const counts = props.views_by_day?.map((d) => d.count) ?? [];
    return Math.max(...counts, 1);
});

const dummyDays = computed<DayView[]>(() => {
    const now = new Date();
    return Array.from({ length: 30 }, (_, i) => {
        const d = new Date(now);
        d.setDate(d.getDate() - (29 - i));
        return {
            date: d.toISOString().slice(0, 10),
            count: Math.floor(Math.random() * 40) + 5,
        };
    });
});

const dummyMax = computed(() => {
    return Math.max(...dummyDays.value.map((d) => d.count), 1);
});

const sourceLabels = computed<Record<string, string>>(() => ({
    QR: 'QR',
    WhatsApp: 'WhatsApp',
    Google: 'Google',
    Social: t('panel.dashboard.source_social'),
    Directo: t('panel.dashboard.source_direct'),
}));
</script>

<template>
    <Head :title="t('panel.dashboard.title')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4">

            <!-- Header con plan badge -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">{{ t('panel.dashboard.title') }}</h1>
                    <p class="text-sm text-muted-foreground">
                        {{ t('panel.dashboard.last_days', { days: current_period.days }) }}
                        ({{ current_period.start }} — {{ current_period.end }})
                    </p>
                </div>
                <span
                    class="rounded-full px-3 py-1 text-xs font-semibold"
                    :class="hasAnalytics
                        ? 'bg-primary text-primary-foreground'
                        : 'bg-muted text-muted-foreground border'"
                >
                    {{ t('panel.dashboard.plan', { plan: currentPlan }) }}
                </span>
            </div>

            <!-- ======================== PRO VIEW ======================== -->
            <template v-if="hasAnalytics">

                <!-- Cards de métricas -->
                <div class="grid gap-4 md:grid-cols-3">
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between pb-2">
                            <CardTitle class="text-sm font-medium text-muted-foreground">
                                {{ t('panel.dashboard.visits_month') }}
                            </CardTitle>
                            <Eye class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-4xl font-bold">{{ total_views.toLocaleString() }}</div>
                            <p class="mt-1 text-xs text-muted-foreground">{{ t('panel.dashboard.scans') }}</p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between pb-2">
                            <CardTitle class="text-sm font-medium text-muted-foreground">
                                {{ t('panel.dashboard.top_menu') }}
                            </CardTitle>
                            <Star class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="truncate text-2xl font-bold">
                                {{ topMenu ? topMenu.name : '—' }}
                            </div>
                            <p class="mt-1 text-xs text-muted-foreground">
                                {{ topMenu ? `${topMenu.count.toLocaleString()} ${t('panel.dashboard.visits_col').toLowerCase()}` : t('panel.dashboard.no_data') }}
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between pb-2">
                            <CardTitle class="text-sm font-medium text-muted-foreground">
                                {{ t('panel.dashboard.main_source') }}
                            </CardTitle>
                            <TrendingUp class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold">{{ mainSource }}</div>
                            <p class="mt-1 text-xs text-muted-foreground">{{ t('panel.dashboard.top_traffic') }}</p>
                        </CardContent>
                    </Card>
                </div>

                <!-- Gráfico de barras por día -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-base font-semibold">
                            <BarChart2 class="h-4 w-4" />
                            {{ t('panel.dashboard.visits_by_day') }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="flex h-40 items-end gap-0.5">
                            <div
                                v-for="day in views_by_day"
                                :key="day.date"
                                class="group relative flex flex-1 flex-col items-center justify-end"
                            >
                                <div
                                    class="w-full rounded-t bg-primary transition-all"
                                    :style="{ height: `${(day.count / chartMax) * 100}%`, minHeight: day.count > 0 ? '2px' : '0' }"
                                />
                                <!-- Tooltip -->
                                <div
                                    class="absolute bottom-full mb-1 hidden rounded bg-popover px-2 py-1 text-xs text-popover-foreground shadow group-hover:block whitespace-nowrap z-10"
                                >
                                    {{ day.date }}: {{ day.count }}
                                </div>
                            </div>
                        </div>
                        <div class="mt-2 flex justify-between text-xs text-muted-foreground">
                            <span>{{ current_period.start }}</span>
                            <span>{{ current_period.end }}</span>
                        </div>
                    </CardContent>
                </Card>

                <!-- Tabla top 5 menús + Desglose por fuente -->
                <div class="grid gap-4 md:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-base font-semibold">{{ t('panel.dashboard.top5_menus') }}</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div v-if="top_menus.length === 0" class="text-sm text-muted-foreground">
                                {{ t('panel.dashboard.no_visits') }}
                            </div>
                            <table v-else class="w-full text-sm">
                                <thead>
                                    <tr class="border-b text-left text-muted-foreground">
                                        <th class="pb-2 font-medium">#</th>
                                        <th class="pb-2 font-medium">{{ t('panel.dashboard.menu_col') }}</th>
                                        <th class="pb-2 text-right font-medium">{{ t('panel.dashboard.visits_col') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="(menu, idx) in top_menus"
                                        :key="menu.menu_id"
                                        class="border-b last:border-0"
                                    >
                                        <td class="py-2 text-muted-foreground">{{ idx + 1 }}</td>
                                        <td class="py-2 truncate max-w-[160px]">{{ menu.name }}</td>
                                        <td class="py-2 text-right font-semibold">{{ menu.count.toLocaleString() }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle class="text-base font-semibold">{{ t('panel.dashboard.origin') }}</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-3">
                                <div
                                    v-for="(count, source) in views_by_source"
                                    :key="source"
                                    class="flex items-center gap-3"
                                >
                                    <span class="w-24 text-sm text-muted-foreground">
                                        {{ sourceLabels[source] ?? source }}
                                    </span>
                                    <div class="flex-1 overflow-hidden rounded-full bg-muted">
                                        <div
                                            class="h-2 rounded-full bg-primary transition-all"
                                            :style="{ width: `${total_views > 0 ? Math.round((count / total_views) * 100) : 0}%` }"
                                        />
                                    </div>
                                    <span class="w-10 text-right text-sm font-semibold">{{ count }}</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

            </template>

            <!-- ======================== FREE VIEW ======================== -->
            <template v-else>

                <!-- Card visitas reales (teaser) -->
                <div class="grid gap-4 md:grid-cols-3">
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between pb-2">
                            <CardTitle class="text-sm font-medium text-muted-foreground">
                                {{ t('panel.dashboard.visits_month') }}
                            </CardTitle>
                            <Eye class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-4xl font-bold">{{ total_views.toLocaleString() }}</div>
                            <p class="mt-1 text-xs text-muted-foreground">{{ t('panel.dashboard.scans') }}</p>
                        </CardContent>
                    </Card>

                    <!-- Card bloqueada: menú popular -->
                    <Card class="relative overflow-hidden">
                        <CardHeader class="flex flex-row items-center justify-between pb-2">
                            <CardTitle class="text-sm font-medium text-muted-foreground">
                                {{ t('panel.dashboard.top_menu') }}
                            </CardTitle>
                            <Star class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold blur-sm select-none">Carta de verano</div>
                            <p class="mt-1 text-xs text-muted-foreground blur-sm select-none">248 visitas</p>
                        </CardContent>
                        <div class="absolute inset-0 flex flex-col items-center justify-center bg-background/70 backdrop-blur-[2px]">
                            <Lock class="mb-1 h-5 w-5 text-muted-foreground" />
                        </div>
                    </Card>

                    <!-- Card bloqueada: fuente -->
                    <Card class="relative overflow-hidden">
                        <CardHeader class="flex flex-row items-center justify-between pb-2">
                            <CardTitle class="text-sm font-medium text-muted-foreground">
                                {{ t('panel.dashboard.main_source') }}
                            </CardTitle>
                            <TrendingUp class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold blur-sm select-none">QR</div>
                            <p class="mt-1 text-xs text-muted-foreground blur-sm select-none">70% del tráfico</p>
                        </CardContent>
                        <div class="absolute inset-0 flex flex-col items-center justify-center bg-background/70 backdrop-blur-[2px]">
                            <Lock class="mb-1 h-5 w-5 text-muted-foreground" />
                        </div>
                    </Card>
                </div>

                <!-- Gráfico + resto bloqueado -->
                <Card class="relative overflow-hidden">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-base font-semibold">
                            <BarChart2 class="h-4 w-4" />
                            {{ t('panel.dashboard.visits_by_day') }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <!-- Gráfico dummy detrás del blur -->
                        <div class="flex h-40 items-end gap-0.5 blur-sm">
                            <div
                                v-for="day in dummyDays"
                                :key="day.date"
                                class="flex flex-1 flex-col items-center justify-end"
                            >
                                <div
                                    class="w-full rounded-t bg-primary/40"
                                    :style="{ height: `${(day.count / dummyMax) * 100}%` }"
                                />
                            </div>
                        </div>
                    </CardContent>
                    <!-- Overlay -->
                    <div class="absolute inset-0 flex flex-col items-center justify-center bg-background/75 backdrop-blur-[2px] gap-3 px-4 text-center">
                        <Lock class="h-7 w-7 text-muted-foreground" />
                        <p class="text-sm font-semibold">{{ t('panel.dashboard.unlock_analytics') }}</p>
                        <p class="text-xs text-muted-foreground">{{ t('panel.dashboard.unlock_description') }}</p>
                        <Link
                            href="/panel/billing/plans"
                            class="mt-1 rounded-md bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground hover:bg-primary/90 transition-colors"
                        >
                            {{ t('panel.billing.view_plans') }}
                        </Link>
                    </div>
                </Card>

            </template>

        </div>
    </AppLayout>
</template>
