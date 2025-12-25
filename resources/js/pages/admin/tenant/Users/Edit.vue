<template>
    <Head title="Editar Usuario" />
    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <HeadingSmall
                title="Editar Usuario"
                description="Modifica la información del usuario"
            />

            <UserForm
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
import { Head, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import HeadingSmall from '@/components/HeadingSmall.vue'
import UserForm from './Form.vue'
import type { BreadcrumbItem, Role, User } from '@/types'
import { index as usersRoute, update } from '@/routes/tenant/users'

interface Props {
    user: User,
    roles: Role[]
}

const props = defineProps<Props>()

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Usuarios',
        href: usersRoute().url,
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
    name: props.user.name,
    last_name: props.user.last_name,
    email: props.user.email,
    password: '',
    password_confirmation: '',
    roles: props.user.roles
})

function submit() {
    form.put(update(props.user.id).url)
}
</script>
