<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    CopyPlus,
    Filter,
    Loader2,
    MoreVertical,
    Pencil,
    Plus,
    Search,
    Trash2,
    Utensils,
} from 'lucide-vue-next';
import { computed, reactive, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

interface Product {
    id: number;
    name: string;
    price: string | number | null;
    image_path?: string | null;
    image_url?: string | null;
    tags: string[] | null;
    menus?: Array<{ id: number; name: string }>;
    sections?: Array<{ id: number; name: string; menu_id: number }>;
    allergens?: Array<{ id: number; name: string; code: string | null }>;
}

interface PaginatedProducts {
    data: Product[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
}

interface Menu {
    id: number;
    name: string;
}

interface Section {
    id: number;
    name: string;
    menu_id: number;
}

interface Allergen {
    id: number;
    name: string;
    code: string | null;
}

interface Filters {
    q?: string;
    menu_id?: string;
    section_id?: string;
    tag?: string;
    allergen_id?: string;
}

interface Props {
    products: PaginatedProducts;
    menus: Menu[];
    sections: Section[];
    allergens: Allergen[];
    filters: Filters;
}

const props = defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    { title: t('catalog.title'), href: '#' },
    { title: t('catalog.products.title'), href: '/panel/catalog/products' },
];

// --- Filters state ---
const localFilters = reactive({
    q: props.filters.q ?? '',
    menu_id: props.filters.menu_id ?? '',
    section_id: props.filters.section_id ?? '',
    tag: props.filters.tag ?? '',
    allergen_id: props.filters.allergen_id ?? '',
});

const sectionsForSelectedMenu = computed(() => {
    if (!localFilters.menu_id) return props.sections;
    return props.sections.filter((s) => s.menu_id === Number(localFilters.menu_id));
});

let filterTimer: ReturnType<typeof setTimeout> | null = null;
function applyFilters() {
    if (filterTimer) clearTimeout(filterTimer);
    filterTimer = setTimeout(() => {
        const query: Record<string, string> = {};
        for (const [k, v] of Object.entries(localFilters)) {
            if (v) query[k] = String(v);
        }
        router.get('/panel/catalog/products', query, { preserveState: true, preserveScroll: true, replace: true });
    }, 350);
}

watch(localFilters, applyFilters);

// --- Selection ---
const selectedIds = ref<Set<number>>(new Set());

function toggleSelect(id: number) {
    const next = new Set(selectedIds.value);
    if (next.has(id)) next.delete(id);
    else next.add(id);
    selectedIds.value = next;
}

function toggleSelectAll() {
    if (selectedIds.value.size === props.products.data.length) {
        selectedIds.value = new Set();
    } else {
        selectedIds.value = new Set(props.products.data.map((p) => p.id));
    }
}

const allSelected = computed(() =>
    props.products.data.length > 0 && selectedIds.value.size === props.products.data.length,
);

// --- Bulk actions ---
const processing = ref(false);

function bulkDelete() {
    if (!confirm(t('catalog.products.confirm_delete'))) return;
    processing.value = true;
    router.post(
        '/panel/catalog/products/bulk-delete',
        { product_ids: Array.from(selectedIds.value) } as never,
        {
            preserveScroll: true,
            onFinish: () => {
                processing.value = false;
                selectedIds.value = new Set();
            },
        },
    );
}

function bulkDuplicate() {
    processing.value = true;
    router.post(
        '/panel/catalog/products/bulk-duplicate',
        { product_ids: Array.from(selectedIds.value) } as never,
        {
            preserveScroll: true,
            onFinish: () => {
                processing.value = false;
                selectedIds.value = new Set();
            },
        },
    );
}

// --- Attach-to-menu modal ---
const attachOpen = ref(false);
const attachForm = reactive({
    menu_id: '' as string | number,
    section_id: '' as string | number,
});

const attachSections = computed(() => {
    if (!attachForm.menu_id) return [];
    return props.sections.filter((s) => s.menu_id === Number(attachForm.menu_id));
});

watch(() => attachForm.menu_id, () => {
    attachForm.section_id = '';
});

function submitAttach() {
    if (!attachForm.menu_id || !attachForm.section_id) return;
    processing.value = true;
    router.post(
        '/panel/catalog/products/bulk-attach-menu',
        {
            product_ids: Array.from(selectedIds.value),
            menu_id: attachForm.menu_id,
            section_id: attachForm.section_id,
        } as never,
        {
            preserveScroll: true,
            onFinish: () => {
                processing.value = false;
                attachOpen.value = false;
                selectedIds.value = new Set();
                attachForm.menu_id = '';
                attachForm.section_id = '';
            },
        },
    );
}

function productImage(p: Product): string | null {
    return p.image_path ?? p.image_url ?? null;
}

function formatPrice(price: string | number | null): string {
    if (price === null || price === undefined) return '';
    const n = typeof price === 'number' ? price : Number(price);
    if (Number.isNaN(n)) return '';
    return n.toFixed(2);
}
</script>

<template>
    <Head :title="t('catalog.products.title')" />

    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="flex h-full flex-1 flex-col gap-5 rounded-xl p-4 pb-24">
            <!-- Header -->
            <div class="flex items-start justify-between gap-4">
                <HeadingSmall
                    :title="t('catalog.products.title')"
                    :description="t('catalog.products.subtitle')"
                />
            </div>

            <!-- Filters -->
            <div class="rounded-xl border bg-card text-card-foreground p-4">
                <div class="grid grid-cols-1 gap-3 md:grid-cols-5">
                    <div class="md:col-span-2 relative">
                        <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            v-model="localFilters.q"
                            :placeholder="t('catalog.products.search_placeholder')"
                            class="panel-input pl-9"
                        />
                    </div>
                    <select v-model="localFilters.menu_id" class="panel-input h-9 rounded-md border px-3 text-sm">
                        <option value="">{{ t('catalog.products.filter_menu') }}</option>
                        <option v-for="m in menus" :key="m.id" :value="m.id">{{ m.name }}</option>
                    </select>
                    <select v-model="localFilters.section_id" class="panel-input h-9 rounded-md border px-3 text-sm">
                        <option value="">{{ t('catalog.products.filter_section') }}</option>
                        <option v-for="s in sectionsForSelectedMenu" :key="s.id" :value="s.id">{{ s.name }}</option>
                    </select>
                    <select v-model="localFilters.allergen_id" class="panel-input h-9 rounded-md border px-3 text-sm">
                        <option value="">{{ t('catalog.products.filter_allergen') }}</option>
                        <option v-for="a in allergens" :key="a.id" :value="a.id">{{ a.name }}</option>
                    </select>
                </div>
            </div>

            <!-- Table -->
            <div class="rounded-xl border bg-card text-card-foreground overflow-hidden">
                <div v-if="products.data.length === 0" class="py-16 text-center">
                    <Utensils class="mx-auto mb-3 h-10 w-10 text-muted-foreground/30" />
                    <p class="text-sm text-muted-foreground">{{ t('catalog.products.empty') }}</p>
                </div>

                <table v-else class="w-full text-sm">
                    <thead class="border-b bg-muted/30">
                        <tr>
                            <th class="w-10 px-4 py-3">
                                <input
                                    type="checkbox"
                                    :checked="allSelected"
                                    class="h-4 w-4 cursor-pointer"
                                    @change="toggleSelectAll"
                                />
                            </th>
                            <th class="w-14 px-2 py-3"></th>
                            <th class="px-4 py-3 text-left panel-label">{{ t('catalog.products.column_name') }}</th>
                            <th class="px-4 py-3 text-right panel-label">{{ t('catalog.products.column_price') }}</th>
                            <th class="px-4 py-3 text-left panel-label hidden md:table-cell">{{ t('catalog.products.column_menus') }}</th>
                            <th class="px-4 py-3 text-right panel-label w-12"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="product in products.data"
                            :key="product.id"
                            class="border-b last:border-b-0 transition-colors hover:bg-muted/20"
                            :class="{ 'bg-primary/5': selectedIds.has(product.id) }"
                        >
                            <td class="px-4 py-3">
                                <input
                                    type="checkbox"
                                    :checked="selectedIds.has(product.id)"
                                    class="h-4 w-4 cursor-pointer"
                                    @change="toggleSelect(product.id)"
                                />
                            </td>
                            <td class="px-2 py-3">
                                <div
                                    v-if="!productImage(product)"
                                    class="flex h-10 w-10 items-center justify-center rounded-md bg-gradient-to-br from-purple-500 to-pink-500 text-sm font-bold text-white"
                                >
                                    {{ product.name.charAt(0).toUpperCase() }}
                                </div>
                                <img
                                    v-else
                                    :src="productImage(product)!"
                                    :alt="product.name"
                                    class="h-10 w-10 rounded-md object-cover"
                                />
                            </td>
                            <td class="px-4 py-3">
                                <Link
                                    :href="`/panel/products/${product.id}/edit`"
                                    class="font-medium text-foreground hover:text-primary"
                                >
                                    {{ product.name }}
                                </Link>
                            </td>
                            <td class="px-4 py-3 text-right tabular-nums text-foreground">
                                {{ formatPrice(product.price) }}
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell">
                                <div v-if="product.menus && product.menus.length > 0" class="flex flex-wrap gap-1">
                                    <span
                                        v-for="m in product.menus.slice(0, 2)"
                                        :key="m.id"
                                        class="rounded bg-muted px-1.5 py-0.5 text-[10px] font-medium text-foreground/80"
                                    >
                                        {{ m.name }}
                                    </span>
                                    <span
                                        v-if="product.menus.length > 2"
                                        class="rounded bg-muted px-1.5 py-0.5 text-[10px] font-medium text-muted-foreground"
                                    >+{{ product.menus.length - 2 }}</span>
                                </div>
                                <span v-else class="text-xs text-muted-foreground">—</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <button class="rounded p-1 text-muted-foreground hover:bg-muted">
                                            <MoreVertical class="h-4 w-4" />
                                        </button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end" class="min-w-[140px]">
                                        <DropdownMenuItem as-child>
                                            <Link :href="`/panel/products/${product.id}/edit`" class="flex items-center gap-2">
                                                <Pencil class="h-3.5 w-3.5" />
                                                {{ t('common.edit') }}
                                            </Link>
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div v-if="products.last_page > 1" class="flex items-center justify-between border-t px-4 py-3 text-xs text-muted-foreground">
                    <span>{{ t('panel.users.total', { count: products.total }) }}</span>
                    <div class="flex gap-1">
                        <Link
                            v-for="link in products.links"
                            :key="link.label"
                            :href="link.url ?? '#'"
                            class="rounded px-2 py-1 transition-colors"
                            :class="[
                                link.active ? 'bg-primary text-primary-foreground' : 'hover:bg-muted',
                                !link.url && 'opacity-40 pointer-events-none',
                            ]"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Sticky bulk action bar -->
        <div
            v-if="selectedIds.size > 0"
            class="pointer-events-none fixed inset-x-0 bottom-0 z-30 flex justify-center p-4"
        >
            <div class="pointer-events-auto flex items-center gap-3 rounded-full border bg-card/95 text-card-foreground px-4 py-2 shadow-lg backdrop-blur border-primary/40">
                <span class="text-xs font-medium text-foreground">
                    {{ t('catalog.products.bulk_selected', { count: selectedIds.size }, selectedIds.size) }}
                </span>
                <Button size="sm" variant="outline" :disabled="processing" @click="bulkDuplicate">
                    <CopyPlus class="mr-1.5 h-3.5 w-3.5" />
                    {{ t('catalog.products.bulk_duplicate') }}
                </Button>
                <Button size="sm" variant="outline" :disabled="processing" @click="attachOpen = true">
                    <Plus class="mr-1.5 h-3.5 w-3.5" />
                    {{ t('catalog.products.bulk_attach') }}
                </Button>
                <Button size="sm" variant="destructive" :disabled="processing" @click="bulkDelete">
                    <Loader2 v-if="processing" class="mr-1.5 h-3.5 w-3.5 animate-spin" />
                    <Trash2 v-else class="mr-1.5 h-3.5 w-3.5" />
                    {{ t('catalog.products.bulk_delete') }}
                </Button>
            </div>
        </div>

        <!-- Attach-to-menu dialog -->
        <Dialog v-model:open="attachOpen">
            <DialogContent class="sm:max-w-[480px]">
                <DialogHeader>
                    <DialogTitle>{{ t('catalog.products.bulk_attach') }}</DialogTitle>
                    <DialogDescription>
                        {{ t('catalog.products.bulk_selected', { count: selectedIds.size }, selectedIds.size) }}
                    </DialogDescription>
                </DialogHeader>
                <div class="space-y-4 py-2">
                    <div class="space-y-1.5">
                        <Label class="panel-label">{{ t('catalog.products.filter_menu') }}</Label>
                        <select v-model="attachForm.menu_id" class="panel-input h-9 w-full rounded-md border px-3 text-sm">
                            <option value="">—</option>
                            <option v-for="m in menus" :key="m.id" :value="m.id">{{ m.name }}</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <Label class="panel-label">{{ t('catalog.products.filter_section') }}</Label>
                        <select v-model="attachForm.section_id" class="panel-input h-9 w-full rounded-md border px-3 text-sm" :disabled="!attachForm.menu_id">
                            <option value="">—</option>
                            <option v-for="s in attachSections" :key="s.id" :value="s.id">{{ s.name }}</option>
                        </select>
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="attachOpen = false">{{ t('common.cancel') }}</Button>
                    <Button :disabled="!attachForm.menu_id || !attachForm.section_id || processing" @click="submitAttach">
                        <Loader2 v-if="processing" class="mr-1.5 h-3.5 w-3.5 animate-spin" />
                        {{ t('common.add') }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
