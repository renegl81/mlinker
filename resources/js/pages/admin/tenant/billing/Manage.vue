<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { router, usePage } from '@inertiajs/vue3';
import { AlertTriangle, CheckCircle, Clock, CreditCard, XCircle } from 'lucide-vue-next';
import { computed } from 'vue';

interface Plan {
    id: number;
    slug: string;
    name: string;
    price: string;
    period: string;
    description: string | null;
}

interface Subscription {
    id: number;
    plan_id: number | null;
    stripe_status: string;
    stripe_price: string | null;
    trial_ends_at: string | null;
    ends_at: string | null;
    created_at: string;
    plan: Plan | null;
}

interface Props {
    subscription: Subscription | null;
}

const props = defineProps<Props>();
const page = usePage();
const flash = computed(() => (page.props as any).flash as { success?: string; error?: string } | undefined);

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Facturación', href: '/panel/billing/plans' },
    { title: 'Gestionar suscripción', href: '#' },
];

const isFree = computed(() => !props.subscription || props.subscription.stripe_status === 'free');
const isOnTrial = computed(() => {
    if (!props.subscription?.trial_ends_at) return false;
    return new Date(props.subscription.trial_ends_at) > new Date();
});
const isCancelled = computed(() => props.subscription?.stripe_status === 'canceled');
const isActive = computed(() => props.subscription?.stripe_status === 'active');

function formatDate(dateStr: string | null): string {
    if (!dateStr) return '—';
    return new Date(dateStr).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
}

function formatPrice(price: string | null): string {
    if (!price) return '—';
    const num = parseFloat(price);
    if (num === 0) return 'Gratis';
    return `${num.toFixed(2).replace('.', ',')}€ / mes`;
}

function getStatusLabel(): string {
    if (isFree.value) return 'Plan gratuito';
    if (isOnTrial.value) return 'Periodo de prueba';
    if (isCancelled.value) return 'Cancelada';
    if (isActive.value) return 'Activa';
    return props.subscription?.stripe_status ?? 'Desconocido';
}

function getStatusVariant(): 'default' | 'secondary' | 'destructive' | 'outline' {
    if (isFree.value) return 'secondary';
    if (isOnTrial.value) return 'default';
    if (isCancelled.value) return 'destructive';
    if (isActive.value) return 'default';
    return 'outline';
}

function cancel() {
    if (!confirm('¿Seguro que quieres cancelar tu suscripción? Podrás seguir usando el plan hasta que acabe el período de facturación.')) return;
    router.post('/panel/billing/cancel');
}

function resume() {
    router.post('/panel/billing/resume');
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
            <HeadingSmall
                title="Gestionar suscripción"
                description="Consulta y gestiona tu plan actual."
            />

            <!-- Flash messages -->
            <div v-if="flash?.error" class="rounded-md border border-destructive/30 bg-destructive/10 px-4 py-3 text-sm text-destructive">
                {{ flash.error }}
            </div>
            <div v-if="flash?.success" class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                <CheckCircle class="mr-2 inline h-4 w-4" />
                {{ flash.success }}
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Plan info -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <CreditCard class="h-5 w-5 text-primary" />
                            Plan actual
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-2xl font-bold">{{ subscription?.plan?.name ?? 'Free' }}</span>
                            <Badge :variant="getStatusVariant()">{{ getStatusLabel() }}</Badge>
                        </div>

                        <div v-if="subscription?.plan?.description" class="text-sm text-muted-foreground">
                            {{ subscription.plan.description }}
                        </div>

                        <Separator />

                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">Precio</span>
                                <span class="font-medium">{{ formatPrice(subscription?.plan?.price ?? '0') }}</span>
                            </div>

                            <div v-if="isOnTrial && subscription?.trial_ends_at" class="flex justify-between text-amber-600">
                                <span class="flex items-center gap-1">
                                    <Clock class="h-3.5 w-3.5" />
                                    Prueba gratis hasta
                                </span>
                                <span class="font-medium">{{ formatDate(subscription.trial_ends_at) }}</span>
                            </div>

                            <div v-if="isCancelled && subscription?.ends_at" class="flex justify-between text-destructive">
                                <span class="flex items-center gap-1">
                                    <XCircle class="h-3.5 w-3.5" />
                                    Acceso hasta
                                </span>
                                <span class="font-medium">{{ formatDate(subscription.ends_at) }}</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Actions -->
                <Card>
                    <CardHeader>
                        <CardTitle>Acciones</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <!-- Free plan -->
                        <div v-if="isFree" class="space-y-3">
                            <p class="text-sm text-muted-foreground">
                                Actualmente usas el plan gratuito. Mejora tu plan para acceder a todas las funcionalidades.
                            </p>
                            <Button as-child class="w-full">
                                <a href="/panel/billing/plans">Mejorar a Pro</a>
                            </Button>
                        </div>

                        <!-- Paid, active or on trial -->
                        <template v-if="!isFree">
                            <Button variant="outline" as-child class="w-full">
                                <a href="/panel/billing/plans">Cambiar de plan</a>
                            </Button>

                            <!-- Resume if cancelled -->
                            <div v-if="isCancelled">
                                <div class="mb-2 flex items-start gap-2 rounded-md border border-amber-200 bg-amber-50 p-3 text-sm text-amber-700">
                                    <AlertTriangle class="mt-0.5 h-4 w-4 shrink-0" />
                                    <span>Tu suscripción está cancelada y finalizará el {{ formatDate(subscription?.ends_at ?? null) }}.</span>
                                </div>
                                <Button class="w-full" @click="resume">
                                    Reanudar suscripción
                                </Button>
                            </div>

                            <!-- Cancel if active or on trial -->
                            <div v-if="!isCancelled">
                                <Button variant="ghost" class="w-full text-destructive hover:bg-destructive/10 hover:text-destructive" @click="cancel">
                                    Cancelar suscripción
                                </Button>
                                <p class="mt-1 text-center text-xs text-muted-foreground">
                                    Podrás seguir usando tu plan hasta el final del período de facturación.
                                </p>
                            </div>
                        </template>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
