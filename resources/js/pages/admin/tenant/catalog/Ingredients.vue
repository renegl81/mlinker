<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import {
    Check,
    GitMerge,
    Languages,
    Leaf,
    Loader2,
    Pencil,
    Search,
    Trash2,
    X,
} from 'lucide-vue-next';
import { computed, reactive, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

interface LocaleMeta {
    native: string;
    flag: string;
}

interface Ingredient {
    id: number;
    name: string;
    products_count: number;
    translations: Record<string, { name?: string }> | null;
}

interface PaginatedIngredients {
    data: Ingredient[];
    current_page: number;
    last_page: number;
    total: number;
    links: Array<{ url: string | null; label: string; active: boolean }>;
}

interface Props {
    ingredients: PaginatedIngredients;
    supportedLocales: Record<string, LocaleMeta>;
    sourceLocale: string;
    filters: { q?: string };
}

const props = defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    { title: t('catalog.title'), href: '#' },
    { title: t('catalog.ingredients.title'), href: '/panel/catalog/ingredients' },
];

// --- Search ---
const searchQuery = ref(props.filters.q ?? '');
let searchTimer: ReturnType<typeof setTimeout> | null = null;
watch(searchQuery, (value) => {
    if (searchTimer) clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        router.get(
            '/panel/catalog/ingredients',
            value ? { q: value } : {},
            { preserveState: true, preserveScroll: true, replace: true },
        );
    }, 350);
});

// --- Selection ---
const selectedIds = ref<Set<number>>(new Set());

function toggleSelect(id: number) {
    const next = new Set(selectedIds.value);
    if (next.has(id)) next.delete(id);
    else next.add(id);
    selectedIds.value = next;
}

function toggleSelectAll() {
    if (selectedIds.value.size === props.ingredients.data.length) {
        selectedIds.value = new Set();
    } else {
        selectedIds.value = new Set(props.ingredients.data.map((i) => i.id));
    }
}

const allSelected = computed(() =>
    props.ingredients.data.length > 0 && selectedIds.value.size === props.ingredients.data.length,
);

// --- Inline name edit ---
const editingId = ref<number | null>(null);
const editingName = ref('');

function startEdit(ingredient: Ingredient) {
    editingId.value = ingredient.id;
    editingName.value = ingredient.name;
}

function cancelEdit() {
    editingId.value = null;
    editingName.value = '';
}

function saveEdit(id: number) {
    if (!editingName.value.trim()) {
        cancelEdit();
        return;
    }
    router.put(
        `/panel/catalog/ingredients/${id}`,
        { name: editingName.value.trim() } as never,
        {
            preserveScroll: true,
            onSuccess: () => cancelEdit(),
        },
    );
}

// --- Delete ---
function deleteIngredient(ingredient: Ingredient) {
    if (!confirm(t('catalog.ingredients.confirm_delete'))) return;
    router.delete(`/panel/catalog/ingredients/${ingredient.id}`, { preserveScroll: true });
}

// --- Translations drawer ---
const translationsOpen = ref(false);
const translationsTarget = ref<Ingredient | null>(null);
const translationsForm = reactive<Record<string, string>>({});
const savingTranslations = ref(false);

function openTranslations(ingredient: Ingredient) {
    translationsTarget.value = ingredient;
    Object.keys(translationsForm).forEach((k) => delete translationsForm[k]);
    for (const code of Object.keys(props.supportedLocales)) {
        if (code === props.sourceLocale) continue;
        translationsForm[code] = ingredient.translations?.[code]?.name ?? '';
    }
    translationsOpen.value = true;
}

function saveTranslations() {
    if (!translationsTarget.value) return;
    savingTranslations.value = true;
    const payload: Record<string, { name: string }> = {};
    for (const [code, name] of Object.entries(translationsForm)) {
        if (name.trim()) {
            payload[code] = { name: name.trim() };
        }
    }
    router.put(
        `/panel/catalog/ingredients/${translationsTarget.value.id}/translations`,
        { translations: payload } as never,
        {
            preserveScroll: true,
            onFinish: () => {
                savingTranslations.value = false;
                translationsOpen.value = false;
            },
        },
    );
}

// --- Merge dialog ---
const mergeOpen = ref(false);
const survivorId = ref<number | null>(null);
const merging = ref(false);

const selectedIngredients = computed(() =>
    props.ingredients.data.filter((i) => selectedIds.value.has(i.id)),
);

function openMerge() {
    if (selectedIds.value.size < 2) return;
    survivorId.value = selectedIngredients.value[0]?.id ?? null;
    mergeOpen.value = true;
}

function submitMerge() {
    if (!survivorId.value) return;
    merging.value = true;
    router.post(
        '/panel/catalog/ingredients/merge',
        {
            ingredient_ids: Array.from(selectedIds.value),
            survivor_id: survivorId.value,
        } as never,
        {
            preserveScroll: true,
            onFinish: () => {
                merging.value = false;
                mergeOpen.value = false;
                selectedIds.value = new Set();
                survivorId.value = null;
            },
        },
    );
}

// --- Helpers ---
function translationStatus(ingredient: Ingredient, code: string): 'done' | 'missing' {
    const value = ingredient.translations?.[code]?.name;
    return value && value.trim() !== '' ? 'done' : 'missing';
}

const targetLocales = computed(() =>
    Object.keys(props.supportedLocales).filter((c) => c !== props.sourceLocale),
);
</script>

<template>
    <Head :title="t('catalog.ingredients.title')" />

    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="flex h-full flex-1 flex-col gap-5 rounded-xl p-4 pb-24">
            <div class="flex items-start justify-between gap-4">
                <HeadingSmall
                    :title="t('catalog.ingredients.title')"
                    :description="t('catalog.ingredients.subtitle')"
                />
            </div>

            <!-- Search -->
            <div class="rounded-xl border bg-card text-card-foreground p-4">
                <div class="relative max-w-sm">
                    <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        v-model="searchQuery"
                        :placeholder="t('catalog.ingredients.search_placeholder')"
                        class="panel-input pl-9"
                    />
                </div>
            </div>

            <!-- Table -->
            <div class="rounded-xl border bg-card text-card-foreground overflow-hidden">
                <div v-if="ingredients.data.length === 0" class="py-16 text-center">
                    <Leaf class="mx-auto mb-3 h-10 w-10 text-muted-foreground/30" />
                    <p class="text-sm text-muted-foreground">{{ t('catalog.ingredients.empty') }}</p>
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
                            <th class="px-4 py-3 text-left panel-label">{{ t('catalog.ingredients.column_name') }}</th>
                            <th class="px-4 py-3 text-left panel-label hidden md:table-cell">{{ t('catalog.ingredients.column_usage') }}</th>
                            <th class="px-4 py-3 text-left panel-label hidden md:table-cell">{{ t('catalog.ingredients.column_translations') }}</th>
                            <th class="px-4 py-3 text-right panel-label w-24"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="ingredient in ingredients.data"
                            :key="ingredient.id"
                            class="border-b last:border-b-0 transition-colors hover:bg-muted/20"
                            :class="{ 'bg-primary/5': selectedIds.has(ingredient.id) }"
                        >
                            <td class="px-4 py-3">
                                <input
                                    type="checkbox"
                                    :checked="selectedIds.has(ingredient.id)"
                                    class="h-4 w-4 cursor-pointer"
                                    @change="toggleSelect(ingredient.id)"
                                />
                            </td>
                            <td class="px-4 py-3">
                                <div v-if="editingId === ingredient.id" class="flex items-center gap-2">
                                    <Input
                                        v-model="editingName"
                                        class="panel-input h-8"
                                        @keydown.enter="saveEdit(ingredient.id)"
                                        @keydown.escape="cancelEdit"
                                    />
                                    <button
                                        type="button"
                                        class="rounded p-1 text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950"
                                        @click="saveEdit(ingredient.id)"
                                    >
                                        <Check class="h-4 w-4" />
                                    </button>
                                    <button
                                        type="button"
                                        class="rounded p-1 text-muted-foreground hover:bg-muted"
                                        @click="cancelEdit"
                                    >
                                        <X class="h-4 w-4" />
                                    </button>
                                </div>
                                <button
                                    v-else
                                    type="button"
                                    class="font-medium text-foreground hover:text-primary text-left"
                                    @click="startEdit(ingredient)"
                                >
                                    {{ ingredient.name }}
                                </button>
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell">
                                <span class="text-xs text-muted-foreground tabular-nums">
                                    {{ t('catalog.ingredients.used_in_products', { count: ingredient.products_count }, ingredient.products_count) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell">
                                <div class="flex gap-1">
                                    <span
                                        v-for="code in targetLocales"
                                        :key="code"
                                        class="inline-flex items-center gap-0.5 rounded px-1.5 py-0.5 text-[10px] font-medium"
                                        :class="
                                            translationStatus(ingredient, code) === 'done'
                                                ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300'
                                                : 'bg-muted text-muted-foreground'
                                        "
                                        :title="supportedLocales[code]?.native"
                                    >
                                        {{ supportedLocales[code]?.flag }}
                                        {{ code }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex justify-end gap-1">
                                    <button
                                        type="button"
                                        class="rounded p-1.5 text-muted-foreground hover:bg-muted hover:text-foreground"
                                        :title="t('catalog.ingredients.edit_translations')"
                                        @click="openTranslations(ingredient)"
                                    >
                                        <Languages class="h-3.5 w-3.5" />
                                    </button>
                                    <button
                                        type="button"
                                        class="rounded p-1.5 text-muted-foreground hover:bg-destructive/10 hover:text-destructive"
                                        :title="t('common.delete')"
                                        @click="deleteIngredient(ingredient)"
                                    >
                                        <Trash2 class="h-3.5 w-3.5" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div v-if="ingredients.last_page > 1" class="flex items-center justify-between border-t px-4 py-3 text-xs text-muted-foreground">
                    <span>{{ ingredients.total }} en total</span>
                </div>
            </div>
        </div>

        <!-- Sticky bar: merge action -->
        <div
            v-if="selectedIds.size >= 2"
            class="pointer-events-none fixed inset-x-0 bottom-0 z-30 flex justify-center p-4"
        >
            <div class="pointer-events-auto flex items-center gap-3 rounded-full border bg-card/95 text-card-foreground px-4 py-2 shadow-lg backdrop-blur border-primary/40">
                <span class="text-xs font-medium text-foreground">
                    {{ selectedIds.size }} seleccionados
                </span>
                <Button size="sm" @click="openMerge">
                    <GitMerge class="mr-1.5 h-3.5 w-3.5" />
                    {{ t('catalog.ingredients.merge') }}
                </Button>
            </div>
        </div>

        <!-- Translations dialog -->
        <Dialog v-model:open="translationsOpen">
            <DialogContent class="sm:max-w-[520px]">
                <DialogHeader>
                    <DialogTitle>{{ t('catalog.ingredients.edit_translations') }}</DialogTitle>
                    <DialogDescription>
                        {{ translationsTarget?.name }}
                    </DialogDescription>
                </DialogHeader>
                <div class="space-y-3 py-2 max-h-[50vh] overflow-y-auto">
                    <div
                        v-for="code in targetLocales"
                        :key="code"
                        class="space-y-1.5"
                    >
                        <Label class="panel-label flex items-center gap-2">
                            <span>{{ supportedLocales[code]?.flag }}</span>
                            <span>{{ supportedLocales[code]?.native }}</span>
                        </Label>
                        <Input
                            v-model="translationsForm[code]"
                            class="panel-input"
                            :placeholder="t('catalog.ingredients.column_name')"
                        />
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="translationsOpen = false">{{ t('common.cancel') }}</Button>
                    <Button :disabled="savingTranslations" @click="saveTranslations">
                        <Loader2 v-if="savingTranslations" class="mr-1.5 h-3.5 w-3.5 animate-spin" />
                        {{ t('common.save') }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Merge dialog -->
        <Dialog v-model:open="mergeOpen">
            <DialogContent class="sm:max-w-[480px]">
                <DialogHeader>
                    <DialogTitle>{{ t('catalog.ingredients.merge_title') }}</DialogTitle>
                    <DialogDescription>{{ t('catalog.ingredients.merge_description') }}</DialogDescription>
                </DialogHeader>
                <div class="space-y-2 py-2">
                    <Label class="panel-label">{{ t('catalog.ingredients.merge_survivor') }}</Label>
                    <div class="space-y-1.5">
                        <label
                            v-for="ing in selectedIngredients"
                            :key="ing.id"
                            class="flex cursor-pointer items-center gap-2 rounded-md border p-2.5 text-sm transition-colors hover:bg-muted/40"
                            :class="survivorId === ing.id ? 'border-primary bg-primary/5' : 'border-border'"
                        >
                            <input
                                type="radio"
                                :value="ing.id"
                                v-model="survivorId"
                                class="h-4 w-4"
                            />
                            <span class="flex-1 font-medium">{{ ing.name }}</span>
                            <span class="text-xs text-muted-foreground">
                                {{ ing.products_count }} usos
                            </span>
                        </label>
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="mergeOpen = false">{{ t('common.cancel') }}</Button>
                    <Button :disabled="!survivorId || merging" @click="submitMerge">
                        <Loader2 v-if="merging" class="mr-1.5 h-3.5 w-3.5 animate-spin" />
                        <GitMerge v-else class="mr-1.5 h-3.5 w-3.5" />
                        {{ t('catalog.ingredients.merge') }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
