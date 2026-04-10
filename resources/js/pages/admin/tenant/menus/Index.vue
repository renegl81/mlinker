<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3'
import { Inertia } from '@inertiajs/inertia'
import { ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import HeadingSmall from '@/components/HeadingSmall.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardFooter } from '@/components/ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Plus, Pencil, Trash2, ChevronLeft, ChevronRight, Eye } from 'lucide-vue-next'
import type { BreadcrumbItem } from '@/types'
import { index as locationMenusRoute, create } from '@/routes/tenant/locations/menus'
import { destroy as menuRouteDestroy, edit as menuRouteEdit, show as menuRouteShow } from '@/routes/tenant/menus'
import MenuFilters, { type MenuFilters as MenuFiltersType } from './Filters.vue'
import { Tooltip, TooltipProvider, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip'

const page = usePage()
const messages = page.props.messages as any

interface Menu {
    id: number
    name: string
    description: string | null
    is_active: boolean
}

interface PaginatedMenus {
    data: Menu[]
    prev_page_url: string | null
    next_page_url: string | null
}

interface Props {
    location: {
        id: number
    }
    menus: PaginatedMenus
    filters?: MenuFiltersType
}

const props = defineProps<Props>()

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Menús',
        href: locationMenusRoute(props.location.id).url,
    },
]

const filters = ref<MenuFiltersType>(props.filters || {})

function applyFilters(appliedFilters: MenuFiltersType) {
    const cleanFilters = Object.fromEntries(
        Object.entries(appliedFilters).filter(([, value]) => value && value.length > 0)
    )

    Inertia.get(locationMenusRoute(props.location.id).url, cleanFilters, {
        preserveState: true,
        replace: true
    })
}

function go(url: string) {
    Inertia.visit(url)
}

function goToShow(id: number) {
    Inertia.visit(menuRouteShow(id).url)
}

function remove(id: number) {
    if (confirm(messages?.menus.actions.confirm_delete)) {
        Inertia.delete(menuRouteDestroy(id).url)
    }
}

/**
 * Navigate to the menu detail unless the click originated from an action
 * button/link (edit/delete/view), which have their own handlers.
 */
function handleRowClick(menuId: number, event: MouseEvent) {
    const target = event.target as HTMLElement
    if (target.closest('[data-row-action]')) return
    goToShow(menuId)
}

function clearFilters() {
    filters.value = {}
    Inertia.get(locationMenusRoute(props.location.id).url, {}, {
        preserveState: true,
        replace: true
    })
}
</script>

<template>
    <Head title="Menús" />
    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <HeadingSmall
                    :title="messages.menus.plural"
                    :description="messages.menus.caption"
                />
                <Button as-child>
                    <Link :href="create(props.location.id).url">
                        <Plus class="mr-2 h-4 w-4" />
                        {{ messages?.menus.actions.create }}
                    </Link>
                </Button>
            </div>

            <MenuFilters
                v-model="filters"
                @apply="applyFilters"
                @clear="clearFilters"
            />

            <Card>
                <CardContent class="pt-6">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{{ messages.menus.fields.name }}</TableHead>
                                <TableHead>{{ messages.menus.fields.description }}</TableHead>
                                <TableHead>{{ messages.menus.fields.status }}</TableHead>
                                <TableHead class="text-right">{{ messages.actions.label }}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="menu in menus.data"
                                :key="menu.id"
                                class="cursor-pointer transition-colors hover:bg-muted/50"
                                @click="handleRowClick(menu.id, $event)"
                            >
                                <TableCell class="font-medium">{{ menu.name }}</TableCell>
                                <TableCell>{{ menu.description || '-' }}</TableCell>
                                <TableCell>
                                    <span
                                        :class="menu.is_active ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400'"
                                    >
                                        {{ menu.is_active ? messages.menus.status.active : messages.menus.status.inactive }}
                                    </span>
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex justify-end gap-2" data-row-action>
                                        <TooltipProvider>
                                            <Tooltip>
                                                <TooltipTrigger as-child>
                                                    <Button variant="outline" size="sm" as-child>
                                                        <Link
                                                            :href="menuRouteShow(menu.id).url"
                                                            :aria-label="messages?.menus.actions.view || 'Ver'"
                                                        >
                                                            <Eye class="h-3 w-3" />
                                                        </Link>
                                                    </Button>
                                                </TooltipTrigger>
                                                <TooltipContent>
                                                    {{ messages?.menus.actions.view || 'Ver' }}
                                                </TooltipContent>
                                            </Tooltip>

                                            <Tooltip>
                                                <TooltipTrigger as-child>
                                                    <Button variant="outline" size="sm" as-child>
                                                        <Link
                                                            :href="menuRouteEdit(menu.id).url"
                                                            :aria-label="messages?.menus.actions.edit"
                                                        >
                                                            <Pencil class="h-3 w-3" />
                                                        </Link>
                                                    </Button>
                                                </TooltipTrigger>
                                                <TooltipContent>
                                                    {{ messages?.menus.actions.edit }}
                                                </TooltipContent>
                                            </Tooltip>

                                            <Tooltip>
                                                <TooltipTrigger as-child>
                                                    <Button
                                                        variant="destructive"
                                                        size="sm"
                                                        @click="remove(menu.id)"
                                                        :aria-label="messages?.menus.actions.delete"
                                                    >
                                                        <Trash2 class="h-3 w-3" />
                                                    </Button>
                                                </TooltipTrigger>
                                                <TooltipContent>
                                                    {{ messages?.menus.actions.delete }}
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
                        :disabled="!menus.prev_page_url"
                        @click="menus.prev_page_url && go(menus.prev_page_url)"
                    >
                        <ChevronLeft class="mr-2 h-4 w-4" />
                        {{ messages.pagination.previous }}
                    </Button>
                    <Button
                        variant="outline"
                        :disabled="!menus.next_page_url"
                        @click="menus.next_page_url && go(menus.next_page_url)"
                    >
                        {{ messages.pagination.next }}
                        <ChevronRight class="ml-2 h-4 w-4" />
                    </Button>
                </CardFooter>
            </Card>
        </div>
    </AppLayout>
</template>
