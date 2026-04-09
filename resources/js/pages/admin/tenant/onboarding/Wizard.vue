<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

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

function submitMenu() {
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

function submitProducts() {
    productsError.value = '';
    const valid = productRows.value.every((p) => p.name.trim() && p.price && p.section_name.trim());
    if (!valid) {
        productsError.value = 'Completa todos los campos de los productos.';
        return;
    }

    router.post(
        route('tenant.onboarding.products'),
        {
            menu_id: props.menu?.id,
            products: productRows.value,
        },
        { preserveScroll: true },
    );
}

// ─── Step 3: Complete ────────────────────────────────────────────────────────
function submitComplete() {
    router.post(route('tenant.onboarding.complete'), { menu_id: props.menu?.id });
}

// ─── Progress ────────────────────────────────────────────────────────────────
const steps = [
    { label: 'Tu negocio' },
    { label: 'Tu menú' },
    { label: 'Tus platos' },
    { label: '¡Listo!' },
];

const progressPercent = computed(() => Math.round((props.step / (steps.length - 1)) * 100));
</script>

<template>
    <div class="flex min-h-svh flex-col items-center justify-start bg-background px-4 py-10">
        <!-- Logo -->
        <div class="mb-8 flex items-center gap-2">
            <img src="/logo.png" alt="MenuLinker" class="h-8" />
            <span class="text-lg font-semibold">MenuLinker</span>
        </div>

        <!-- Progress indicator -->
        <div class="mb-8 w-full max-w-lg">
            <div class="mb-2 flex justify-between">
                <span
                    v-for="(s, i) in steps"
                    :key="i"
                    class="text-xs font-medium"
                    :class="i <= step ? 'text-primary' : 'text-muted-foreground'"
                >
                    {{ s.label }}
                </span>
            </div>
            <div class="h-2 w-full overflow-hidden rounded-full bg-muted">
                <div
                    class="h-2 rounded-full bg-primary transition-all duration-300"
                    :style="{ width: progressPercent + '%' }"
                />
            </div>
        </div>

        <!-- Card -->
        <div class="w-full max-w-lg rounded-xl border bg-card p-8 shadow-sm">

            <!-- ── Paso 0: Tu negocio ── -->
            <template v-if="step === 0">
                <h1 class="mb-1 text-2xl font-semibold">Tu negocio</h1>
                <p class="mb-6 text-sm text-muted-foreground">Cuéntanos un poco sobre tu local. Esto solo tomará un minuto.</p>

                <form @submit.prevent="submitLocation" class="space-y-4">
                    <div class="space-y-1">
                        <Label for="loc-name">Nombre del local <span class="text-destructive">*</span></Label>
                        <Input id="loc-name" v-model="locationForm.name" placeholder="Ej. Cafetería El Sol" :class="{ 'border-destructive': locationForm.errors.name }" />
                        <p v-if="locationForm.errors.name" class="text-xs text-destructive">{{ locationForm.errors.name }}</p>
                    </div>
                    <div class="space-y-1">
                        <Label for="loc-address">Dirección</Label>
                        <Input id="loc-address" v-model="locationForm.address" placeholder="Calle Mayor, 10" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <Label for="loc-city">Ciudad</Label>
                            <Input id="loc-city" v-model="locationForm.city" placeholder="Madrid" />
                        </div>
                        <div class="space-y-1">
                            <Label for="loc-phone">Teléfono</Label>
                            <Input id="loc-phone" v-model="locationForm.phone" placeholder="+34 600 000 000" />
                        </div>
                    </div>
                    <Button type="submit" class="w-full" :disabled="locationForm.processing">
                        Continuar →
                    </Button>
                </form>
            </template>

            <!-- ── Paso 1: Tu menú ── -->
            <template v-else-if="step === 1">
                <h1 class="mb-1 text-2xl font-semibold">Tu menú</h1>
                <p class="mb-6 text-sm text-muted-foreground">Dale un nombre a tu menú digital.</p>

                <form @submit.prevent="submitMenu" class="space-y-4">
                    <input type="hidden" name="location_id" :value="location?.id" />
                    <div class="space-y-1">
                        <Label for="menu-name">Nombre del menú <span class="text-destructive">*</span></Label>
                        <Input id="menu-name" v-model="menuForm.name" placeholder="Ej. Carta de Verano" :class="{ 'border-destructive': menuForm.errors.name }" />
                        <p v-if="menuForm.errors.name" class="text-xs text-destructive">{{ menuForm.errors.name }}</p>
                    </div>
                    <div class="space-y-1">
                        <Label for="menu-desc">Descripción</Label>
                        <Input id="menu-desc" v-model="menuForm.description" placeholder="Una breve descripción (opcional)" />
                    </div>
                    <Button type="submit" class="w-full" :disabled="menuForm.processing">
                        Continuar →
                    </Button>
                </form>
            </template>

            <!-- ── Paso 2: Tus platos ── -->
            <template v-else-if="step === 2">
                <h1 class="mb-1 text-2xl font-semibold">Tus platos</h1>
                <p class="mb-6 text-sm text-muted-foreground">Añade al menos un producto a tu menú. Podrás añadir más desde el panel.</p>

                <div class="mb-4 space-y-3">
                    <div
                        v-for="(row, index) in productRows"
                        :key="index"
                        class="grid grid-cols-[1fr_6rem_7rem_auto] gap-2 items-end"
                    >
                        <div class="space-y-1">
                            <Label v-if="index === 0">Nombre</Label>
                            <Input v-model="row.name" placeholder="Ej. Ensalada César" />
                        </div>
                        <div class="space-y-1">
                            <Label v-if="index === 0">Precio (€)</Label>
                            <Input v-model="row.price" type="number" step="0.01" min="0" placeholder="0.00" />
                        </div>
                        <div class="space-y-1">
                            <Label v-if="index === 0">Sección</Label>
                            <select
                                v-model="row.section_name"
                                class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                            >
                                <option v-for="opt in sectionOptions" :key="opt" :value="opt">{{ opt }}</option>
                                <option value="__custom__">Otra...</option>
                            </select>
                        </div>
                        <button
                            type="button"
                            class="mb-0.5 text-muted-foreground hover:text-destructive transition-colors"
                            :class="{ 'mt-5': index === 0 }"
                            @click="removeProduct(index)"
                            :disabled="productRows.length === 1"
                            title="Eliminar"
                        >
                            ✕
                        </button>
                    </div>

                    <!-- Input libre para sección "Otra" -->
                    <template v-for="(row, index) in productRows" :key="'custom-' + index">
                        <div v-if="row.section_name === '__custom__'" class="pl-0">
                            <Label>Nombre de la sección personalizada</Label>
                            <Input v-model="row.section_name" placeholder="Ej. Tapas" @input="(e: Event) => { row.section_name = (e.target as HTMLInputElement).value }" />
                        </div>
                    </template>
                </div>

                <p v-if="productsError" class="mb-2 text-xs text-destructive">{{ productsError }}</p>

                <button
                    type="button"
                    class="mb-6 text-sm text-primary underline-offset-2 hover:underline"
                    @click="addProduct"
                >
                    + Añadir otro plato
                </button>

                <Button class="w-full" @click="submitProducts">
                    Continuar →
                </Button>
            </template>

            <!-- ── Paso 3: ¡Listo! ── -->
            <template v-else-if="step === 3">
                <div class="mb-4 flex items-center justify-center text-5xl">🎉</div>
                <h1 class="mb-1 text-center text-2xl font-semibold">¡Tu menú está casi listo!</h1>
                <p class="mb-6 text-center text-sm text-muted-foreground">
                    Haz clic en "Finalizar" para generar el código QR de tu menú y acceder al panel de administración.
                </p>

                <div class="mb-6 space-y-3">
                    <div class="rounded-lg border bg-muted/40 p-4 text-sm">
                        <p class="font-medium">Resumen:</p>
                        <ul class="mt-1 list-inside list-disc text-muted-foreground">
                            <li v-if="location">Local: <span class="text-foreground">{{ location.name }}</span></li>
                            <li v-if="menu">Menú: <span class="text-foreground">{{ menu.name }}</span></li>
                            <li v-if="products?.length">{{ products.length }} producto(s) añadido(s)</li>
                        </ul>
                    </div>
                </div>

                <Button class="w-full" @click="submitComplete">
                    Finalizar y generar QR
                </Button>
            </template>

        </div>
    </div>
</template>
