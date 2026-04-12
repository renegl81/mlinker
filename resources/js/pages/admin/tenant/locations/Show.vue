<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import MenuSection from './components/MenuSection.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    destroy as locationRouteDestroy,
    edit as locationRouteEdit,
    index as locationsRoute,
} from '@/routes/tenant/locations';
import type { BreadcrumbItem, Location } from '@/types';
import { Inertia } from '@inertiajs/inertia';
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    ArrowLeft,
    BadgeDollarSign,
    Clock,
    ExternalLink,
    Eye,
    Globe,
    MapPin,
    Monitor,
    Pencil,
    Phone,
    RefreshCw,
    Smartphone,
    Trash2,
    X,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

interface Props {
    location: Location;
    publicUrl: string | null;
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
        title: messages.value.locations.plural,
        href: locationsRoute().url,
    },
    {
        title: props.location.name,
        href: '#',
    },
];

function remove() {
    if (confirm(messages.value.locations.actions.confirm_delete)) {
        Inertia.delete(locationRouteDestroy(props.location.id).url);
    }
}
</script>

<template>
    <Head :title="`${messages.locations.single}: ${location.name}`" />

    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
            <div
                class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="flex items-center gap-4">
                    <Button variant="outline" size="icon" as-child>
                        <Link :href="locationsRoute().url">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                    </Button>
                    <HeadingSmall
                        :title="location.name"
                        :description="messages.locations.caption"
                    />
                </div>
                <div class="flex items-center gap-2">
                    <Button v-if="publicUrl" variant="outline" @click="showPreview = !showPreview">
                        <Eye class="mr-2 h-4 w-4" />
                        <span class="hidden sm:inline">{{ t('panel.location_show.preview') }}</span>
                    </Button>
                    <Button variant="outline" as-child>
                        <Link :href="locationRouteEdit(location.id)">
                            <Pencil class="mr-2 h-4 w-4" />
                            {{ messages.locations.actions.edit }}
                        </Link>
                    </Button>
                    <Button variant="destructive" @click="remove">
                        <Trash2 class="mr-2 h-4 w-4" />
                        {{ messages.locations.actions.delete }}
                    </Button>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="space-y-6 lg:col-span-2">
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg font-semibold">
                                {{
                                    messages.locations.form.title_show ||
                                    'Información General'
                                }}
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div class="flex flex-col gap-1.5">
                                    <span
                                        class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                                    >
                                        {{ messages.locations.fields.address }}
                                    </span>
                                    <div class="flex items-start gap-2 text-sm">
                                        <MapPin
                                            class="mt-0.5 h-4 w-4 shrink-0 text-primary"
                                        />
                                        <span>{{ location.address }}</span>
                                    </div>
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <span
                                        class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                                    >
                                        {{ messages.locations.fields.phone }}
                                    </span>
                                    <div
                                        class="flex items-center gap-2 text-sm"
                                    >
                                        <Phone
                                            class="h-4 w-4 shrink-0 text-primary"
                                        />
                                        <span>{{
                                            location.phone || '---'
                                        }}</span>
                                    </div>
                                </div>
                            </div>

                            <Separator />

                            <div
                                class="grid grid-cols-1 gap-4 text-sm md:grid-cols-3"
                            >
                                <div class="space-y-1">
                                    <span
                                        class="block font-medium text-muted-foreground"
                                        >{{
                                            messages.locations.fields.city
                                        }}</span
                                    >
                                    <p>{{ location.city }}</p>
                                </div>
                                <div class="space-y-1">
                                    <span
                                        class="block font-medium text-muted-foreground"
                                        >{{
                                            messages.locations.fields.province
                                        }}</span
                                    >
                                    <p>{{ location.province }}</p>
                                </div>
                                <div class="space-y-1">
                                    <span
                                        class="block font-medium text-muted-foreground"
                                        >{{
                                            messages.locations.fields
                                                .postal_code
                                        }}</span
                                    >
                                    <p>{{ location.postal_code }}</p>
                                </div>
                            </div>

                            <div
                                v-if="location.description"
                                class="space-y-2 pt-2"
                            >
                                <span
                                    class="text-xs font-bold tracking-wider text-muted-foreground uppercase"
                                >
                                    {{ messages.locations.fields.description }}
                                </span>
                                <p
                                    class="text-sm leading-relaxed text-pretty text-muted-foreground"
                                >
                                    {{ location.description }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <MenuSection
                        :location-id="location.id"
                        :menus="location.menus"
                    />
                </div>

                <div class="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-md font-semibold">{{
                                messages.locations.fields.configuration ||
                                'Configuración'
                            }}</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4 text-sm">
                            <div
                                class="flex items-center justify-between border-b border-muted py-1"
                            >
                                <div
                                    class="flex items-center gap-2 text-muted-foreground"
                                >
                                    <Globe class="h-4 w-4" />
                                    <span>{{
                                        messages.locations.fields.country
                                    }}</span>
                                </div>
                                <span class="text-right font-medium">{{
                                    location.country?.name
                                }}</span>
                            </div>
                            <div
                                class="flex items-center justify-between border-b border-muted py-1"
                            >
                                <div
                                    class="flex items-center gap-2 text-muted-foreground"
                                >
                                    <BadgeDollarSign class="h-4 w-4" />
                                    <span>{{
                                        messages.locations.fields.currency
                                    }}</span>
                                </div>
                                <span class="font-medium uppercase">{{
                                    location.currency
                                }}</span>
                            </div>
                            <div
                                class="flex items-center justify-between border-b border-muted py-1"
                            >
                                <div
                                    class="flex items-center gap-2 text-muted-foreground"
                                >
                                    <Clock class="h-4 w-4" />
                                    <span>{{
                                        messages.locations.fields.time_zone
                                    }}</span>
                                </div>
                                <span
                                    class="max-w-[120px] truncate font-medium"
                                    :title="location.time_zone"
                                >
                                    {{ location.time_zone }}
                                </span>
                            </div>
                        </CardContent>
                    </Card>

                    <Card
                        :class="
                            location.is_active
                                ? 'border-green-200 bg-green-50/50'
                                : 'border-red-200 bg-red-50/50'
                        "
                    >
                        <CardContent
                            class="flex items-center justify-center gap-2 py-4"
                        >
                            <div
                                :class="
                                    location.is_active
                                        ? 'bg-green-500'
                                        : 'bg-red-500'
                                "
                                class="h-2 w-2 animate-pulse rounded-full"
                            />
                            <span
                                :class="
                                    location.is_active
                                        ? 'text-green-700'
                                        : 'text-red-700'
                                "
                                class="text-xs font-bold tracking-widest uppercase"
                            >
                                {{
                                    location.is_active
                                        ? messages.locations.fields.active
                                        : messages.locations.fields.inactive
                                }}
                            </span>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>

    <Teleport to="body">
        <Transition name="preview-slide">
            <div v-if="showPreview && publicUrl" class="preview-overlay">
                <div class="preview-panel">
                    <div class="preview-header">
                        <div class="preview-header-left">
                            <Eye class="h-4 w-4" />
                            <span class="preview-header-title">{{ t('panel.location_show.preview') }}</span>
                        </div>
                        <div class="preview-header-actions">
                            <div class="preview-device-toggle">
                                <button
                                    type="button"
                                    class="preview-device-btn"
                                    :class="{ 'is-active': previewMode === 'mobile' }"
                                    @click="previewMode = 'mobile'"
                                    :title="t('panel.location_show.preview_mobile')"
                                >
                                    <Smartphone class="h-4 w-4" />
                                </button>
                                <button
                                    type="button"
                                    class="preview-device-btn"
                                    :class="{ 'is-active': previewMode === 'desktop' }"
                                    @click="previewMode = 'desktop'"
                                    :title="t('panel.location_show.preview_desktop')"
                                >
                                    <Monitor class="h-4 w-4" />
                                </button>
                            </div>
                            <button type="button" class="preview-action-btn" @click="refreshPreview" :title="t('panel.location_show.refresh')">
                                <RefreshCw class="h-4 w-4" />
                            </button>
                            <a :href="publicUrl" target="_blank" rel="noopener" class="preview-action-btn" :title="t('panel.location_show.open_new_tab')">
                                <ExternalLink class="h-4 w-4" />
                            </a>
                            <button type="button" class="preview-close-btn" @click="showPreview = false">
                                <X class="h-4 w-4" />
                            </button>
                        </div>
                    </div>
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
                                :src="publicUrl"
                                class="preview-iframe"
                                :title="location.name"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
    </AppLayout>
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
