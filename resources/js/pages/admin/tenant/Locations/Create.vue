<template>
    <Head title="Crear Usuario" />
    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <HeadingSmall
                title="Crear Usuario"
                description="Agrega un nuevo usuario al sistema"
            />

            <LocationForm
                :form="form"
                :title="messages.locations.form.title_create"
                :description="messages.locations.form.description_create"
                :submit-text="messages.locations.form.title_create"
                :countries="props.countries"
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
import LocationForm from './Form.vue';
import type { BreadcrumbItem, Country } from '@/types';
import { index as locationsRoute, store } from '@/routes/tenant/locations';

const page = usePage();
const messages = page.props.messages;
const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: messages.locations.plural,
        href: locationsRoute().url,
    },
    {
        title: messages.actions.create,
        href: '#',
    },
];
interface Props {
    countries: Array<Country>;
}
const props = defineProps<Props>();
interface FormState {
    name: string;
    description: string;
    address: string;
    phone: string;
    postal_code: string;
    latitude: string;
    longitude: string;
    city: string;
    province: string;
    country_id: number;
    currency: string;
    time_zone: string;
    lang: string;
    languages: Object<string, string>;
}

const form = useForm<FormState>({
    name: '',
    description: '',
    address: '',
    phone: '',
    postal_code: '',
    latitude: '',
    longitude: '',
    city: '',
    province: '',
    country_id: 0,
    currency: '',
    time_zone: '',
    lang: '',
    languages: {} as any,
});
function updateField(field: string, value: any) {
    form[field] = value;
}
function submit() {
    form.post(store().url);
}
</script>
