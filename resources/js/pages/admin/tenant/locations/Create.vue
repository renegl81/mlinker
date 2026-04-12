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
const messages = page.props.messages as any;
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

const form = useForm({
    name: null as string | null,
    description: null as string | null,
    address: null as string | null,
    phone: null as string | null,
    postal_code: null as string | null,
    latitude: null as string | null,
    longitude: null as string | null,
    city: null as string | null,
    province: null as string | null,
    country_id: null as number | null,
    currency: null as string | null,
    time_zone: null as string | null,
    lang: null as string | null,
    languages: null as any,
    image_url: null as string | null,
    primary_color: null as string | null,
    secondary_color: null as string | null,
    order_email: null as string | null,
    order_whatsapp: null as string | null,
    is_pet_friendly: false,
    has_wifi: false,
    has_terrace: false,
    has_parking: false,
    is_accessible: false,
    reservation_url: null as string | null,
    reservation_phone: null as string | null,
    instagram: null as string | null,
    facebook: null as string | null,
    google_maps_url: null as string | null,
});

function updateField(field: string, value: any) {
    form[field] = value;
}
function submit() {
    form.post(store().url);
}
</script>
