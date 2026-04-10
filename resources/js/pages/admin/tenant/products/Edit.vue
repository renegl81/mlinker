<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, useForm, router } from '@inertiajs/vue3';
import type { Allergen, CatalogIngredient, Ingredient, Section } from './Form.vue';
import ProductForm from './Form.vue';

interface ProductFull {
    id: number;
    name: string;
    description: string | null;
    price: string | number;
    calories: string | number | null;
    image_url: string | null;
    image_path?: string | null;
    tags: string[] | null;
    sections: Array<{ id: number; name: string }>;
    allergens: Array<{ id: number; name: string; code: string | null }>;
    ingredients: Array<{ id: number; name: string }>;
}

interface MenuBasic {
    id: number;
    name: string;
    location_id: number;
}

interface Props {
    product: ProductFull;
    menu: MenuBasic | null;
    sections: Section[];
    allergens: Allergen[];
    ingredients: Ingredient[];
    catalogIngredients?: CatalogIngredient[];
}

const props = defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Menús', href: `/panel/locations/${props.menu?.location_id ?? ''}/menus` },
    { title: props.menu?.name ?? 'Menú', href: `/panel/menus/${props.menu?.id ?? ''}` },
    { title: props.product.name, href: '#' },
];

const currentSectionId = props.product.sections[0]?.id ?? null;

const form = useForm({
    name: props.product.name,
    description: props.product.description ?? '',
    price: props.product.price,
    calories: props.product.calories ?? null,
    // Pass the resolved image_path (http URL) so the Form.vue can show the preview
    image_url: props.product.image_path ?? props.product.image_url,
    section_id: currentSectionId,
    allergen_ids: props.product.allergens.map((a) => a.id),
    ingredient_names: props.product.ingredients.map((i) => i.name),
    tags: props.product.tags ?? [],
});

function updateField(field: string, value: unknown) {
    (form as any)[field] = value;
}

function submit() {
    form.put(`/panel/products/${props.product.id}`, {
        preserveScroll: true,
    });
}

function deleteProduct() {
    if (!confirm('¿Eliminar este plato? Esta acción no se puede deshacer.')) return;
    router.delete(`/panel/products/${props.product.id}`);
}
</script>

<template>
    <Head :title="`Editar: ${product.name}`" />

    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <HeadingSmall
                :title="`Editar: ${product.name}`"
                description="Modifica la información del plato"
            />

            <ProductForm
                :form="form"
                :sections="sections"
                :allergens="allergens"
                :ingredients="ingredients"
                :catalog-ingredients="catalogIngredients"
                :cancel-href="`/panel/menus/${menu?.id ?? ''}`"
                :is-edit="true"
                :show-delete-button="true"
                @submit="submit"
                @delete="deleteProduct"
                @update:field="updateField"
            />
        </div>
    </AppLayout>
</template>
