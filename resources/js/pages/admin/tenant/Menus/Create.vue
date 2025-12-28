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
                @submit="submit"
                @update:field="updateField"
            />
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import HeadingSmall from '@/components/HeadingSmall.vue'
import MenuForm from './Form.vue'
import type { BreadcrumbItem } from '@/types'
import { index as menusRoute, store } from '@/routes/tenant/menus'

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Menús',
        href: menusRoute().url,
    },
    {
        title: 'Crear',
        href: '#',
    },
]

interface FormState {
    name: string
    description: string
    is_active: boolean
}

const form = useForm<FormState>({
    name: '',
    description: '',
    is_active: true
})

function updateField(field: string, value: any) {
    form[field] = value
}

function submit() {
    form.post(store().url)
}
</script>
