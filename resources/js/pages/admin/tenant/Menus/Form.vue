<template>
    <Card class="my-5">
        <form @submit.prevent="$emit('submit')">
            <CardHeader>
                <CardTitle>{{ title }}</CardTitle>
                <CardDescription>{{ description }}</CardDescription>
            </CardHeader>
            <CardContent>
                <div class="grid grid-cols-1 gap-4 my-5">
                    <div class="space-y-2">
                        <Label for="name">{{ page.props.messages.menus.fields.name }}</Label>
                        <Input
                            id="name"
                            :model-value="form.name"
                            @update:model-value="$emit('update:field', 'name', $event)"
                            :placeholder="page.props.messages.menus.placeholders.name"
                            :class="{ 'border-destructive': form.errors.name }"
                        />
                        <p v-if="form.errors.name" class="text-sm text-destructive">
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="description">{{ page.props.messages.menus.fields.description }}</Label>
                        <textarea
                            id="description"
                            :value="form.description"
                            @input="$emit('update:field', 'description', ($event.target as HTMLTextAreaElement).value)"
                            :placeholder="page.props.messages.menus.placeholders.description"
                            :class="[
                                'flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
                                { 'border-destructive': form.errors.description }
                            ]"
                            rows="3"
                        />
                        <p v-if="form.errors.description" class="text-sm text-destructive">
                            {{ form.errors.description }}
                        </p>
                    </div>

                    <div class="flex items-center space-x-2">
                        <button
                            type="button"
                            role="switch"
                            :aria-checked="form.is_active"
                            @click="$emit('update:field', 'is_active', !form.is_active)"
                            :class="[
                                'peer inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background disabled:cursor-not-allowed disabled:opacity-50',
                                form.is_active ? 'bg-primary' : 'bg-input'
                            ]"
                        >
                            <span
                                :class="[
                                    'pointer-events-none block h-5 w-5 rounded-full bg-background shadow-lg ring-0 transition-transform',
                                    form.is_active ? 'translate-x-5' : 'translate-x-0'
                                ]"
                            />
                        </button>
                        <Label for="is_active" class="cursor-pointer">
                            {{ page.props.messages.menus.fields.is_active }}
                        </Label>
                    </div>
                </div>
            </CardContent>

            <CardFooter class="flex justify-between my-5">
                <Button variant="outline" type="button" as-child>
                    <Link :href="menusRoute().url">
                        <X class="mr-2 h-4 w-4" />
                        {{ page.props.messages.menus.actions.cancel }}
                    </Link>
                </Button>
                <Button type="submit" :disabled="form.processing">
                    <Save class="mr-2 h-4 w-4" />
                    {{ form.processing ? processingText : submitText }}
                </Button>
            </CardFooter>
        </form>
    </Card>
</template>

<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card'
import { Save, X } from 'lucide-vue-next'
import { index as menusRoute } from '@/routes/tenant/menus'

interface Props {
    form: {
        name: string
        description: string
        is_active: boolean
        errors: Record<string, string>
        processing: boolean
    }
    title?: string
    description?: string
    submitText?: string
    processingText?: string
    isEdit?: boolean
}

const props = withDefaults(defineProps<Props>(), {
    isEdit: false
})

const page = usePage()

const title = computed(() => {
    if (props.title) return props.title
    return props.isEdit
        ? page.props.messages.menus.form.title_edit
        : page.props.messages.menus.form.title_create
})

const description = computed(() => {
    if (props.description) return props.description
    return props.isEdit
        ? page.props.messages.menus.form.description_edit
        : page.props.messages.menus.form.description_create
})

const submitText = computed(() => {
    return props.submitText || page.props.messages.menus.actions.save
})

const processingText = computed(() => {
    return props.processingText || page.props.messages.menus.actions.saving
})

defineEmits<{
    'submit': []
    'update:field': [field: string, value: any]
}>()
</script>
