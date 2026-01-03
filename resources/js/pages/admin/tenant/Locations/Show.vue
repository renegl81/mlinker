<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Inertia } from '@inertiajs/inertia';
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import {
    Pencil,
    Trash2,
    ArrowLeft,
    MapPin,
    Phone,
    Globe,
    Clock,
    BadgeDollarSign,
} from 'lucide-vue-next';
import type { BreadcrumbItem, Location } from '@/types';
import {
    index as locationsRoute,
    edit as locationRouteEdit,
    destroy as locationRouteDestroy,
} from '@/routes/tenant/locations';

const page = usePage();
const messages = computed(() => page.props.messages as any);

interface Props {
    location: Location;
}

const props = defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: messages.value.locations.plural,
        href: locationsRoute().url,
    },
    {
        title: props.location.name,
        href: '#',
    },
];

function remove() {
    if (confirm(messages.value.locations.actions.confirm_delete)) {
        Inertia.delete(locationRouteDestroy(props.location.id).url);
    }
}
</script>

<template>
    <Head :title="`${messages.locations.single}: ${location.name}`" />

    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="outline" size="icon" as-child>
                        <Link :href="locationsRoute().url">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                    </Button>
                    <HeadingSmall
                        :title="location.name"
                        :description="messages.locations.fields.description"
                    />
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" as-child>
                        <Link :href="locationRouteEdit(location.id)">
                            <Pencil class="mr-2 h-4 w-4" />
                            {{ messages.locations.actions.edit }}
                        </Link>
                    </Button>
                    <Button variant="destructive" @click="remove">
                        <Trash2 class="mr-2 h-4 w-4" />
                        {{ messages.locations.actions.delete }}
                    </Button>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <Card class="lg:col-span-2">
                    <CardHeader>
                        <CardTitle>{{
                            messages.locations.form.title_show ||
                            'Detalles de la Ubicación'
                        }}</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="flex flex-col gap-1">
                                <span
                                    class="text-sm font-medium text-muted-foreground"
                                    >{{
                                        messages.locations.fields.address
                                    }}</span
                                >
                                <div class="flex items-center gap-2">
                                    <MapPin class="h-4 w-4 text-primary" />
                                    <span>{{ location.address }}</span>
                                </div>
                            </div>
                            <div class="flex flex-col gap-1">
                                <span
                                    class="text-sm font-medium text-muted-foreground"
                                    >{{ messages.locations.fields.phone }}</span
                                >
                                <div class="flex items-center gap-2">
                                    <Phone class="h-4 w-4 text-primary" />
                                    <span>{{ location.phone || 'N/A' }}</span>
                                </div>
                            </div>
                        </div>

                        <Separator />

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <div class="flex flex-col gap-1">
                                <span
                                    class="text-sm font-medium text-muted-foreground"
                                    >{{ messages.locations.fields.city }}</span
                                >
                                <span>{{ location.city }}</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <span
                                    class="text-sm font-medium text-muted-foreground"
                                    >{{
                                        messages.locations.fields.province
                                    }}</span
                                >
                                <span>{{ location.province }}</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <span
                                    class="text-sm font-medium text-muted-foreground"
                                    >{{
                                        messages.locations.fields.postal_code
                                    }}</span
                                >
                                <span>{{ location.postal_code }}</span>
                            </div>
                        </div>

                        <Separator v-if="location.description" />

                        <div
                            v-if="location.description"
                            class="flex flex-col gap-1"
                        >
                            <span
                                class="text-sm font-medium text-muted-foreground"
                                >{{
                                    messages.locations.fields.description
                                }}</span
                            >
                            <p class="text-sm leading-relaxed">
                                {{ location.description }}
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <div class="flex flex-col gap-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>{{
                                messages.locations.fields.configuration ||
                                'Configuración'
                            }}</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <Globe
                                        class="h-4 w-4 text-muted-foreground"
                                    />
                                    <span class="text-sm">{{
                                        messages.locations.fields.country
                                    }}</span>
                                </div>
                                <span class="font-medium">{{
                                    location.country?.name ||
                                    location.country_id
                                }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <BadgeDollarSign
                                        class="h-4 w-4 text-muted-foreground"
                                    />
                                    <span class="text-sm">{{
                                        messages.locations.fields.currency
                                    }}</span>
                                </div>
                                <span class="font-medium uppercase">{{
                                    location.currency
                                }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <Clock
                                        class="h-4 w-4 text-muted-foreground"
                                    />
                                    <span class="text-sm">{{
                                        messages.locations.fields.time_zone
                                    }}</span>
                                </div>
                                <span class="text-xs font-medium">{{
                                    location.time_zone
                                }}</span>
                            </div>
                        </CardContent>
                    </Card>

                    <Card
                        :class="
                            location.is_active
                                ? 'border-green-200 bg-green-50'
                                : 'border-red-200 bg-red-50'
                        "
                    >
                        <CardContent class="py-4 text-center">
                            <span
                                :class="
                                    location.is_active
                                        ? 'text-green-700'
                                        : 'text-red-700'
                                "
                                class="text-sm font-bold uppercase"
                            >
                                {{
                                    location.is_active
                                        ? messages.locations.fields.active
                                        : messages.locations.fields.inactive
                                }}
                            </span>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
