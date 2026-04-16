<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import type { Menu, Product } from '@/types';
import { useForm, usePage } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

interface Props {
    open: boolean;
    menu: Menu;
    product: Product | null;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    close: [];
}>();

const page = usePage();
const messages = computed(() => page.props.messages as any);

const isEditing = computed(() => !!props.product);
const dialogTitle = computed(() =>
    isEditing.value
        ? messages.value.products?.actions?.edit || 'Editar Producto'
        : messages.value.products?.actions?.create || 'Nuevo Producto',
);

const form = useForm({
    name: '',
    description: '',
    price: '',
    calories: '',
    image: null as File | null,
});

watch(
    () => props.product,
    (product) => {
        if (product) {
            form.name = product.name;
            form.description = product.description || '';
            form.price = product.price.toString();
            form.calories = product.calories?.toString() || '';
            form.image = null;
        } else {
            form.reset();
        }
    },
    { immediate: true },
);

const handleSubmit = () => {
    const onSuccess = () => {
        emit('close');
        form.reset();
    };

    if (isEditing.value && props.product) {
        form.put(`/menus/${props.menu.id}/products/${props.product.id}`, {
            onSuccess,
        });

        return;
    }

    form.post(`/menus/${props.menu.id}/products`, {
        onSuccess: () => {
            onSuccess();
        },
    });
};
</script>

<template>
    <Dialog :open="open" @update:open="(val) => !val && emit('close')">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>{{ dialogTitle }}</DialogTitle>
            </DialogHeader>

            <form @submit.prevent="handleSubmit" class="space-y-4">
                <div class="space-y-2">
                    <Label for="name">
                        {{ messages.products?.fields?.name || 'Nombre' }}
                    </Label>
                    <Input id="name" v-model="form.name" type="text" required />
                    <p v-if="form.errors.name" class="text-sm text-destructive">
                        {{ form.errors.name }}
                    </p>
                </div>

                <div class="space-y-2">
                    <Label for="description">
                        {{
                            messages.products?.fields?.description ||
                            'Descripción'
                        }}
                    </Label>
                    <Textarea
                        id="description"
                        v-model="form.description"
                        rows="3"
                    />
                    <p
                        v-if="form.errors.description"
                        class="text-sm text-destructive"
                    >
                        {{ form.errors.description }}
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label for="price">
                            {{
                                messages.products?.fields?.price || 'Precio (€)'
                            }}
                        </Label>
                        <Input
                            id="price"
                            v-model="form.price"
                            type="number"
                            step="0.01"
                            required
                        />
                        <p
                            v-if="form.errors.price"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.price }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="calories">
                            {{
                                messages.products?.fields?.calories ||
                                'Calorías (kcal)'
                            }}
                        </Label>
                        <Input
                            id="calories"
                            v-model="form.calories"
                            type="number"
                            step="0.01"
                        />
                        <p
                            v-if="form.errors.calories"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.calories }}
                        </p>
                    </div>
                </div>

                <div class="space-y-2">
                    <Label for="image">
                        {{ messages.products?.fields?.image || 'Imagen' }}
                    </Label>
                    <Input
                        id="image"
                        type="file"
                        accept="image/*"
                        @change="
                            (e: Event) => {
                                const target = e.target as HTMLInputElement;
                                form.image = target.files?.[0] || null;
                            }
                        "
                    />
                    <p
                        v-if="form.errors.image"
                        class="text-sm text-destructive"
                    >
                        {{ form.errors.image }}
                    </p>
                    <p
                        v-if="product?.image_url && !form.image"
                        class="text-xs text-muted-foreground"
                    >
                        {{ t('panel.product_form.current_image') }}: {{ product.image_url }}
                    </p>
                </div>

                <div class="flex justify-end gap-2">
                    <Button
                        type="button"
                        variant="outline"
                        @click="emit('close')"
                    >
                        {{ messages.actions?.cancel || 'Cancelar' }}
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        {{
                            form.processing
                                ? messages.actions?.saving || 'Guardando...'
                                : messages.actions?.save || 'Guardar'
                        }}
                    </Button>
                </div>
            </form>
        </DialogContent>
    </Dialog>
</template>
