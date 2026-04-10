<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import {
    ChevronLeft,
    ChevronRight,
    Crown,
    Lock,
    Pencil,
    Plus,
    Search,
    Trash2,
    UserCog,
} from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';

const page = usePage();
const messages = page.props.messages as any;

interface TenantUser {
    id: number;
    name: string;
    last_name?: string | null;
    email: string;
    is_active: boolean;
    tenant_role: 'owner' | 'editor' | null;
    tenant_permissions: { scope?: string; location_ids?: number[] };
    joined_at: string | null;
}

interface PaginatedUsers {
    data: TenantUser[];
    prev_page_url: string | null;
    next_page_url: string | null;
    current_page: number;
    last_page: number;
    total: number;
}

interface Props {
    users: PaginatedUsers;
    filters: { search?: string; role?: string };
    hasTeam: boolean;
    currentUserRole: 'owner' | 'editor' | null;
}

const props = defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    { title: messages.users.plural, href: '/panel/users' },
];

const searchQuery = ref(props.filters.search ?? '');
const roleFilter = ref(props.filters.role ?? '');

let debounce: ReturnType<typeof setTimeout> | null = null;
function applyFilters() {
    if (debounce) clearTimeout(debounce);
    debounce = setTimeout(() => {
        const query: Record<string, string> = {};
        if (searchQuery.value) query.search = searchQuery.value;
        if (roleFilter.value) query.role = roleFilter.value;
        router.get('/panel/users', query, { preserveState: true, replace: true });
    }, 300);
}

function clearFilters() {
    searchQuery.value = '';
    roleFilter.value = '';
    router.get('/panel/users', {}, { preserveState: true, replace: true });
}

function remove(id: number) {
    if (!confirm(messages.users.actions.confirm_delete)) return;
    router.delete(`/panel/users/${id}`);
}

function go(url: string | null) {
    if (url) router.visit(url);
}

const canCreate = props.hasTeam && props.currentUserRole === 'owner';
</script>

<template>
    <Head :title="messages.users.plural" />
    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="flex h-full flex-1 flex-col gap-5 rounded-xl p-4">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <HeadingSmall
                    :title="messages.users.plural"
                    :description="messages.users.caption"
                />
                <Button v-if="canCreate" as-child>
                    <Link href="/panel/users/create">
                        <Plus class="mr-2 h-4 w-4" />
                        {{ messages.users.actions.create }}
                    </Link>
                </Button>
            </div>

            <!-- Plan upgrade banner (Free plan) -->
            <Alert
                v-if="!hasTeam"
                class="border-amber-500 bg-amber-50 text-amber-900 dark:bg-amber-950 dark:text-amber-200"
            >
                <Lock class="size-4 text-amber-600" />
                <AlertTitle>Gestión de equipo no disponible</AlertTitle>
                <AlertDescription>
                    Invita a colaboradores a tu tenant actualizando a un plan Pro o superior.
                    <Link href="/panel/billing/plans" class="ml-1 font-semibold underline underline-offset-2">
                        Ver planes
                    </Link>
                </AlertDescription>
            </Alert>

            <!-- Non-owner banner -->
            <Alert
                v-else-if="currentUserRole !== 'owner'"
                class="border-sky-500 bg-sky-50 text-sky-900 dark:bg-sky-950 dark:text-sky-200"
            >
                <Lock class="size-4 text-sky-600" />
                <AlertTitle>Solo visualización</AlertTitle>
                <AlertDescription>
                    Solo los owners del tenant pueden crear, editar o eliminar usuarios.
                </AlertDescription>
            </Alert>

            <!-- Filters -->
            <div class="rounded-xl border bg-card text-card-foreground p-4">
                <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
                    <div class="md:col-span-2 relative">
                        <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            v-model="searchQuery"
                            placeholder="Buscar por nombre o email..."
                            class="panel-input pl-9"
                            @update:model-value="applyFilters"
                        />
                    </div>
                    <select
                        v-model="roleFilter"
                        class="panel-input h-9 rounded-md border px-3 text-sm"
                        @change="applyFilters"
                    >
                        <option value="">Todos los roles</option>
                        <option value="owner">Owner</option>
                        <option value="editor">Editor</option>
                    </select>
                    <Button variant="outline" @click="clearFilters">Limpiar</Button>
                </div>
            </div>

            <!-- Table -->
            <div class="rounded-xl border bg-card text-card-foreground overflow-hidden">
                <div v-if="users.data.length === 0" class="py-12 text-center">
                    <UserCog class="mx-auto mb-3 h-10 w-10 text-muted-foreground/30" />
                    <p class="text-sm text-muted-foreground">No hay usuarios que coincidan con los filtros.</p>
                </div>
                <Table v-else>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="panel-label">{{ messages.users.fields.name }}</TableHead>
                            <TableHead class="panel-label">{{ messages.users.fields.email }}</TableHead>
                            <TableHead class="panel-label">Rol</TableHead>
                            <TableHead class="panel-label">Scope</TableHead>
                            <TableHead class="text-right panel-label">{{ messages.actions.label }}</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="u in users.data" :key="u.id">
                            <TableCell class="font-medium text-foreground">
                                {{ u.name }} {{ u.last_name }}
                            </TableCell>
                            <TableCell class="text-foreground">{{ u.email }}</TableCell>
                            <TableCell>
                                <span
                                    v-if="u.tenant_role === 'owner'"
                                    class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-semibold text-amber-700 dark:bg-amber-950 dark:text-amber-300"
                                >
                                    <Crown class="h-3 w-3" />
                                    Owner
                                </span>
                                <span
                                    v-else-if="u.tenant_role === 'editor'"
                                    class="inline-flex items-center gap-1 rounded-full bg-sky-100 px-2 py-0.5 text-[11px] font-semibold text-sky-700 dark:bg-sky-950 dark:text-sky-300"
                                >
                                    <Pencil class="h-3 w-3" />
                                    Editor
                                </span>
                                <span v-else class="text-xs text-muted-foreground">—</span>
                            </TableCell>
                            <TableCell>
                                <span
                                    v-if="u.tenant_permissions?.scope === 'locations'"
                                    class="text-xs text-muted-foreground"
                                >
                                    {{ (u.tenant_permissions.location_ids ?? []).length }} location(es)
                                </span>
                                <span v-else class="text-xs text-muted-foreground">Todas</span>
                            </TableCell>
                            <TableCell class="text-right">
                                <div class="flex justify-end gap-1.5">
                                    <Button v-if="canCreate" variant="outline" size="sm" as-child>
                                        <Link :href="`/panel/users/${u.id}/edit`">
                                            <Pencil class="h-3 w-3" />
                                        </Link>
                                    </Button>
                                    <Button
                                        v-if="canCreate && u.tenant_role !== 'owner'"
                                        variant="destructive"
                                        size="sm"
                                        @click="remove(u.id)"
                                    >
                                        <Trash2 class="h-3 w-3" />
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
                <div v-if="users.last_page > 1" class="flex items-center justify-between border-t px-4 py-3 text-xs text-muted-foreground">
                    <span>{{ users.total }} en total</span>
                    <div class="flex gap-1">
                        <Button variant="outline" size="sm" :disabled="!users.prev_page_url" @click="go(users.prev_page_url)">
                            <ChevronLeft class="h-4 w-4" />
                        </Button>
                        <Button variant="outline" size="sm" :disabled="!users.next_page_url" @click="go(users.next_page_url)">
                            <ChevronRight class="h-4 w-4" />
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
