<script setup lang="ts">
import { Menu } from '@/types';
import { computed } from 'vue';

interface Props {
    menu: Menu;
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
</script>

<template>
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

                            <!-- Información Adicional -->
                            <div
                                class="mt-2 flex flex-wrap gap-3 text-xs text-muted-foreground"
                            >
                                <span
                                    v-if="
                                        menu.show_calories && product.calories
                                    "
                                    class="flex items-center gap-1"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="h-3 w-3"
                                    >
                                        <path d="M12 2L2 7l10 5 10-5-10-5z" />
                                        <path d="M2 17l10 5 10-5" />
                                        <path d="M2 12l10 5 10-5" />
                                    </svg>
                                    {{ product.calories }} kcal
                                </span>

                                <span
                                    v-if="
                                        product.allergens &&
                                        product.allergens.length > 0
                                    "
                                    class="flex items-center gap-1"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="h-3 w-3"
                                    >
                                        <path
                                            d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"
                                        />
                                        <line x1="12" y1="9" x2="12" y2="13" />
                                        <line
                                            x1="12"
                                            y1="17"
                                            x2="12.01"
                                            y2="17"
                                        />
                                    </svg>
                                    Alérgenos:
                                    {{ product.allergens.join(', ') }}
                                </span>
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
            </div>
        </footer>
    </div>
</template>
