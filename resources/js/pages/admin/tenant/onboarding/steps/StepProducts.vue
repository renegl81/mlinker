<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Loader2, Plus, UtensilsCrossed, X } from 'lucide-vue-next';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

interface ProductRow {
    name: string;
    price: string;
}

const props = defineProps<{
    sectionName: string;
    menuId: number;
    allSections: Array<{ name: string; products: ProductRow[] }>;
}>();

const emit = defineEmits<{
    addSection: [];
    back: [];
    done: [];
}>();

// Products for the current section
const rows = ref<ProductRow[]>([{ name: '', price: '' }]);
const error = ref('');
const processing = ref(false);

function addRow() {
    rows.value.push({ name: '', price: '' });
}

function removeRow(index: number) {
    if (rows.value.length > 1) rows.value.splice(index, 1);
}

function validate(): boolean {
    return rows.value.every((r) => r.name.trim() !== '' && r.price !== '');
}

function buildPayload() {
    const products: Array<{ name: string; price: string; section_name: string }> = [];

    // Include all previously saved sections from parent
    for (const sec of props.allSections) {
        for (const p of sec.products) {
            products.push({ name: p.name, price: p.price, section_name: sec.name });
        }
    }

    // Add current section rows
    for (const r of rows.value) {
        products.push({ name: r.name, price: r.price, section_name: props.sectionName });
    }

    return { menu_id: props.menuId, products };
}

function submit() {
    error.value = '';
    if (!validate()) {
        error.value = t('panel.onboarding.products_error');
        return;
    }

    processing.value = true;
    router.post(route('tenant.onboarding.products'), buildPayload(), {
        preserveScroll: true,
        onFinish: () => (processing.value = false),
    });
}

function addAnotherSection() {
    if (!validate()) {
        error.value = t('panel.onboarding.products_error');
        return;
    }
    error.value = '';
    emit('addSection', rows.value as any);
}

const inputClass =
    'flex h-10 w-full rounded-lg border border-slate-700 bg-slate-900/60 px-3 py-2 text-sm text-white placeholder:text-slate-500 transition-colors focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/30';
</script>

<template>
    <div>
        <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-teal-500/15">
            <UtensilsCrossed class="h-6 w-6 text-teal-400" />
        </div>
        <h1 class="mb-2 text-2xl font-bold text-white">
            {{ t('panel.onboarding.products_title', { section: props.sectionName }) }}
        </h1>
        <p class="mb-6 text-sm text-slate-400">
            {{ t('panel.onboarding.products_subtitle') }}
        </p>

        <!-- Previously added sections (summary chips) -->
        <div v-if="props.allSections.length > 0" class="mb-4 flex flex-wrap gap-2">
            <span
                v-for="sec in props.allSections"
                :key="sec.name"
                class="inline-flex items-center gap-1 rounded-full bg-teal-500/10 border border-teal-500/30 px-3 py-1 text-xs font-medium text-teal-300"
            >
                {{ sec.name }}
                <span class="text-teal-500/60">({{ sec.products.length }})</span>
            </span>
        </div>

        <!-- Current section header -->
        <div class="mb-3 flex items-center gap-2">
            <span class="rounded-lg bg-slate-700/60 px-3 py-1.5 text-sm font-semibold text-white">
                {{ props.sectionName }}
            </span>
        </div>

        <!-- Product rows -->
        <div class="mb-4 space-y-2">
            <div
                v-for="(row, i) in rows"
                :key="i"
                class="flex items-center gap-2"
            >
                <input
                    v-model="row.name"
                    type="text"
                    :placeholder="t('panel.onboarding.product_name_placeholder')"
                    :class="[inputClass, 'flex-1']"
                />
                <div class="relative w-24">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm">€</span>
                    <input
                        v-model="row.price"
                        type="number"
                        step="0.01"
                        min="0"
                        :placeholder="t('panel.onboarding.product_price_placeholder')"
                        :class="[inputClass, 'pl-7']"
                    />
                </div>
                <button
                    v-if="rows.length > 1"
                    type="button"
                    class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg text-slate-500 transition-colors hover:bg-slate-800 hover:text-red-400"
                    :title="t('common.delete')"
                    @click="removeRow(i)"
                >
                    <X class="h-4 w-4" />
                </button>
            </div>
        </div>

        <!-- Add row -->
        <button
            type="button"
            class="mb-5 inline-flex items-center gap-1.5 text-sm font-medium text-teal-400 transition-colors hover:text-teal-300"
            @click="addRow"
        >
            <Plus class="h-4 w-4" />
            {{ t('panel.onboarding.add_product') }}
        </button>

        <p v-if="error" class="mb-3 text-xs text-red-400">{{ error }}</p>

        <!-- Actions -->
        <div class="flex flex-col gap-3">
            <button
                type="button"
                :disabled="processing"
                class="flex h-11 w-full items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-teal-600 to-cyan-600 px-6 font-semibold text-white shadow-lg shadow-teal-500/20 transition-all hover:from-teal-500 hover:to-cyan-500 disabled:cursor-not-allowed disabled:opacity-60"
                @click="submit"
            >
                <Loader2 v-if="processing" class="h-4 w-4 animate-spin" />
                <span>{{ t('panel.onboarding.publish_cta') }}</span>
            </button>

            <button
                type="button"
                class="inline-flex items-center justify-center gap-1.5 text-sm font-medium text-teal-400 transition-colors hover:text-teal-300"
                @click="addAnotherSection"
            >
                {{ t('panel.onboarding.add_section') }}
            </button>

            <button
                type="button"
                class="text-sm text-slate-500 transition-colors hover:text-slate-300"
                @click="emit('back')"
            >
                ← {{ t('panel.onboarding.back') }}
            </button>
        </div>
    </div>
</template>
