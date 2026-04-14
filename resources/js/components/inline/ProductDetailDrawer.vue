<script setup lang="ts">
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetDescription,
    SheetFooter,
    SheetClose,
} from '@/components/ui/sheet';
import AllergenIcon from '@/components/ui/AllergenIcon.vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

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

interface Props {
    open: boolean;
    product: Product | null;
    menuId: number;
    allergens: Allergen[];
}

const props = defineProps<Props>();
const emit = defineEmits<{
    'update:open': [value: boolean];
    'saved': [];
}>();

const form = ref({
    name: '',
    description: '',
    price: '',
    calories: '',
    tags: [] as string[],
    allergen_ids: [] as number[],
    ingredient_names: [] as string[],
});

const saving = ref(false);
const ingredientInput = ref('');

watch(() => props.product, (p) => {
    if (!p) return;
    form.value = {
        name: p.name,
        description: p.description ?? '',
        price: p.price !== null && p.price !== undefined ? String(p.price) : '',
        calories: p.calories !== null && p.calories !== undefined ? String(p.calories) : '',
        tags: p.tags ?? [],
        allergen_ids: p.allergens.map((a) => a.id),
        ingredient_names: p.ingredients?.map((i) => i.name) ?? [],
    };
    ingredientInput.value = '';
}, { immediate: true });

const TAG_OPTIONS = ['vegetarian', 'vegan', 'spicy', 'gluten_free'];
const TAG_LABELS: Record<string, string> = {
    vegetarian: '🌱 Vegetariano',
    vegan: '🌿 Vegano',
    spicy: '🌶️ Picante',
    gluten_free: '🚫🌾 Sin gluten',
};

function toggleTag(tag: string) {
    const idx = form.value.tags.indexOf(tag);
    if (idx >= 0) form.value.tags.splice(idx, 1);
    else form.value.tags.push(tag);
}

function toggleAllergen(id: number) {
    const idx = form.value.allergen_ids.indexOf(id);
    if (idx >= 0) form.value.allergen_ids.splice(idx, 1);
    else form.value.allergen_ids.push(id);
}

function addIngredient() {
    const name = ingredientInput.value.trim();
    if (name && !form.value.ingredient_names.includes(name)) {
        form.value.ingredient_names.push(name);
    }
    ingredientInput.value = '';
}

function removeIngredient(name: string) {
    form.value.ingredient_names = form.value.ingredient_names.filter((n) => n !== name);
}

function submit() {
    if (!props.product) return;
    saving.value = true;
    router.put(`/panel/products/${props.product.id}`, {
        name: form.value.name,
        description: form.value.description || null,
        price: form.value.price,
        calories: form.value.calories || null,
        tags: form.value.tags,
        allergen_ids: form.value.allergen_ids,
        ingredient_names: form.value.ingredient_names,
        section_id: null,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            emit('saved');
            emit('update:open', false);
        },
        onFinish: () => {
            saving.value = false;
        },
    });
}
</script>

<template>
    <Sheet :open="open" @update:open="$emit('update:open', $event)">
        <SheetContent side="right" class="w-full sm:max-w-lg overflow-y-auto">
            <SheetHeader>
                <SheetTitle>{{ t('panel.menu_show.edit_dish') }}</SheetTitle>
                <SheetDescription v-if="product">{{ product.name }}</SheetDescription>
            </SheetHeader>

            <div v-if="product" class="mt-4 space-y-4 pb-4">
                <!-- Nombre -->
                <div class="space-y-1.5">
                    <Label for="drawer-name">{{ t('panel.menu_show.product_name') }}</Label>
                    <Input id="drawer-name" v-model="form.name" />
                </div>

                <!-- Descripción -->
                <div class="space-y-1.5">
                    <Label for="drawer-desc">{{ t('panel.menu_show.product_description') }}</Label>
                    <textarea
                        id="drawer-desc"
                        v-model="form.description"
                        rows="3"
                        class="w-full resize-none rounded-md border border-input bg-background px-3 py-2 text-sm outline-none ring-offset-background focus:ring-2 focus:ring-ring"
                    />
                </div>

                <!-- Precio y calorías -->
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1.5">
                        <Label for="drawer-price">{{ t('panel.menu_show.product_price') }}</Label>
                        <Input id="drawer-price" v-model="form.price" type="number" step="0.01" min="0" />
                    </div>
                    <div class="space-y-1.5">
                        <Label for="drawer-calories">{{ t('panel.menu_show.product_calories') }}</Label>
                        <Input id="drawer-calories" v-model="form.calories" type="number" step="1" min="0" />
                    </div>
                </div>

                <!-- Tags -->
                <div class="space-y-1.5">
                    <Label>{{ t('panel.menu_show.product_tags') }}</Label>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="tag in TAG_OPTIONS"
                            :key="tag"
                            type="button"
                            class="rounded-full border px-2.5 py-0.5 text-xs transition-colors"
                            :class="form.tags.includes(tag) ? 'border-teal-500 bg-teal-50 text-teal-700' : 'border-input text-muted-foreground hover:border-teal-400'"
                            @click="toggleTag(tag)"
                        >
                            {{ TAG_LABELS[tag] }}
                        </button>
                    </div>
                </div>

                <!-- Alérgenos -->
                <div class="space-y-1.5">
                    <Label>{{ t('panel.menu_show.product_allergens') }}</Label>
                    <div class="flex flex-wrap gap-2">
                        <button
                            v-for="allergen in allergens"
                            :key="allergen.id"
                            type="button"
                            class="flex items-center gap-1 rounded-full border px-2 py-0.5 text-xs transition-colors"
                            :class="form.allergen_ids.includes(allergen.id) ? 'border-teal-500 bg-teal-50 text-teal-700' : 'border-input text-muted-foreground hover:border-teal-400'"
                            @click="toggleAllergen(allergen.id)"
                        >
                            <AllergenIcon :code="allergen.code" size="xs" />
                            {{ allergen.name }}
                        </button>
                    </div>
                </div>

                <!-- Ingredientes -->
                <div class="space-y-1.5">
                    <Label>{{ t('panel.menu_show.product_ingredients') }}</Label>
                    <div class="flex flex-wrap gap-1 mb-1">
                        <span
                            v-for="ing in form.ingredient_names"
                            :key="ing"
                            class="inline-flex items-center gap-1 rounded-full bg-muted px-2 py-0.5 text-xs"
                        >
                            {{ ing }}
                            <button type="button" class="text-muted-foreground hover:text-destructive" @click="removeIngredient(ing)">×</button>
                        </span>
                    </div>
                    <div class="flex gap-2">
                        <Input
                            v-model="ingredientInput"
                            :placeholder="t('panel.menu_show.product_ingredient_placeholder')"
                            class="flex-1"
                            @keydown.enter.prevent="addIngredient"
                        />
                        <Button type="button" variant="outline" size="sm" @click="addIngredient">+</Button>
                    </div>
                </div>
            </div>

            <SheetFooter class="sticky bottom-0 bg-background pt-2 border-t">
                <SheetClose as-child>
                    <Button variant="outline" type="button">{{ t('common.cancel') }}</Button>
                </SheetClose>
                <Button type="button" :disabled="saving" @click="submit">
                    {{ saving ? '...' : t('common.save') }}
                </Button>
            </SheetFooter>
        </SheetContent>
    </Sheet>
</template>
