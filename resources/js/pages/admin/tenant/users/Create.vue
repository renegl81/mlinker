<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ArrowLeft, Save } from 'lucide-vue-next';
import RoleScopeForm from './RoleScopeForm.vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const page = usePage();
const messages = page.props.messages as any;

interface Location {
    id: number;
    name: string;
}

interface Props {
    locations: Location[];
}

defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    { title: messages.users.plural, href: '/panel/users' },
    { title: messages.users.actions.create, href: '#' },
];

const form = useForm({
    name: '',
    last_name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'editor' as 'owner' | 'editor',
    location_scope: 'all' as 'all' | 'locations',
    location_ids: [] as number[],
});

function updateField(field: string, value: unknown) {
    (form as any)[field] = value;
}

function submit() {
    form.post('/panel/users');
}
</script>

<template>
    <Head :title="messages.users.actions.create" />
    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="flex h-full flex-1 flex-col gap-5 rounded-xl p-4 max-w-3xl mx-auto w-full">
            <div class="flex items-center gap-3">
                <Button variant="outline" size="icon" as-child>
                    <Link href="/panel/users">
                        <ArrowLeft class="h-4 w-4" />
                    </Link>
                </Button>
                <HeadingSmall
                    :title="messages.users.actions.create"
                    :description="t('panel.users.create_description')"
                />
            </div>

            <form @submit.prevent="submit" class="space-y-5">
                <div class="rounded-xl border bg-card text-card-foreground p-5 space-y-4">
                    <p class="panel-label">{{ t('panel.users.basic_info') }}</p>
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="space-y-1.5">
                            <Label class="panel-label">{{ messages.users.fields.name }} *</Label>
                            <Input v-model="form.name" class="panel-input" />
                            <p v-if="form.errors.name" class="text-xs text-destructive">{{ form.errors.name }}</p>
                        </div>
                        <div class="space-y-1.5">
                            <Label class="panel-label">{{ messages.users.fields.last_name }}</Label>
                            <Input v-model="form.last_name" class="panel-input" />
                        </div>
                        <div class="space-y-1.5 md:col-span-2">
                            <Label class="panel-label">{{ messages.users.fields.email }} *</Label>
                            <Input v-model="form.email" type="email" class="panel-input" />
                            <p v-if="form.errors.email" class="text-xs text-destructive">{{ form.errors.email }}</p>
                        </div>
                        <div class="space-y-1.5">
                            <Label class="panel-label">{{ messages.users.fields.password }} *</Label>
                            <Input v-model="form.password" type="password" class="panel-input" />
                            <p v-if="form.errors.password" class="text-xs text-destructive">{{ form.errors.password }}</p>
                        </div>
                        <div class="space-y-1.5">
                            <Label class="panel-label">{{ messages.users.fields.password_confirmation }} *</Label>
                            <Input v-model="form.password_confirmation" type="password" class="panel-input" />
                        </div>
                    </div>
                </div>

                <RoleScopeForm
                    :form="form"
                    :locations="locations"
                    @update:field="updateField"
                />

                <div class="flex justify-end gap-2">
                    <Button variant="outline" type="button" as-child>
                        <Link href="/panel/users">{{ messages.users.actions.cancel }}</Link>
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        <Save class="mr-2 h-4 w-4" />
                        {{ form.processing ? messages.users.actions.saving : messages.users.actions.save }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
