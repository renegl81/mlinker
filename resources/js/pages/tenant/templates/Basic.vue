<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import ShareMenu from '@/components/public/ShareMenu.vue';
import AllergenIcon from '@/components/ui/AllergenIcon.vue';
import { Menu } from '@/types';
import { computed } from 'vue';

interface MetaProps {
    title: string;
    description: string;
    image: string | null;
    url: string;
}

interface JsonLd {
    '@context': string;
    '@type': string;
    [key: string]: unknown;
}

interface Props {
    menu: Menu;
    showBranding: boolean;
    tenantSlug: string;
    meta: MetaProps;
    jsonLd: JsonLd;
    shareUrl: string;
}

const props = defineProps<Props>();

const groupedSections = computed(() => {
    if (!props.menu.sections) return [];

    return props.menu.sections.map((section) => ({
        ...section,
        products: section.products || [],
    }));
});

const formatPrice = (price: number): string => {
    if (!props.menu.show_prices) return '';

    const formatted = price.toFixed(2);
    return props.menu.show_currency
        ? `${props.menu.location?.currency || '$'} ${formatted}`
        : formatted;
};

const jsonLdString = computed(() => JSON.stringify(props.jsonLd));
</script>

<template>
    <Head>
        <title>{{ meta.title }}</title>
        <meta name="description" :content="meta.description" />
        <meta property="og:title" :content="meta.title" />
        <meta property="og:description" :content="meta.description" />
        <meta v-if="meta.image" property="og:image" :content="meta.image" />
        <meta property="og:url" :content="meta.url" />
        <meta property="og:type" content="restaurant.menu" />
        <!-- eslint-disable-next-line vue/no-v-text-v-html-on-component -->
        <component :is="'script'" type="application/ld+json" v-html="jsonLdString" />
    </Head>

    <div class="min-h-screen bg-background">
        <!-- Header del Menú -->
        <header class="border-b bg-card">
            <div class="container mx-auto px-4 py-8">
                <div
                    class="flex flex-col items-center gap-4 md:flex-row md:items-start md:gap-8"
                >
                    <img
                        v-if="menu.image_path"
                        :src="menu.image_path"
                        :alt="menu.name"
                        class="h-32 w-32 rounded-lg object-cover shadow-md"
                    />
                    <div class="flex-1 text-center md:text-left">
                        <h1
                            class="text-3xl font-bold tracking-tight md:text-4xl"
                        >
                            {{ menu.name }}
                        </h1>
                        <p
                            v-if="menu.description"
                            class="mt-2 text-muted-foreground"
                        >
                            {{ menu.description }}
                        </p>
                    </div>
                </div>
            </div>
        </header>

        <!-- Secciones del Menú -->
        <main class="container mx-auto px-4 py-8">
            <div v-if="groupedSections.length === 0" class="py-12 text-center">
                <p class="text-lg text-muted-foreground">
                    Este menú aún no tiene productos disponibles.
                </p>
            </div>

            <div
                v-for="section in groupedSections"
                :key="section.id"
                class="mb-12"
            >
                <!-- Encabezado de Sección -->
                <div class="mb-6 border-b pb-2">
                    <h2 class="text-2xl font-semibold tracking-tight">
                        {{ section.name }}
                    </h2>
                    <p
                        v-if="section.description"
                        class="mt-1 text-sm text-muted-foreground"
                    >
                        {{ section.description }}
                    </p>
                </div>

                <!-- Productos de la Sección -->
                <div class="grid gap-6">
                    <div
                        v-for="product in section.products"
                        :key="product.id"
                        class="flex gap-4 rounded-lg border bg-card p-4 transition-shadow hover:shadow-md"
                    >
                        <!-- Imagen del Producto -->
                        <img
                            v-if="product.image_url"
                            :src="product.image_url"
                            :alt="product.name"
                            class="h-24 w-24 flex-shrink-0 rounded-md object-cover"
                        />

                        <!-- Información del Producto -->
                        <div class="flex flex-1 flex-col justify-between">
                            <div>
                                <div
                                    class="flex items-start justify-between gap-4"
                                >
                                    <h3 class="text-lg font-semibold">
                                        {{ product.name }}
                                    </h3>
                                    <span
                                        v-if="menu.show_prices && product.price"
                                        class="flex-shrink-0 font-semibold text-primary"
                                    >
                                        {{ formatPrice(product.price) }}
                                    </span>
                                </div>

                                <p
                                    v-if="product.description"
                                    class="mt-1 text-sm text-muted-foreground"
                                >
                                    {{ product.description }}
                                </p>
                            </div>

                            <!-- Tags (vegetarian, vegan, etc.) -->
                            <div v-if="product.tags && product.tags.length > 0" class="mt-1 flex gap-1 flex-wrap">
                                <span v-if="product.tags.includes('vegetarian')" title="Vegetariano" class="text-sm">🌱</span>
                                <span v-if="product.tags.includes('vegan')" title="Vegano" class="text-sm">🌿</span>
                                <span v-if="product.tags.includes('spicy')" title="Picante" class="text-sm">🌶️</span>
                                <span v-if="product.tags.includes('gluten_free')" title="Sin gluten" class="text-sm">🚫🌾</span>
                            </div>

                            <!-- Información Adicional -->
                            <div class="mt-2 flex flex-wrap items-center gap-3 text-xs text-muted-foreground">
                                <span
                                    v-if="menu.show_calories && product.calories"
                                    class="flex items-center gap-1"
                                >
                                    {{ product.calories }} kcal
                                </span>

                                <!-- Allergens with icons -->
                                <div
                                    v-if="product.allergens && product.allergens.length > 0"
                                    class="flex items-center gap-0.5 flex-wrap"
                                >
                                    <AllergenIcon
                                        v-for="allergen in product.allergens"
                                        :key="allergen.id"
                                        :code="allergen.code"
                                        size="sm"
                                        :title="allergen.name"
                                        class="cursor-default"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="section.products.length === 0"
                        class="rounded-lg border border-dashed bg-muted/50 p-8 text-center"
                    >
                        <p class="text-sm text-muted-foreground">
                            Esta sección aún no tiene productos.
                        </p>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="border-t bg-card py-6">
            <div
                class="container mx-auto px-4 text-center text-sm text-muted-foreground"
            >
                <p>{{ menu.location?.name }}</p>
                <p v-if="menu.location?.address">{{ menu.location.address }}</p>
                <p v-if="menu.location?.phone">{{ menu.location.phone }}</p>

                <!-- Branding MenuLinker (solo plan Free) -->
                <div
                    v-if="showBranding"
                    class="mt-3 border-t border-muted pt-3"
                >
                    <a
                        :href="`https://menulinker.com?ref=${tenantSlug}`"
                        target="_blank"
                        rel="noopener"
                        class="text-xs text-muted-foreground/60 transition-colors hover:text-muted-foreground"
                    >
                        Menú digital por MenuLinker — Crea el tuyo gratis
                    </a>
                </div>
            </div>
        </footer>

        <!-- FAB Compartir -->
        <ShareMenu
            :url="shareUrl"
            :menu-name="menu.name"
            :location-name="menu.location?.name ?? ''"
        />
    </div>
</template>
