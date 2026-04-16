<template>
    <Head title="Crear Usuario" />
    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <HeadingSmall
                title="Crear Usuario"
                description="Agrega un nuevo usuario al sistema"
            />

            <UserForm
                :form="form"
                title="Información del Usuario"
                description="Completa los datos del nuevo usuario."
                submit-text="Crear Usuario"
                :roles="props.roles"
                @submit="submit"
                @update:name="form.name = $event"
                @update:email="form.email = $event"
                @update:password="form.password = $event"
                @update:password-confirmation="form.password_confirmation = $event"
            />
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import HeadingSmall from '@/components/HeadingSmall.vue'
import UserForm from './Form.vue'
import type { BreadcrumbItem, Role } from '@/types'
import { index as usersRoute, store } from '@/routes/users'

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Usuarios',
        href: usersRoute().url,
    },
    {
        title: 'Crear',
        href: '#',
    },
]
interface Props {
    roles: Role[]
}

const props = defineProps<Props>()

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: ''
})

function submit() {
    form.post(store().url)
}
</script>
