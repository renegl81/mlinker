<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Check, ChevronDown, Save, X } from 'lucide-vue-next';
import { index as locationsRoute } from '@/routes/tenant/locations';
import type { Country, LocationImage, OpeningHour } from '@/types';
import { cn } from '@/lib/utils';
import {
    SelectContent,
    SelectIcon,
    SelectItem,
    SelectItemIndicator,
    SelectItemText,
    SelectPortal,
    SelectRoot,
    SelectTrigger,
    SelectValue,
    SelectViewport,
} from 'reka-ui';

interface OpeningHourEntry {
    weekday: number;
    opens_at: string;
    closes_at: string;
    is_closed: boolean;
}

interface Props {
    form: {
        name: string;
        address: string;
        phone: string;
        city: string;
        province: string;
        postal_code: string;
        currency: string;
        time_zone: string;
        description?: string;
        country_id?: number;
        latitude?: string;
        longitude?: string;
        image_url?: string | null;
        primary_color?: string | null;
        secondary_color?: string | null;
        order_email?: string | null;
        order_whatsapp?: string | null;
        is_pet_friendly?: boolean;
        has_wifi?: boolean;
        has_terrace?: boolean;
        has_parking?: boolean;
        is_accessible?: boolean;
        reservation_url?: string | null;
        reservation_phone?: string | null;
        instagram?: string | null;
        facebook?: string | null;
        google_maps_url?: string | null;
        opening_hours?: OpeningHourEntry[];
        [key: string]: any;
        errors: Record<string, string>;
        processing: boolean;
    };
    title?: string;
    description?: string;
    submitText?: string;
    processingText?: string;
    isEdit?: boolean;
    countries: Country[];
    locationId?: number;
    images?: LocationImage[];
    existingOpeningHours?: OpeningHour[];
}

const props = withDefaults(defineProps<Props>(), {
    isEdit: false,
});

const emit = defineEmits<{
    submit: [];
    'update:field': [field: string, value: any];
}>();

const page = usePage();
const messages = page.props.messages as any;

// ── Tabs ──
const tabs = computed(() => [
    { id: 'general', label: t('panel.locations.tab_general'), icon: '📍' },
    { id: 'schedule', label: t('panel.locations.tab_schedule'), icon: '🕐' },
    { id: 'contact', label: t('panel.locations.tab_contact'), icon: '📞' },
    { id: 'appearance', label: t('panel.locations.tab_appearance'), icon: '🎨' },
]);
const activeTab = ref('general');

// ── Image preview ──
const imagePreview = computed(() => {
    const val = props.form.image_url;
    if (!val || typeof val !== 'string') return null;
    if (val.startsWith('data:') || val.startsWith('http')) return val;
    return null;
});

function handleFileChange(event: Event) {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = () => {
        emit('update:field', 'image_url', reader.result as string);
    };
    reader.readAsDataURL(file);
}

function removeImage() {
    emit('update:field', 'image_url', null);
    const input = document.getElementById('location_image') as HTMLInputElement | null;
    if (input) input.value = '';
}

// ── Opening hours ──
const DEFAULT_OPENS_AT = '09:00';
const DEFAULT_CLOSES_AT = '22:00';

function buildDefaultHours(): OpeningHourEntry[] {
    return Array.from({ length: 7 }, (_, idx) => ({
        weekday: idx,
        opens_at: DEFAULT_OPENS_AT,
        closes_at: DEFAULT_CLOSES_AT,
        is_closed: false,
    }));
}

function initOpeningHours(): OpeningHourEntry[] {
    const defaults = buildDefaultHours();
    const existing = props.existingOpeningHours ?? [];
    if (existing.length === 0) return defaults;
    return defaults.map((def) => {
        const found = existing.find((h) => h.weekday === def.weekday);
        if (!found) return def;
        return {
            weekday: found.weekday,
            opens_at: found.opens_at ?? DEFAULT_OPENS_AT,
            closes_at: found.closes_at ?? DEFAULT_CLOSES_AT,
            is_closed: found.is_closed,
        };
    });
}

const openingHours = ref<OpeningHourEntry[]>(initOpeningHours());

watch(openingHours, (val) => { emit('update:field', 'opening_hours', val); }, { deep: true });

const weekdayLabels = computed(() => [
    t('panel.locations.monday'), t('panel.locations.tuesday'), t('panel.locations.wednesday'),
    t('panel.locations.thursday'), t('panel.locations.friday'), t('panel.locations.saturday'),
    t('panel.locations.sunday'),
]);
const weekdays = computed(() => weekdayLabels.value.map((label, idx) => ({ label, idx })));

// ── Amenities ──
const amenities = computed(() => [
    { key: 'is_pet_friendly', icon: '🐾', label: t('panel.locations.pet_friendly') },
    { key: 'has_wifi', icon: '📶', label: t('panel.locations.wifi') },
    { key: 'has_terrace', icon: '☀️', label: t('panel.locations.terrace') },
    { key: 'has_parking', icon: '🅿️', label: t('panel.locations.parking') },
    { key: 'is_accessible', icon: '♿', label: t('panel.locations.accessible') },
]);

// ── Gallery ──
const galleryImages = ref<LocationImage[]>(props.images ?? []);
watch(() => props.images, (val) => { galleryImages.value = val ?? []; });

function uploadGalleryImage(event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0];
    if (!file) return;
    router.post(`/panel/locations/${props.locationId}/images`, { image: file }, { forceFormData: true, preserveScroll: true });
}

function deleteGalleryImage(imageId: number) {
    router.delete(`/panel/location-images/${imageId}`, { preserveScroll: true });
}

// ── Computed labels ──
const title = computed(() => props.title || (props.isEdit ? messages.locations.form.title_edit : messages.locations.form.title_create));
const description = computed(() => props.description || (props.isEdit ? messages.locations.form.description_edit : messages.locations.form.description_create));
const submitText = computed(() => props.submitText || messages.locations.actions.save);
const processingText = computed(() => props.processingText || messages.locations.actions.saving);
</script>

<template>
    <Card class="my-5">
        <form @submit.prevent="$emit('submit')">
            <CardHeader>
                <CardTitle>{{ title }}</CardTitle>
                <CardDescription>{{ description }}</CardDescription>
            </CardHeader>

            <!-- Tab navigation -->
            <div class="border-b border-border px-6">
                <nav class="-mb-px flex gap-1 overflow-x-auto" role="tablist">
                    <button
                        v-for="tab in tabs"
                        :key="tab.id"
                        type="button"
                        role="tab"
                        :aria-selected="activeTab === tab.id"
                        class="flex shrink-0 items-center gap-1.5 border-b-2 px-3 py-2.5 text-sm font-medium transition-colors whitespace-nowrap"
                        :class="activeTab === tab.id
                            ? 'border-primary text-primary'
                            : 'border-transparent text-muted-foreground hover:text-foreground hover:border-border'"
                        @click="activeTab = tab.id"
                    >
                        <span class="text-base">{{ tab.icon }}</span>
                        <span class="hidden sm:inline">{{ tab.label }}</span>
                    </button>
                </nav>
            </div>

            <CardContent class="pt-6">
                <!-- ═══ TAB 1: General ═══ -->
                <div v-show="activeTab === 'general'" class="space-y-5">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="name">{{ messages.locations.fields.name }}</Label>
                            <Input id="name" :model-value="form.name" @update:model-value="$emit('update:field', 'name', $event)" :placeholder="messages.locations.placeholders.name" :class="{ 'border-destructive': form.errors.name }" />
                            <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                        </div>
                        <div class="space-y-2">
                            <Label for="phone">{{ messages.locations.fields.phone }}</Label>
                            <Input id="phone" :model-value="form.phone" @update:model-value="$emit('update:field', 'phone', $event)" :placeholder="messages.locations.placeholders.phone" :class="{ 'border-destructive': form.errors.phone }" />
                            <p v-if="form.errors.phone" class="text-sm text-destructive">{{ form.errors.phone }}</p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="address">{{ messages.locations.fields.address }}</Label>
                        <Input id="address" :model-value="form.address" @update:model-value="$emit('update:field', 'address', $event)" :placeholder="messages.locations.placeholders.address" :class="{ 'border-destructive': form.errors.address }" />
                        <p v-if="form.errors.address" class="text-sm text-destructive">{{ form.errors.address }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                        <div class="space-y-2">
                            <Label for="city">{{ messages.locations.fields.city }}</Label>
                            <Input id="city" :model-value="form.city" @update:model-value="$emit('update:field', 'city', $event)" :placeholder="messages.locations.placeholders.city" :class="{ 'border-destructive': form.errors.city }" />
                        </div>
                        <div class="space-y-2">
                            <Label for="province">{{ messages.locations.fields.province }}</Label>
                            <Input id="province" :model-value="form.province" @update:model-value="$emit('update:field', 'province', $event)" :placeholder="messages.locations.placeholders.province" />
                        </div>
                        <div class="space-y-2">
                            <Label for="postal_code">{{ messages.locations.fields.postal_code }}</Label>
                            <Input id="postal_code" :model-value="form.postal_code" @update:model-value="$emit('update:field', 'postal_code', $event)" :placeholder="messages.locations.placeholders.postal_code" />
                        </div>
                        <div class="space-y-2">
                            <Label for="country_id">{{ messages.locations.fields.country }}</Label>
                            <SelectRoot :model-value="form.country_id" @update:model-value="$emit('update:field', 'country_id', $event)">
                                <SelectTrigger id="country_id" :class="cn('flex h-9 w-full items-center justify-between rounded-lg border border-input bg-transparent px-3 py-1 text-sm outline-none', { 'border-destructive': form.errors.country_id })">
                                    <SelectValue :placeholder="page.props.messages.countries.single" />
                                    <SelectIcon><ChevronDown class="h-4 w-4 opacity-50" /></SelectIcon>
                                </SelectTrigger>
                                <SelectPortal>
                                    <SelectContent class="relative z-50 max-h-96 min-w-[8rem] overflow-hidden rounded-md border bg-popover text-popover-foreground shadow-md" position="popper">
                                        <SelectViewport class="p-1">
                                            <SelectItem v-for="country in props.countries" :key="country.id" :value="country.id" class="relative flex w-full cursor-default items-center rounded-sm py-1.5 pr-2 pl-8 text-sm outline-none select-none focus:bg-accent data-[highlighted]:bg-accent">
                                                <SelectItemIndicator class="absolute left-2 flex h-3.5 w-3.5 items-center justify-center"><Check class="h-4 w-4" /></SelectItemIndicator>
                                                <SelectItemText>{{ country.name }}</SelectItemText>
                                            </SelectItem>
                                        </SelectViewport>
                                    </SelectContent>
                                </SelectPortal>
                            </SelectRoot>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="space-y-2">
                            <Label for="currency">{{ messages.locations.fields.currency }}</Label>
                            <Input id="currency" :model-value="form.currency" @update:model-value="$emit('update:field', 'currency', $event)" :placeholder="messages.locations.placeholders.currency" maxlength="3" />
                        </div>
                        <div class="space-y-2">
                            <Label for="time_zone">{{ messages.locations.fields.time_zone }}</Label>
                            <Input id="time_zone" :model-value="form.time_zone" @update:model-value="$emit('update:field', 'time_zone', $event)" :placeholder="messages.locations.placeholders.time_zone" />
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="description">{{ messages.locations.fields.description }}</Label>
                        <Textarea id="description" :model-value="form.description" @update:model-value="$emit('update:field', 'description', $event)" :placeholder="messages.locations.placeholders.description" :class="{ 'border-destructive': form.errors.description }" />
                    </div>

                    <!-- Cover image -->
                    <div class="space-y-2">
                        <Label for="location_image">{{ t('panel.locations.photo_label') }}</Label>
                        <div class="flex items-start gap-4">
                            <div v-if="imagePreview" class="relative shrink-0">
                                <img :src="imagePreview" :alt="t('panel.locations.photo_label')" class="h-28 w-40 rounded-xl object-cover border" />
                                <button type="button" class="absolute -top-2 -right-2 flex h-6 w-6 items-center justify-center rounded-full bg-destructive text-destructive-foreground text-xs shadow" @click="removeImage">✕</button>
                            </div>
                            <div class="flex-1">
                                <input id="location_image" type="file" accept="image/jpeg,image/jpg,image/png,image/webp" class="block w-full text-sm text-muted-foreground file:mr-3 file:rounded-lg file:border-0 file:bg-primary file:px-4 file:py-2 file:text-sm file:font-medium file:text-primary-foreground hover:file:bg-primary/90 cursor-pointer" @change="handleFileChange" />
                                <p class="mt-1.5 text-xs text-muted-foreground">{{ t('panel.locations.photo_hint') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ═══ TAB 2: Schedule & amenities ═══ -->
                <div v-show="activeTab === 'schedule'" class="space-y-6">
                    <!-- Opening hours -->
                    <div>
                        <h3 class="text-sm font-semibold mb-1">{{ t('panel.locations.opening_hours') }}</h3>
                        <p class="text-xs text-muted-foreground mb-3">{{ t('panel.locations.opening_hours_hint') }}</p>
                        <div class="space-y-1.5">
                            <div v-for="(day, idx) in weekdays" :key="idx" class="flex items-center gap-3 rounded-lg border border-input px-3 py-2">
                                <span class="w-20 text-sm font-medium shrink-0">{{ day.label }}</span>
                                <template v-if="!openingHours[idx].is_closed">
                                    <Input type="time" :model-value="openingHours[idx].opens_at" @update:model-value="openingHours[idx].opens_at = $event as string" class="h-8 w-28 text-xs" />
                                    <span class="text-xs text-muted-foreground">—</span>
                                    <Input type="time" :model-value="openingHours[idx].closes_at" @update:model-value="openingHours[idx].closes_at = $event as string" class="h-8 w-28 text-xs" />
                                </template>
                                <span v-else class="text-xs text-muted-foreground italic flex-1">{{ t('panel.locations.closed') }}</span>
                                <button type="button" class="relative ml-auto h-5 w-9 rounded-full transition-colors shrink-0" :class="openingHours[idx].is_closed ? 'bg-input' : 'bg-primary'" @click="openingHours[idx].is_closed = !openingHours[idx].is_closed">
                                    <span class="absolute top-0.5 left-0.5 h-4 w-4 rounded-full bg-white shadow transition-transform" :class="openingHours[idx].is_closed ? 'translate-x-0' : 'translate-x-4'" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Amenities -->
                    <div>
                        <h3 class="text-sm font-semibold mb-1">{{ t('panel.locations.amenities') }}</h3>
                        <p class="text-xs text-muted-foreground mb-3">{{ t('panel.locations.amenities_hint') }}</p>
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-5">
                            <label v-for="amenity in amenities" :key="amenity.key" class="flex items-center gap-2 rounded-lg border border-input px-3 py-2.5 cursor-pointer transition-colors" :class="form[amenity.key] ? 'border-primary/50 bg-primary/5' : ''">
                                <input type="checkbox" :checked="!!form[amenity.key]" @change="$emit('update:field', amenity.key, ($event.target as HTMLInputElement).checked)" class="sr-only" />
                                <span class="text-lg">{{ amenity.icon }}</span>
                                <span class="text-xs font-medium">{{ amenity.label }}</span>
                                <span v-if="form[amenity.key]" class="ml-auto text-primary text-sm font-bold">✓</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- ═══ TAB 3: Contact & social ═══ -->
                <div v-show="activeTab === 'contact'" class="space-y-6">
                    <!-- Order channels -->
                    <div>
                        <h3 class="text-sm font-semibold mb-1">{{ t('panel.locations.order_channels') }}</h3>
                        <p class="text-xs text-muted-foreground mb-3">{{ t('panel.locations.order_channels_hint') }}</p>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="order_email">{{ t('panel.locations.order_email') }}</Label>
                                <Input id="order_email" :model-value="form.order_email" @update:model-value="$emit('update:field', 'order_email', $event)" type="email" placeholder="pedidos@mirestaurante.com" />
                            </div>
                            <div class="space-y-2">
                                <Label for="order_whatsapp">{{ t('panel.locations.order_whatsapp') }}</Label>
                                <Input id="order_whatsapp" :model-value="form.order_whatsapp" @update:model-value="$emit('update:field', 'order_whatsapp', $event)" type="tel" placeholder="+34 612 345 678" />
                                <p class="text-xs text-muted-foreground">{{ t('panel.locations.order_whatsapp_hint') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Reservations -->
                    <div>
                        <h3 class="text-sm font-semibold mb-1">{{ t('panel.locations.reservations') }}</h3>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="reservation_phone">{{ t('panel.locations.reservation_phone') }}</Label>
                                <Input id="reservation_phone" :model-value="form.reservation_phone" @update:model-value="$emit('update:field', 'reservation_phone', $event)" type="tel" placeholder="+34 612 345 678" />
                            </div>
                            <div class="space-y-2">
                                <Label for="reservation_url">{{ t('panel.locations.reservation_url') }}</Label>
                                <Input id="reservation_url" :model-value="form.reservation_url" @update:model-value="$emit('update:field', 'reservation_url', $event)" type="url" placeholder="https://reservas.ejemplo.com" />
                            </div>
                        </div>
                    </div>

                    <!-- Social media -->
                    <div>
                        <h3 class="text-sm font-semibold mb-1">{{ t('panel.locations.social_media') }}</h3>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="instagram">Instagram</Label>
                                <Input id="instagram" :model-value="form.instagram" @update:model-value="$emit('update:field', 'instagram', $event)" placeholder="@mirestaurante" />
                            </div>
                            <div class="space-y-2">
                                <Label for="facebook">Facebook</Label>
                                <Input id="facebook" :model-value="form.facebook" @update:model-value="$emit('update:field', 'facebook', $event)" placeholder="https://facebook.com/mirestaurante" />
                            </div>
                        </div>
                        <div class="mt-4 space-y-2">
                            <Label for="google_maps_url">{{ t('panel.locations.google_maps') }}</Label>
                            <Input id="google_maps_url" :model-value="form.google_maps_url" @update:model-value="$emit('update:field', 'google_maps_url', $event)" type="url" placeholder="https://maps.google.com/..." />
                        </div>
                    </div>
                </div>

                <!-- ═══ TAB 4: Appearance ═══ -->
                <div v-show="activeTab === 'appearance'" class="space-y-6">
                    <!-- Brand colors -->
                    <div>
                        <h3 class="text-sm font-semibold mb-1">{{ t('panel.locations.brand_colors') }}</h3>
                        <p class="text-xs text-muted-foreground mb-3">{{ t('panel.locations.brand_colors_hint') }}</p>
                        <div class="flex flex-wrap gap-4">
                            <div class="flex items-center gap-2">
                                <input type="color" :value="form.primary_color || '#0d9488'" class="h-10 w-14 cursor-pointer rounded-lg border border-input p-0.5" @input="$emit('update:field', 'primary_color', ($event.target as HTMLInputElement).value)" />
                                <div>
                                    <span class="text-sm font-medium">{{ t('panel.locations.color_primary') }}</span>
                                    <span class="block text-xs text-muted-foreground font-mono">{{ form.primary_color || '#0d9488' }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="color" :value="form.secondary_color || '#f59e0b'" class="h-10 w-14 cursor-pointer rounded-lg border border-input p-0.5" @input="$emit('update:field', 'secondary_color', ($event.target as HTMLInputElement).value)" />
                                <div>
                                    <span class="text-sm font-medium">{{ t('panel.locations.color_secondary') }}</span>
                                    <span class="block text-xs text-muted-foreground font-mono">{{ form.secondary_color || '#f59e0b' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Gallery -->
                    <div v-if="isEdit && locationId">
                        <h3 class="text-sm font-semibold mb-1">{{ t('panel.locations.gallery') }}</h3>
                        <p class="text-xs text-muted-foreground mb-3">{{ t('panel.locations.gallery_hint') }}</p>
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-5">
                            <div v-for="img in galleryImages" :key="img.id" class="group relative aspect-square overflow-hidden rounded-lg border">
                                <img :src="img.path" :alt="img.alt || ''" class="h-full w-full object-cover" />
                                <button type="button" class="absolute inset-0 flex items-center justify-center bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity" @click="deleteGalleryImage(img.id)">
                                    <span class="text-white text-xs font-medium">{{ t('common.delete') }}</span>
                                </button>
                            </div>
                            <label v-if="galleryImages.length < 5" class="flex aspect-square cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-input hover:border-primary/50 transition-colors">
                                <input type="file" accept="image/*" class="hidden" @change="uploadGalleryImage" />
                                <span class="text-2xl text-muted-foreground">+</span>
                                <span class="text-[10px] text-muted-foreground mt-1">{{ t('panel.locations.add_image') }}</span>
                            </label>
                        </div>
                    </div>
                    <div v-else class="rounded-lg border border-dashed p-6 text-center text-sm text-muted-foreground">
                        {{ t('panel.locations.gallery_save_first') }}
                    </div>
                </div>
            </CardContent>

            <CardFooter class="mt-6 flex justify-between border-t pt-6">
                <Button variant="outline" type="button" as-child>
                    <Link :href="locationsRoute().url">
                        <X class="mr-2 h-4 w-4" />
                        {{ messages.locations.actions.cancel }}
                    </Link>
                </Button>
                <Button type="submit" :disabled="form.processing">
                    <Save class="mr-2 h-4 w-4" />
                    {{ form.processing ? processingText : submitText }}
                </Button>
            </CardFooter>
        </form>
    </Card>
</template>
