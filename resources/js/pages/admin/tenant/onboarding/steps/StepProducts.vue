<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Loader2, Plus, UtensilsCrossed, X } from 'lucide-vue-next';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

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
</script>

<template>
    <div>
        <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10">
            <UtensilsCrossed class="h-6 w-6 text-primary" />
        </div>
        <h1 class="mb-2 text-2xl font-bold text-foreground">
            {{ t('panel.onboarding.products_title', { section: props.sectionName }) }}
        </h1>
        <p class="mb-6 text-sm text-muted-foreground">
            {{ t('panel.onboarding.products_subtitle') }}
        </p>

        <!-- Previously added sections (summary chips) -->
        <div v-if="props.allSections.length > 0" class="mb-4 flex flex-wrap gap-2">
            <span
                v-for="sec in props.allSections"
                :key="sec.name"
                class="inline-flex items-center gap-1 rounded-full bg-primary/10 border border-primary/20 px-3 py-1 text-xs font-medium text-primary"
            >
                {{ sec.name }}
                <span class="text-primary/60">({{ sec.products.length }})</span>
            </span>
        </div>

        <!-- Current section header -->
        <div class="mb-3 flex items-center gap-2">
            <span class="rounded-lg bg-muted px-3 py-1.5 text-sm font-semibold text-foreground">
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
                <Input
                    v-model="row.name"
                    type="text"
                    :placeholder="t('panel.onboarding.product_name_placeholder')"
                    class="flex-1"
                />
                <div class="relative w-24">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground text-sm">€</span>
                    <Input
                        v-model="row.price"
                        type="number"
                        step="0.01"
                        min="0"
                        :placeholder="t('panel.onboarding.product_price_placeholder')"
                        class="pl-7"
                    />
                </div>
                <Button
                    v-if="rows.length > 1"
                    type="button"
                    variant="ghost"
                    size="icon"
                    class="flex-shrink-0 text-muted-foreground hover:text-destructive"
                    :title="t('common.delete')"
                    @click="removeRow(i)"
                >
                    <X class="h-4 w-4" />
                </Button>
            </div>
        </div>

        <!-- Add row -->
        <Button
            type="button"
            variant="ghost"
            size="sm"
            class="mb-5 text-primary hover:text-primary/80"
            @click="addRow"
        >
            <Plus class="h-4 w-4" />
            {{ t('panel.onboarding.add_product') }}
        </Button>

        <p v-if="error" class="mb-3 text-xs text-destructive">{{ error }}</p>

        <!-- Actions -->
        <div class="flex flex-col gap-3">
            <Button
                type="button"
                :disabled="processing"
                class="w-full"
                size="lg"
                @click="submit"
            >
                <Loader2 v-if="processing" class="h-4 w-4 animate-spin" />
                <span>{{ t('panel.onboarding.publish_cta') }}</span>
            </Button>

            <Button
                type="button"
                variant="outline"
                size="sm"
                @click="addAnotherSection"
            >
                {{ t('panel.onboarding.add_section') }}
            </Button>

            <Button
                type="button"
                variant="ghost"
                class="text-muted-foreground"
                @click="emit('back')"
            >
                ← {{ t('panel.onboarding.back') }}
            </Button>
        </div>
    </div>
</template>
