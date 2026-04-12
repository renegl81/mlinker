<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import AllergenIcon from '@/components/ui/AllergenIcon.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import AppLayout from '@/layouts/AppLayout.vue';
import { index as locationMenusRoute } from '@/routes/tenant/locations/menus';
import { destroy as menuRouteDestroy, edit as menuRouteEdit } from '@/routes/tenant/menus';
import type { BreadcrumbItem, Menu } from '@/types';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { useConfirmDialog } from '@/composables/useConfirmDialog';
import {
    ArrowDown,
    ArrowLeft,
    ArrowRight,
    ArrowUp,
    BookOpen,
    Copy,
    Download,
    Eye,
    ExternalLink,
    Globe,
    Image as ImageIcon,
    Monitor,
    MoreHorizontal,
    Palette,
    Pencil,
    Plus,
    QrCode as QrCodeIcon,
    RefreshCw,
    Smartphone,
    Trash2,
    Upload,
    X,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const { confirm: confirmDialog } = useConfirmDialog();

interface Allergen {
    id: number;
    name: string;
    code: string | null;
}

interface Product {
    id: number;
    name: string;
    description: string | null;
    price: string | number | null;
    image_url: string | null;
    image_path: string | null;
    tags: string[] | null;
    allergens: Allergen[];
}

interface Section {
    id: number;
    name: string;
    description: string | null;
    sort_order: number;
    products?: Product[];
}

interface MenuWithSections extends Menu {
    sections: Section[];
}

function formatPrice(price: string | number | null | undefined): string {
    if (price === null || price === undefined || price === '') return '';
    const n = typeof price === 'string' ? parseFloat(price) : price;
    if (Number.isNaN(n)) return '';
    return n.toFixed(2) + ' €';
}

const TAG_LABELS: Record<string, string> = {
    vegetarian: '🌱',
    vegan: '🌿',
    spicy: '🌶️',
    gluten_free: '🚫🌾',
};

interface LocationOption {
    id: number;
    name: string;
}

interface Props {
    menu: MenuWithSections;
    qrCodeImageUrl: string | null;
    publicMenuUrl: string;
    locations: LocationOption[];
}

const props = defineProps<Props>();
const page = usePage();
const messages = computed(() => page.props.messages as any);

const showPreview = ref(false);
const previewMode = ref<'mobile' | 'desktop'>('mobile');
const iframeKey = ref(0);

function refreshPreview() {
    iframeKey.value++;
}

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: messages.value.menus.plural,
        href: locationMenusRoute(props.menu.location_id).url,
    },
    {
        title: props.menu.name,
        href: '#',
    },
];

// ── QR ─────────────────────────────────────────────────────────────────────
function generateQr() {
    router.post(`/panel/menus/${props.menu.id}/qr-code`, {}, { preserveScroll: true });
}
function downloadQr() {
    window.location.href = `/panel/menus/${props.menu.id}/qr-code/download`;
}
async function deleteQr() {
    const ok = await confirmDialog({
        description: t('panel.menu_show.delete_qr_confirm'),
        confirmLabel: t('common.delete'),
        variant: 'destructive',
    });
    if (!ok) return;
    router.delete(`/panel/menus/${props.menu.id}/qr-code`, { preserveScroll: true });
}

// ── Menu delete ─────────────────────────────────────────────────────────────
async function remove() {
    const ok = await confirmDialog({
        description: messages.value.menus.actions.confirm_delete,
        confirmLabel: t('common.delete'),
        variant: 'destructive',
    });
    if (!ok) return;
    router.delete(menuRouteDestroy(props.menu.id).url, {
        onSuccess: () => {
            router.visit(locationMenusRoute(props.menu.location_id).url);
        },
    });
}

// ── Menu duplicate ───────────────────────────────────────────────────────────
const duplicatingMenu = ref(false);

function duplicateMenu() {
    duplicatingMenu.value = true;
    router.post(`/panel/menus/${props.menu.id}/duplicate`, {}, {
        onFinish: () => { duplicatingMenu.value = false; },
    });
}

// ── Clone menu to another location ──────────────────────────────────────────
const showCloneDialog = ref(false);
const cloneTargetLocationId = ref<number | null>(null);
const cloning = ref(false);

const availableLocations = computed(() => props.locations ?? []);

function submitClone() {
    if (!cloneTargetLocationId.value) return;
    cloning.value = true;
    router.post(`/panel/menus/${props.menu.id}/clone`, {
        location_id: cloneTargetLocationId.value,
    }, {
        onFinish: () => {
            cloning.value = false;
            showCloneDialog.value = false;
        },
    });
}

// ── Products ────────────────────────────────────────────────────────────────
async function deleteProduct(productId: number) {
    const ok = await confirmDialog({
        description: t('panel.menu_show.delete_dish_confirm'),
        confirmLabel: t('common.delete'),
        variant: 'destructive',
    });
    if (!ok) return;
    router.delete(`/panel/products/${productId}`, { preserveScroll: true });
}

function duplicateProduct(productId: number) {
    router.post(`/panel/products/${productId}/duplicate`, {}, { preserveScroll: true });
}

// ── Sections ─────────────────────────────────────────────────────────────────
// Add section inline form
const showAddSection = ref(false);
const newSectionName = ref('');
const newSectionDescription = ref('');

function submitAddSection() {
    if (!newSectionName.value.trim()) return;
    router.post(
        `/panel/menus/${props.menu.id}/sections`,
        {
            name: newSectionName.value.trim(),
            description: newSectionDescription.value.trim() || null,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                showAddSection.value = false;
                newSectionName.value = '';
                newSectionDescription.value = '';
            },
        },
    );
}

// Edit section inline
const editingSectionId = ref<number | null>(null);
const editSectionName = ref('');
const editSectionDescription = ref('');

function startEditSection(section: Section) {
    editingSectionId.value = section.id;
    editSectionName.value = section.name;
    editSectionDescription.value = section.description ?? '';
}

function cancelEditSection() {
    editingSectionId.value = null;
}

function submitEditSection(section: Section) {
    router.put(
        `/panel/sections/${section.id}`,
        {
            name: editSectionName.value.trim(),
            description: editSectionDescription.value.trim() || null,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                editingSectionId.value = null;
            },
        },
    );
}

async function deleteSection(sectionId: number) {
    const ok = await confirmDialog({
        description: t('panel.menu_show.delete_section_confirm'),
        confirmLabel: t('common.delete'),
        variant: 'destructive',
    });
    if (!ok) return;
    router.delete(`/panel/sections/${sectionId}`, { preserveScroll: true });
}

function moveSection(sections: Section[], sectionId: number, direction: 'up' | 'down') {
    const sorted = [...sections].sort((a, b) => a.sort_order - b.sort_order);
    const idx = sorted.findIndex((s) => s.id === sectionId);
    if (direction === 'up' && idx === 0) return;
    if (direction === 'down' && idx === sorted.length - 1) return;

    const swapIdx = direction === 'up' ? idx - 1 : idx + 1;
    [sorted[idx], sorted[swapIdx]] = [sorted[swapIdx], sorted[idx]];

    router.post(
        `/panel/menus/${props.menu.id}/sections/reorder`,
        { section_ids: sorted.map((s) => s.id) },
        { preserveScroll: true },
    );
}

// ── Excel import ─────────────────────────────────────────────────────────────
const showImportDialog = ref(false);
const selectedFile = ref<File | null>(null);
const importing = ref(false);
const fileInput = ref<HTMLInputElement | null>(null);

function handleFileSelect(event: Event) {
    const input = event.target as HTMLInputElement;
    selectedFile.value = input.files?.[0] ?? null;
}

function submitImport() {
    if (!selectedFile.value) return;
    importing.value = true;

    router.post(
        `/panel/menus/${props.menu.id}/import`,
        { file: selectedFile.value },
        {
            forceFormData: true,
            onSuccess: () => {
                showImportDialog.value = false;
                selectedFile.value = null;
                if (fileInput.value) fileInput.value.value = '';
            },
            onFinish: () => {
                importing.value = false;
            },
        },
    );
}
</script>

<template>
    <Head :title="`${messages.menus.singular}: ${menu.name}`" />

    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="outline" size="icon" as-child>
                        <Link :href="locationMenusRoute(menu.location_id).url">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                    </Button>
                    <HeadingSmall
                        :title="menu.name"
                        :description="messages.menus.caption"
                    />
                </div>
                <div class="flex items-center gap-2">
                    <!-- Primary actions (always visible) -->
                    <Button variant="outline" @click="showPreview = !showPreview">
                        <Eye class="mr-2 h-4 w-4" />
                        <span class="hidden sm:inline">{{ t('panel.menu_show.preview') }}</span>
                    </Button>
                    <Button as-child>
                        <Link :href="menuRouteEdit(menu.id).url">
                            <Pencil class="mr-2 h-4 w-4" />
                            {{ messages.menus.actions.edit }}
                        </Link>
                    </Button>

                    <!-- More actions dropdown -->
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline" size="icon">
                                <MoreHorizontal class="h-4 w-4" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-56">
                            <DropdownMenuItem as-child>
                                <Link :href="`/panel/menus/${menu.id}/customize`" class="flex cursor-pointer items-center gap-2">
                                    <Palette class="h-4 w-4" />
                                    {{ t('panel.menu_show.customize') }}
                                </Link>
                            </DropdownMenuItem>
                            <DropdownMenuItem as-child>
                                <Link :href="`/panel/menus/${menu.id}/translations`" class="flex cursor-pointer items-center gap-2">
                                    <Globe class="h-4 w-4" />
                                    {{ t('translations.title') }}
                                </Link>
                            </DropdownMenuItem>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem class="flex cursor-pointer items-center gap-2" @click="duplicateMenu" :disabled="duplicatingMenu">
                                <Copy class="h-4 w-4" />
                                {{ t('panel.menu_show.duplicate_menu') }}
                            </DropdownMenuItem>
                            <DropdownMenuItem
                                v-if="availableLocations.length > 0"
                                class="flex cursor-pointer items-center gap-2"
                                @click="showCloneDialog = true"
                            >
                                <ArrowRight class="h-4 w-4" />
                                {{ t('panel.menu_show.clone_to_location') }}
                            </DropdownMenuItem>
                            <DropdownMenuItem class="flex cursor-pointer items-center gap-2" @click="showImportDialog = true">
                                <Upload class="h-4 w-4" />
                                {{ t('panel.menu_show.import_excel') }}
                            </DropdownMenuItem>
                            <DropdownMenuItem as-child>
                                <a :href="`/panel/menus/import/template`" download class="flex cursor-pointer items-center gap-2">
                                    <Download class="h-4 w-4" />
                                    {{ t('panel.menu_show.download_template') }}
                                </a>
                            </DropdownMenuItem>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem class="flex cursor-pointer items-center gap-2 text-destructive focus:text-destructive" @click="remove">
                                <Trash2 class="h-4 w-4" />
                                {{ messages.menus.actions.delete }}
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="space-y-6 lg:col-span-2">
                    <!-- Información general -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg font-semibold">
                                {{ messages.menus.form.title_edit || 'Información del Menú' }}
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div class="flex flex-col gap-1.5">
                                    <span class="text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                        {{ messages.menus.fields.name }}
                                    </span>
                                    <p class="text-sm font-medium">{{ menu.name }}</p>
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <span class="text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                        {{ messages.menus.fields.location }}
                                    </span>
                                    <p class="text-sm">{{ menu.location?.name || '---' }}</p>
                                </div>
                            </div>

                            <div v-if="menu.description" class="flex flex-col gap-1.5">
                                <span class="text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                    {{ messages.menus.fields.description }}
                                </span>
                                <p class="text-sm leading-relaxed text-pretty text-muted-foreground">
                                    {{ menu.description }}
                                </p>
                            </div>

                            <div v-if="menu.template" class="flex flex-col gap-1.5">
                                <span class="text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                    {{ messages.menus.fields.template }}
                                </span>
                                <p class="text-sm">{{ menu.template.name }}</p>
                            </div>

                            <Separator />

                            <!-- Imagen del menú -->
                            <div class="flex flex-col gap-2">
                                <span class="text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                    {{ messages.menus.fields.image_url || 'Imagen' }}
                                </span>
                                <div
                                    v-if="menu.image_path"
                                    class="overflow-hidden rounded-lg border"
                                >
                                    <img
                                        :src="menu.image_path"
                                        :alt="menu.name"
                                        class="h-48 w-full object-cover"
                                    />
                                </div>
                                <div
                                    v-else
                                    class="flex h-32 items-center justify-center rounded-lg border-2 border-dashed text-muted-foreground"
                                >
                                    <div class="flex flex-col items-center gap-2">
                                        <ImageIcon class="h-8 w-8 opacity-40" />
                                        <span class="text-xs">{{ t('panel.menu_show.no_image') }}</span>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Secciones del menú -->
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0">
                            <CardTitle class="flex items-center gap-2 text-lg">
                                <BookOpen class="h-5 w-5 text-primary" />
                                {{ t('panel.menu_show.sections_title') }}
                            </CardTitle>
                            <Button size="sm" variant="outline" @click="showAddSection = !showAddSection">
                                <Plus class="mr-1 h-4 w-4" />
                                {{ t('panel.menu_show.add_section') }}
                            </Button>
                        </CardHeader>
                        <CardContent>
                            <!-- Inline form: new section -->
                            <div
                                v-if="showAddSection"
                                class="mb-4 rounded-lg border border-dashed bg-muted/20 p-4"
                            >
                                <h4 class="mb-3 text-sm font-semibold">{{ t('panel.menu_show.new_section') }}</h4>
                                <div class="space-y-3">
                                    <div class="space-y-1">
                                        <Label for="new_section_name">{{ t('panel.menu_show.section_name') }}</Label>
                                        <Input
                                            id="new_section_name"
                                            v-model="newSectionName"
                                            :placeholder="t('panel.menu_show.section_placeholder')"
                                            @keydown.enter.prevent="submitAddSection"
                                        />
                                    </div>
                                    <div class="space-y-1">
                                        <Label for="new_section_desc">{{ t('panel.menu_show.section_desc') }}</Label>
                                        <Input
                                            id="new_section_desc"
                                            v-model="newSectionDescription"
                                            :placeholder="t('panel.menu_show.section_desc_placeholder')"
                                        />
                                    </div>
                                    <div class="flex gap-2">
                                        <Button size="sm" @click="submitAddSection">{{ t('common.save') }}</Button>
                                        <Button size="sm" variant="outline" @click="showAddSection = false">{{ t('common.cancel') }}</Button>
                                    </div>
                                </div>
                            </div>

                            <div v-if="menu.sections && menu.sections.length > 0" class="space-y-6">
                                <div
                                    v-for="(section, sectionIdx) in [...menu.sections].sort((a, b) => a.sort_order - b.sort_order)"
                                    :key="section.id"
                                    class="space-y-3"
                                >
                                    <!-- Cabecera de sección -->
                                    <div v-if="editingSectionId !== section.id" class="flex items-baseline justify-between border-b pb-2">
                                        <div class="flex-1">
                                            <h3 class="text-base font-semibold">{{ section.name }}</h3>
                                            <p
                                                v-if="section.description"
                                                class="mt-0.5 text-xs text-muted-foreground"
                                            >
                                                {{ section.description }}
                                            </p>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <!-- Move up/down buttons -->
                                            <Button
                                                size="icon"
                                                variant="ghost"
                                                class="h-7 w-7"
                                                :disabled="sectionIdx === 0"
                                                @click="moveSection(menu.sections, section.id, 'up')"
                                                :title="t('panel.menu_show.move_up')"
                                            >
                                                <ArrowUp class="h-3 w-3" />
                                            </Button>
                                            <Button
                                                size="icon"
                                                variant="ghost"
                                                class="h-7 w-7"
                                                :disabled="sectionIdx === menu.sections.length - 1"
                                                @click="moveSection(menu.sections, section.id, 'down')"
                                                :title="t('panel.menu_show.move_down')"
                                            >
                                                <ArrowDown class="h-3 w-3" />
                                            </Button>
                                            <!-- Add product to this section -->
                                            <Button
                                                size="sm"
                                                variant="outline"
                                                class="ml-2 h-7 px-2 text-xs"
                                                as-child
                                            >
                                                <Link :href="`/panel/menus/${menu.id}/products/create?section_id=${section.id}`">
                                                    <Plus class="mr-1 h-3 w-3" />
                                                    {{ t('panel.menu_show.add_dish') }}
                                                </Link>
                                            </Button>
                                            <!-- Edit section -->
                                            <Button
                                                size="icon"
                                                variant="ghost"
                                                class="h-7 w-7"
                                                @click="startEditSection(section)"
                                                :title="t('panel.menu_show.edit_section')"
                                            >
                                                <Pencil class="h-3 w-3" />
                                            </Button>
                                            <!-- Delete section -->
                                            <Button
                                                size="icon"
                                                variant="ghost"
                                                class="h-7 w-7 text-destructive hover:text-destructive"
                                                @click="deleteSection(section.id)"
                                                :title="t('panel.menu_show.delete_section')"
                                            >
                                                <Trash2 class="h-3 w-3" />
                                            </Button>
                                        </div>
                                    </div>

                                    <!-- Inline edit section form -->
                                    <div v-else class="rounded-lg border border-primary/30 bg-primary/5 p-3 mb-2">
                                        <div class="space-y-2">
                                            <Input v-model="editSectionName" :placeholder="t('panel.menu_show.section_name_placeholder')" />
                                            <Input v-model="editSectionDescription" :placeholder="t('panel.menu_show.section_desc_edit_placeholder')" />
                                            <div class="flex gap-2">
                                                <Button size="sm" @click="submitEditSection(section)">{{ t('common.save') }}</Button>
                                                <Button size="sm" variant="outline" @click="cancelEditSection">{{ t('common.cancel') }}</Button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Productos de la sección -->
                                    <div
                                        v-if="section.products && section.products.length > 0"
                                        class="space-y-2"
                                    >
                                        <div
                                            v-for="product in section.products"
                                            :key="product.id"
                                            class="flex items-start justify-between gap-4 rounded-md border bg-muted/20 p-3"
                                        >
                                            <!-- Product image placeholder or thumb -->
                                            <div class="shrink-0">
                                                <div
                                                    v-if="!product.image_path"
                                                    class="flex h-10 w-10 items-center justify-center rounded-md bg-gradient-to-br from-teal-500 to-cyan-500 text-base font-bold text-white"
                                                >
                                                    {{ product.name.charAt(0).toUpperCase() }}
                                                </div>
                                                <img
                                                    v-else
                                                    :src="product.image_path"
                                                    :alt="product.name"
                                                    class="h-10 w-10 rounded-md object-cover"
                                                />
                                            </div>

                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-1.5 flex-wrap">
                                                    <p class="text-sm font-medium">{{ product.name }}</p>
                                                    <!-- Tags -->
                                                    <span
                                                        v-for="tag in (product.tags ?? [])"
                                                        :key="tag"
                                                        :title="tag"
                                                        class="text-base leading-none"
                                                    >{{ TAG_LABELS[tag] ?? tag }}</span>
                                                </div>
                                                <p
                                                    v-if="product.description"
                                                    class="mt-0.5 line-clamp-2 text-xs text-muted-foreground"
                                                >
                                                    {{ product.description }}
                                                </p>
                                                <!-- Allergens -->
                                                <div v-if="product.allergens && product.allergens.length > 0" class="mt-1 flex gap-1 flex-wrap">
                                                    <AllergenIcon
                                                        v-for="allergen in product.allergens"
                                                        :key="allergen.id"
                                                        :code="allergen.code"
                                                        size="sm"
                                                        :title="allergen.name"
                                                    />
                                                </div>
                                            </div>

                                            <div class="flex items-center gap-1 shrink-0">
                                                <span class="text-sm font-semibold text-primary mr-2">
                                                    {{ formatPrice(product.price) }}
                                                </span>
                                                <!-- Duplicate -->
                                                <Button
                                                    size="icon"
                                                    variant="ghost"
                                                    class="h-7 w-7"
                                                    @click="duplicateProduct(product.id)"
                                                    title="Duplicar plato"
                                                >
                                                    <Copy class="h-3 w-3" />
                                                </Button>
                                                <!-- Edit -->
                                                <Button
                                                    size="icon"
                                                    variant="ghost"
                                                    class="h-7 w-7"
                                                    as-child
                                                >
                                                    <Link :href="`/panel/products/${product.id}/edit`" :title="t('panel.menu_show.edit_dish')">
                                                        <Pencil class="h-3 w-3" />
                                                    </Link>
                                                </Button>
                                                <!-- Delete -->
                                                <Button
                                                    size="icon"
                                                    variant="ghost"
                                                    class="h-7 w-7 text-destructive hover:text-destructive"
                                                    @click="deleteProduct(product.id)"
                                                    :title="t('panel.menu_show.delete_dish')"
                                                >
                                                    <Trash2 class="h-3 w-3" />
                                                </Button>
                                            </div>
                                        </div>
                                    </div>

                                    <div
                                        v-else
                                        class="flex items-center justify-between rounded-md border border-dashed bg-muted/10 p-3 text-xs text-muted-foreground"
                                    >
                                        <span>{{ t('panel.menu_show.section_no_dishes') }}</span>
                                        <Button size="sm" variant="ghost" class="h-7 px-2 text-xs" as-child>
                                            <Link :href="`/panel/menus/${menu.id}/products/create?section_id=${section.id}`">
                                                <Plus class="mr-1 h-3 w-3" />
                                                {{ t('panel.menu_show.add_dish') }}
                                            </Link>
                                        </Button>
                                    </div>
                                </div>
                            </div>

                            <div
                                v-else
                                class="flex flex-col items-center justify-center rounded-lg border-2 border-dashed py-8 text-center"
                            >
                                <BookOpen class="mb-2 h-8 w-8 text-muted-foreground/30" />
                                <p class="mb-3 text-sm text-muted-foreground">
                                    {{ t('panel.menu_show.no_sections') }}
                                </p>
                                <Button size="sm" @click="showAddSection = true">
                                    <Plus class="mr-2 h-4 w-4" />
                                    {{ t('panel.menu_show.add_first_section') }}
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Columna lateral: configuración y estado -->
                <div class="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-md font-semibold">
                                {{ messages.menus.fields.settings || 'Configuración' }}
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3 text-sm">
                            <div class="flex items-center justify-between border-b border-muted py-1.5">
                                <span class="text-muted-foreground">{{ messages.menus.fields.show_prices }}</span>
                                <Badge :variant="menu.show_prices ? 'default' : 'secondary'">
                                    {{ menu.show_prices ? messages.menus.status.active : messages.menus.status.inactive }}
                                </Badge>
                            </div>
                            <div class="flex items-center justify-between border-b border-muted py-1.5">
                                <span class="text-muted-foreground">{{ messages.menus.fields.show_currency }}</span>
                                <Badge :variant="menu.show_currency ? 'default' : 'secondary'">
                                    {{ menu.show_currency ? messages.menus.status.active : messages.menus.status.inactive }}
                                </Badge>
                            </div>
                            <div class="flex items-center justify-between py-1.5">
                                <span class="text-muted-foreground">{{ messages.menus.fields.show_calories }}</span>
                                <Badge :variant="menu.show_calories ? 'default' : 'secondary'">
                                    {{ menu.show_calories ? messages.menus.status.active : messages.menus.status.inactive }}
                                </Badge>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Código QR -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2 text-md font-semibold">
                                <QrCodeIcon class="h-4 w-4 text-primary" />
                                {{ t('panel.menu_show.qr_title') }}
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div
                                v-if="qrCodeImageUrl"
                                class="flex items-center justify-center rounded-lg border bg-white p-3"
                            >
                                <img
                                    :src="qrCodeImageUrl"
                                    :alt="t('panel.menu_show.qr_title')"
                                    class="h-40 w-40 object-contain"
                                />
                            </div>
                            <div
                                v-else
                                class="flex h-40 flex-col items-center justify-center rounded-lg border-2 border-dashed text-muted-foreground"
                            >
                                <QrCodeIcon class="h-8 w-8 opacity-40" />
                                <span class="mt-2 text-xs">{{ t('panel.menu_show.qr_none') }}</span>
                            </div>

                            <div class="flex flex-col gap-2">
                                <Button @click="generateQr" class="w-full" size="sm">
                                    <RefreshCw class="mr-2 h-4 w-4" />
                                    {{ qrCodeImageUrl ? t('panel.menu_show.qr_regenerate') : t('panel.menu_show.qr_generate') }}
                                </Button>
                                <Button
                                    v-if="qrCodeImageUrl"
                                    variant="outline"
                                    size="sm"
                                    class="w-full"
                                    @click="downloadQr"
                                >
                                    <Download class="mr-2 h-4 w-4" />
                                    {{ t('panel.menu_show.qr_download') }}
                                </Button>
                                <Button
                                    v-if="qrCodeImageUrl"
                                    variant="ghost"
                                    size="sm"
                                    class="w-full text-destructive"
                                    @click="deleteQr"
                                >
                                    <Trash2 class="mr-2 h-4 w-4" />
                                    {{ t('panel.menu_show.qr_delete') }}
                                </Button>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Personalización activa -->
                    <Card v-if="menu.customization">
                        <CardHeader class="pb-2">
                            <CardTitle class="flex items-center gap-2 text-sm font-medium">
                                <Palette class="h-4 w-4 text-primary" />
                                {{ t('panel.menu_show.customized') }}
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="flex flex-wrap gap-1.5">
                                <Badge v-if="menu.customization?.colors" variant="secondary" class="text-[10px]">
                                    {{ t('panel.customizer.colors') }}
                                </Badge>
                                <Badge v-if="menu.customization?.fonts" variant="secondary" class="text-[10px]">
                                    {{ t('panel.customizer.fonts') }}
                                </Badge>
                                <Badge v-if="menu.customization?.layout" variant="secondary" class="text-[10px]">
                                    {{ t('panel.customizer.layout') }}
                                </Badge>
                                <Badge v-if="menu.customization?.spacing" variant="secondary" class="text-[10px]">
                                    {{ t('panel.customizer.spacing') }}
                                </Badge>
                                <Badge v-if="menu.customization?.header" variant="secondary" class="text-[10px]">
                                    {{ t('panel.customizer.header_section') }}
                                </Badge>
                                <Badge v-if="menu.customization?.sections" variant="secondary" class="text-[10px]">
                                    {{ t('panel.customizer.sections_style') }}
                                </Badge>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Estado del menú -->
                    <Card
                        :class="
                            menu.is_active
                                ? 'border-green-200 bg-green-50/50'
                                : 'border-red-200 bg-red-50/50'
                        "
                    >
                        <CardContent class="flex items-center justify-center gap-2 py-4">
                            <div
                                :class="menu.is_active ? 'bg-green-500' : 'bg-red-500'"
                                class="h-2 w-2 animate-pulse rounded-full"
                            />
                            <span
                                :class="menu.is_active ? 'text-green-700' : 'text-red-700'"
                                class="text-xs font-bold tracking-widest uppercase"
                            >
                                {{
                                    menu.is_active
                                        ? messages.menus.status.active
                                        : messages.menus.status.inactive
                                }}
                            </span>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>

    <!-- ═══ IMPORT DIALOG ═══ -->
    <Dialog v-model:open="showImportDialog">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>{{ t('panel.menu_show.import_excel') }}</DialogTitle>
                <DialogDescription>{{ t('panel.menu_show.import_description') }}</DialogDescription>
            </DialogHeader>
            <div class="space-y-4">
                <div class="rounded-lg border border-dashed p-4 text-center">
                    <input
                        ref="fileInput"
                        type="file"
                        accept=".xlsx,.xls,.csv"
                        class="hidden"
                        @change="handleFileSelect"
                    />
                    <button
                        type="button"
                        class="text-sm text-primary hover:underline"
                        @click="(fileInput as HTMLInputElement | null)?.click()"
                    >
                        {{ selectedFile ? selectedFile.name : t('panel.menu_show.select_file') }}
                    </button>
                    <p class="mt-1 text-xs text-muted-foreground">{{ t('panel.menu_show.import_formats') }}</p>
                </div>
                <a
                    href="/panel/menus/import/template"
                    download
                    class="flex items-center gap-2 text-xs text-primary hover:underline"
                >
                    <Download class="h-3 w-3" />
                    {{ t('panel.menu_show.download_template') }}
                </a>
            </div>
            <DialogFooter>
                <Button variant="outline" @click="showImportDialog = false">{{ t('common.cancel') }}</Button>
                <Button :disabled="!selectedFile || importing" @click="submitImport">
                    <Upload v-if="!importing" class="mr-2 h-4 w-4" />
                    {{ importing ? t('panel.menu_show.importing') : t('panel.menu_show.import_excel') }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>

    <!-- ═══ CLONE TO LOCATION DIALOG ═══ -->
    <Dialog v-model:open="showCloneDialog">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>{{ t('panel.menu_show.clone_to_location') }}</DialogTitle>
                <DialogDescription>{{ t('panel.menu_show.clone_to_location_desc') }}</DialogDescription>
            </DialogHeader>
            <div class="space-y-3">
                <Label>{{ t('panel.menu_show.select_location') }}</Label>
                <select
                    v-model="cloneTargetLocationId"
                    class="flex h-9 w-full rounded-lg border border-input bg-transparent px-3 py-1 text-sm outline-none"
                >
                    <option :value="null" disabled>{{ t('panel.menu_show.select_location') }}</option>
                    <option
                        v-for="loc in availableLocations"
                        :key="loc.id"
                        :value="loc.id"
                    >
                        {{ loc.name }}
                    </option>
                </select>
            </div>
            <DialogFooter class="gap-2 sm:gap-0">
                <Button variant="outline" @click="showCloneDialog = false">{{ t('common.cancel') }}</Button>
                <Button :disabled="!cloneTargetLocationId || cloning" @click="submitClone">
                    {{ cloning ? '...' : t('panel.menu_show.clone_confirm') }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>

    <!-- ═══ PREVIEW PANEL ═══ -->
    <Teleport to="body">
        <Transition name="preview-slide">
            <div v-if="showPreview" class="preview-overlay">
                <div class="preview-panel">
                    <!-- Header -->
                    <div class="preview-header">
                        <div class="preview-header-left">
                            <Eye class="h-4 w-4" />
                            <span class="preview-header-title">{{ t('panel.menu_show.preview') }}</span>
                        </div>
                        <div class="preview-header-actions">
                            <!-- Device toggle -->
                            <div class="preview-device-toggle">
                                <button
                                    type="button"
                                    class="preview-device-btn"
                                    :class="{ 'is-active': previewMode === 'mobile' }"
                                    @click="previewMode = 'mobile'"
                                    :title="t('panel.menu_show.preview_mobile')"
                                >
                                    <Smartphone class="h-4 w-4" />
                                </button>
                                <button
                                    type="button"
                                    class="preview-device-btn"
                                    :class="{ 'is-active': previewMode === 'desktop' }"
                                    @click="previewMode = 'desktop'"
                                    :title="t('panel.menu_show.preview_desktop')"
                                >
                                    <Monitor class="h-4 w-4" />
                                </button>
                            </div>
                            <button type="button" class="preview-action-btn" @click="refreshPreview" :title="t('panel.menu_show.refresh_preview')">
                                <RefreshCw class="h-4 w-4" />
                            </button>
                            <a :href="publicMenuUrl" target="_blank" rel="noopener" class="preview-action-btn" :title="t('panel.menu_show.open_new_tab')">
                                <ExternalLink class="h-4 w-4" />
                            </a>
                            <button type="button" class="preview-close-btn" @click="showPreview = false">
                                <X class="h-4 w-4" />
                            </button>
                        </div>
                    </div>
                    <!-- Iframe container -->
                    <div class="preview-body">
                        <div
                            class="preview-frame"
                            :class="{
                                'preview-frame-mobile': previewMode === 'mobile',
                                'preview-frame-desktop': previewMode === 'desktop',
                            }"
                        >
                            <iframe
                                :key="iframeKey"
                                :src="publicMenuUrl"
                                class="preview-iframe"
                                :title="menu.name"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.preview-overlay {
    position: fixed;
    inset: 0;
    z-index: 50;
    display: flex;
    justify-content: flex-end;
    background: oklch(0 0 0 / 0.4);
    backdrop-filter: blur(4px);
}

.preview-panel {
    width: 100%;
    max-width: 480px;
    height: 100%;
    display: flex;
    flex-direction: column;
    background: var(--color-card);
    color: var(--color-card-foreground);
    border-left: 1px solid var(--color-border);
    box-shadow: -20px 0 60px oklch(0 0 0 / 0.15);
}

@media (min-width: 1024px) {
    .preview-panel { max-width: 520px; }
}

.preview-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid var(--color-border);
    flex-shrink: 0;
}

.preview-header-left {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--color-foreground);
}

.preview-header-title {
    font-size: 0.85rem;
    font-weight: 600;
}

.preview-header-actions {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.preview-device-toggle {
    display: flex;
    align-items: center;
    gap: 2px;
    padding: 3px;
    border-radius: 8px;
    background: var(--color-muted);
    margin-right: 0.5rem;
}

.preview-device-btn {
    padding: 5px 8px;
    border-radius: 6px;
    border: none;
    background: transparent;
    color: var(--color-muted-foreground);
    cursor: pointer;
    transition: all 150ms;
    display: flex;
    align-items: center;
}

.preview-device-btn.is-active {
    background: var(--color-card);
    color: var(--color-foreground);
    box-shadow: 0 1px 3px oklch(0 0 0 / 0.08);
}

.preview-action-btn {
    padding: 6px;
    border-radius: 6px;
    border: none;
    background: transparent;
    color: var(--color-muted-foreground);
    cursor: pointer;
    transition: all 150ms;
    display: flex;
    align-items: center;
    text-decoration: none;
}

.preview-action-btn:hover {
    background: var(--color-muted);
    color: var(--color-foreground);
}

.preview-close-btn {
    padding: 6px;
    border-radius: 6px;
    border: none;
    background: transparent;
    color: var(--color-muted-foreground);
    cursor: pointer;
    transition: all 150ms;
    display: flex;
    align-items: center;
    margin-left: 0.25rem;
}

.preview-close-btn:hover {
    background: oklch(0.55 0.2 25 / 0.1);
    color: oklch(0.55 0.2 25);
}

.preview-body {
    flex: 1;
    overflow: hidden;
    display: flex;
    align-items: flex-start;
    justify-content: center;
    padding: 1.5rem;
    background: var(--color-muted);
}

.preview-frame {
    transition: all 400ms cubic-bezier(.2,.65,.2,1);
    overflow: hidden;
    background: #fff;
    flex-shrink: 0;
}

.preview-frame-mobile {
    width: 375px;
    height: calc(100vh - 120px);
    max-height: 812px;
    border-radius: 2rem;
    box-shadow:
        0 0 0 8px oklch(0.25 0.01 260),
        0 20px 60px -20px oklch(0 0 0 / 0.35);
}

.preview-frame-desktop {
    width: 100%;
    height: calc(100vh - 120px);
    border-radius: 12px;
    box-shadow: 0 8px 30px -12px oklch(0 0 0 / 0.2);
}

.preview-iframe {
    width: 100%;
    height: 100%;
    border: none;
    display: block;
}

.preview-frame-mobile .preview-iframe {
    border-radius: 2rem;
}

.preview-frame-desktop .preview-iframe {
    border-radius: 12px;
}

/* Transition */
.preview-slide-enter-active,
.preview-slide-leave-active {
    transition: opacity 250ms ease, transform 250ms cubic-bezier(.2,.65,.2,1);
}

.preview-slide-enter-active .preview-panel,
.preview-slide-leave-active .preview-panel {
    transition: transform 300ms cubic-bezier(.2,.65,.2,1);
}

.preview-slide-enter-from {
    opacity: 0;
}

.preview-slide-enter-from .preview-panel {
    transform: translateX(100%);
}

.preview-slide-leave-to {
    opacity: 0;
}

.preview-slide-leave-to .preview-panel {
    transform: translateX(100%);
}

@media (max-width: 640px) {
    .preview-panel { max-width: 100%; }
    .preview-body { padding: 0.75rem; }
    .preview-frame-mobile {
        width: 100%;
        border-radius: 0;
        box-shadow: none;
        height: calc(100vh - 80px);
    }
}
</style>
