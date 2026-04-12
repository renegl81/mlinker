<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { Check, Globe, Loader2, Plus, Sparkles, Store, UtensilsCrossed, X, Zap } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

interface Location {
    id: number;
    name: string;
    address?: string | null;
    city?: string | null;
    phone?: string | null;
}

interface Menu {
    id: number;
    name: string;
    description?: string | null;
}

interface Product {
    id: number;
    name: string;
    price: string;
}

const props = defineProps<{
    step: number;
    location?: Location | null;
    menu?: Menu | null;
    products?: Product[];
}>();

// ─── Step 0: Tu web ───────────────────────────────────────────────────────────
const websiteForm = useForm({
    has_website: true,
    business_type: 'restaurant' as string,
});

const businessTypes = computed(() => [
    { value: 'restaurant', label: t('panel.onboarding.type_restaurant'), emoji: '🍽️' },
    { value: 'cafe',       label: t('panel.onboarding.type_cafe'),       emoji: '☕' },
    { value: 'bar',        label: t('panel.onboarding.type_bar'),        emoji: '🍹' },
    { value: 'fastfood',   label: t('panel.onboarding.type_fastfood'),   emoji: '🍔' },
    { value: 'finedining', label: t('panel.onboarding.type_finedining'), emoji: '⭐' },
]);

function submitWebsite() {
    websiteForm.post(route('tenant.onboarding.website'));
}

// ─── Step 1: Location ─────────────────────────────────────────────────────────
const locationForm = useForm({
    name: props.location?.name ?? '',
    address: props.location?.address ?? '',
    city: props.location?.city ?? '',
    phone: props.location?.phone ?? '',
});

function submitLocation() {
    locationForm.post(route('tenant.onboarding.location'));
}

// ─── Step 2: Menu ─────────────────────────────────────────────────────────────
const menuForm = useForm({
    name: props.menu?.name ?? '',
    description: props.menu?.description ?? '',
    location_id: props.location?.id ?? 0,
});

watch(
    () => props.location?.id,
    (id) => {
        if (id) menuForm.location_id = id;
    },
    { immediate: true },
);

function submitMenu() {
    if (props.location?.id) menuForm.location_id = props.location.id;
    menuForm.post(route('tenant.onboarding.menu'));
}

// ─── Step 3: Products ─────────────────────────────────────────────────────────
const sectionOptions = ['Entrantes', 'Principales', 'Postres', 'Bebidas'];

interface ProductRow {
    name: string;
    price: string;
    section_name: string;
}

const productRows = ref<ProductRow[]>([{ name: '', price: '', section_name: 'Principales' }]);

function addProduct() {
    productRows.value.push({ name: '', price: '', section_name: 'Principales' });
}

function removeProduct(index: number) {
    if (productRows.value.length > 1) productRows.value.splice(index, 1);
}

const productsError = ref('');
const productsProcessing = ref(false);

function submitProducts() {
    productsError.value = '';
    const valid = productRows.value.every((p) => p.name.trim() && p.price && p.section_name.trim());
    if (!valid) {
        productsError.value = t('panel.onboarding.products_error');
        return;
    }

    productsProcessing.value = true;
    router.post(
        route('tenant.onboarding.products'),
        { menu_id: props.menu?.id, products: productRows.value },
        { preserveScroll: true, onFinish: () => (productsProcessing.value = false) },
    );
}

// ─── Step 4: Complete ─────────────────────────────────────────────────────────
const completeProcessing = ref(false);

function submitComplete() {
    completeProcessing.value = true;
    router.post(
        route('tenant.onboarding.complete'),
        { menu_id: props.menu?.id },
        { onFinish: () => (completeProcessing.value = false) },
    );
}

// ─── Progress ─────────────────────────────────────────────────────────────────
const steps = computed(() => [
    { label: t('panel.onboarding.step_website') },
    { label: t('panel.onboarding.step_business') },
    { label: t('panel.onboarding.step_menu') },
    { label: t('panel.onboarding.step_dishes') },
    { label: t('panel.onboarding.step_done') },
]);

const progressPercent = computed(() => Math.round((props.step / (steps.value.length - 1)) * 100));

// ─── Shared classes ───────────────────────────────────────────────────────────
const inputClass =
    'flex h-11 w-full rounded-lg border border-slate-700 bg-slate-900/60 px-4 py-2 text-sm text-white placeholder:text-slate-500 transition-colors focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/30';
const labelClass = 'text-sm font-medium text-slate-300';
const errorClass = 'text-xs text-red-400';
</script>

<template>
    <div class="min-h-svh bg-slate-950 text-slate-200 selection:bg-teal-500 selection:text-white">
        <div class="flex min-h-svh flex-col items-center justify-start px-4 py-10">
            <!-- Logo -->
            <div class="mb-10 flex items-center gap-2">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-teal-500 to-cyan-500 shadow-lg shadow-teal-500/20">
                    <Sparkles class="h-5 w-5 text-white" />
                </div>
                <span class="text-lg font-bold text-white">MenuLinker</span>
            </div>

            <!-- Progress indicator -->
            <div class="mb-8 w-full max-w-xl">
                <div class="mb-3 flex justify-between">
                    <div
                        v-for="(s, i) in steps"
                        :key="i"
                        class="flex flex-1 flex-col items-center gap-2"
                    >
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-full border-2 text-xs font-bold transition-all"
                            :class="
                                i < step
                                    ? 'border-teal-500 bg-teal-500 text-white'
                                    : i === step
                                      ? 'border-teal-500 bg-teal-500/20 text-teal-300'
                                      : 'border-slate-700 bg-slate-900 text-slate-500'
                            "
                        >
                            <Check v-if="i < step" class="h-4 w-4" />
                            <span v-else>{{ i + 1 }}</span>
                        </div>
                        <span
                            class="text-[11px] font-medium"
                            :class="i <= step ? 'text-teal-300' : 'text-slate-500'"
                        >
                            {{ s.label }}
                        </span>
                    </div>
                </div>
                <div class="relative mt-2 h-1.5 w-full overflow-hidden rounded-full bg-slate-800">
                    <div
                        class="absolute left-0 top-0 h-full rounded-full bg-gradient-to-r from-teal-500 to-cyan-500 transition-all duration-500"
                        :style="{ width: progressPercent + '%' }"
                    />
                </div>
            </div>

            <!-- Card -->
            <div class="w-full max-w-xl rounded-2xl border border-slate-800 bg-slate-900/60 p-8 shadow-2xl backdrop-blur-sm">

                <!-- ── Paso 0: Tu web ── -->
                <template v-if="step === 0">
                    <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-teal-500/15">
                        <Globe class="h-6 w-6 text-teal-400" />
                    </div>
                    <h1 class="mb-2 text-2xl font-bold text-white">{{ t('panel.onboarding.website_title') }}</h1>
                    <p class="mb-6 text-sm text-slate-400">
                        {{ t('panel.onboarding.website_body') }}
                    </p>

                    <form class="space-y-6" @submit.prevent="submitWebsite">
                        <!-- Toggle has_website -->
                        <div class="flex items-start gap-4 rounded-xl border border-slate-700 bg-slate-900/60 p-4">
                            <button
                                type="button"
                                role="switch"
                                :aria-checked="websiteForm.has_website"
                                class="relative mt-0.5 h-6 w-11 flex-shrink-0 rounded-full transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500"
                                :class="websiteForm.has_website ? 'bg-teal-600' : 'bg-slate-700'"
                                @click="websiteForm.has_website = !websiteForm.has_website"
                            >
                                <span
                                    class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white transition-transform"
                                    :class="websiteForm.has_website ? 'translate-x-5' : 'translate-x-0'"
                                />
                            </button>
                            <div>
                                <p class="text-sm font-semibold text-white">{{ t('panel.onboarding.publish_website') }}</p>
                                <p class="mt-0.5 text-xs text-slate-400">
                                    {{ t('panel.onboarding.publish_website_hint') }}
                                </p>
                            </div>
                        </div>

                        <!-- Business type (only if has_website) -->
                        <Transition
                            enter-active-class="transition-all duration-300"
                            enter-from-class="opacity-0 -translate-y-2"
                            enter-to-class="opacity-100 translate-y-0"
                            leave-active-class="transition-all duration-200"
                            leave-from-class="opacity-100 translate-y-0"
                            leave-to-class="opacity-0 -translate-y-2"
                        >
                            <div v-if="websiteForm.has_website" class="space-y-3">
                                <p :class="labelClass">{{ t('panel.onboarding.business_type') }}</p>
                                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                                    <button
                                        v-for="bt in businessTypes"
                                        :key="bt.value"
                                        type="button"
                                        class="flex flex-col items-center gap-1.5 rounded-xl border-2 px-3 py-3 text-sm font-medium transition-all"
                                        :class="
                                            websiteForm.business_type === bt.value
                                                ? 'border-teal-500 bg-teal-500/15 text-white'
                                                : 'border-slate-700 bg-slate-900/60 text-slate-400 hover:border-slate-600 hover:text-white'
                                        "
                                        @click="websiteForm.business_type = bt.value"
                                    >
                                        <span class="text-xl leading-none">{{ bt.emoji }}</span>
                                        <span class="text-xs">{{ bt.label }}</span>
                                    </button>
                                </div>
                            </div>
                        </Transition>

                        <button
                            type="submit"
                            :disabled="websiteForm.processing"
                            class="mt-2 flex h-11 w-full items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-teal-600 to-cyan-600 px-6 font-semibold text-white shadow-lg shadow-teal-500/20 transition-all hover:from-teal-500 hover:to-cyan-500 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            <Loader2 v-if="websiteForm.processing" class="h-4 w-4 animate-spin" />
                            <span>{{ t('panel.onboarding.continue') }}</span>
                            <span v-if="!websiteForm.processing">→</span>
                        </button>
                    </form>
                </template>

                <!-- ── Paso 1: Tu negocio ── -->
                <template v-else-if="step === 1">
                    <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-teal-500/15">
                        <Store class="h-6 w-6 text-teal-400" />
                    </div>
                    <h1 class="mb-2 text-2xl font-bold text-white">{{ t('panel.onboarding.business_title') }}</h1>
                    <p class="mb-6 text-sm text-slate-400">
                        {{ t('panel.onboarding.business_body') }}
                    </p>

                    <form class="space-y-5" @submit.prevent="submitLocation">
                        <div class="space-y-2">
                            <label for="loc-name" :class="labelClass">
                                {{ t('panel.onboarding.location_name') }} <span class="text-red-400">*</span>
                            </label>
                            <input
                                id="loc-name"
                                v-model="locationForm.name"
                                type="text"
                                :placeholder="t('panel.onboarding.location_name_placeholder')"
                                :class="[inputClass, locationForm.errors.name && 'border-red-500']"
                            />
                            <p v-if="locationForm.errors.name" :class="errorClass">{{ locationForm.errors.name }}</p>
                        </div>

                        <div class="space-y-2">
                            <label for="loc-address" :class="labelClass">{{ t('panel.onboarding.address') }}</label>
                            <input
                                id="loc-address"
                                v-model="locationForm.address"
                                type="text"
                                :placeholder="t('panel.onboarding.address_placeholder')"
                                :class="inputClass"
                            />
                        </div>

                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div class="space-y-2">
                                <label for="loc-city" :class="labelClass">{{ t('panel.onboarding.city') }}</label>
                                <input
                                    id="loc-city"
                                    v-model="locationForm.city"
                                    type="text"
                                    :placeholder="t('panel.onboarding.city_placeholder')"
                                    :class="inputClass"
                                />
                            </div>
                            <div class="space-y-2">
                                <label for="loc-phone" :class="labelClass">{{ t('panel.onboarding.phone') }}</label>
                                <input
                                    id="loc-phone"
                                    v-model="locationForm.phone"
                                    type="text"
                                    placeholder="+34 600 000 000"
                                    :class="inputClass"
                                />
                            </div>
                        </div>

                        <button
                            type="submit"
                            :disabled="locationForm.processing"
                            class="mt-2 flex h-11 w-full items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-teal-600 to-cyan-600 px-6 font-semibold text-white shadow-lg shadow-teal-500/20 transition-all hover:from-teal-500 hover:to-cyan-500 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            <Loader2 v-if="locationForm.processing" class="h-4 w-4 animate-spin" />
                            <span>{{ t('panel.onboarding.continue') }}</span>
                            <span v-if="!locationForm.processing">→</span>
                        </button>
                    </form>
                </template>

                <!-- ── Paso 2: Tu menú ── -->
                <template v-else-if="step === 2">
                    <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-teal-500/15">
                        <UtensilsCrossed class="h-6 w-6 text-teal-400" />
                    </div>
                    <h1 class="mb-2 text-2xl font-bold text-white">{{ t('panel.onboarding.menu_title') }}</h1>
                    <p class="mb-6 text-sm text-slate-400">{{ t('panel.onboarding.menu_body') }}</p>

                    <form class="space-y-5" @submit.prevent="submitMenu">
                        <div
                            v-if="menuForm.errors.location_id"
                            class="rounded-lg border border-red-500/50 bg-red-500/10 p-3 text-sm text-red-300"
                        >
                            {{ menuForm.errors.location_id }}
                        </div>

                        <div class="space-y-2">
                            <label for="menu-name" :class="labelClass">
                                {{ t('panel.onboarding.menu_name') }} <span class="text-red-400">*</span>
                            </label>
                            <input
                                id="menu-name"
                                v-model="menuForm.name"
                                type="text"
                                :placeholder="t('panel.onboarding.menu_name_placeholder')"
                                :class="[inputClass, menuForm.errors.name && 'border-red-500']"
                            />
                            <p v-if="menuForm.errors.name" :class="errorClass">{{ menuForm.errors.name }}</p>
                        </div>

                        <div class="space-y-2">
                            <label for="menu-desc" :class="labelClass">{{ t('common.description') }}</label>
                            <input
                                id="menu-desc"
                                v-model="menuForm.description"
                                type="text"
                                :placeholder="t('panel.onboarding.menu_desc_placeholder')"
                                :class="inputClass"
                            />
                        </div>

                        <button
                            type="submit"
                            :disabled="menuForm.processing"
                            class="mt-2 flex h-11 w-full items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-teal-600 to-cyan-600 px-6 font-semibold text-white shadow-lg shadow-teal-500/20 transition-all hover:from-teal-500 hover:to-cyan-500 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            <Loader2 v-if="menuForm.processing" class="h-4 w-4 animate-spin" />
                            <span>{{ t('panel.onboarding.continue') }}</span>
                            <span v-if="!menuForm.processing">→</span>
                        </button>
                    </form>
                </template>

                <!-- ── Paso 3: Tus platos ── -->
                <template v-else-if="step === 3">
                    <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-teal-500/15">
                        <Zap class="h-6 w-6 text-teal-400" />
                    </div>
                    <h1 class="mb-2 text-2xl font-bold text-white">{{ t('panel.onboarding.dishes_title') }}</h1>
                    <p class="mb-6 text-sm text-slate-400">
                        {{ t('panel.onboarding.dishes_body') }}
                    </p>

                    <div class="mb-4 space-y-4">
                        <div
                            v-for="(row, index) in productRows"
                            :key="index"
                            class="rounded-lg border border-slate-800 bg-slate-950/50 p-4"
                        >
                            <div class="mb-3 flex items-center justify-between">
                                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                                    {{ t('panel.onboarding.dish_number', { n: index + 1 }) }}
                                </span>
                                <button
                                    v-if="productRows.length > 1"
                                    type="button"
                                    class="text-slate-500 transition-colors hover:text-red-400"
                                    :title="t('common.delete')"
                                    @click="removeProduct(index)"
                                >
                                    <X class="h-4 w-4" />
                                </button>
                            </div>

                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-[1fr_7rem]">
                                <div class="space-y-2">
                                    <label :class="labelClass">{{ t('common.name') }}</label>
                                    <input
                                        v-model="row.name"
                                        type="text"
                                        :placeholder="t('panel.onboarding.dish_name_placeholder')"
                                        :class="inputClass"
                                    />
                                </div>
                                <div class="space-y-2">
                                    <label :class="labelClass">{{ t('panel.product_form.field_price') }}</label>
                                    <input
                                        v-model="row.price"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        placeholder="0.00"
                                        :class="inputClass"
                                    />
                                </div>
                            </div>

                            <div class="mt-3 space-y-2">
                                <label :class="labelClass">{{ t('panel.product_form.field_section') }}</label>
                                <select v-model="row.section_name" :class="inputClass">
                                    <option v-for="opt in sectionOptions" :key="opt" :value="opt" class="bg-slate-900">
                                        {{ opt }}
                                    </option>
                                    <option value="__custom__" class="bg-slate-900">{{ t('panel.onboarding.other_section') }}</option>
                                </select>
                                <input
                                    v-if="row.section_name === '__custom__'"
                                    type="text"
                                    :placeholder="t('panel.onboarding.custom_section_placeholder')"
                                    :class="[inputClass, 'mt-2']"
                                    @input="(e: Event) => (row.section_name = (e.target as HTMLInputElement).value)"
                                />
                            </div>
                        </div>
                    </div>

                    <button
                        type="button"
                        class="mb-6 inline-flex items-center gap-1.5 text-sm font-medium text-teal-400 transition-colors hover:text-teal-300"
                        @click="addProduct"
                    >
                        <Plus class="h-4 w-4" />
                        {{ t('panel.onboarding.add_dish') }}
                    </button>

                    <p v-if="productsError" :class="[errorClass, 'mb-3']">{{ productsError }}</p>

                    <button
                        type="button"
                        :disabled="productsProcessing"
                        class="flex h-11 w-full items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-teal-600 to-cyan-600 px-6 font-semibold text-white shadow-lg shadow-teal-500/20 transition-all hover:from-teal-500 hover:to-cyan-500 disabled:cursor-not-allowed disabled:opacity-60"
                        @click="submitProducts"
                    >
                        <Loader2 v-if="productsProcessing" class="h-4 w-4 animate-spin" />
                        <span>{{ t('panel.onboarding.continue') }}</span>
                        <span v-if="!productsProcessing">→</span>
                    </button>
                </template>

                <!-- ── Paso 4: ¡Listo! ── -->
                <template v-else-if="step === 4">
                    <div class="mb-4 flex items-center justify-center">
                        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-teal-500 to-cyan-500 text-4xl shadow-lg shadow-teal-500/30">
                            🎉
                        </div>
                    </div>
                    <h1 class="mb-2 text-center text-2xl font-bold text-white">{{ t('panel.onboarding.complete_title') }}</h1>
                    <p class="mb-6 text-center text-sm text-slate-400">
                        {{ t('panel.onboarding.complete_body') }}
                    </p>

                    <div class="mb-6 rounded-lg border border-slate-800 bg-slate-950/50 p-5">
                        <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ t('panel.onboarding.summary') }}</p>
                        <ul class="space-y-2 text-sm">
                            <li v-if="location" class="flex items-start gap-2">
                                <Check class="mt-0.5 h-4 w-4 shrink-0 text-teal-400" />
                                <span class="text-slate-400">{{ t('panel.onboarding.summary_location') }}: <span class="font-medium text-white">{{ location.name }}</span></span>
                            </li>
                            <li v-if="menu" class="flex items-start gap-2">
                                <Check class="mt-0.5 h-4 w-4 shrink-0 text-teal-400" />
                                <span class="text-slate-400">{{ t('panel.onboarding.summary_menu') }}: <span class="font-medium text-white">{{ menu.name }}</span></span>
                            </li>
                            <li v-if="products?.length" class="flex items-start gap-2">
                                <Check class="mt-0.5 h-4 w-4 shrink-0 text-teal-400" />
                                <span class="text-slate-400">
                                    <span class="font-medium text-white">{{ products.length }}</span> {{ t('panel.onboarding.products_added', products.length) }}
                                </span>
                            </li>
                        </ul>
                    </div>

                    <button
                        type="button"
                        :disabled="completeProcessing"
                        class="flex h-11 w-full items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-teal-600 to-cyan-600 px-6 font-semibold text-white shadow-lg shadow-teal-500/20 transition-all hover:from-teal-500 hover:to-cyan-500 disabled:cursor-not-allowed disabled:opacity-60"
                        @click="submitComplete"
                    >
                        <Loader2 v-if="completeProcessing" class="h-4 w-4 animate-spin" />
                        <Sparkles v-else class="h-4 w-4" />
                        <span>{{ t('panel.onboarding.finish') }}</span>
                    </button>
                </template>
            </div>

            <!-- Footer -->
            <p class="mt-8 text-xs text-slate-600">
                {{ t('panel.onboarding.step_counter', { current: step + 1, total: steps.length }) }}
            </p>
        </div>
    </div>
</template>
