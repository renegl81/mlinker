<template>
    <Head :title="messages.locations.form.title_create" />
    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <HeadingSmall
                :title="messages.locations.form.title_create"
                description=""
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
    name?: string | null;
    description?: string | null;
    address?: string | null;
    phone?: string | null;
    postal_code?: string | null;
    latitude?: string | null;
    longitude?: string | null;
    city?: string | null;
    province?: string | null;
    country_id?: number | null;
    currency?: string | null;
    time_zone?: string | null;
    lang?: string | null;
    languages?: Array<string>;
}

const form = useForm<FormState>({
    name: null,
    description: null,
    address: null,
    phone: null,
    postal_code: null,
    latitude: null,
    longitude: null,
    city: null,
    province: null,
    country_id: null,
    currency: null,
    time_zone: null,
    lang: null,
    languages: null as any,
});
function updateField(field: string, value: any) {
    form[field] = value;
}
function submit() {
    form.post(store().url);
}
</script>
