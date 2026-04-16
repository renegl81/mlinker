<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import type { Menu, Product } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { Edit, Image as ImageIcon, Plus, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import ProductFromDialog from '@/pages/admin/tenant/locations/components/ProductFromDialog.vue';

interface Props {
    open: boolean;
    menu: Menu;
}

defineProps<Props>();
const emit = defineEmits<{
    close: [];
}>();

const page = usePage();
const messages = computed(() => page.props.messages as any);

const isProductFormOpen = ref(false);
const selectedProduct = ref<Product | null>(null);

const openProductForm = (product?: Product) => {
    selectedProduct.value = product || null;
    isProductFormOpen.value = true;
};

const closeProductForm = () => {
    isProductFormOpen.value = false;
    selectedProduct.value = null;
};
</script>

<template>
    <Dialog :open="open" @update:open="(val) => !val && emit('close')">
        <DialogContent class="max-w-4xl">
            <DialogHeader>
                <DialogTitle>
                    {{ messages.products?.manage || 'Gestionar Productos' }} -
                    {{ menu.name }}
                </DialogTitle>
            </DialogHeader>

            <div class="space-y-4">
                <div class="flex justify-end">
                    <Button size="sm" @click="openProductForm()">
                        <Plus class="mr-2 h-4 w-4" />
                        {{
                            messages.products?.actions?.create ||
                            'Nuevo Producto'
                        }}
                    </Button>
                </div>

                <div v-if="menu.products && menu.products.length > 0">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="w-16"></TableHead>
                                <TableHead>{{
                                    messages.products?.fields?.name || 'Nombre'
                                }}</TableHead>
                                <TableHead>{{
                                    messages.products?.fields?.price || 'Precio'
                                }}</TableHead>
                                <TableHead>{{
                                    messages.products?.fields?.calories ||
                                    'Calorías'
                                }}</TableHead>
                                <TableHead class="text-right">{{
                                    messages.actions?.label || 'Acciones'
                                }}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="product in menu.products"
                                :key="product.id"
                            >
                                <TableCell>
                                    <div
                                        class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-md border bg-muted"
                                    >
                                        <img
                                            v-if="product.image_url"
                                            :src="product.image_url"
                                            :alt="product.name"
                                            class="h-full w-full object-cover"
                                        />
                                        <ImageIcon
                                            v-else
                                            class="h-4 w-4 text-muted-foreground"
                                        />
                                    </div>
                                </TableCell>
                                <TableCell class="font-medium">
                                    {{ product.name }}
                                    <p
                                        v-if="product.description"
                                        class="line-clamp-1 text-xs font-normal text-muted-foreground"
                                    >
                                        {{ product.description }}
                                    </p>
                                </TableCell>
                                <TableCell>{{ product.price }}€</TableCell>
                                <TableCell>
                                    {{
                                        product.calories
                                            ? `${product.calories} kcal`
                                            : '-'
                                    }}
                                </TableCell>
                                <TableCell class="text-right">
                                    <div
                                        class="flex items-center justify-end gap-2"
                                    >
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            @click="openProductForm(product)"
                                        >
                                            <Edit class="h-4 w-4" />
                                        </Button>
                                        <Button variant="ghost" size="icon">
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <div
                    v-else
                    class="flex flex-col items-center justify-center rounded-lg border-2 border-dashed py-10 text-center"
                >
                    <p class="text-sm font-medium text-muted-foreground">
                        {{
                            messages.products?.empty ||
                            'No hay productos en este menú.'
                        }}
                    </p>
                </div>
            </div>

            <ProductFromDialog
                :open="isProductFormOpen"
                :menu="menu"
                :product="selectedProduct"
                @close="closeProductForm"
            />
        </DialogContent>
    </Dialog>
</template>
