<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';
import { index as menusRoute } from '@/routes/tenant/locations/menus';
import { Location, Template } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { Check, ChevronDown, Image as ImageIcon, Save, Trash2, X } from 'lucide-vue-next';
import {
    SelectContent,
    SelectIcon,
    SelectItem,
    SelectItemIndicator,
    SelectItemText,
    SelectPortal,
    SelectRoot,
    SelectTrigger,
    SelectValue,
    SelectViewport,
} from 'reka-ui';
import { computed } from 'vue';

interface Props {
    form: {
        name: string;
        description: string;
        is_active: boolean;
        show_currency: boolean;
        show_prices: boolean;
        show_calories: boolean;
        image_url: string | null;
        template_id?: number;
        errors: Record<string, string>;
        processing: boolean;
    };
    location: Location;
    templates: Template[];
    title?: string;
    description?: string;
    submitText?: string;
    processingText?: string;
    isEdit?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    isEdit: false,
});

const emit = defineEmits<{
    submit: [];
    'update:field': [field: string, value: any];
}>();

const page = usePage();
const messages = page.props.messages as any;

/**
 * Resolve the preview URL from the current form.image_url value.
 * - Null/empty → no preview
 * - base64 data URI → use as-is (freshly uploaded by the user)
 * - http(s) absolute URL → use as-is (already resolved by backend)
 * - anything else (relative storage path) → skip, backend should pre-resolve
 *
 * For already-saved images, the parent page (Edit.vue) should pass the
 * already-resolved URL from the Menu::$image_path accessor instead of the
 * raw relative path.
 */
const imagePreview = computed<string | null>(() => {
    const value = props.form.image_url;
    if (!value || typeof value !== 'string') return null;
    if (value.startsWith('data:') || value.startsWith('http')) {
        return value;
    }
    return null;
});

function removeImage() {
    emit('update:field', 'image_url', null);
    // Also clear the native file input value so the user can re-select the same file.
    const input = document.getElementById('image_url') as HTMLInputElement | null;
    if (input) input.value = '';
}

const title = computed(() => {
    if (props.title) return props.title;
    return props.isEdit
        ? messages.menus.form.title_edit
        : messages.menus.form.title_create;
});

const description = computed(() => {
    if (props.description) return props.description;
    return props.isEdit
        ? messages.menus.form.description_edit
        : messages.menus.form.description_create;
});

const submitText = computed(() => {
    return props.submitText || messages.menus.actions.save;
});

const processingText = computed(() => {
    return props.processingText || messages.menus.actions.saving;
});

const handleFileChange = async (event: Event) => {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];

    if (!file) {
        emit('update:field', 'image_url', null);
        return;
    }

    // Convertir archivo a base64
    const reader = new FileReader();
    reader.onload = () => {
        emit('update:field', 'image_url', reader.result as string);
    };
    reader.readAsDataURL(file);
};
</script>

<template>
    <form @submit.prevent="$emit('submit')">
        <div class="my-5 grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Columna Izquierda: Información Básica -->
            <Card>
                <CardHeader>
                    <CardTitle>{{ title }}</CardTitle>
                    <CardDescription>{{ description }}</CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="space-y-2">
                        <Label for="name">{{
                            messages.menus.fields.name
                        }}</Label>
                        <Input
                            id="name"
                            :model-value="form.name"
                            @update:model-value="
                                $emit('update:field', 'name', $event)
                            "
                            :placeholder="messages.menus.placeholders.name"
                            :class="{ 'border-destructive': form.errors.name }"
                        />
                        <p
                            v-if="form.errors.name"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="description">{{
                            messages.menus.fields.description
                        }}</Label>
                        <textarea
                            id="description"
                            :value="form.description"
                            @input="
                                $emit(
                                    'update:field',
                                    'description',
                                    ($event.target as HTMLTextAreaElement)
                                        .value,
                                )
                            "
                            :placeholder="
                                messages.menus.placeholders.description
                            "
                            :class="[
                                'flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50',
                                {
                                    'border-destructive':
                                        form.errors.description,
                                },
                            ]"
                            rows="3"
                        />
                        <p
                            v-if="form.errors.description"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.description }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="image_url">{{
                            messages.menus.fields.image || 'Imagen'
                        }}</Label>

                        <!-- Preview area -->
                        <div
                            v-if="imagePreview"
                            class="group relative overflow-hidden rounded-lg border bg-muted/30"
                        >
                            <img
                                :src="imagePreview"
                                :alt="form.name || 'Preview'"
                                class="h-48 w-full object-cover"
                            />
                            <button
                                type="button"
                                class="absolute right-2 top-2 flex h-8 w-8 items-center justify-center rounded-full bg-background/90 text-foreground shadow-md transition-colors hover:bg-destructive hover:text-destructive-foreground"
                                @click="removeImage"
                                aria-label="Eliminar imagen"
                            >
                                <Trash2 class="h-4 w-4" />
                            </button>
                        </div>
                        <div
                            v-else
                            class="flex h-32 flex-col items-center justify-center gap-2 rounded-lg border-2 border-dashed bg-muted/10 text-muted-foreground"
                        >
                            <ImageIcon class="h-8 w-8 opacity-40" />
                            <span class="text-xs">Sin imagen seleccionada</span>
                        </div>

                        <Input
                            id="image_url"
                            type="file"
                            accept="image/jpeg,image/jpg,image/png,image/gif,image/webp"
                            @change="handleFileChange"
                            :class="{
                                'border-destructive': form.errors.image_url,
                            }"
                        />
                        <p class="text-xs text-muted-foreground">
                            JPG, PNG, GIF o WebP. Máx. 2 MB.
                        </p>
                        <p
                            v-if="form.errors.image_url"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.image_url }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="template_id">{{
                            messages.menus.fields.template
                        }}</Label>
                        <SelectRoot
                            :model-value="form.template_id"
                            @update:model-value="
                                $emit('update:field', 'template_id', $event)
                            "
                        >
                            <SelectTrigger
                                id="template_id"
                                :class="
                                    cn(
                                        'flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50',
                                        {
                                            'border-destructive':
                                                form.errors.template_id,
                                        },
                                    )
                                "
                            >
                                <SelectValue
                                    :placeholder="messages.templates?.single"
                                />
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
                                            v-for="template in templates"
                                            :key="template.id"
                                            :value="template.id"
                                            class="relative flex w-full cursor-default items-center rounded-sm py-1.5 pr-2 pl-8 text-sm outline-none select-none focus:bg-accent focus:text-accent-foreground data-[highlighted]:bg-accent data-[highlighted]:text-accent-foreground"
                                        >
                                            <SelectItemIndicator
                                                class="absolute left-2 flex h-3.5 w-3.5 items-center justify-center"
                                            >
                                                <Check class="h-4 w-4" />
                                            </SelectItemIndicator>
                                            <SelectItemText>
                                                {{ template.name }}
                                            </SelectItemText>
                                        </SelectItem>
                                    </SelectViewport>
                                </SelectContent>
                            </SelectPortal>
                        </SelectRoot>
                        <p
                            v-if="form.errors.template_id"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.template_id }}
                        </p>
                    </div>
                </CardContent>
            </Card>

            <!-- Columna Derecha: Configuración -->
            <Card>
                <CardHeader>
                    <CardTitle>{{
                        messages.menus.fields.settings || 'Configuración'
                    }}</CardTitle>
                    <CardDescription>{{
                        messages.menus.fields.settings_description ||
                        'Personaliza cómo se muestra tu menú'
                    }}</CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="flex items-center space-x-2">
                        <button
                            type="button"
                            role="switch"
                            :aria-checked="form.is_active"
                            @click="
                                $emit(
                                    'update:field',
                                    'is_active',
                                    !form.is_active,
                                )
                            "
                            :class="[
                                'peer inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent transition-colors focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50',
                                form.is_active ? 'bg-primary' : 'bg-input',
                            ]"
                        >
                            <span
                                :class="[
                                    'pointer-events-none block h-5 w-5 rounded-full bg-background shadow-lg ring-0 transition-transform',
                                    form.is_active
                                        ? 'translate-x-5'
                                        : 'translate-x-0',
                                ]"
                            />
                        </button>
                        <Label for="is_active" class="cursor-pointer">
                            {{ messages.menus.fields.is_active }}
                        </Label>
                    </div>

                    <div class="flex items-center space-x-2">
                        <button
                            type="button"
                            role="switch"
                            :aria-checked="form.show_currency"
                            @click="
                                $emit(
                                    'update:field',
                                    'show_currency',
                                    !form.show_currency,
                                )
                            "
                            :class="[
                                'peer inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent transition-colors focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50',
                                form.show_currency ? 'bg-primary' : 'bg-input',
                            ]"
                        >
                            <span
                                :class="[
                                    'pointer-events-none block h-5 w-5 rounded-full bg-background shadow-lg ring-0 transition-transform',
                                    form.show_currency
                                        ? 'translate-x-5'
                                        : 'translate-x-0',
                                ]"
                            />
                        </button>
                        <Label for="show_currency" class="cursor-pointer">
                            {{
                                messages.menus.fields.show_currency ||
                                'Mostrar moneda'
                            }}
                        </Label>
                    </div>

                    <div class="flex items-center space-x-2">
                        <button
                            type="button"
                            role="switch"
                            :aria-checked="form.show_prices"
                            @click="
                                $emit(
                                    'update:field',
                                    'show_prices',
                                    !form.show_prices,
                                )
                            "
                            :class="[
                                'peer inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent transition-colors focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50',
                                form.show_prices ? 'bg-primary' : 'bg-input',
                            ]"
                        >
                            <span
                                :class="[
                                    'pointer-events-none block h-5 w-5 rounded-full bg-background shadow-lg ring-0 transition-transform',
                                    form.show_prices
                                        ? 'translate-x-5'
                                        : 'translate-x-0',
                                ]"
                            />
                        </button>
                        <Label for="show_prices" class="cursor-pointer">
                            {{
                                messages.menus.fields.show_prices ||
                                'Mostrar precios'
                            }}
                        </Label>
                    </div>

                    <div class="flex items-center space-x-2">
                        <button
                            type="button"
                            role="switch"
                            :aria-checked="form.show_calories"
                            @click="
                                $emit(
                                    'update:field',
                                    'show_calories',
                                    !form.show_calories,
                                )
                            "
                            :class="[
                                'peer inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent transition-colors focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:ring-offset-background focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50',
                                form.show_calories ? 'bg-primary' : 'bg-input',
                            ]"
                        >
                            <span
                                :class="[
                                    'pointer-events-none block h-5 w-5 rounded-full bg-background shadow-lg ring-0 transition-transform',
                                    form.show_calories
                                        ? 'translate-x-5'
                                        : 'translate-x-0',
                                ]"
                            />
                        </button>
                        <Label for="show_calories" class="cursor-pointer">
                            {{
                                messages.menus.fields.show_calories ||
                                'Mostrar calorías'
                            }}
                        </Label>
                    </div>
                </CardContent>
            </Card>
        </div>

        <div class="my-5 flex justify-between">
            <Button variant="outline" type="button" as-child>
                <Link :href="menusRoute(location.id).url">
                    <X class="mr-2 h-4 w-4" />
                    {{ messages.menus.actions.cancel }}
                </Link>
            </Button>
            <Button type="submit" :disabled="form.processing">
                <Save class="mr-2 h-4 w-4" />
                {{ form.processing ? processingText : submitText }}
            </Button>
        </div>
    </form>
</template>
