<template>
    <Head :title="t('panel.menus.edit_title')" />
    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <HeadingSmall
                :title="t('panel.menus.edit_title')"
                :description="t('panel.menus.edit_description')"
            />

            <MenuForm
                :form="form"
                :title="t('panel.menus.info_title')"
                :description="t('panel.menus.edit_form_description')"
                :submit-text="t('common.save')"
                :is-edit="true"
                :location="props.location"
                :templates="props.templates"
                @submit="submit"
                @update:field="updateField"
            />
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { index as locationMenusRoute } from '@/routes/tenant/locations/menus'
import { update } from '@/routes/tenant/menus'
import type { BreadcrumbItem, Location, Template } from '@/types'
import { Head, useForm } from '@inertiajs/vue3'
import MenuForm from './Form.vue'
import { useI18n } from 'vue-i18n'

const { t } = useI18n()

interface Menu {
    id: number
    location_id: number
    name: string
    description: string | null
    is_active: boolean
    template_id: number | null
    show_currency: boolean
    show_prices: boolean
    show_calories: boolean
    image_url: string | null
    image_path: string | null
}

interface Props {
    menu: Menu
    location: Location
    templates: Template[]
}

const props = defineProps<Props>()

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: t('nav.menus'),
        href: locationMenusRoute(props.menu.location_id).url,
    },
    {
        title: t('common.edit'),
        href: '#',
    },
]

interface FormState {
    name: string
    description: string
    is_active: boolean
    template_id: number | null
    show_currency: boolean
    show_prices: boolean
    show_calories: boolean
    image_url: string | null
}

const form = useForm<FormState>({
    name: props.menu.name,
    description: props.menu.description ?? '',
    is_active: props.menu.is_active,
    template_id: props.menu.template_id ?? null,
    show_currency: props.menu.show_currency,
    show_prices: props.menu.show_prices,
    show_calories: props.menu.show_calories,
    // Use the resolved URL (image_path accessor) so the preview displays
    // correctly. On submit, if the user does not upload a new file, we send
    // the original image_url back to the backend.
    image_url: props.menu.image_path ?? props.menu.image_url ?? null,
})

function updateField<K extends keyof FormState>(field: K, value: FormState[K] | unknown) {
    (form as unknown as Record<string, unknown>)[field as string] = value
}

function submit() {
    form.put(update(props.menu.id).url)
}
</script>
