<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import type { BreadcrumbItem } from '@/types';

interface HomeTemplateConfig {
    name: string;
    business_types: string[];
    description: string;
}

const props = defineProps<{
    hasWebsite: boolean;
    businessType: string;
    homeTemplate: string;
    businessTypes: Record<string, string>;
    homeTemplates: Record<string, HomeTemplateConfig>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Página web pública', href: '#' },
];

const form = useForm({
    has_website:   props.hasWebsite,
    business_type: props.businessType,
    home_template: props.homeTemplate,
});

const availableTemplates = computed(() =>
    Object.entries(props.homeTemplates).filter(([, config]) =>
        config.business_types.includes(form.business_type),
    ),
);

function save() {
    form.put(route('tenant.settings.website.update'));
}

const page = usePage();
const tenantId = computed(() => (page.props as Record<string, unknown>).tenant_id as string | undefined);
const publicUrl = computed(() => tenantId.value ? `https://${tenantId.value}.menulinker.com` : null);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Página web pública" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall
                    title="Página web pública"
                    description="Configura si quieres publicar una página web pública en tu subdominio de MenuLinker."
                />

                <form class="space-y-8" @submit.prevent="save">

                    <!-- Toggle has_website -->
                    <div class="space-y-4">
                        <h3 class="text-sm font-semibold text-foreground">Visibilidad</h3>
                        <label class="flex cursor-pointer items-start gap-4 rounded-lg border border-border bg-card p-4 transition-colors hover:bg-accent/30">
                            <input
                                v-model="form.has_website"
                                type="checkbox"
                                class="mt-0.5 h-4 w-4 rounded border-border accent-primary"
                            />
                            <div>
                                <p class="text-sm font-medium text-foreground">Publicar página web</p>
                                <p class="mt-0.5 text-sm text-muted-foreground">
                                    Tus clientes podrán ver tu carta, horarios y contacto en tu subdominio.
                                    <template v-if="publicUrl">
                                        <a
                                            :href="publicUrl"
                                            target="_blank"
                                            rel="noopener"
                                            class="ml-1 text-primary underline underline-offset-4"
                                        >
                                            Ver mi web →
                                        </a>
                                    </template>
                                </p>
                            </div>
                        </label>
                    </div>

                    <template v-if="form.has_website">
                        <!-- Business type -->
                        <div class="space-y-3">
                            <h3 class="text-sm font-semibold text-foreground">Tipo de negocio</h3>
                            <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 md:grid-cols-5">
                                <button
                                    v-for="(label, value) in businessTypes"
                                    :key="value"
                                    type="button"
                                    class="flex flex-col items-center gap-1.5 rounded-lg border-2 px-3 py-3 text-sm transition-all"
                                    :class="
                                        form.business_type === value
                                            ? 'border-primary bg-primary/10 font-semibold text-primary'
                                            : 'border-border bg-card text-muted-foreground hover:border-primary/50 hover:text-foreground'
                                    "
                                    @click="form.business_type = value; form.home_template = availableTemplates[0]?.[0] ?? form.home_template"
                                >
                                    <span class="text-xl">
                                        {{ { restaurant: '🍽️', cafe: '☕', bar: '🍹', fastfood: '🍔', finedining: '⭐' }[value] ?? '🏠' }}
                                    </span>
                                    <span class="text-xs">{{ label }}</span>
                                </button>
                            </div>
                        </div>

                        <!-- Template selector -->
                        <div class="space-y-3">
                            <h3 class="text-sm font-semibold text-foreground">Plantilla</h3>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <label
                                    v-for="([key, tmpl]) in availableTemplates"
                                    :key="key"
                                    class="flex cursor-pointer items-start gap-3 rounded-lg border-2 p-4 transition-colors"
                                    :class="
                                        form.home_template === key
                                            ? 'border-primary bg-primary/5'
                                            : 'border-border bg-card hover:border-primary/40'
                                    "
                                >
                                    <input
                                        v-model="form.home_template"
                                        type="radio"
                                        :value="key"
                                        class="mt-0.5 accent-primary"
                                    />
                                    <div>
                                        <p class="text-sm font-medium text-foreground">{{ tmpl.name }}</p>
                                        <p class="mt-0.5 text-xs text-muted-foreground">{{ tmpl.description }}</p>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </template>

                    <!-- Error messages -->
                    <p v-if="form.errors.has_website" class="text-sm text-destructive">{{ form.errors.has_website }}</p>
                    <p v-if="form.errors.business_type" class="text-sm text-destructive">{{ form.errors.business_type }}</p>
                    <p v-if="form.errors.home_template" class="text-sm text-destructive">{{ form.errors.home_template }}</p>

                    <div class="flex items-center gap-4">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center gap-2 rounded-lg bg-primary px-5 py-2 text-sm font-semibold text-primary-foreground transition-opacity hover:opacity-90 disabled:opacity-60"
                        >
                            Guardar cambios
                        </button>
                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p v-if="form.recentlySuccessful" class="text-sm text-muted-foreground">
                                Guardado correctamente.
                            </p>
                        </Transition>
                    </div>
                </form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
