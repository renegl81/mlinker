<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { Link } from '@inertiajs/vue3';
import { Lock } from 'lucide-vue-next';
import type { Component } from 'vue';
import { useI18n } from 'vue-i18n';

interface Props {
    title: string;
    icon: Component;
    description: string;
    previewValue?: string;
    previewCaption?: string;
    badgeVariant: 'pro' | 'soon';
    ctaHref?: string;
}

withDefaults(defineProps<Props>(), {
    ctaHref: '/panel/billing/plans',
});

const { t } = useI18n();
</script>

<template>
    <TooltipProvider>
        <Card class="relative overflow-hidden">
            <CardHeader class="flex flex-row items-center justify-between pb-2">
                <CardTitle class="text-sm font-medium text-muted-foreground">
                    {{ title }}
                </CardTitle>
                <div class="flex items-center gap-1.5">
                    <Badge
                        v-if="badgeVariant === 'pro'"
                        variant="default"
                        class="h-5 bg-teal-500 px-1.5 text-[10px] font-semibold uppercase text-white hover:bg-teal-600"
                    >
                        {{ t('panel.dashboard.locked.pro_badge') }}
                    </Badge>
                    <Badge
                        v-else
                        variant="secondary"
                        class="h-5 px-1.5 text-[10px] font-semibold uppercase"
                    >
                        {{ t('panel.dashboard.locked.soon_badge') }}
                    </Badge>
                    <component :is="icon" class="h-4 w-4 text-muted-foreground" />
                </div>
            </CardHeader>
            <CardContent>
                <div v-if="previewValue" class="text-2xl font-bold opacity-40 select-none">
                    {{ previewValue }}
                </div>
                <p v-if="previewCaption" class="mt-1 text-xs text-muted-foreground opacity-40 select-none">
                    {{ previewCaption }}
                </p>
                <p v-else class="mt-1 text-xs text-muted-foreground opacity-40 select-none">
                    {{ t('panel.dashboard.locked.sample_label') }}
                </p>
            </CardContent>

            <!-- Overlay -->
            <div class="absolute inset-0 flex flex-col items-center justify-center gap-2 bg-background/70 backdrop-blur-[2px] px-3 text-center">
                <Tooltip>
                    <TooltipTrigger as-child>
                        <Lock class="h-5 w-5 text-muted-foreground cursor-default" aria-hidden="true" />
                    </TooltipTrigger>
                    <TooltipContent>
                        <p class="max-w-[180px] text-xs">{{ description }}</p>
                    </TooltipContent>
                </Tooltip>
                <Link
                    v-if="badgeVariant === 'pro' && ctaHref"
                    :href="ctaHref"
                    class="rounded-md bg-teal-500 px-3 py-1.5 text-xs font-semibold text-white transition-colors hover:bg-teal-600"
                >
                    {{ t('panel.dashboard.locked.unlock_cta') }}
                </Link>
            </div>
        </Card>
    </TooltipProvider>
</template>
