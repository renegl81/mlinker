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
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/AppLayout.vue';
import { index as locationMenusRoute } from '@/routes/tenant/locations/menus';
import { destroy as menuRouteDestroy, edit as menuRouteEdit } from '@/routes/tenant/menus';
import type { BreadcrumbItem, Menu } from '@/types';
import { Inertia } from '@inertiajs/inertia';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import {
    ArrowDown,
    ArrowLeft,
    ArrowUp,
    BookOpen,
    Copy,
    Download,
    Globe,
    Image as ImageIcon,
    Pencil,
    Plus,
    QrCode as QrCodeIcon,
    RefreshCw,
    Trash2,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

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

interface Props {
    menu: MenuWithSections;
    qrCodeImageUrl: string | null;
}

const props = defineProps<Props>();
const page = usePage();
const messages = computed(() => page.props.messages as any);

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
function deleteQr() {
    if (!confirm('¿Eliminar el código QR de este menú?')) return;
    router.delete(`/panel/menus/${props.menu.id}/qr-code`, { preserveScroll: true });
}

// ── Menu delete ─────────────────────────────────────────────────────────────
function remove() {
    if (confirm(messages.value.menus.actions.confirm_delete)) {
        Inertia.delete(menuRouteDestroy(props.menu.id).url);
    }
}

// ── Products ────────────────────────────────────────────────────────────────
function deleteProduct(productId: number) {
    if (!confirm('¿Eliminar este plato? Esta acción no se puede deshacer.')) return;
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

function deleteSection(sectionId: number) {
    if (!confirm('¿Eliminar esta sección? Los platos no se borrarán.')) return;
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
                    <Button variant="outline" as-child>
                        <Link :href="`/panel/menus/${menu.id}/translations`">
                            <Globe class="mr-2 h-4 w-4" />
                            Traducciones
                        </Link>
                    </Button>
                    <Button variant="outline" as-child>
                        <Link :href="menuRouteEdit(menu.id).url">
                            <Pencil class="mr-2 h-4 w-4" />
                            {{ messages.menus.actions.edit }}
                        </Link>
                    </Button>
                    <Button variant="destructive" @click="remove">
                        <Trash2 class="mr-2 h-4 w-4" />
                        {{ messages.menus.actions.delete }}
                    </Button>
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
                                        <span class="text-xs">Sin imagen</span>
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
                                Secciones y platos
                            </CardTitle>
                            <Button size="sm" variant="outline" @click="showAddSection = !showAddSection">
                                <Plus class="mr-1 h-4 w-4" />
                                Añadir sección
                            </Button>
                        </CardHeader>
                        <CardContent>
                            <!-- Inline form: new section -->
                            <div
                                v-if="showAddSection"
                                class="mb-4 rounded-lg border border-dashed bg-muted/20 p-4"
                            >
                                <h4 class="mb-3 text-sm font-semibold">Nueva sección</h4>
                                <div class="space-y-3">
                                    <div class="space-y-1">
                                        <Label for="new_section_name">Nombre *</Label>
                                        <Input
                                            id="new_section_name"
                                            v-model="newSectionName"
                                            placeholder="Ej. Entrantes"
                                            @keydown.enter.prevent="submitAddSection"
                                        />
                                    </div>
                                    <div class="space-y-1">
                                        <Label for="new_section_desc">Descripción</Label>
                                        <Input
                                            id="new_section_desc"
                                            v-model="newSectionDescription"
                                            placeholder="Opcional..."
                                        />
                                    </div>
                                    <div class="flex gap-2">
                                        <Button size="sm" @click="submitAddSection">Guardar</Button>
                                        <Button size="sm" variant="outline" @click="showAddSection = false">Cancelar</Button>
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
                                                title="Subir sección"
                                            >
                                                <ArrowUp class="h-3 w-3" />
                                            </Button>
                                            <Button
                                                size="icon"
                                                variant="ghost"
                                                class="h-7 w-7"
                                                :disabled="sectionIdx === menu.sections.length - 1"
                                                @click="moveSection(menu.sections, section.id, 'down')"
                                                title="Bajar sección"
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
                                                    Añadir plato
                                                </Link>
                                            </Button>
                                            <!-- Edit section -->
                                            <Button
                                                size="icon"
                                                variant="ghost"
                                                class="h-7 w-7"
                                                @click="startEditSection(section)"
                                                title="Editar sección"
                                            >
                                                <Pencil class="h-3 w-3" />
                                            </Button>
                                            <!-- Delete section -->
                                            <Button
                                                size="icon"
                                                variant="ghost"
                                                class="h-7 w-7 text-destructive hover:text-destructive"
                                                @click="deleteSection(section.id)"
                                                title="Eliminar sección"
                                            >
                                                <Trash2 class="h-3 w-3" />
                                            </Button>
                                        </div>
                                    </div>

                                    <!-- Inline edit section form -->
                                    <div v-else class="rounded-lg border border-primary/30 bg-primary/5 p-3 mb-2">
                                        <div class="space-y-2">
                                            <Input v-model="editSectionName" placeholder="Nombre de la sección" />
                                            <Input v-model="editSectionDescription" placeholder="Descripción (opcional)" />
                                            <div class="flex gap-2">
                                                <Button size="sm" @click="submitEditSection(section)">Guardar</Button>
                                                <Button size="sm" variant="outline" @click="cancelEditSection">Cancelar</Button>
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
                                                    v-if="!product.image_url"
                                                    class="flex h-10 w-10 items-center justify-center rounded-md bg-gradient-to-br from-purple-500 to-pink-500 text-base font-bold text-white"
                                                >
                                                    {{ product.name.charAt(0).toUpperCase() }}
                                                </div>
                                                <img
                                                    v-else
                                                    :src="product.image_url"
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
                                                    <Link :href="`/panel/products/${product.id}/edit`" title="Editar plato">
                                                        <Pencil class="h-3 w-3" />
                                                    </Link>
                                                </Button>
                                                <!-- Delete -->
                                                <Button
                                                    size="icon"
                                                    variant="ghost"
                                                    class="h-7 w-7 text-destructive hover:text-destructive"
                                                    @click="deleteProduct(product.id)"
                                                    title="Eliminar plato"
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
                                        <span>Esta sección aún no tiene platos.</span>
                                        <Button size="sm" variant="ghost" class="h-7 px-2 text-xs" as-child>
                                            <Link :href="`/panel/menus/${menu.id}/products/create?section_id=${section.id}`">
                                                <Plus class="mr-1 h-3 w-3" />
                                                Añadir plato
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
                                    No hay secciones en este menú.
                                </p>
                                <Button size="sm" @click="showAddSection = true">
                                    <Plus class="mr-2 h-4 w-4" />
                                    Añadir primera sección
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
                                Código QR
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div
                                v-if="qrCodeImageUrl"
                                class="flex items-center justify-center rounded-lg border bg-white p-3"
                            >
                                <img
                                    :src="qrCodeImageUrl"
                                    alt="Código QR del menú"
                                    class="h-40 w-40 object-contain"
                                />
                            </div>
                            <div
                                v-else
                                class="flex h-40 flex-col items-center justify-center rounded-lg border-2 border-dashed text-muted-foreground"
                            >
                                <QrCodeIcon class="h-8 w-8 opacity-40" />
                                <span class="mt-2 text-xs">Sin QR generado</span>
                            </div>

                            <div class="flex flex-col gap-2">
                                <Button @click="generateQr" class="w-full" size="sm">
                                    <RefreshCw class="mr-2 h-4 w-4" />
                                    {{ qrCodeImageUrl ? 'Regenerar' : 'Generar QR' }}
                                </Button>
                                <Button
                                    v-if="qrCodeImageUrl"
                                    variant="outline"
                                    size="sm"
                                    class="w-full"
                                    @click="downloadQr"
                                >
                                    <Download class="mr-2 h-4 w-4" />
                                    Descargar PNG
                                </Button>
                                <Button
                                    v-if="qrCodeImageUrl"
                                    variant="ghost"
                                    size="sm"
                                    class="w-full text-destructive"
                                    @click="deleteQr"
                                >
                                    <Trash2 class="mr-2 h-4 w-4" />
                                    Eliminar QR
                                </Button>
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
</template>
