<script setup lang="ts">
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
import { Separator } from '@/components/ui/separator';
import { FONT_PAIRINGS, buildGoogleFontsUrl } from '@/config/fontPairings';
import TemplatePreview from '@/components/template-bodies/TemplatePreview.vue';
import type { MenuCustomization } from '@/components/template-bodies/TemplatePreview.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import {
    ArrowLeft,
    Check,
    ChevronDown,
    ChevronRight,
    Eye,
    ExternalLink,
    Layout,
    Lock,
    Monitor,
    Palette,
    RotateCcw,
    Smartphone,
    Type,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

interface TemplateOption {
    id: number;
    name: string;
    component_name: string;
}

interface Props {
    menu: {
        id: number;
        name: string;
        location_id: number;
        template_id: number;
    };
    publicMenuUrl: string | null;
    customization: Record<string, any>;
    templates: TemplateOption[];
}

const props = defineProps<Props>();
const page = usePage();

const planFeatures = computed(() => (page.props.tenant as any)?.plan_features ?? {});
const canColors = computed(() => !!planFeatures.value.menu_colors);
const canFonts = computed(() => !!planFeatures.value.menu_fonts);
const canLayout = computed(() => !!planFeatures.value.menu_layout);
const canAdvanced = computed(() => !!planFeatures.value.menu_advanced_style);

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    { title: props.menu.name, href: `/panel/menus/${props.menu.id}` },
    { title: t('panel.customizer.title'), href: '#' },
];

// ── Local state (mirrors customization, synced to backend on change) ──
const colors = ref({
    accent: props.customization?.colors?.accent ?? '',
    bg: props.customization?.colors?.bg ?? '',
    ink: props.customization?.colors?.ink ?? '',
    ink_soft: props.customization?.colors?.ink_soft ?? '',
    rule: props.customization?.colors?.rule ?? '',
});

const fonts = ref({
    display: props.customization?.fonts?.display ?? '',
    body: props.customization?.fonts?.body ?? '',
});

const layout = ref({
    show_allergens: props.customization?.layout?.show_allergens ?? true,
    show_ingredients: props.customization?.layout?.show_ingredients ?? true,
    show_product_images: props.customization?.layout?.show_product_images ?? true,
    show_section_descriptions: props.customization?.layout?.show_section_descriptions ?? true,
    image_position: props.customization?.layout?.image_position ?? 'left',
    price_alignment: props.customization?.layout?.price_alignment ?? 'right',
});

const spacing = ref({
    density: props.customization?.spacing?.density ?? 'comfortable',
});

const header = ref({
    show_restaurant_name: props.customization?.header?.show_restaurant_name ?? true,
    tagline: props.customization?.header?.tagline ?? '',
    name_display_style: props.customization?.header?.name_display_style ?? 'default',
});

const sections = ref({
    divider_style: props.customization?.sections?.divider_style ?? 'line',
    title_alignment: props.customization?.sections?.title_alignment ?? 'left',
    numbering: props.customization?.sections?.numbering ?? 'none',
});

// ── Template selection ──
const activeTemplateId = ref<number>(props.menu.template_id);
const isTemplatePatching = ref(false);

const currentComponentName = computed<string>(() => {
    const tpl = props.templates.find(t => t.id === activeTemplateId.value);
    return tpl?.component_name ?? props.templates[0]?.component_name ?? 'Basic';
});

const currentCustomization = computed<MenuCustomization>(() => ({
    colors: {
        accent: colors.value.accent || undefined,
        bg: colors.value.bg || undefined,
        ink: colors.value.ink || undefined,
        ink_soft: colors.value.ink_soft || undefined,
        rule: colors.value.rule || undefined,
    },
    fonts: {
        display: fonts.value.display || undefined,
        body: fonts.value.body || undefined,
    },
    layout: {
        show_allergens: layout.value.show_allergens,
        show_ingredients: layout.value.show_ingredients,
        show_product_images: layout.value.show_product_images,
        show_section_descriptions: layout.value.show_section_descriptions,
        image_position: layout.value.image_position,
        price_alignment: layout.value.price_alignment,
    },
    spacing: { density: spacing.value.density },
    header: {
        show_restaurant_name: header.value.show_restaurant_name,
        tagline: header.value.tagline || undefined,
        name_display_style: header.value.name_display_style,
    },
    sections: {
        divider_style: sections.value.divider_style,
        title_alignment: sections.value.title_alignment,
        numbering: sections.value.numbering,
    },
}));

function selectTemplate(tpl: TemplateOption) {
    if (tpl.id === activeTemplateId.value || isTemplatePatching.value) return;
    const previousId = activeTemplateId.value;
    activeTemplateId.value = tpl.id;
    isTemplatePatching.value = true;

    axios.patch(`/panel/menus/${props.menu.id}/inline`, { template_id: tpl.id })
        .catch(() => {
            activeTemplateId.value = previousId;
        })
        .finally(() => {
            isTemplatePatching.value = false;
        });
}

// ── Collapsible sections ──
const openSections = ref<Set<string>>(new Set(['template', 'colors']));
function toggleSection(id: string) {
    if (openSections.value.has(id)) {
        openSections.value.delete(id);
    } else {
        openSections.value.add(id);
    }
}
function isSectionOpen(id: string) {
    return openSections.value.has(id);
}

// ── Preview ──
const previewMode = ref<'mobile' | 'desktop'>('mobile');

// ── Save state ──
const saveStatus = ref<'idle' | 'saving' | 'saved'>('idle');
let saveTimeout: ReturnType<typeof setTimeout> | null = null;
let savedTimeout: ReturnType<typeof setTimeout> | null = null;

function buildPayload(): Record<string, any> {
    const payload: Record<string, any> = {};

    // Only include non-empty values
    const c = colors.value;
    const colorsObj: Record<string, string> = {};
    if (c.accent) colorsObj.accent = c.accent;
    if (c.bg) colorsObj.bg = c.bg;
    if (c.ink) colorsObj.ink = c.ink;
    if (c.ink_soft) colorsObj.ink_soft = c.ink_soft;
    if (c.rule) colorsObj.rule = c.rule;
    if (Object.keys(colorsObj).length) payload.colors = colorsObj;

    const f = fonts.value;
    const fontsObj: Record<string, string> = {};
    if (f.display) fontsObj.display = f.display;
    if (f.body) fontsObj.body = f.body;
    if (Object.keys(fontsObj).length) payload.fonts = fontsObj;

    // Layout — only include non-default values
    const l = layout.value;
    const layoutObj: Record<string, any> = {};
    if (!l.show_allergens) layoutObj.show_allergens = false;
    if (!l.show_ingredients) layoutObj.show_ingredients = false;
    if (!l.show_product_images) layoutObj.show_product_images = false;
    if (!l.show_section_descriptions) layoutObj.show_section_descriptions = false;
    if (l.image_position !== 'left') layoutObj.image_position = l.image_position;
    if (l.price_alignment !== 'right') layoutObj.price_alignment = l.price_alignment;
    if (Object.keys(layoutObj).length) payload.layout = layoutObj;

    // Spacing
    if (spacing.value.density !== 'comfortable') {
        payload.spacing = { density: spacing.value.density };
    }

    // Header
    const h = header.value;
    const headerObj: Record<string, any> = {};
    if (!h.show_restaurant_name) headerObj.show_restaurant_name = false;
    if (h.tagline) headerObj.tagline = h.tagline;
    if (h.name_display_style !== 'default') headerObj.name_display_style = h.name_display_style;
    if (Object.keys(headerObj).length) payload.header = headerObj;

    // Sections
    const s = sections.value;
    const sectionsObj: Record<string, any> = {};
    if (s.divider_style !== 'line') sectionsObj.divider_style = s.divider_style;
    if (s.title_alignment !== 'left') sectionsObj.title_alignment = s.title_alignment;
    if (s.numbering !== 'none') sectionsObj.numbering = s.numbering;
    if (Object.keys(sectionsObj).length) payload.sections = sectionsObj;

    return payload;
}

function save() {
    if (saveTimeout) clearTimeout(saveTimeout);
    if (savedTimeout) clearTimeout(savedTimeout);

    saveStatus.value = 'saving';

    saveTimeout = setTimeout(() => {
        router.patch(`/panel/menus/${props.menu.id}/customization`, {
            customization: buildPayload(),
        }, {
            preserveScroll: true,
            onSuccess: () => {
                saveStatus.value = 'saved';
                savedTimeout = setTimeout(() => {
                    saveStatus.value = 'idle';
                }, 2000);
            },
            onError: () => {
                saveStatus.value = 'idle';
            },
        });
    }, 800);
}

// Auto-save on any change
watch([colors, fonts, layout, spacing, header, sections], save, { deep: true });

// ── Reset dialog ──
const showResetDialog = ref(false);

function confirmReset() {
    showResetDialog.value = false;
    router.delete(`/panel/menus/${props.menu.id}/customization`, {
        preserveScroll: true,
        onSuccess: () => {
            // Reset local state
            colors.value = { accent: '', bg: '', ink: '', ink_soft: '', rule: '' };
            fonts.value = { display: '', body: '' };
            layout.value = { show_allergens: true, show_ingredients: true, show_product_images: true, show_section_descriptions: true, image_position: 'left', price_alignment: 'right' };
            spacing.value = { density: 'comfortable' };
            header.value = { show_restaurant_name: true, tagline: '', name_display_style: 'default' };
            sections.value = { divider_style: 'line', title_alignment: 'left', numbering: 'none' };
        },
    });
}

// ── Font pairing selection ──
function selectFontPairing(pairing: typeof FONT_PAIRINGS[0]) {
    fonts.value.display = pairing.display;
    fonts.value.body = pairing.body;
}

const selectedPairingId = computed(() => {
    return FONT_PAIRINGS.find(
        p => p.display === fonts.value.display && p.body === fonts.value.body
    )?.id ?? null;
});

// Preload font for preview
const preloadedFonts = ref<Set<string>>(new Set());
function preloadFont(pairing: typeof FONT_PAIRINGS[0]) {
    if (preloadedFonts.value.has(pairing.id)) return;
    const url = buildGoogleFontsUrl([pairing.display, pairing.body]);
    if (url) {
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = url;
        document.head.appendChild(link);
        preloadedFonts.value.add(pairing.id);
    }
}
</script>

<template>
    <Head :title="t('panel.customizer.title')" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-[calc(100vh-3.5rem)] overflow-hidden">

            <!-- ═══════ LEFT SIDEBAR ═══════ -->
            <aside class="w-[380px] flex-shrink-0 overflow-y-auto border-r border-border bg-card p-4 space-y-1">

                <!-- Header with back + status -->
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2">
                        <Button variant="ghost" size="icon" as-child>
                            <Link :href="`/panel/menus/${menu.id}`">
                                <ArrowLeft class="h-4 w-4" />
                            </Link>
                        </Button>
                        <div>
                            <h1 class="text-sm font-semibold">{{ t('panel.customizer.title') }}</h1>
                            <p class="text-xs text-muted-foreground truncate max-w-[240px]">{{ menu.name }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span
                            v-if="saveStatus === 'saving'"
                            class="text-xs text-muted-foreground animate-pulse"
                        >{{ t('panel.customizer.saving') }}</span>
                        <span
                            v-else-if="saveStatus === 'saved'"
                            class="flex items-center gap-1 text-xs text-green-600"
                        >
                            <Check class="h-3 w-3" />
                            {{ t('panel.customizer.saved') }}
                        </span>
                    </div>
                </div>

                <Separator />

                <!-- ── TEMPLATE SECTION ── -->
                <div class="pt-2" v-if="templates.length > 0">
                    <button
                        type="button"
                        class="flex w-full items-center justify-between py-2 text-sm font-medium text-foreground hover:text-foreground transition-colors"
                        @click="toggleSection('template')"
                    >
                        <span class="flex items-center gap-2">
                            <Layout class="h-4 w-4" />
                            {{ t('panel.customizer.template') }}
                        </span>
                        <ChevronDown v-if="isSectionOpen('template')" class="h-4 w-4" />
                        <ChevronRight v-else class="h-4 w-4" />
                    </button>

                    <div v-if="isSectionOpen('template')" class="pb-3">
                        <div
                            class="grid grid-cols-2 gap-2"
                            role="radiogroup"
                            :aria-label="t('panel.customizer.template')"
                        >
                            <button
                                v-for="tpl in templates"
                                :key="tpl.id"
                                type="button"
                                role="radio"
                                :aria-checked="activeTemplateId === tpl.id"
                                class="relative flex flex-col overflow-hidden rounded-lg border-2 text-left transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2"
                                :class="activeTemplateId === tpl.id
                                    ? 'border-primary bg-primary/5 shadow-sm'
                                    : 'border-input hover:border-primary/40'"
                                @click="selectTemplate(tpl)"
                            >
                                <!-- Live mini preview -->
                                <div class="relative h-20 w-full overflow-hidden bg-muted/50" aria-hidden="true">
                                    <div
                                        class="pointer-events-none absolute left-0 top-0"
                                        style="width: 600px; height: 800px; transform: scale(0.165); transform-origin: top left;"
                                    >
                                        <TemplatePreview :component-name="tpl.component_name" />
                                    </div>
                                </div>
                                <div class="px-2 py-1.5 flex items-center justify-between gap-1">
                                    <span class="text-[11px] font-medium leading-tight truncate">{{ tpl.name }}</span>
                                    <Check
                                        v-if="activeTemplateId === tpl.id"
                                        class="h-3 w-3 flex-shrink-0 text-primary"
                                    />
                                </div>
                            </button>
                        </div>
                    </div>
                </div>

                <Separator />

                <!-- ── COLORS SECTION ── -->
                <div class="pt-2">
                    <button
                        type="button"
                        class="flex w-full items-center justify-between py-2 text-sm font-medium hover:text-foreground transition-colors"
                        :class="canColors ? 'text-foreground' : 'text-muted-foreground'"
                        @click="toggleSection('colors')"
                    >
                        <span class="flex items-center gap-2">
                            <Palette class="h-4 w-4" />
                            {{ t('panel.customizer.colors') }}
                            <Lock v-if="!canColors" class="h-3 w-3 text-muted-foreground" />
                        </span>
                        <ChevronDown v-if="isSectionOpen('colors')" class="h-4 w-4" />
                        <ChevronRight v-else class="h-4 w-4" />
                    </button>

                    <div v-if="isSectionOpen('colors')" class="pb-3 space-y-3">
                        <p class="text-xs text-muted-foreground">{{ t('panel.customizer.colors_desc') }}</p>

                        <template v-if="canColors">
                            <div v-for="(label, key) in { accent: 'accent', bg: 'background', ink: 'text_color', ink_soft: 'text_secondary', rule: 'dividers' }" :key="key" class="flex items-center gap-3">
                                <input
                                    type="color"
                                    :value="colors[key as keyof typeof colors] || '#000000'"
                                    @input="colors[key as keyof typeof colors] = ($event.target as HTMLInputElement).value"
                                    class="h-8 w-10 cursor-pointer rounded-lg border border-input p-0.5"
                                />
                                <div class="flex-1 min-w-0">
                                    <Label class="text-xs">{{ t(`panel.customizer.${label}`) }}</Label>
                                    <Input
                                        :model-value="colors[key as keyof typeof colors]"
                                        @update:model-value="colors[key as keyof typeof colors] = $event as string"
                                        placeholder="#000000"
                                        class="h-7 text-xs mt-0.5 font-mono"
                                    />
                                </div>
                            </div>
                        </template>
                        <div v-else class="rounded-lg border border-dashed p-3 text-center">
                            <Lock class="mx-auto h-5 w-5 text-muted-foreground mb-1" />
                            <p class="text-xs text-muted-foreground">{{ t('panel.customizer.upgrade_required', { plan: 'Pro' }) }}</p>
                            <Button variant="link" size="sm" as-child class="mt-1 h-auto p-0 text-xs">
                                <Link href="/panel/billing/plans">{{ t('panel.billing.view_plans') }}</Link>
                            </Button>
                        </div>
                    </div>
                </div>

                <Separator />

                <!-- ── FONTS SECTION ── -->
                <div class="pt-2">
                    <button
                        type="button"
                        class="flex w-full items-center justify-between py-2 text-sm font-medium hover:text-foreground transition-colors"
                        :class="canFonts ? 'text-foreground' : 'text-muted-foreground'"
                        @click="toggleSection('fonts')"
                    >
                        <span class="flex items-center gap-2">
                            <Type class="h-4 w-4" />
                            {{ t('panel.customizer.fonts') }}
                            <Lock v-if="!canFonts" class="h-3 w-3 text-muted-foreground" />
                        </span>
                        <ChevronDown v-if="isSectionOpen('fonts')" class="h-4 w-4" />
                        <ChevronRight v-else class="h-4 w-4" />
                    </button>

                    <div v-if="isSectionOpen('fonts')" class="pb-3 space-y-3">
                        <p class="text-xs text-muted-foreground">{{ t('panel.customizer.fonts_desc') }}</p>

                        <template v-if="canFonts">
                            <p class="text-xs font-medium text-muted-foreground uppercase tracking-wider">{{ t('panel.customizer.font_pairing') }}</p>
                            <div class="grid grid-cols-2 gap-2">
                                <button
                                    v-for="pairing in FONT_PAIRINGS"
                                    :key="pairing.id"
                                    type="button"
                                    class="relative rounded-lg border p-2.5 text-left transition-all hover:border-primary/50 hover:shadow-sm"
                                    :class="selectedPairingId === pairing.id ? 'border-primary ring-2 ring-primary/20 bg-primary/5' : 'border-input'"
                                    @click="selectFontPairing(pairing)"
                                    @mouseenter="preloadFont(pairing)"
                                >
                                    <div class="text-[13px] font-semibold leading-tight truncate" :style="{ fontFamily: `'${pairing.display}', serif` }">
                                        {{ pairing.name }}
                                    </div>
                                    <div class="mt-0.5 text-[10px] text-muted-foreground truncate" :style="{ fontFamily: `'${pairing.body}', sans-serif` }">
                                        {{ pairing.display }} + {{ pairing.body }}
                                    </div>
                                    <Check v-if="selectedPairingId === pairing.id" class="absolute top-1.5 right-1.5 h-3.5 w-3.5 text-primary" />
                                </button>
                            </div>
                        </template>
                        <div v-else class="rounded-lg border border-dashed p-3 text-center">
                            <Lock class="mx-auto h-5 w-5 text-muted-foreground mb-1" />
                            <p class="text-xs text-muted-foreground">{{ t('panel.customizer.upgrade_required', { plan: 'Pro' }) }}</p>
                            <Button variant="link" size="sm" as-child class="mt-1 h-auto p-0 text-xs">
                                <Link href="/panel/billing/plans">{{ t('panel.billing.view_plans') }}</Link>
                            </Button>
                        </div>
                    </div>
                </div>

                <Separator />

                <!-- ── LAYOUT SECTION ── -->
                <div class="pt-2">
                    <button
                        type="button"
                        class="flex w-full items-center justify-between py-2 text-sm font-medium hover:text-foreground transition-colors"
                        :class="canLayout ? 'text-foreground' : 'text-muted-foreground'"
                        @click="toggleSection('layout')"
                    >
                        <span class="flex items-center gap-2">
                            <Eye class="h-4 w-4" />
                            {{ t('panel.customizer.layout') }}
                            <Lock v-if="!canLayout" class="h-3 w-3 text-muted-foreground" />
                        </span>
                        <ChevronDown v-if="isSectionOpen('layout')" class="h-4 w-4" />
                        <ChevronRight v-else class="h-4 w-4" />
                    </button>

                    <div v-if="isSectionOpen('layout')" class="pb-3 space-y-3">
                        <p class="text-xs text-muted-foreground">{{ t('panel.customizer.layout_desc') }}</p>

                        <template v-if="canLayout">
                            <!-- Toggles -->
                            <div v-for="(label, key) in { show_allergens: 'show_allergens', show_ingredients: 'show_ingredients', show_product_images: 'show_images', show_section_descriptions: 'show_section_desc' }" :key="key" class="flex items-center justify-between">
                                <Label class="text-xs">{{ t(`panel.customizer.${label}`) }}</Label>
                                <button
                                    type="button"
                                    class="relative h-5 w-9 rounded-full transition-colors"
                                    :class="layout[key as keyof typeof layout] ? 'bg-primary' : 'bg-input'"
                                    @click="(layout as any)[key] = !(layout as any)[key]"
                                >
                                    <span
                                        class="absolute top-0.5 left-0.5 h-4 w-4 rounded-full bg-white shadow transition-transform"
                                        :class="(layout as any)[key] ? 'translate-x-4' : 'translate-x-0'"
                                    />
                                </button>
                            </div>

                            <Separator />

                            <!-- Image position -->
                            <div class="space-y-1.5">
                                <Label class="text-xs">{{ t('panel.customizer.image_position') }}</Label>
                                <div class="grid grid-cols-4 gap-1">
                                    <button
                                        v-for="pos in (['left', 'right', 'top', 'hidden'] as const)"
                                        :key="pos"
                                        type="button"
                                        class="rounded-md border px-2 py-1.5 text-[10px] font-medium transition-all"
                                        :class="layout.image_position === pos ? 'border-primary bg-primary/10 text-primary' : 'border-input hover:border-primary/30'"
                                        @click="layout.image_position = pos"
                                    >
                                        {{ t(`panel.customizer.image_${pos}`) }}
                                    </button>
                                </div>
                            </div>

                            <!-- Price alignment -->
                            <div class="space-y-1.5">
                                <Label class="text-xs">{{ t('panel.customizer.price_alignment') }}</Label>
                                <div class="grid grid-cols-2 gap-1">
                                    <button
                                        v-for="align in (['right', 'inline'] as const)"
                                        :key="align"
                                        type="button"
                                        class="rounded-md border px-2 py-1.5 text-[10px] font-medium transition-all"
                                        :class="layout.price_alignment === align ? 'border-primary bg-primary/10 text-primary' : 'border-input hover:border-primary/30'"
                                        @click="layout.price_alignment = align"
                                    >
                                        {{ t(`panel.customizer.price_${align}`) }}
                                    </button>
                                </div>
                            </div>
                        </template>
                        <div v-else class="rounded-lg border border-dashed p-3 text-center">
                            <Lock class="mx-auto h-5 w-5 text-muted-foreground mb-1" />
                            <p class="text-xs text-muted-foreground">{{ t('panel.customizer.upgrade_required', { plan: 'Pro' }) }}</p>
                        </div>
                    </div>
                </div>

                <Separator />

                <!-- ── SPACING SECTION (Business) ── -->
                <div class="pt-2">
                    <button
                        type="button"
                        class="flex w-full items-center justify-between py-2 text-sm font-medium hover:text-foreground transition-colors"
                        :class="canAdvanced ? 'text-foreground' : 'text-muted-foreground'"
                        @click="toggleSection('spacing')"
                    >
                        <span class="flex items-center gap-2">
                            {{ t('panel.customizer.spacing') }}
                            <Lock v-if="!canAdvanced" class="h-3 w-3 text-muted-foreground" />
                        </span>
                        <ChevronDown v-if="isSectionOpen('spacing')" class="h-4 w-4" />
                        <ChevronRight v-else class="h-4 w-4" />
                    </button>

                    <div v-if="isSectionOpen('spacing')" class="pb-3 space-y-3">
                        <template v-if="canAdvanced">
                            <p class="text-xs text-muted-foreground">{{ t('panel.customizer.spacing_desc') }}</p>
                            <div class="grid grid-cols-3 gap-1">
                                <button
                                    v-for="d in (['compact', 'comfortable', 'spacious'] as const)"
                                    :key="d"
                                    type="button"
                                    class="rounded-md border px-2 py-2 text-[10px] font-medium transition-all"
                                    :class="spacing.density === d ? 'border-primary bg-primary/10 text-primary' : 'border-input hover:border-primary/30'"
                                    @click="spacing.density = d"
                                >
                                    {{ t(`panel.customizer.density_${d}`) }}
                                </button>
                            </div>
                        </template>
                        <div v-else class="rounded-lg border border-dashed p-3 text-center">
                            <Lock class="mx-auto h-5 w-5 text-muted-foreground mb-1" />
                            <p class="text-xs text-muted-foreground">{{ t('panel.customizer.upgrade_required', { plan: 'Business' }) }}</p>
                        </div>
                    </div>
                </div>

                <Separator />

                <!-- ── HEADER SECTION (Business) ── -->
                <div class="pt-2">
                    <button
                        type="button"
                        class="flex w-full items-center justify-between py-2 text-sm font-medium hover:text-foreground transition-colors"
                        :class="canAdvanced ? 'text-foreground' : 'text-muted-foreground'"
                        @click="toggleSection('header')"
                    >
                        <span class="flex items-center gap-2">
                            {{ t('panel.customizer.header_section') }}
                            <Lock v-if="!canAdvanced" class="h-3 w-3 text-muted-foreground" />
                        </span>
                        <ChevronDown v-if="isSectionOpen('header')" class="h-4 w-4" />
                        <ChevronRight v-else class="h-4 w-4" />
                    </button>

                    <div v-if="isSectionOpen('header')" class="pb-3 space-y-3">
                        <template v-if="canAdvanced">
                            <p class="text-xs text-muted-foreground">{{ t('panel.customizer.header_desc') }}</p>

                            <!-- Show restaurant name -->
                            <div class="flex items-center justify-between">
                                <Label class="text-xs">{{ t('panel.customizer.show_name') }}</Label>
                                <button
                                    type="button"
                                    class="relative h-5 w-9 rounded-full transition-colors"
                                    :class="header.show_restaurant_name ? 'bg-primary' : 'bg-input'"
                                    @click="header.show_restaurant_name = !header.show_restaurant_name"
                                >
                                    <span
                                        class="absolute top-0.5 left-0.5 h-4 w-4 rounded-full bg-white shadow transition-transform"
                                        :class="header.show_restaurant_name ? 'translate-x-4' : 'translate-x-0'"
                                    />
                                </button>
                            </div>

                            <!-- Name display style -->
                            <div class="space-y-1.5">
                                <Label class="text-xs">{{ t('panel.customizer.name_style') }}</Label>
                                <div class="grid grid-cols-3 gap-1">
                                    <button
                                        v-for="style in (['default', 'uppercase', 'italic'] as const)"
                                        :key="style"
                                        type="button"
                                        class="rounded-md border px-2 py-1.5 text-[10px] font-medium transition-all"
                                        :class="header.name_display_style === style ? 'border-primary bg-primary/10 text-primary' : 'border-input hover:border-primary/30'"
                                        @click="header.name_display_style = style"
                                    >
                                        {{ t(`panel.customizer.name_${style}`) }}
                                    </button>
                                </div>
                            </div>

                            <!-- Tagline -->
                            <div class="space-y-1.5">
                                <Label class="text-xs">{{ t('panel.customizer.tagline') }}</Label>
                                <Input
                                    v-model="header.tagline"
                                    :placeholder="t('panel.customizer.tagline_placeholder')"
                                    class="h-8 text-xs"
                                />
                            </div>
                        </template>
                        <div v-else class="rounded-lg border border-dashed p-3 text-center">
                            <Lock class="mx-auto h-5 w-5 text-muted-foreground mb-1" />
                            <p class="text-xs text-muted-foreground">{{ t('panel.customizer.upgrade_required', { plan: 'Business' }) }}</p>
                        </div>
                    </div>
                </div>

                <Separator />

                <!-- ── SECTIONS STYLE (Business) ── -->
                <div class="pt-2">
                    <button
                        type="button"
                        class="flex w-full items-center justify-between py-2 text-sm font-medium hover:text-foreground transition-colors"
                        :class="canAdvanced ? 'text-foreground' : 'text-muted-foreground'"
                        @click="toggleSection('sections')"
                    >
                        <span class="flex items-center gap-2">
                            {{ t('panel.customizer.sections_style') }}
                            <Lock v-if="!canAdvanced" class="h-3 w-3 text-muted-foreground" />
                        </span>
                        <ChevronDown v-if="isSectionOpen('sections')" class="h-4 w-4" />
                        <ChevronRight v-else class="h-4 w-4" />
                    </button>

                    <div v-if="isSectionOpen('sections')" class="pb-3 space-y-3">
                        <template v-if="canAdvanced">
                            <p class="text-xs text-muted-foreground">{{ t('panel.customizer.sections_desc') }}</p>

                            <!-- Divider style -->
                            <div class="space-y-1.5">
                                <Label class="text-xs">{{ t('panel.customizer.divider_style') }}</Label>
                                <div class="grid grid-cols-3 gap-1">
                                    <button
                                        v-for="style in (['line', 'ornament', 'none'] as const)"
                                        :key="style"
                                        type="button"
                                        class="rounded-md border px-2 py-1.5 text-[10px] font-medium transition-all"
                                        :class="sections.divider_style === style ? 'border-primary bg-primary/10 text-primary' : 'border-input hover:border-primary/30'"
                                        @click="sections.divider_style = style"
                                    >
                                        {{ t(`panel.customizer.divider_${style}`) }}
                                    </button>
                                </div>
                            </div>

                            <!-- Title alignment -->
                            <div class="space-y-1.5">
                                <Label class="text-xs">{{ t('panel.customizer.title_alignment') }}</Label>
                                <div class="grid grid-cols-2 gap-1">
                                    <button
                                        v-for="align in (['left', 'center'] as const)"
                                        :key="align"
                                        type="button"
                                        class="rounded-md border px-2 py-1.5 text-[10px] font-medium transition-all"
                                        :class="sections.title_alignment === align ? 'border-primary bg-primary/10 text-primary' : 'border-input hover:border-primary/30'"
                                        @click="sections.title_alignment = align"
                                    >
                                        {{ t(`panel.customizer.align_${align}`) }}
                                    </button>
                                </div>
                            </div>

                            <!-- Numbering -->
                            <div class="space-y-1.5">
                                <Label class="text-xs">{{ t('panel.customizer.numbering') }}</Label>
                                <div class="grid grid-cols-3 gap-1">
                                    <button
                                        v-for="num in (['none', 'roman', 'arabic'] as const)"
                                        :key="num"
                                        type="button"
                                        class="rounded-md border px-2 py-1.5 text-[10px] font-medium transition-all"
                                        :class="sections.numbering === num ? 'border-primary bg-primary/10 text-primary' : 'border-input hover:border-primary/30'"
                                        @click="sections.numbering = num"
                                    >
                                        {{ t(`panel.customizer.number_${num}`) }}
                                    </button>
                                </div>
                            </div>
                        </template>
                        <div v-else class="rounded-lg border border-dashed p-3 text-center">
                            <Lock class="mx-auto h-5 w-5 text-muted-foreground mb-1" />
                            <p class="text-xs text-muted-foreground">{{ t('panel.customizer.upgrade_required', { plan: 'Business' }) }}</p>
                        </div>
                    </div>
                </div>

                <Separator />

                <!-- Reset button -->
                <div class="pt-3 pb-6">
                    <Button variant="ghost" class="w-full text-destructive hover:bg-destructive/10 hover:text-destructive" @click="showResetDialog = true">
                        <RotateCcw class="mr-2 h-4 w-4" />
                        {{ t('panel.customizer.reset') }}
                    </Button>
                </div>
            </aside>

            <!-- ═══════ RIGHT PREVIEW ═══════ -->
            <div class="flex-1 flex flex-col min-w-0">
                <!-- Preview toolbar -->
                <div class="flex items-center justify-between border-b border-border px-4 py-2 bg-muted/30 flex-shrink-0">
                    <span class="text-xs font-medium text-muted-foreground">{{ t('panel.customizer.preview') }}</span>
                    <div class="flex items-center gap-1">
                        <div class="flex items-center gap-0.5 rounded-lg bg-muted p-0.5 mr-2">
                            <button
                                type="button"
                                class="rounded-md px-2 py-1 text-xs transition-all"
                                :class="previewMode === 'mobile' ? 'bg-card text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'"
                                @click="previewMode = 'mobile'"
                            >
                                <Smartphone class="h-3.5 w-3.5" />
                            </button>
                            <button
                                type="button"
                                class="rounded-md px-2 py-1 text-xs transition-all"
                                :class="previewMode === 'desktop' ? 'bg-card text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'"
                                @click="previewMode = 'desktop'"
                            >
                                <Monitor class="h-3.5 w-3.5" />
                            </button>
                        </div>
                        <a v-if="publicMenuUrl" :href="publicMenuUrl" target="_blank" rel="noopener" class="rounded-md p-1.5 text-muted-foreground hover:bg-muted hover:text-foreground transition-all" :title="t('panel.menu_show.open_new_tab')">
                            <ExternalLink class="h-3.5 w-3.5" />
                        </a>
                    </div>
                </div>

                <!-- Live preview (replaces iframe) -->
                <div class="flex-1 flex items-start justify-center overflow-auto bg-muted/50 p-6">
                    <div
                        class="transition-all duration-400 overflow-hidden bg-white flex-shrink-0"
                        :class="{
                            'w-[375px] rounded-[2rem] shadow-[0_0_0_8px_oklch(0.25_0.01_260),0_20px_60px_-20px_oklch(0_0_0/0.35)]': previewMode === 'mobile',
                            'w-full rounded-xl shadow-[0_8px_30px_-12px_oklch(0_0_0/0.2)]': previewMode === 'desktop',
                        }"
                    >
                        <TemplatePreview
                            :component-name="currentComponentName"
                            :menu="props.menu as any"
                            :customization="currentCustomization"
                            :interactive="false"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Reset confirmation dialog -->
        <Dialog v-model:open="showResetDialog">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2">
                        <RotateCcw class="h-5 w-5 text-destructive" />
                        {{ t('panel.customizer.reset') }}
                    </DialogTitle>
                    <DialogDescription>
                        {{ t('panel.customizer.reset_confirm') }}
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter class="gap-2 sm:gap-0">
                    <Button variant="outline" @click="showResetDialog = false">
                        {{ t('common.cancel') }}
                    </Button>
                    <Button variant="destructive" @click="confirmReset">
                        {{ t('panel.customizer.reset') }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
