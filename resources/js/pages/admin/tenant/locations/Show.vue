<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import MenuSection from './components/MenuSection.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    destroy as locationRouteDestroy,
    edit as locationRouteEdit,
    index as locationsRoute,
} from '@/routes/tenant/locations';
import type { BreadcrumbItem, Location } from '@/types';
import { Inertia } from '@inertiajs/inertia';
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    ArrowLeft,
    BadgeDollarSign,
    Clock,
    Globe,
    MapPin,
    Pencil,
    Phone,
    Trash2,
} from 'lucide-vue-next';
import { computed } from 'vue';


interface Props {
    location: Location;
}

const props = defineProps<Props>();
const page = usePage();
const messages = computed(() => page.props.messages as any);

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
            <div
                class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="flex items-center gap-4">
                    <Button variant="outline" size="icon" as-child>
                        <Link :href="locationsRoute().url">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                    </Button>
                    <HeadingSmall
                        :title="location.name"
                        :description="messages.locations.caption"
                    />
                </div>
                <div class="flex items-center gap-2">
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
                <div class="space-y-6 lg:col-span-2">
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg font-semibold">
                                {{
                                    messages.locations.form.title_show ||
                                    'Información General'
                                }}
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div class="flex flex-col gap-1.5">
                                    <span
                                        class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                                    >
                                        {{ messages.locations.fields.address }}
                                    </span>
                                    <div class="flex items-start gap-2 text-sm">
                                        <MapPin
                                            class="mt-0.5 h-4 w-4 shrink-0 text-primary"
                                        />
                                        <span>{{ location.address }}</span>
                                    </div>
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <span
                                        class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                                    >
                                        {{ messages.locations.fields.phone }}
                                    </span>
                                    <div
                                        class="flex items-center gap-2 text-sm"
                                    >
                                        <Phone
                                            class="h-4 w-4 shrink-0 text-primary"
                                        />
                                        <span>{{
                                            location.phone || '---'
                                        }}</span>
                                    </div>
                                </div>
                            </div>

                            <Separator />

                            <div
                                class="grid grid-cols-1 gap-4 text-sm md:grid-cols-3"
                            >
                                <div class="space-y-1">
                                    <span
                                        class="block font-medium text-muted-foreground"
                                        >{{
                                            messages.locations.fields.city
                                        }}</span
                                    >
                                    <p>{{ location.city }}</p>
                                </div>
                                <div class="space-y-1">
                                    <span
                                        class="block font-medium text-muted-foreground"
                                        >{{
                                            messages.locations.fields.province
                                        }}</span
                                    >
                                    <p>{{ location.province }}</p>
                                </div>
                                <div class="space-y-1">
                                    <span
                                        class="block font-medium text-muted-foreground"
                                        >{{
                                            messages.locations.fields
                                                .postal_code
                                        }}</span
                                    >
                                    <p>{{ location.postal_code }}</p>
                                </div>
                            </div>

                            <div
                                v-if="location.description"
                                class="space-y-2 pt-2"
                            >
                                <span
                                    class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                                >
                                    {{ messages.locations.fields.description }}
                                </span>
                                <p
                                    class="text-sm leading-relaxed text-pretty text-muted-foreground"
                                >
                                    {{ location.description }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <MenuSection
                        :location-id="location.id"
                        :menus="location.menus"
                    />
                </div>

                <div class="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-md font-semibold">{{
                                messages.locations.fields.configuration ||
                                'Configuración'
                            }}</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4 text-sm">
                            <div
                                class="flex items-center justify-between border-b border-muted py-1"
                            >
                                <div
                                    class="flex items-center gap-2 text-muted-foreground"
                                >
                                    <Globe class="h-4 w-4" />
                                    <span>{{
                                        messages.locations.fields.country
                                    }}</span>
                                </div>
                                <span class="text-right font-medium">{{
                                    location.country?.name
                                }}</span>
                            </div>
                            <div
                                class="flex items-center justify-between border-b border-muted py-1"
                            >
                                <div
                                    class="flex items-center gap-2 text-muted-foreground"
                                >
                                    <BadgeDollarSign class="h-4 w-4" />
                                    <span>{{
                                        messages.locations.fields.currency
                                    }}</span>
                                </div>
                                <span class="font-medium uppercase">{{
                                    location.currency
                                }}</span>
                            </div>
                            <div
                                class="flex items-center justify-between border-b border-muted py-1"
                            >
                                <div
                                    class="flex items-center gap-2 text-muted-foreground"
                                >
                                    <Clock class="h-4 w-4" />
                                    <span>{{
                                        messages.locations.fields.time_zone
                                    }}</span>
                                </div>
                                <span
                                    class="max-w-[120px] truncate font-medium"
                                    :title="location.time_zone"
                                >
                                    {{ location.time_zone }}
                                </span>
                            </div>
                        </CardContent>
                    </Card>

                    <Card
                        :class="
                            location.is_active
                                ? 'border-green-200 bg-green-50/50'
                                : 'border-red-200 bg-red-50/50'
                        "
                    >
                        <CardContent
                            class="flex items-center justify-center gap-2 py-4"
                        >
                            <div
                                :class="
                                    location.is_active
                                        ? 'bg-green-500'
                                        : 'bg-red-500'
                                "
                                class="h-2 w-2 animate-pulse rounded-full"
                            />
                            <span
                                :class="
                                    location.is_active
                                        ? 'text-green-700'
                                        : 'text-red-700'
                                "
                                class="text-xs font-bold tracking-widest uppercase"
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
