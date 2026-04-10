<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { CheckCircle } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const breadcrumbItems: BreadcrumbItem[] = [
    { title: t('nav.billing'), href: '/panel/billing/plans' },
    { title: t('panel.billing.success_title'), href: '#' },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="flex h-full flex-1 flex-col items-center justify-center gap-6 rounded-xl p-8">
            <Card class="w-full max-w-md text-center">
                <CardContent class="flex flex-col items-center gap-6 pt-8 pb-8">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-green-100">
                        <CheckCircle class="h-9 w-9 text-green-600" />
                    </div>

                    <div class="space-y-2">
                        <h1 class="text-2xl font-bold">{{ t('panel.billing.success_title') }}</h1>
                        <p class="text-muted-foreground">
                            {{ t('panel.billing.success_body') }}
                        </p>
                    </div>

                    <div class="flex w-full flex-col gap-3">
                        <Button as-child class="w-full">
                            <a href="/panel">{{ t('panel.billing.go_to_panel') }}</a>
                        </Button>
                        <Button variant="outline" as-child class="w-full">
                            <a href="/panel/billing/manage">{{ t('panel.billing.manage_subscription') }}</a>
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
