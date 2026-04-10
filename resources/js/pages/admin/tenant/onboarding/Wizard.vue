<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { Check, Loader2, Plus, Sparkles, X } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

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

// ─── Step 0: Location ───────────────────────────────────────────────────────
const locationForm = useForm({
    name: props.location?.name ?? '',
    address: props.location?.address ?? '',
    city: props.location?.city ?? '',
    phone: props.location?.phone ?? '',
});

function submitLocation() {
    locationForm.post(route('tenant.onboarding.location'));
}

// ─── Step 1: Menu ────────────────────────────────────────────────────────────
const menuForm = useForm({
    name: props.menu?.name ?? '',
    description: props.menu?.description ?? '',
    location_id: props.location?.id ?? 0,
});

// Keep location_id in sync with props after step 0 completes and props update.
watch(
    () => props.location?.id,
    (id) => {
        if (id) {
            menuForm.location_id = id;
        }
    },
    { immediate: true },
);

function submitMenu() {
    // Safety: ensure we always send the latest location id from props.
    if (props.location?.id) {
        menuForm.location_id = props.location.id;
    }
    menuForm.post(route('tenant.onboarding.menu'));
}

// ─── Step 2: Products ────────────────────────────────────────────────────────
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
    if (productRows.value.length > 1) {
        productRows.value.splice(index, 1);
    }
}

const productsError = ref('');
const productsProcessing = ref(false);

function submitProducts() {
    productsError.value = '';
    const valid = productRows.value.every((p) => p.name.trim() && p.price && p.section_name.trim());
    if (!valid) {
        productsError.value = 'Completa todos los campos de los productos.';
        return;
    }

    productsProcessing.value = true;
    router.post(
        route('tenant.onboarding.products'),
        {
            menu_id: props.menu?.id,
            products: productRows.value,
        },
        {
            preserveScroll: true,
            onFinish: () => (productsProcessing.value = false),
        },
    );
}

// ─── Step 3: Complete ────────────────────────────────────────────────────────
const completeProcessing = ref(false);

function submitComplete() {
    completeProcessing.value = true;
    router.post(
        route('tenant.onboarding.complete'),
        { menu_id: props.menu?.id },
        { onFinish: () => (completeProcessing.value = false) },
    );
}

// ─── Progress ────────────────────────────────────────────────────────────────
const steps = [
    { label: 'Tu negocio' },
    { label: 'Tu menú' },
    { label: 'Tus platos' },
    { label: '¡Listo!' },
];

const progressPercent = computed(() => Math.round((props.step / (steps.length - 1)) * 100));

// ─── Shared input classes (explicit colors, no theme tokens) ────────────────
const inputClass =
    'flex h-11 w-full rounded-lg border border-slate-700 bg-slate-900/60 px-4 py-2 text-sm text-white placeholder:text-slate-500 transition-colors focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/30';
const labelClass = 'text-sm font-medium text-slate-300';
const errorClass = 'text-xs text-red-400';
</script>

<template>
    <div class="min-h-svh bg-slate-950 text-slate-200 selection:bg-purple-500 selection:text-white">
        <div class="flex min-h-svh flex-col items-center justify-start px-4 py-10">
            <!-- Logo -->
            <div class="mb-10 flex items-center gap-2">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-purple-500 to-pink-500 shadow-lg shadow-purple-500/20">
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
                                    ? 'border-purple-500 bg-purple-500 text-white'
                                    : i === step
                                      ? 'border-purple-500 bg-purple-500/20 text-purple-300'
                                      : 'border-slate-700 bg-slate-900 text-slate-500'
                            "
                        >
                            <Check v-if="i < step" class="h-4 w-4" />
                            <span v-else>{{ i + 1 }}</span>
                        </div>
                        <span
                            class="text-[11px] font-medium"
                            :class="i <= step ? 'text-purple-300' : 'text-slate-500'"
                        >
                            {{ s.label }}
                        </span>
                    </div>
                </div>
                <div class="relative mt-2 h-1.5 w-full overflow-hidden rounded-full bg-slate-800">
                    <div
                        class="absolute left-0 top-0 h-full rounded-full bg-gradient-to-r from-purple-500 to-pink-500 transition-all duration-500"
                        :style="{ width: progressPercent + '%' }"
                    />
                </div>
            </div>

            <!-- Card -->
            <div class="w-full max-w-xl rounded-2xl border border-slate-800 bg-slate-900/60 p-8 shadow-2xl backdrop-blur-sm">
                <!-- ── Paso 0: Tu negocio ── -->
                <template v-if="step === 0">
                    <h1 class="mb-2 text-2xl font-bold text-white">Tu negocio</h1>
                    <p class="mb-6 text-sm text-slate-400">
                        Cuéntanos un poco sobre tu local. Esto solo tomará un minuto.
                    </p>

                    <form class="space-y-5" @submit.prevent="submitLocation">
                        <div class="space-y-2">
                            <label for="loc-name" :class="labelClass">
                                Nombre del local <span class="text-red-400">*</span>
                            </label>
                            <input
                                id="loc-name"
                                v-model="locationForm.name"
                                type="text"
                                placeholder="Ej. Cafetería El Sol"
                                :class="[inputClass, locationForm.errors.name && 'border-red-500']"
                            />
                            <p v-if="locationForm.errors.name" :class="errorClass">{{ locationForm.errors.name }}</p>
                        </div>

                        <div class="space-y-2">
                            <label for="loc-address" :class="labelClass">Dirección</label>
                            <input
                                id="loc-address"
                                v-model="locationForm.address"
                                type="text"
                                placeholder="Calle Mayor, 10"
                                :class="inputClass"
                            />
                        </div>

                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div class="space-y-2">
                                <label for="loc-city" :class="labelClass">Ciudad</label>
                                <input
                                    id="loc-city"
                                    v-model="locationForm.city"
                                    type="text"
                                    placeholder="Madrid"
                                    :class="inputClass"
                                />
                            </div>
                            <div class="space-y-2">
                                <label for="loc-phone" :class="labelClass">Teléfono</label>
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
                            class="mt-2 flex h-11 w-full items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-purple-600 to-pink-600 px-6 font-semibold text-white shadow-lg shadow-purple-500/20 transition-all hover:from-purple-500 hover:to-pink-500 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            <Loader2 v-if="locationForm.processing" class="h-4 w-4 animate-spin" />
                            <span>Continuar</span>
                            <span v-if="!locationForm.processing">→</span>
                        </button>
                    </form>
                </template>

                <!-- ── Paso 1: Tu menú ── -->
                <template v-else-if="step === 1">
                    <h1 class="mb-2 text-2xl font-bold text-white">Tu menú</h1>
                    <p class="mb-6 text-sm text-slate-400">Dale un nombre a tu menú digital.</p>

                    <form class="space-y-5" @submit.prevent="submitMenu">
                        <div
                            v-if="menuForm.errors.location_id"
                            class="rounded-lg border border-red-500/50 bg-red-500/10 p-3 text-sm text-red-300"
                        >
                            {{ menuForm.errors.location_id }}
                        </div>

                        <div class="space-y-2">
                            <label for="menu-name" :class="labelClass">
                                Nombre del menú <span class="text-red-400">*</span>
                            </label>
                            <input
                                id="menu-name"
                                v-model="menuForm.name"
                                type="text"
                                placeholder="Ej. Carta de Verano"
                                :class="[inputClass, menuForm.errors.name && 'border-red-500']"
                            />
                            <p v-if="menuForm.errors.name" :class="errorClass">{{ menuForm.errors.name }}</p>
                        </div>

                        <div class="space-y-2">
                            <label for="menu-desc" :class="labelClass">Descripción</label>
                            <input
                                id="menu-desc"
                                v-model="menuForm.description"
                                type="text"
                                placeholder="Una breve descripción (opcional)"
                                :class="inputClass"
                            />
                        </div>

                        <button
                            type="submit"
                            :disabled="menuForm.processing"
                            class="mt-2 flex h-11 w-full items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-purple-600 to-pink-600 px-6 font-semibold text-white shadow-lg shadow-purple-500/20 transition-all hover:from-purple-500 hover:to-pink-500 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            <Loader2 v-if="menuForm.processing" class="h-4 w-4 animate-spin" />
                            <span>Continuar</span>
                            <span v-if="!menuForm.processing">→</span>
                        </button>
                    </form>
                </template>

                <!-- ── Paso 2: Tus platos ── -->
                <template v-else-if="step === 2">
                    <h1 class="mb-2 text-2xl font-bold text-white">Tus platos</h1>
                    <p class="mb-6 text-sm text-slate-400">
                        Añade al menos un producto a tu menú. Podrás añadir más desde el panel.
                    </p>

                    <div class="mb-4 space-y-4">
                        <div
                            v-for="(row, index) in productRows"
                            :key="index"
                            class="rounded-lg border border-slate-800 bg-slate-950/50 p-4"
                        >
                            <div class="mb-3 flex items-center justify-between">
                                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                                    Plato #{{ index + 1 }}
                                </span>
                                <button
                                    v-if="productRows.length > 1"
                                    type="button"
                                    class="text-slate-500 transition-colors hover:text-red-400"
                                    title="Eliminar"
                                    @click="removeProduct(index)"
                                >
                                    <X class="h-4 w-4" />
                                </button>
                            </div>

                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-[1fr_7rem]">
                                <div class="space-y-2">
                                    <label :class="labelClass">Nombre</label>
                                    <input
                                        v-model="row.name"
                                        type="text"
                                        placeholder="Ej. Ensalada César"
                                        :class="inputClass"
                                    />
                                </div>
                                <div class="space-y-2">
                                    <label :class="labelClass">Precio (€)</label>
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
                                <label :class="labelClass">Sección</label>
                                <select v-model="row.section_name" :class="inputClass">
                                    <option v-for="opt in sectionOptions" :key="opt" :value="opt" class="bg-slate-900">
                                        {{ opt }}
                                    </option>
                                    <option value="__custom__" class="bg-slate-900">Otra…</option>
                                </select>
                                <input
                                    v-if="row.section_name === '__custom__'"
                                    type="text"
                                    placeholder="Ej. Tapas"
                                    :class="[inputClass, 'mt-2']"
                                    @input="(e: Event) => (row.section_name = (e.target as HTMLInputElement).value)"
                                />
                            </div>
                        </div>
                    </div>

                    <button
                        type="button"
                        class="mb-6 inline-flex items-center gap-1.5 text-sm font-medium text-purple-400 transition-colors hover:text-purple-300"
                        @click="addProduct"
                    >
                        <Plus class="h-4 w-4" />
                        Añadir otro plato
                    </button>

                    <p v-if="productsError" :class="[errorClass, 'mb-3']">{{ productsError }}</p>

                    <button
                        type="button"
                        :disabled="productsProcessing"
                        class="flex h-11 w-full items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-purple-600 to-pink-600 px-6 font-semibold text-white shadow-lg shadow-purple-500/20 transition-all hover:from-purple-500 hover:to-pink-500 disabled:cursor-not-allowed disabled:opacity-60"
                        @click="submitProducts"
                    >
                        <Loader2 v-if="productsProcessing" class="h-4 w-4 animate-spin" />
                        <span>Continuar</span>
                        <span v-if="!productsProcessing">→</span>
                    </button>
                </template>

                <!-- ── Paso 3: ¡Listo! ── -->
                <template v-else-if="step === 3">
                    <div class="mb-4 flex items-center justify-center">
                        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-br from-purple-500 to-pink-500 text-4xl shadow-lg shadow-purple-500/30">
                            🎉
                        </div>
                    </div>
                    <h1 class="mb-2 text-center text-2xl font-bold text-white">¡Tu menú está casi listo!</h1>
                    <p class="mb-6 text-center text-sm text-slate-400">
                        Haz clic en "Finalizar" para generar el código QR de tu menú y acceder al panel de administración.
                    </p>

                    <div class="mb-6 rounded-lg border border-slate-800 bg-slate-950/50 p-5">
                        <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Resumen</p>
                        <ul class="space-y-2 text-sm">
                            <li v-if="location" class="flex items-start gap-2">
                                <Check class="mt-0.5 h-4 w-4 shrink-0 text-purple-400" />
                                <span class="text-slate-400">Local: <span class="font-medium text-white">{{ location.name }}</span></span>
                            </li>
                            <li v-if="menu" class="flex items-start gap-2">
                                <Check class="mt-0.5 h-4 w-4 shrink-0 text-purple-400" />
                                <span class="text-slate-400">Menú: <span class="font-medium text-white">{{ menu.name }}</span></span>
                            </li>
                            <li v-if="products?.length" class="flex items-start gap-2">
                                <Check class="mt-0.5 h-4 w-4 shrink-0 text-purple-400" />
                                <span class="text-slate-400">
                                    <span class="font-medium text-white">{{ products.length }}</span> producto{{ products.length === 1 ? '' : 's' }} añadido{{ products.length === 1 ? '' : 's' }}
                                </span>
                            </li>
                        </ul>
                    </div>

                    <button
                        type="button"
                        :disabled="completeProcessing"
                        class="flex h-11 w-full items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-purple-600 to-pink-600 px-6 font-semibold text-white shadow-lg shadow-purple-500/20 transition-all hover:from-purple-500 hover:to-pink-500 disabled:cursor-not-allowed disabled:opacity-60"
                        @click="submitComplete"
                    >
                        <Loader2 v-if="completeProcessing" class="h-4 w-4 animate-spin" />
                        <Sparkles v-else class="h-4 w-4" />
                        <span>Finalizar y generar QR</span>
                    </button>
                </template>
            </div>

            <!-- Footer -->
            <p class="mt-8 text-xs text-slate-600">
                Paso {{ step + 1 }} de {{ steps.length }}
            </p>
        </div>
    </div>
</template>
