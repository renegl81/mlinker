<template>
    <Head title="Crear Menú" />
    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <HeadingSmall
                title="Crear Menú"
                description="Agrega un nuevo menú al sistema"
            />

            <MenuForm
                :form="form"
                title="Información del Menú"
                description="Completa los datos del nuevo menú."
                submit-text="Crear Menú"
                :location="props.location"
                :templates="props.templates"
                @submit="submit"
                @update:field="updateField"
            />
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import MenuForm from './Form.vue';
import type { BreadcrumbItem, Location, Template } from '@/types';
import { index as menusRoute, store } from '@/routes/tenant/locations/menus';

const props = defineProps<{
    location: Location;
    templates: Template[];
}>();

const page = usePage();
const messages = page.props.messages as any;
const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: messages.menus.plural,
        href: menusRoute(props.location.id).url,
    },
    {
        title: messages.actions.create,
        href: '#',
    },
];

interface FormState {
    name: string;
    description: string;
    is_active: boolean;
    location_id: number;
    template_id?: number | null;
    show_currency: boolean;
    show_prices: boolean;
    show_calories: boolean;
    image_url: File | null;
}

const form = useForm<FormState>({
    name: '',
    description: '',
    is_active: true,
    location_id: props.location.id,
    template_id: null,
    show_currency: true,
    show_prices: true,
    show_calories: true,
    image_url: null,
});

function updateField<K extends keyof FormState>(
    field: K,
    value: FormState[K] | any,
) {
    (form as any)[field] = value;
}

function submit() {
    form.post(store(props.location.id).url);
}
</script>
