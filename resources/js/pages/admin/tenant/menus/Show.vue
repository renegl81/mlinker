<script setup lang="ts">
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
import { Switch } from '@/components/ui/switch';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import InlineEditableText from '@/components/inline/InlineEditableText.vue';
import InlineEditablePrice from '@/components/inline/InlineEditablePrice.vue';
import SaveIndicator from '@/components/inline/SaveIndicator.vue';
import ProductDetailDrawer from '@/components/inline/ProductDetailDrawer.vue';
import { useMenuEditor } from '@/composables/useMenuEditor';
import AppLayout from '@/layouts/AppLayout.vue';
import { index as locationMenusRoute } from '@/routes/tenant/locations/menus';
import { destroy as menuRouteDestroy } from '@/routes/tenant/menus';
import type { BreadcrumbItem, Menu } from '@/types';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { useConfirmDialog } from '@/composables/useConfirmDialog';
import TemplatePreview from '@/components/template-bodies/TemplatePreview.vue';
import {
    ArrowDown,
    ArrowLeft,
    ArrowRight,
    ArrowUp,
    BookOpen,
    Check,
    ChevronDown,
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
    Settings,
    Smartphone,
    Trash2,
    Upload,
    X,
} from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, reactive, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const { confirm: confirmDialog } = useConfirmDialog();

interface Allergen {
    id: number;
    name: string;
    code: string | null;
}

interface Ingredient {
    id: number;
    name: string;
}

interface Product {
    id: number;
    name: string;
    description: string | null;
    price: string | number | null;
    calories: string | number | null;
    image_url: string | null;
    image_path: string | null;
    tags: string[] | null;
    allergens: Allergen[];
    ingredients?: Ingredient[];
}

interface Section {
    id: number;
    name: string;
    description: string | null;
    sort_order: number;
    products?: Product[];
}

interface SupportedLocale {
    native: string;
    flag: string;
}

interface Template {
    id: number;
    name: string;
    component_name: string;
}

interface MenuWithSections extends Menu {
    sections: Section[];
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
    templates: Template[];
    supportedLocales: Record<string, SupportedLocale>;
    allergens: Allergen[];
}

const props = defineProps<Props>();
const page = usePage();
const messages = computed(() => page.props.messages as any);

// ── Local reactive copy of menu for optimistic updates ───────────────────────
const localMenu = reactive({
    name: props.menu.name,
    description: props.menu.description,
    is_active: props.menu.is_active,
    show_prices: props.menu.show_prices,
    show_currency: props.menu.show_currency,
    show_calories: props.menu.show_calories,
});

// ── Menu editor composable ───────────────────────────────────────────────────
const { saveStatus, saveError, patchMenu, patchSection, patchProduct } = useMenuEditor(props.menu.id);

async function saveName(value: string) {
    localMenu.name = value;
    try { await patchMenu({ name: value }); } catch { localMenu.name = props.menu.name; }
}
async function saveDescription(value: string) {
    localMenu.description = value || null;
    try { await patchMenu({ description: value || null }); } catch { localMenu.description = props.menu.description; }
}
async function saveFlag(flag: 'is_active' | 'show_prices' | 'show_currency' | 'show_calories', value: boolean) {
    (localMenu as Record<string, unknown>)[flag] = value;
    try { await patchMenu({ [flag]: value }); } catch { (localMenu as Record<string, unknown>)[flag] = (props.menu as unknown as Record<string, unknown>)[flag]; }
}

async function saveSectionName(sectionId: number, value: string, section: Section) {
    const oldName = section.name;
    section.name = value;
    try { await patchSection(sectionId, { name: value }); } catch { section.name = oldName; }
}
async function saveSectionDescription(sectionId: number, value: string, section: Section) {
    const oldDesc = section.description;
    section.description = value || null;
    try { await patchSection(sectionId, { description: value || null }); } catch { section.description = oldDesc; }
}

async function saveProductName(productId: number, value: string, product: Product) {
    const old = product.name;
    product.name = value;
    try { await patchProduct(productId, { name: value }); } catch { product.name = old; }
}
async function saveProductDescription(productId: number, value: string, product: Product) {
    const old = product.description;
    product.description = value || null;
    try { await patchProduct(productId, { description: value || null }); } catch { product.description = old; }
}
async function saveProductPrice(productId: number, value: number | null, product: Product) {
    const old = product.price;
    product.price = value;
    try { await patchProduct(productId, { price: value }); } catch { product.price = old; }
}

// ── Product detail drawer ────────────────────────────────────────────────────
const drawerOpen = ref(false);
const drawerProduct = ref<Product | null>(null);

function openProductDrawer(product: Product) {
    drawerProduct.value = product;
    drawerOpen.value = true;
}

// ── Visibility options panel toggle ─────────────────────────────────────────
const showVisibilityOptions = ref(false);

const showPreview = ref(false);
const previewMode = ref<'mobile' | 'desktop'>('mobile');
const iframeKey = ref(0);

function refreshPreview() {
    iframeKey.value++;
}

// ── Template switcher (preview panel) ───────────────────────────────────────
const activeTemplateId = ref<number>(props.menu.template_id);
const isTemplatePatching = ref(false);
const templateAnnouncement = ref('');

const activeTemplateIndex = computed(() =>
    props.templates.findIndex(t => t.id === activeTemplateId.value),
);

async function selectPreviewTemplate(template: Template) {
    if (template.id === activeTemplateId.value || isTemplatePatching.value) return;
    const previousId = activeTemplateId.value;
    activeTemplateId.value = template.id;
    isTemplatePatching.value = true;
    templateAnnouncement.value = '';

    try {
        await patchMenu({ template_id: template.id });
        refreshPreview();
        templateAnnouncement.value = `Plantilla cambiada a ${template.name}`;
    } catch {
        activeTemplateId.value = previousId;
    } finally {
        isTemplatePatching.value = false;
    }
}

function navigateTemplate(direction: 1 | -1) {
    const idx = activeTemplateIndex.value;
    const next = idx + direction;
    if (next >= 0 && next < props.templates.length) {
        selectPreviewTemplate(props.templates[next]);
    }
}

function handlePreviewKeydown(e: KeyboardEvent) {
    if (!showPreview.value) return;
    if (e.key === 'ArrowLeft') { e.preventDefault(); navigateTemplate(-1); }
    if (e.key === 'ArrowRight') { e.preventDefault(); navigateTemplate(1); }
    if (e.key === 'Escape') { showPreview.value = false; }
}

onMounted(() => window.addEventListener('keydown', handlePreviewKeydown));
onUnmounted(() => window.removeEventListener('keydown', handlePreviewKeydown));

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
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="flex items-start gap-4 flex-1 min-w-0">
                    <Button variant="outline" size="icon" class="mt-1 shrink-0" as-child>
                        <Link :href="locationMenusRoute(menu.location_id).url">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                    </Button>
                    <div class="flex-1 min-w-0">
                        <!-- Título editable inline -->
                        <h1 class="text-xl font-semibold leading-tight">
                            <InlineEditableText
                                :model-value="localMenu.name"
                                :placeholder="t('panel.menu_show.menu_name_placeholder')"
                                empty-label="Sin nombre"
                                @save="saveName"
                            >
                                <span class="text-xl font-semibold">{{ localMenu.name }}</span>
                            </InlineEditableText>
                        </h1>
                        <!-- Descripción editable inline -->
                        <div class="mt-0.5 text-sm text-muted-foreground">
                            <InlineEditableText
                                :model-value="localMenu.description ?? ''"
                                :placeholder="t('panel.menu_show.menu_desc_placeholder')"
                                :empty-label="t('panel.menu_show.menu_desc_add')"
                                :multiline="true"
                                @save="saveDescription"
                            />
                        </div>
                        <!-- Save indicator + is_active switch -->
                        <div class="mt-1.5 flex items-center gap-3">
                            <SaveIndicator :status="saveStatus" :error-message="saveError" />
                            <label class="flex items-center gap-1.5 cursor-pointer select-none">
                                <Switch
                                    :checked="localMenu.is_active"
                                    @update:checked="saveFlag('is_active', $event)"
                                    :aria-label="t('panel.menu_show.is_active_label')"
                                />
                                <span class="text-xs font-medium" :class="localMenu.is_active ? 'text-teal-700' : 'text-muted-foreground'">
                                    {{ localMenu.is_active ? messages.menus.status.active : messages.menus.status.inactive }}
                                </span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <!-- Preview -->
                    <TooltipProvider>
                        <Tooltip>
                            <TooltipTrigger as-child>
                                <Button variant="outline" @click="showPreview = !showPreview">
                                    <Eye class="mr-2 h-4 w-4" />
                                    <span class="hidden sm:inline">{{ t('panel.menu_show.preview') }}</span>
                                </Button>
                            </TooltipTrigger>
                            <TooltipContent>{{ t('panel.menu_show.preview') }}</TooltipContent>
                        </Tooltip>
                    </TooltipProvider>

                    <!-- More actions dropdown -->
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline" size="icon" :aria-label="t('panel.menu_show.more_actions')" :title="t('panel.menu_show.more_actions')">
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
                        <CardHeader class="pb-3">
                            <CardTitle class="text-lg font-semibold">
                                {{ messages.menus.form.title_edit || 'Información del Menú' }}
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div class="flex flex-col gap-1">
                                    <span class="text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                        {{ messages.menus.fields.name }}
                                    </span>
                                    <InlineEditableText
                                        :model-value="localMenu.name"
                                        :placeholder="t('panel.menu_show.menu_name_placeholder')"
                                        @save="saveName"
                                    >
                                        <span class="text-sm font-medium">{{ localMenu.name }}</span>
                                    </InlineEditableText>
                                </div>
                                <div class="flex flex-col gap-1">
                                    <span class="text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                        {{ messages.menus.fields.location }}
                                    </span>
                                    <p class="text-sm">{{ menu.location?.name || '---' }}</p>
                                </div>
                            </div>

                            <div class="flex flex-col gap-1">
                                <span class="text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                    {{ messages.menus.fields.description }}
                                </span>
                                <InlineEditableText
                                    :model-value="localMenu.description ?? ''"
                                    :placeholder="t('panel.menu_show.menu_desc_placeholder')"
                                    :empty-label="t('panel.menu_show.menu_desc_add')"
                                    :multiline="true"
                                    @save="saveDescription"
                                />
                            </div>

                            <div v-if="menu.template" class="flex flex-col gap-1">
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
                                        :alt="localMenu.name"
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
                                    <!-- Cabecera de sección — edición inline -->
                                    <div class="flex items-start justify-between border-b pb-2">
                                        <div class="flex-1 min-w-0">
                                            <h3 class="text-base font-semibold">
                                                <InlineEditableText
                                                    :model-value="section.name"
                                                    :placeholder="t('panel.menu_show.section_name_placeholder')"
                                                    @save="saveSectionName(section.id, $event, section)"
                                                >
                                                    <span class="text-base font-semibold">{{ section.name }}</span>
                                                </InlineEditableText>
                                            </h3>
                                            <div class="mt-0.5 text-xs text-muted-foreground">
                                                <InlineEditableText
                                                    :model-value="section.description ?? ''"
                                                    :placeholder="t('panel.menu_show.section_desc')"
                                                    :empty-label="t('panel.menu_show.section_desc_add')"
                                                    @save="saveSectionDescription(section.id, $event, section)"
                                                />
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-1 ml-2 shrink-0">
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
                                            <!-- Delete section -->
                                            <TooltipProvider>
                                                <Tooltip>
                                                    <TooltipTrigger as-child>
                                                        <Button
                                                            size="icon"
                                                            variant="ghost"
                                                            class="h-7 w-7 text-destructive hover:text-destructive"
                                                            @click="deleteSection(section.id)"
                                                            :aria-label="t('panel.menu_show.delete_section')"
                                                        >
                                                            <Trash2 class="h-3 w-3" />
                                                        </Button>
                                                    </TooltipTrigger>
                                                    <TooltipContent>{{ t('panel.menu_show.delete_section') }}</TooltipContent>
                                                </Tooltip>
                                            </TooltipProvider>
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
                                                    <InlineEditableText
                                                        :model-value="product.name"
                                                        :placeholder="t('panel.menu_show.product_name')"
                                                        @save="saveProductName(product.id, $event, product)"
                                                    >
                                                        <span class="text-sm font-medium">{{ product.name }}</span>
                                                    </InlineEditableText>
                                                    <!-- Tags -->
                                                    <span
                                                        v-for="tag in (product.tags ?? [])"
                                                        :key="tag"
                                                        :title="tag"
                                                        class="text-base leading-none"
                                                    >{{ TAG_LABELS[tag] ?? tag }}</span>
                                                </div>
                                                <div class="mt-0.5 text-xs text-muted-foreground">
                                                    <InlineEditableText
                                                        :model-value="product.description ?? ''"
                                                        :placeholder="t('panel.menu_show.product_desc_placeholder')"
                                                        :empty-label="t('panel.menu_show.product_desc_add')"
                                                        @save="saveProductDescription(product.id, $event, product)"
                                                    />
                                                </div>
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
                                                <!-- Precio editable inline -->
                                                <InlineEditablePrice
                                                    :model-value="product.price"
                                                    @save="saveProductPrice(product.id, $event, product)"
                                                />
                                                <!-- Editar detalle completo (drawer) -->
                                                <TooltipProvider>
                                                    <Tooltip>
                                                        <TooltipTrigger as-child>
                                                            <Button
                                                                size="icon"
                                                                variant="ghost"
                                                                class="h-7 w-7"
                                                                @click="openProductDrawer(product)"
                                                                :aria-label="t('panel.menu_show.edit_dish')"
                                                            >
                                                                <Pencil class="h-3 w-3" />
                                                            </Button>
                                                        </TooltipTrigger>
                                                        <TooltipContent>{{ t('panel.menu_show.edit_dish_detail') }}</TooltipContent>
                                                    </Tooltip>
                                                </TooltipProvider>
                                                <!-- Duplicate -->
                                                <Button
                                                    size="icon"
                                                    variant="ghost"
                                                    class="h-7 w-7"
                                                    @click="duplicateProduct(product.id)"
                                                    :title="t('panel.menu_show.duplicate_dish')"
                                                >
                                                    <Copy class="h-3 w-3" />
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
                    <!-- Opciones de visualización con switches inline -->
                    <Card>
                        <CardHeader class="pb-2">
                            <button
                                type="button"
                                class="flex w-full items-center justify-between text-left"
                                @click="showVisibilityOptions = !showVisibilityOptions"
                                :aria-expanded="showVisibilityOptions"
                            >
                                <CardTitle class="text-md font-semibold flex items-center gap-1.5">
                                    <Settings class="h-4 w-4 text-primary" />
                                    {{ messages.menus.fields.settings || 'Visualización' }}
                                </CardTitle>
                                <ChevronDown
                                    class="h-4 w-4 text-muted-foreground transition-transform duration-200"
                                    :class="showVisibilityOptions ? 'rotate-180' : ''"
                                />
                            </button>
                        </CardHeader>
                        <CardContent v-if="showVisibilityOptions" class="space-y-3 text-sm pt-0">
                            <div class="flex items-center justify-between py-1.5">
                                <label :for="`switch-show-prices`" class="cursor-pointer text-muted-foreground">
                                    {{ messages.menus.fields.show_prices }}
                                </label>
                                <Switch
                                    id="switch-show-prices"
                                    :checked="localMenu.show_prices"
                                    @update:checked="saveFlag('show_prices', $event)"
                                />
                            </div>
                            <div class="flex items-center justify-between py-1.5">
                                <label :for="`switch-show-currency`" class="cursor-pointer text-muted-foreground">
                                    {{ messages.menus.fields.show_currency }}
                                </label>
                                <Switch
                                    id="switch-show-currency"
                                    :checked="localMenu.show_currency"
                                    @update:checked="saveFlag('show_currency', $event)"
                                />
                            </div>
                            <div class="flex items-center justify-between py-1.5">
                                <label :for="`switch-show-calories`" class="cursor-pointer text-muted-foreground">
                                    {{ messages.menus.fields.show_calories }}
                                </label>
                                <Switch
                                    id="switch-show-calories"
                                    :checked="localMenu.show_calories"
                                    @update:checked="saveFlag('show_calories', $event)"
                                />
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

                    <!-- Estado del menú (con switch) -->
                    <Card
                        :class="localMenu.is_active ? 'border-teal-200 bg-teal-50/50' : 'border-muted'"
                    >
                        <CardContent class="flex items-center justify-between gap-2 py-3 px-4">
                            <div class="flex items-center gap-2">
                                <div
                                    :class="localMenu.is_active ? 'bg-teal-500' : 'bg-muted-foreground/40'"
                                    class="h-2 w-2 animate-pulse rounded-full"
                                />
                                <span
                                    :class="localMenu.is_active ? 'text-teal-700' : 'text-muted-foreground'"
                                    class="text-xs font-bold tracking-widest uppercase"
                                >
                                    {{ localMenu.is_active ? messages.menus.status.active : messages.menus.status.inactive }}
                                </span>
                            </div>
                            <Switch
                                :checked="localMenu.is_active"
                                @update:checked="saveFlag('is_active', $event)"
                                :aria-label="t('panel.menu_show.is_active_label')"
                            />
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>

    <!-- ═══ PRODUCT DETAIL DRAWER ═══ -->
    <ProductDetailDrawer
        :open="drawerOpen"
        :product="drawerProduct"
        :menu-id="menu.id"
        :allergens="allergens"
        @update:open="drawerOpen = $event"
        @saved="drawerOpen = false"
    />

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

                    <!-- Template strip -->
                    <div
                        v-if="templates.length > 0"
                        class="preview-template-strip"
                        role="radiogroup"
                        aria-label="Plantilla"
                    >
                        <div class="preview-template-strip-inner">
                            <button
                                v-for="tpl in templates"
                                :key="tpl.id"
                                type="button"
                                role="radio"
                                :aria-checked="activeTemplateId === tpl.id"
                                :tabindex="activeTemplateId === tpl.id ? 0 : -1"
                                class="preview-tpl-thumb"
                                :class="{ 'is-active': activeTemplateId === tpl.id }"
                                :title="tpl.name"
                                @click="selectPreviewTemplate(tpl)"
                            >
                                <!-- Live mini preview -->
                                <div class="preview-tpl-thumb-frame" aria-hidden="true">
                                    <div class="preview-tpl-thumb-scale">
                                        <TemplatePreview :component-name="tpl.component_name" />
                                    </div>
                                </div>
                                <span class="preview-tpl-name">{{ tpl.name }}</span>
                                <span v-if="activeTemplateId === tpl.id" class="preview-tpl-check">
                                    <Check class="h-2.5 w-2.5 text-white" />
                                </span>
                            </button>
                        </div>
                    </div>

                    <!-- Aria live region for template change announcements -->
                    <div aria-live="polite" aria-atomic="true" class="sr-only">{{ templateAnnouncement }}</div>

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

/* ── Template strip ── */
.preview-template-strip {
    flex-shrink: 0;
    border-bottom: 1px solid var(--color-border);
    background: var(--color-card);
    padding: 0.5rem 0.75rem;
}

.preview-template-strip-inner {
    display: flex;
    gap: 0.5rem;
    overflow-x: auto;
    scrollbar-width: thin;
    padding-bottom: 2px;
}

.preview-tpl-thumb {
    position: relative;
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    border: none;
    background: transparent;
    cursor: pointer;
    padding: 2px;
    border-radius: 8px;
    transition: all 150ms;
    outline: none;
}

.preview-tpl-thumb:focus-visible {
    box-shadow: 0 0 0 2px var(--color-ring);
}

.preview-tpl-thumb-frame {
    width: 56px;
    height: 72px;
    overflow: hidden;
    border-radius: 6px;
    border: 2px solid transparent;
    transition: border-color 150ms;
    background: #f5f5f5;
}

.preview-tpl-thumb.is-active .preview-tpl-thumb-frame {
    border-color: oklch(0.6 0.14 185);
    box-shadow: 0 0 0 1px oklch(0.6 0.14 185 / 0.4);
}

.preview-tpl-thumb:not(.is-active):hover .preview-tpl-thumb-frame {
    border-color: var(--color-border);
}

/* Scale the 600×800 body down to fit the 56×72 thumbnail */
.preview-tpl-thumb-scale {
    width: 600px;
    height: 800px;
    transform: scale(0.09);
    transform-origin: top left;
    pointer-events: none;
}

.preview-tpl-name {
    font-size: 9px;
    font-weight: 500;
    color: var(--color-muted-foreground);
    max-width: 56px;
    text-align: center;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    line-height: 1.2;
}

.preview-tpl-thumb.is-active .preview-tpl-name {
    color: oklch(0.6 0.14 185);
}

.preview-tpl-check {
    position: absolute;
    top: 4px;
    right: 4px;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    background: oklch(0.6 0.14 185);
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
