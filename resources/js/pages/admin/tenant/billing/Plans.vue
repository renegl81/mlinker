<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { router, usePage } from '@inertiajs/vue3';
import { Check, Zap } from 'lucide-vue-next';
import { computed } from 'vue';

interface Plan {
    id: number;
    slug: string;
    name: string;
    price: string;
    period: string;
    description: string | null;
    stripe_price_id: string | null;
    max_locations: number;
    max_menus_per_location: number;
    max_products: number;
    max_images: number;
    has_analytics: boolean;
    has_custom_qr: boolean;
    has_multilang: boolean;
    has_api_access: boolean;
    has_custom_domain: boolean;
    show_branding: boolean;
    trial_days: number;
    sort_order: number;
}

interface CurrentSubscription {
    plan_id: number | null;
    stripe_status: string;
    plan: Plan | null;
}

interface Props {
    plans: Plan[];
    currentSubscription: CurrentSubscription | null;
}

const props = defineProps<Props>();
const page = usePage();
const flash = computed(() => (page.props as any).flash as { success?: string; error?: string } | undefined);

const currentPlanId = computed(() => props.currentSubscription?.plan_id ?? null);
const isFreePlan = computed(() => props.currentSubscription?.stripe_status === 'free');

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Facturación', href: '#' },
    { title: 'Planes', href: '#' },
];

function formatPrice(price: string): string {
    const num = parseFloat(price);
    if (num === 0) return 'Gratis';
    return `${num.toFixed(2).replace('.', ',')}€`;
}

function formatLimit(val: number, label: string): string {
    if (val === 0) return `${label} ilimitados`;
    return `${val} ${label}`;
}

function getPlanFeatures(plan: Plan): string[] {
    const features: string[] = [];
    features.push(formatLimit(plan.max_locations, 'ubicaciones'));
    features.push(formatLimit(plan.max_menus_per_location, 'menús por ubicación'));
    features.push(formatLimit(plan.max_products, 'productos'));
    if (plan.max_images === 0) {
        features.push('Imágenes ilimitadas');
    } else if (plan.max_images > 0) {
        features.push(`${plan.max_images} imágenes`);
    } else {
        features.push('Sin imágenes');
    }
    if (plan.has_analytics) features.push('Analíticas');
    if (plan.has_custom_qr) features.push('QR personalizado');
    if (plan.has_multilang) features.push('Multiidioma');
    if (plan.has_api_access) features.push('Acceso API');
    if (plan.has_custom_domain) features.push('Dominio personalizado');
    if (!plan.show_branding) features.push('Sin branding MenuLinker');
    return features;
}

function isCurrentPlan(plan: Plan): boolean {
    return plan.id === currentPlanId.value;
}

function isEnterprise(plan: Plan): boolean {
    return plan.slug === 'enterprise';
}

function getCtaLabel(plan: Plan): string {
    if (isCurrentPlan(plan)) return 'Plan actual';
    if (isEnterprise(plan)) return 'Contactar';
    if (isFreePlan.value) {
        return plan.trial_days > 0 ? `Empezar prueba de ${plan.trial_days} días` : 'Mejorar';
    }
    return 'Cambiar a este plan';
}

function selectPlan(plan: Plan) {
    if (isCurrentPlan(plan)) return;
    if (isEnterprise(plan)) {
        window.location.href = 'mailto:hola@menulinker.com?subject=Plan Enterprise';
        return;
    }
    if (!plan.stripe_price_id) {
        alert('Este plan no está disponible online. Contacta con nosotros.');
        return;
    }
    router.post('/panel/billing/checkout', { plan_slug: plan.slug });
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
            <HeadingSmall
                title="Planes y precios"
                description="Elige el plan que mejor se adapte a tu negocio."
            />

            <!-- Flash messages -->
            <div v-if="flash?.error" class="rounded-md border border-destructive/30 bg-destructive/10 px-4 py-3 text-sm text-destructive">
                {{ flash.error }}
            </div>
            <div v-if="flash?.success" class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                {{ flash.success }}
            </div>

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4">
                <Card
                    v-for="plan in plans"
                    :key="plan.id"
                    :class="[
                        'relative flex flex-col transition-shadow',
                        isCurrentPlan(plan) ? 'border-primary shadow-md ring-2 ring-primary' : 'hover:shadow-md',
                        plan.slug === 'pro' ? 'border-primary/50' : '',
                    ]"
                >
                    <!-- Current plan badge -->
                    <div v-if="isCurrentPlan(plan)" class="absolute -top-3 left-1/2 -translate-x-1/2">
                        <Badge class="bg-primary text-primary-foreground px-3 py-0.5 text-xs font-semibold shadow">
                            Plan actual
                        </Badge>
                    </div>

                    <!-- Popular badge -->
                    <div v-if="plan.slug === 'pro' && !isCurrentPlan(plan)" class="absolute -top-3 left-1/2 -translate-x-1/2">
                        <Badge variant="secondary" class="px-3 py-0.5 text-xs font-semibold shadow">
                            <Zap class="mr-1 h-3 w-3" />
                            Popular
                        </Badge>
                    </div>

                    <CardHeader class="pb-4">
                        <CardTitle class="text-xl font-bold">{{ plan.name }}</CardTitle>
                        <CardDescription v-if="plan.description">{{ plan.description }}</CardDescription>
                        <div class="mt-3 flex items-baseline gap-1">
                            <span class="text-3xl font-extrabold tracking-tight">{{ formatPrice(plan.price) }}</span>
                            <span v-if="parseFloat(plan.price) > 0" class="text-sm text-muted-foreground">/ mes</span>
                        </div>
                        <p v-if="plan.trial_days > 0 && !isCurrentPlan(plan)" class="text-xs text-green-600 font-medium mt-1">
                            {{ plan.trial_days }} días de prueba gratis
                        </p>
                    </CardHeader>

                    <CardContent class="flex-1">
                        <ul class="space-y-2">
                            <li
                                v-for="(feature, idx) in getPlanFeatures(plan)"
                                :key="idx"
                                class="flex items-center gap-2 text-sm"
                            >
                                <Check class="h-4 w-4 shrink-0 text-primary" />
                                <span>{{ feature }}</span>
                            </li>
                        </ul>
                    </CardContent>

                    <CardFooter class="pt-4">
                        <Button
                            class="w-full"
                            :variant="isCurrentPlan(plan) ? 'outline' : (plan.slug === 'pro' ? 'default' : 'outline')"
                            :disabled="isCurrentPlan(plan)"
                            @click="selectPlan(plan)"
                        >
                            {{ getCtaLabel(plan) }}
                        </Button>
                    </CardFooter>
                </Card>
            </div>

            <!-- Link to manage -->
            <div v-if="!isFreePlan" class="text-center">
                <a href="/panel/billing/manage" class="text-sm text-muted-foreground underline hover:text-foreground">
                    Gestionar suscripción actual
                </a>
            </div>
        </div>
    </AppLayout>
</template>
