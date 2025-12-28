<template>
    <Head title="Editar Menú" />
    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <HeadingSmall
                title="Editar Menú"
                description="Modifica la información del menú"
            />

            <MenuForm
                :form="form"
                title="Información del Menú"
                description="Actualiza los datos del menú."
                submit-text="Guardar Cambios"
                :is-edit="true"
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
import { index as menusRoute, update } from '@/routes/tenant/menus'

interface Menu {
    id: number
    name: string
    description: string | null
    is_active: boolean
}

interface Props {
    menu: Menu
}

const props = defineProps<Props>()

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Menús',
        href: menusRoute().url,
    },
    {
        title: 'Editar',
        href: '#',
    },
]

function updateField(field: string, value: any) {
    form[field] = value
}

const form = useForm({
    name: props.menu.name,
    description: props.menu.description || '',
    is_active: props.menu.is_active
})

function submit() {
    form.put(update(props.menu.id).url)
}
</script>
