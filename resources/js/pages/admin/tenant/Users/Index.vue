<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3'
import { Inertia } from '@inertiajs/inertia'
import { ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import HeadingSmall from '@/components/HeadingSmall.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardFooter } from '@/components/ui/card'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Plus, Pencil, Trash2, ChevronLeft, ChevronRight } from 'lucide-vue-next'
import type { BreadcrumbItem } from '@/types'
import { index as usersRoute, create, destroy as userRouteDestroy } from '@/routes/tenant/users'
import UserFilters, { type UserFilters as UserFiltersType } from './Filters.vue'
import { Tooltip, TooltipProvider, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';

const page = usePage()
interface User {
    id: number
    name: string
    email: string
}

interface PaginatedUsers {
    data: User[]
    prev_page_url: string | null
    next_page_url: string | null
}

interface Props {
    users: PaginatedUsers
    filters?: UserFiltersType
}

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Usuarios',
        href: usersRoute().url,
    },
]

const props = defineProps<Props>()

const filters = ref<UserFiltersType>(props.filters || {})

function applyFilters(appliedFilters: UserFiltersType) {
    const cleanFilters = Object.fromEntries(
        Object.entries(appliedFilters).filter(([_, value]) => value && value.length > 0)
    )

    Inertia.get(usersRoute().url, cleanFilters, {
        preserveState: true,
        replace: true
    })
}

function go(url: string) {
    Inertia.visit(url)
}

function remove(id: number) {
    if (confirm(page.props.messages?.users.actions.confirm_delete)) {
        Inertia.delete(userRouteDestroy(id).url)
    }
}

function clearFilters() {
    filters.value = {}
    Inertia.get(usersRoute().url, {}, {
        preserveState: true,
        replace: true
    })
}
</script>

<template>
    <Head title="Usuarios" />
    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <HeadingSmall
                    :title="page.props.messages.users.plural"
                    :description="page.props.messages.users.caption"
                />
                <Button as-child>
                    <Link :href="create()">
                        <Plus class="mr-2 h-4 w-4" />
                        {{ page.props.messages?.users.actions.create }}
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
                                <TableHead>{{ page.props.messages.users.fields.name }}</TableHead>
                                <TableHead>{{ page.props.messages.users.fields.email }}</TableHead>
                                <TableHead class="text-right">{{ page.props.messages.actions.label }}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="u in users.data" :key="u.id">
                                <TableCell class="font-medium">{{ u.name }}</TableCell>
                                <TableCell>{{ u.email }}</TableCell>
                                <TableCell class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <TooltipProvider>
                                            <Tooltip>
                                                <TooltipTrigger as-child>
                                                    <Button variant="outline" size="sm" as-child>
                                                        <Link
                                                            :href="`/users/${u.id}/edit`"
                                                            :aria-label="page.props.messages?.users.actions.edit"
                                                        >
                                                            <Pencil class="h-3 w-3" />
                                                        </Link>
                                                    </Button>
                                                </TooltipTrigger>
                                                <TooltipContent>
                                                    {{ page.props.messages?.users.actions.edit }}
                                                </TooltipContent>
                                            </Tooltip>

                                            <Tooltip>
                                                <TooltipTrigger as-child>
                                                    <Button
                                                        variant="destructive"
                                                        size="sm"
                                                        @click="remove(u.id)"
                                                        :aria-label="page.props.messages?.users.actions.delete"
                                                    >
                                                        <Trash2 class="h-3 w-3" />
                                                    </Button>
                                                </TooltipTrigger>
                                                <TooltipContent>
                                                    {{ page.props.messages?.users.actions.delete }}
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
                        :disabled="!users.prev_page_url"
                        @click="users.prev_page_url && go(users.prev_page_url)"
                    >
                        <ChevronLeft class="mr-2 h-4 w-4" />
                        {{ page.props.messages.pagination.previous }}
                    </Button>
                    <Button
                        variant="outline"
                        :disabled="!users.next_page_url"
                        @click="users.next_page_url && go(users.next_page_url)"
                    >
                        {{ page.props.messages.pagination.next }}
                        <ChevronRight class="ml-2 h-4 w-4" />
                    </Button>
                </CardFooter>
            </Card>
        </div>
    </AppLayout>
</template>
