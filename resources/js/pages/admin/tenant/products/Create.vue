<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import type { Allergen, CatalogIngredient, Ingredient, Section } from './Form.vue';
import ProductForm from './Form.vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

interface Menu {
    id: number;
    name: string;
    location_id: number;
}

interface Props {
    menu: Menu;
    sections: Section[];
    allergens: Allergen[];
    ingredients: Ingredient[];
    catalogIngredients?: CatalogIngredient[];
    defaultSectionId?: string | null;
}

const props = defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    { title: t('nav.menus'), href: `/panel/locations/${props.menu.location_id}/menus` },
    { title: props.menu.name, href: `/panel/menus/${props.menu.id}` },
    { title: t('panel.product_form.create_title'), href: '#' },
];

const form = useForm({
    name: '',
    description: '',
    price: '',
    calories: null as number | null,
    image_url: null as string | null,
    section_id: props.defaultSectionId ? Number(props.defaultSectionId) : (props.sections[0]?.id ?? null),
    allergen_ids: [] as number[],
    ingredient_names: [] as string[],
    tags: [] as string[],
});

function updateField(field: string, value: unknown) {
    (form as any)[field] = value;
}

function submit() {
    form.post(`/panel/menus/${props.menu.id}/products`, {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head :title="t('panel.product_form.create_title')" />

    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <HeadingSmall
                :title="t('panel.product_form.create_title')"
                :description="t('panel.product_form.create_description')"
            />

            <ProductForm
                :form="form"
                :sections="sections"
                :allergens="allergens"
                :ingredients="ingredients"
                :catalog-ingredients="catalogIngredients"
                :cancel-href="`/panel/menus/${menu.id}`"
                @submit="submit"
                @update:field="updateField"
            />
        </div>
    </AppLayout>
</template>
