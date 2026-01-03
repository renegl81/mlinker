<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Inertia } from '@inertiajs/inertia';
import { computed, ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    Plus,
    Pencil,
    Trash2,
    ChevronLeft,
    ChevronRight,
} from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';
import {
    index as locationsRoute,
    create,
    destroy as locationRouteDestroy, edit,
} from '@/routes/tenant/locations';
import UserFilters, {
    type UserFilters as UserFiltersType,
} from './Filters.vue';
import {
    Tooltip,
    TooltipProvider,
    TooltipContent,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import type { Location } from '@/types';
const page = usePage();
const messages = computed(() => page.props.messages as any);
interface PaginatedUsers {
    data: Location[];
    prev_page_url: string | null;
    next_page_url: string | null;
}

interface Props {
    locations: PaginatedUsers;
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
            ([_, value]) => value && value.length > 0,
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
    if (confirm(messages?.locations.actions.confirm_delete)) {
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
                        {{ messages?.locations.actions.create }}
                    </Link>
                </Button>
            </div>

            <UserFilters
                v-model="filters"
                @apply="applyFilters"
                @clear="clearFilters"
            />

            <Card>
                <CardContent class="pt-6">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{{
                                    messages.locations.fields.name
                                }}</TableHead>
                                <TableHead>{{
                                    messages.locations.fields.email
                                }}</TableHead>
                                <TableHead class="text-right">{{
                                    messages.actions.label
                                }}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="u in locations.data" :key="u.id">
                                <TableCell class="font-medium">{{
                                    u.name
                                }}</TableCell>
                                <TableCell class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <TooltipProvider>
                                            <Tooltip>
                                                <TooltipTrigger as-child>
                                                    <Button
                                                        variant="outline"
                                                        size="sm"
                                                        as-child
                                                    >
                                                        <Link
                                                            :href="edit(u.id)"
                                                            :aria-label="
                                                                messages
                                                                    ?.locations
                                                                    .actions
                                                                    .edit
                                                            "
                                                        >
                                                            <Pencil
                                                                class="h-3 w-3"
                                                            />
                                                        </Link>
                                                    </Button>
                                                </TooltipTrigger>
                                                <TooltipContent>
                                                    {{
                                                        page.props.messages
                                                            ?.locations.actions.edit
                                                    }}
                                                </TooltipContent>
                                            </Tooltip>

                                            <Tooltip>
                                                <TooltipTrigger as-child>
                                                    <Button
                                                        variant="destructive"
                                                        size="sm"
                                                        @click="remove(u.id)"
                                                        :aria-label="
                                                            page.props.messages
                                                                ?.locations.actions
                                                                .delete
                                                        "
                                                    >
                                                        <Trash2
                                                            class="h-3 w-3"
                                                        />
                                                    </Button>
                                                </TooltipTrigger>
                                                <TooltipContent>
                                                    {{
                                                        page.props.messages
                                                            ?.locations.actions
                                                            .delete
                                                    }}
                                                </TooltipContent>
                                            </Tooltip>
                                        </TooltipProvider>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
                <CardFooter class="flex justify-between">
                    <Button
                        variant="outline"
                        :disabled="!locations.prev_page_url"
                        @click="locations.prev_page_url && go(locations.prev_page_url)"
                    >
                        <ChevronLeft class="mr-2 h-4 w-4" />
                        {{ messages.pagination.previous }}
                    </Button>
                    <Button
                        variant="outline"
                        :disabled="!locations.next_page_url"
                        @click="locations.next_page_url && go(locations.next_page_url)"
                    >
                        {{ messages.pagination.next }}
                        <ChevronRight class="ml-2 h-4 w-4" />
                    </Button>
                </CardFooter>
            </Card>
        </div>
    </AppLayout>
</template>
