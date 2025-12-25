<template>
    <Card class="my-5">
        <form @submit.prevent="$emit('submit')">
            <CardHeader>
                <CardTitle>{{ title }}</CardTitle>
                <CardDescription>{{ description }}</CardDescription>
            </CardHeader>
            <CardContent>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 my-5">
                    <div class="space-y-2">
                        <Label for="name">{{ page.props.messages.users.fields.name }}</Label>
                        <Input
                            id="name"
                            :model-value="form.name"
                            @update:model-value="$emit('update:name', $event)"
                            :placeholder="page.props.messages.users.placeholders.name"
                            :class="{ 'border-destructive': form.errors.name }"
                        />
                        <p v-if="form.errors.name" class="text-sm text-destructive">
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="email">{{ page.props.messages.users.fields.email }}</Label>
                        <Input
                            id="email"
                            type="email"
                            :model-value="form.email"
                            @update:model-value="$emit('update:email', $event)"
                            :placeholder="page.props.messages.users.placeholders.email"
                            :class="{ 'border-destructive': form.errors.email }"
                        />
                        <p v-if="form.errors.email" class="text-sm text-destructive">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="password">
                            {{ page.props.messages.users.fields.password }}
                            <span v-if="isEdit" class="text-muted-foreground text-xs">({{ page.props.messages.users.fields.optional }})</span>
                        </Label>
                        <Input
                            id="password"
                            type="password"
                            :model-value="form.password"
                            @update:model-value="$emit('update:password', $event)"
                            :placeholder="isEdit ? page.props.messages.users.placeholders.password_edit : page.props.messages.users.placeholders.password"
                            :class="{ 'border-destructive': form.errors.password }"
                        />
                        <p v-if="form.errors.password" class="text-sm text-destructive">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="password_confirmation">
                            {{ page.props.messages.users.fields.password_confirmation }}
                            <span v-if="isEdit" class="text-muted-foreground text-xs">({{ page.props.messages.users.fields.optional }})</span>
                        </Label>
                        <Input
                            id="password_confirmation"
                            type="password"
                            :model-value="form.password_confirmation"
                            @update:model-value="$emit('update:passwordConfirmation', $event)"
                            :placeholder="page.props.messages.users.placeholders.password_confirmation"
                            :class="{ 'border-destructive': form.errors.password_confirmation }"
                        />
                    </div>

                    <div class="space-y-2">
                        <Label for="role">{{ page.props.messages.users.fields.role }}</Label>
                        <SelectRoot
                            multiple="multiple"
                            :model-value="form.roles.map(role => role.id.toString())"
                            @update:model-value="$emit('update:roles', $event)"
                        >
                            <SelectTrigger
                                id="role"
                                :class="cn(
                                    'flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
                                    { 'border-destructive': form.errors.roles }
                                )"
                            >
                                <SelectValue :placeholder="page.props.messages.users.placeholders.roles" />
                                <SelectIcon>
                                    <ChevronDown class="h-4 w-4 opacity-50" />
                                </SelectIcon>
                            </SelectTrigger>
                            <SelectPortal>
                                <SelectContent
                                    class="relative z-50 max-h-96 min-w-[8rem] overflow-hidden rounded-md border bg-popover text-popover-foreground shadow-md"
                                    position="popper"
                                >
                                    <SelectViewport class="p-1">
                                        <SelectItem
                                            v-for="role in props.roles"
                                            :key="role.id"
                                            :value="role.id.toString()"
                                            class="relative flex w-full cursor-default select-none items-center rounded-sm py-1.5 pl-8 pr-2 text-sm outline-none focus:bg-accent focus:text-accent-foreground"
                                        >
                                            <SelectItemIndicator class="absolute left-2 flex h-3.5 w-3.5 items-center justify-center">
                                                <Check class="h-4 w-4" />
                                            </SelectItemIndicator>
                                            <SelectItemText>{{ role.name }}</SelectItemText>
                                        </SelectItem>
                                    </SelectViewport>
                                </SelectContent>
                            </SelectPortal>
                        </SelectRoot>
                        <p v-if="form.errors.role" class="text-sm text-destructive">
                            {{ form.errors.role }}
                        </p>
                    </div>
                </div>
            </CardContent>

            <CardFooter class="flex justify-between my-5">
                <Button variant="outline" type="button" as-child>
                    <Link :href="usersRoute().url">
                        <X class="mr-2 h-4 w-4" />
                        {{ page.props.messages.users.actions.cancel }}
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
import {
    SelectRoot,
    SelectTrigger,
    SelectValue,
    SelectIcon,
    SelectPortal,
    SelectContent,
    SelectViewport,
    SelectItem,
    SelectItemText,
    SelectItemIndicator
} from 'reka-ui'
import { Save, X, ChevronDown, Check } from 'lucide-vue-next'
import { index as usersRoute } from '@/routes/tenant/users'
import { Role } from '@/types'
import { cn } from '@/lib/utils'

interface Props {
    form: {
        name: string
        email: string
        password: string
        password_confirmation: string
        role: string
        errors: Record<string, string>
        processing: boolean,
        roles: Role[]
    }
    title?: string
    description?: string
    submitText?: string
    processingText?: string
    isEdit?: boolean
    roles: Role[]
}

const props = withDefaults(defineProps<Props>(), {
    isEdit: false
})

const page = usePage()

const title = computed(() => {
    if (props.title) return props.title
    return props.isEdit
        ? page.props.messages.users.form.title_edit
        : page.props.messages.users.form.title_create
})

const description = computed(() => {
    if (props.description) return props.description
    return props.isEdit
        ? page.props.messages.users.form.description_edit
        : page.props.messages.users.form.description_create
})

const submitText = computed(() => {
    return props.submitText || page.props.messages.users.actions.save
})

const processingText = computed(() => {
    return props.processingText || page.props.messages.users.actions.saving
})

defineEmits<{
    'submit': []
    'update:name': [value: string]
    'update:email': [value: string]
    'update:password': [value: string]
    'update:passwordConfirmation': [value: string]
    'update:role': [value: string]
}>()
</script>
