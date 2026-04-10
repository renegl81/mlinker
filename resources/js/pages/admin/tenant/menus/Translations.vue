<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem, Menu } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ExclamationTriangleIcon } from '@heroicons/vue/24/solid';
import { ArrowLeft, Globe } from 'lucide-vue-next';
import { computed, reactive } from 'vue';


interface TranslationFields {
    name: string;
    description: string;
}

interface SectionWithProducts {
    id: number;
    name: string;
    description: string | null;
    translations: Record<string, TranslationFields> | null;
    products: Array<{
        id: number;
        name: string;
        description: string | null;
        translations: Record<string, TranslationFields> | null;
    }>;
}

interface MenuWithSections extends Menu {
    sections: SectionWithProducts[];
}

interface Props {
    menu: MenuWithSections;
    hasMultilang: boolean;
}

const props = defineProps<Props>();
const page = usePage();
const messages = computed(() => page.props.messages as any);

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: messages.value.menus?.plural ?? 'Menús',
        href: `/panel/locations/${props.menu.location_id}/menus`,
    },
    {
        title: props.menu.name,
        href: `/panel/menus/${props.menu.id}`,
    },
    {
        title: 'Traducciones',
        href: '#',
    },
];

// Reactive form state: { sections: {[id]: {name, description}}, products: {[id]: {name, description}} }
const form = reactive<{
    sections: Record<number, TranslationFields>;
    products: Record<number, TranslationFields>;
}>({
    sections: {},
    products: {},
});

// Initialize form with existing translations
for (const section of props.menu.sections ?? []) {
    form.sections[section.id] = {
        name: section.translations?.en?.name ?? '',
        description: section.translations?.en?.description ?? '',
    };
    for (const product of section.products ?? []) {
        form.products[product.id] = {
            name: product.translations?.en?.name ?? '',
            description: product.translations?.en?.description ?? '',
        };
    }
}

function save() {
    const sections = Object.entries(form.sections).map(([id, translations]) => ({
        id: Number(id),
        translations: { en: translations },
    }));

    const products = Object.entries(form.products).map(([id, translations]) => ({
        id: Number(id),
        translations: { en: translations },
    }));

    router.put(`/panel/menus/${props.menu.id}/translations`, {
        sections,
        products,
    }, {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head :title="`Traducciones — ${menu.name}`" />

    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">

            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="outline" size="icon" as-child>
                        <Link :href="`/panel/menus/${menu.id}`">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                    </Button>
                    <HeadingSmall
                        title="Traducciones del menú"
                        :description="`Gestiona las traducciones de '${menu.name}'`"
                    />
                </div>
                <Button v-if="hasMultilang" @click="save" class="gap-2">
                    <Globe class="h-4 w-4" />
                    Guardar traducciones
                </Button>
            </div>

            <!-- Upgrade required banner -->
            <Alert v-if="!hasMultilang" class="border-amber-500 bg-amber-50 text-amber-900 dark:bg-amber-950 dark:text-amber-200">
                <ExclamationTriangleIcon class="size-4 text-amber-600" />
                <AlertTitle>Plan Business o superior requerido</AlertTitle>
                <AlertDescription>
                    Las traducciones multiidioma están disponibles en los planes Business y Enterprise.
                    <Link href="/panel/billing/plans" class="ml-1 font-semibold underline underline-offset-2">
                        Ver planes
                    </Link>
                </AlertDescription>
            </Alert>

            <!-- Translations form (only when plan allows) -->
            <template v-if="hasMultilang">
                <Card v-for="section in menu.sections" :key="section.id">
                    <CardHeader>
                        <CardTitle class="text-base">
                            Sección: {{ section.name }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">

                        <!-- Section translation -->
                        <div class="rounded-md border p-4 space-y-3 bg-muted/30">
                            <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                Traducción de la sección
                            </p>
                            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                                <div class="space-y-1.5">
                                    <Label class="text-xs text-muted-foreground">Nombre (ES) — original</Label>
                                    <Input :value="section.name" disabled class="bg-muted/50 text-muted-foreground" />
                                </div>
                                <div class="space-y-1.5">
                                    <Label class="text-xs">Nombre (EN)</Label>
                                    <Input v-model="form.sections[section.id].name" placeholder="Section name in English" />
                                </div>
                            </div>
                            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                                <div class="space-y-1.5">
                                    <Label class="text-xs text-muted-foreground">Descripción (ES) — original</Label>
                                    <Input :value="section.description ?? ''" disabled class="bg-muted/50 text-muted-foreground" />
                                </div>
                                <div class="space-y-1.5">
                                    <Label class="text-xs">Descripción (EN)</Label>
                                    <Input v-model="form.sections[section.id].description" placeholder="Section description in English" />
                                </div>
                            </div>
                        </div>

                        <Separator v-if="section.products && section.products.length > 0" />

                        <!-- Product translations -->
                        <div
                            v-for="product in section.products"
                            :key="product.id"
                            class="rounded-md border p-4 space-y-3"
                        >
                            <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                Producto: {{ product.name }}
                            </p>
                            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                                <div class="space-y-1.5">
                                    <Label class="text-xs text-muted-foreground">Nombre (ES) — original</Label>
                                    <Input :value="product.name" disabled class="bg-muted/50 text-muted-foreground" />
                                </div>
                                <div class="space-y-1.5">
                                    <Label class="text-xs">Nombre (EN)</Label>
                                    <Input v-model="form.products[product.id].name" placeholder="Product name in English" />
                                </div>
                            </div>
                            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                                <div class="space-y-1.5">
                                    <Label class="text-xs text-muted-foreground">Descripción (ES) — original</Label>
                                    <Input :value="product.description ?? ''" disabled class="bg-muted/50 text-muted-foreground" />
                                </div>
                                <div class="space-y-1.5">
                                    <Label class="text-xs">Descripción (EN)</Label>
                                    <Input v-model="form.products[product.id].description" placeholder="Product description in English" />
                                </div>
                            </div>
                        </div>

                    </CardContent>
                </Card>

                <div v-if="!menu.sections || menu.sections.length === 0" class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed py-16 text-center">
                    <Globe class="mb-3 h-10 w-10 text-muted-foreground/30" />
                    <p class="text-sm text-muted-foreground">
                        Este menú no tiene secciones todavía.
                    </p>
                </div>

                <!-- Bottom save button -->
                <div v-if="menu.sections && menu.sections.length > 0" class="flex justify-end pt-2">
                    <Button @click="save" class="gap-2">
                        <Globe class="h-4 w-4" />
                        Guardar traducciones
                    </Button>
                </div>
            </template>

        </div>
    </AppLayout>
</template>
