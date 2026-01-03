<template>
    <Head title="Editar Usuario" />
    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <HeadingSmall
                title="Editar Usuario"
                description="Modifica la información del usuario"
            />

            <LocationForm
                :form="form"
                title="Información del Usuario"
                description="Actualiza los datos del usuario. Deja la contraseña vacía si no deseas cambiarla."
                submit-text="Guardar Cambios"
                :is-edit="true"
                :roles="props.roles"
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
import type { BreadcrumbItem, Country, Location } from '@/types';
import { index as locationsRoute, update } from '@/routes/tenant/locations';

interface Props {
    location: Location;
    countries: Country[];
}

const props = defineProps<Props>();
const page = usePage()
const messages = page.props.messages
const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: messages.locations.plural,
        href: locationsRoute().url,
    },
    {
        title: messages.actions.edit,
        href: '#',
    },
];

function updateField(field: string, value: any) {
    form[field] = value;
}

const form = useForm({
    name: props.location.name ?? null,
    description: props.location.description ?? null,
    address: props.location.address ?? null,
    phone: props.location.phone ?? null,
    postal_code: props.location.postal_code ?? null,
    latitude: props.location.latitude ?? null,
    longitude: props.location.longitude ?? null,
    city: props.location.city ?? null,
    province: props.location.province ?? null,
    country_id: props.location.country_id ?? null,
    currency: props.location.currency ?? null,
    time_zone: props.location.time_zone ?? null,
    lang: props.location.lang ?? null,
    languages: props.location.languages ?? [],
});

function submit() {
    form.put(update(props.location.id).url);
}
</script>
