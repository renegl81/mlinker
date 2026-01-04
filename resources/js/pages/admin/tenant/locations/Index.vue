<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import LocationCard from './components/LocationCard.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    create,
    destroy as locationRouteDestroy,
    index as locationsRoute,
} from '@/routes/tenant/locations';
import type { BreadcrumbItem, Location } from '@/types';
import { Inertia } from '@inertiajs/inertia';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight, MapPin, Plus } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import UserFilters, {
    type UserFilters as UserFiltersType,
} from './Filters.vue';

const page = usePage();
const messages = computed(() => page.props.messages as any);

interface PaginatedLocations {
    data: Location[];
    prev_page_url: string | null;
    next_page_url: string | null;
}

interface Props {
    locations: PaginatedLocations;
    filters?: UserFiltersType;
}

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: messages.value.locations.plural,
        href: locationsRoute().url,
    },
];

const props = defineProps<Props>();

const filters = ref<UserFiltersType>(props.filters || {});

function applyFilters(appliedFilters: UserFiltersType) {
    const cleanFilters = Object.fromEntries(
        Object.entries(appliedFilters).filter(
            ([, value]) => value && value.length > 0,
        ),
    );

    Inertia.get(locationsRoute().url, cleanFilters, {
        preserveState: true,
        replace: true,
    });
}

function go(url: string) {
    Inertia.visit(url);
}

function remove(id: number) {
    if (confirm(messages.value.locations.actions.confirm_delete)) {
        Inertia.delete(locationRouteDestroy(id).url);
    }
}

function clearFilters() {
    filters.value = {};
    Inertia.get(
        locationsRoute().url,
        {},
        {
            preserveState: true,
            replace: true,
        },
    );
}
</script>

<template>
    <Head :title="messages.locations.plural" />
    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <HeadingSmall
                    :title="messages.locations.plural"
                    :description="messages.locations.caption"
                />
                <Button as-child>
                    <Link :href="create()">
                        <Plus class="mr-2 h-4 w-4" />
                        {{ messages.locations.actions.create }}
                    </Link>
                </Button>
            </div>

            <UserFilters
                v-model="filters"
                @apply="applyFilters"
                @clear="clearFilters"
            />

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                <LocationCard
                    v-for="location in locations.data"
                    :key="location.id"
                    :location="location"
                    @delete="remove"
                />
            </div>

            <div
                v-if="locations.data.length === 0"
                class="flex flex-col items-center justify-center rounded-lg border-2 border-dashed py-12 text-center"
            >
                <MapPin class="mb-3 h-12 w-12 text-muted-foreground/30" />
                <p class="text-sm font-medium text-muted-foreground">
                    {{
                        messages.locations.empty ||
                        'No hay ubicaciones disponibles.'
                    }}
                </p>
            </div>

            <div
                v-if="locations.prev_page_url || locations.next_page_url"
                class="flex justify-between"
            >
                <Button
                    variant="outline"
                    :disabled="!locations.prev_page_url"
                    @click="
                        locations.prev_page_url && go(locations.prev_page_url)
                    "
                >
                    <ChevronLeft class="mr-2 h-4 w-4" />
                    {{ messages.pagination.previous }}
                </Button>
                <Button
                    variant="outline"
                    :disabled="!locations.next_page_url"
                    @click="
                        locations.next_page_url && go(locations.next_page_url)
                    "
                >
                    {{ messages.pagination.next }}
                    <ChevronRight class="ml-2 h-4 w-4" />
                </Button>
            </div>
        </div>
    </AppLayout>
</template>
