<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/AppLayout.vue';
import { index as locationMenusRoute } from '@/routes/tenant/locations/menus';
import { destroy as menuRouteDestroy, edit as menuRouteEdit } from '@/routes/tenant/menus';
import type { BreadcrumbItem, Menu } from '@/types';
import { Inertia } from '@inertiajs/inertia';
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    ArrowLeft,
    BookOpen,
    Image as ImageIcon,
    Pencil,
    Trash2,
} from 'lucide-vue-next';
import { computed } from 'vue';

interface Section {
    id: number;
    name: string;
    description: string | null;
}

interface MenuWithSections extends Menu {
    sections: Section[];
}

interface Props {
    menu: MenuWithSections;
}

const props = defineProps<Props>();
const page = usePage();
const messages = computed(() => page.props.messages as any);

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: messages.value.menus.plural,
        href: locationMenusRoute(props.menu.location_id).url,
    },
    {
        title: props.menu.name,
        href: '#',
    },
];

function remove() {
    if (confirm(messages.value.menus.actions.confirm_delete)) {
        Inertia.delete(menuRouteDestroy(props.menu.id).url);
    }
}
</script>

<template>
    <Head :title="`${messages.menus.singular}: ${menu.name}`" />

    <AppLayout :breadcrumbs="breadcrumbItems">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="outline" size="icon" as-child>
                        <Link :href="locationMenusRoute(menu.location_id).url">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                    </Button>
                    <HeadingSmall
                        :title="menu.name"
                        :description="messages.menus.caption"
                    />
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" as-child>
                        <Link :href="menuRouteEdit(menu.id).url">
                            <Pencil class="mr-2 h-4 w-4" />
                            {{ messages.menus.actions.edit }}
                        </Link>
                    </Button>
                    <Button variant="destructive" @click="remove">
                        <Trash2 class="mr-2 h-4 w-4" />
                        {{ messages.menus.actions.delete }}
                    </Button>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="space-y-6 lg:col-span-2">
                    <!-- Información general -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg font-semibold">
                                {{ messages.menus.form.title_edit || 'Información del Menú' }}
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div class="flex flex-col gap-1.5">
                                    <span class="text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                        {{ messages.menus.fields.name }}
                                    </span>
                                    <p class="text-sm font-medium">{{ menu.name }}</p>
                                </div>
                                <div class="flex flex-col gap-1.5">
                                    <span class="text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                        {{ messages.menus.fields.location }}
                                    </span>
                                    <p class="text-sm">{{ menu.location?.name || '---' }}</p>
                                </div>
                            </div>

                            <div v-if="menu.description" class="flex flex-col gap-1.5">
                                <span class="text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                    {{ messages.menus.fields.description }}
                                </span>
                                <p class="text-sm leading-relaxed text-pretty text-muted-foreground">
                                    {{ menu.description }}
                                </p>
                            </div>

                            <div v-if="menu.template" class="flex flex-col gap-1.5">
                                <span class="text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                    {{ messages.menus.fields.template }}
                                </span>
                                <p class="text-sm">{{ menu.template.name }}</p>
                            </div>

                            <Separator />

                            <!-- Imagen del menú -->
                            <div class="flex flex-col gap-2">
                                <span class="text-xs font-bold tracking-wider text-muted-foreground uppercase">
                                    {{ messages.menus.fields.image_url || 'Imagen' }}
                                </span>
                                <div
                                    v-if="menu.image_path"
                                    class="overflow-hidden rounded-lg border"
                                >
                                    <img
                                        :src="menu.image_path"
                                        :alt="menu.name"
                                        class="h-48 w-full object-cover"
                                    />
                                </div>
                                <div
                                    v-else
                                    class="flex h-32 items-center justify-center rounded-lg border-2 border-dashed text-muted-foreground"
                                >
                                    <div class="flex flex-col items-center gap-2">
                                        <ImageIcon class="h-8 w-8 opacity-40" />
                                        <span class="text-xs">Sin imagen</span>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Secciones del menú -->
                    <Card>
                        <CardHeader class="flex flex-row items-center justify-between space-y-0">
                            <CardTitle class="flex items-center gap-2 text-lg">
                                <BookOpen class="h-5 w-5 text-primary" />
                                Secciones
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div v-if="menu.sections && menu.sections.length > 0" class="space-y-2">
                                <div
                                    v-for="section in menu.sections"
                                    :key="section.id"
                                    class="flex items-start gap-3 rounded-md border p-3"
                                >
                                    <div class="flex-1">
                                        <p class="text-sm font-medium">{{ section.name }}</p>
                                        <p
                                            v-if="section.description"
                                            class="mt-0.5 text-xs text-muted-foreground"
                                        >
                                            {{ section.description }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div
                                v-else
                                class="flex flex-col items-center justify-center rounded-lg border-2 border-dashed py-8 text-center"
                            >
                                <BookOpen class="mb-2 h-8 w-8 text-muted-foreground/30" />
                                <p class="text-sm text-muted-foreground">
                                    No hay secciones en este menú.
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Columna lateral: configuración y estado -->
                <div class="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-md font-semibold">
                                {{ messages.menus.fields.settings || 'Configuración' }}
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3 text-sm">
                            <div class="flex items-center justify-between border-b border-muted py-1.5">
                                <span class="text-muted-foreground">{{ messages.menus.fields.show_prices }}</span>
                                <Badge :variant="menu.show_prices ? 'default' : 'secondary'">
                                    {{ menu.show_prices ? messages.menus.status.active : messages.menus.status.inactive }}
                                </Badge>
                            </div>
                            <div class="flex items-center justify-between border-b border-muted py-1.5">
                                <span class="text-muted-foreground">{{ messages.menus.fields.show_currency }}</span>
                                <Badge :variant="menu.show_currency ? 'default' : 'secondary'">
                                    {{ menu.show_currency ? messages.menus.status.active : messages.menus.status.inactive }}
                                </Badge>
                            </div>
                            <div class="flex items-center justify-between py-1.5">
                                <span class="text-muted-foreground">{{ messages.menus.fields.show_calories }}</span>
                                <Badge :variant="menu.show_calories ? 'default' : 'secondary'">
                                    {{ menu.show_calories ? messages.menus.status.active : messages.menus.status.inactive }}
                                </Badge>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Estado del menú -->
                    <Card
                        :class="
                            menu.is_active
                                ? 'border-green-200 bg-green-50/50'
                                : 'border-red-200 bg-red-50/50'
                        "
                    >
                        <CardContent class="flex items-center justify-center gap-2 py-4">
                            <div
                                :class="menu.is_active ? 'bg-green-500' : 'bg-red-500'"
                                class="h-2 w-2 animate-pulse rounded-full"
                            />
                            <span
                                :class="menu.is_active ? 'text-green-700' : 'text-red-700'"
                                class="text-xs font-bold tracking-widest uppercase"
                            >
                                {{
                                    menu.is_active
                                        ? messages.menus.status.active
                                        : messages.menus.status.inactive
                                }}
                            </span>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

