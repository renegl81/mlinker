<script setup lang="ts">
import AllergenIcon from '@/components/ui/AllergenIcon.vue';
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
import { Image as ImageIcon, Plus, Save, Sparkles, Trash2, Users, X } from 'lucide-vue-next';
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
import { Check, ChevronDown } from 'lucide-vue-next';
import { computed, ref } from 'vue';

export interface Allergen {
    id: number;
    name: string;
    code: string | null;
}

export interface Ingredient {
    id: number;
    name: string;
}

export interface CatalogIngredient {
    name: string;
    has_translations: boolean;
    popularity: number;
}

export interface Section {
    id: number;
    name: string;
}

export interface ProductFormData {
    name: string;
    description: string;
    price: string | number;
    calories: string | number | null;
    image_url: string | null;
    section_id: number | null;
    allergen_ids: number[];
    ingredient_names: string[];
    tags: string[];
    errors: Record<string, string>;
    processing: boolean;
}

interface Props {
    form: ProductFormData;
    sections: Section[];
    allergens: Allergen[];
    ingredients: Ingredient[];
    catalogIngredients?: CatalogIngredient[];
    cancelHref: string;
    isEdit?: boolean;
    showDeleteButton?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    isEdit: false,
    showDeleteButton: false,
    catalogIngredients: () => [],
});

const emit = defineEmits<{
    submit: [];
    delete: [];
    'update:field': [field: string, value: unknown];
}>();

// Image preview
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
    const input = document.getElementById('product_image') as HTMLInputElement | null;
    if (input) input.value = '';
}

const handleFileChange = (event: Event) => {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];
    if (!file) {
        emit('update:field', 'image_url', null);
        return;
    }
    const reader = new FileReader();
    reader.onload = () => {
        emit('update:field', 'image_url', reader.result as string);
    };
    reader.readAsDataURL(file);
};

// Allergen toggle
function toggleAllergen(id: number) {
    const current = [...props.form.allergen_ids];
    const idx = current.indexOf(id);
    if (idx === -1) {
        current.push(id);
    } else {
        current.splice(idx, 1);
    }
    emit('update:field', 'allergen_ids', current);
}

// Ingredient tag input
const ingredientInput = ref('');

function addIngredient() {
    const name = ingredientInput.value.trim();
    if (!name) return;
    const current = [...props.form.ingredient_names];
    if (!current.includes(name)) {
        current.push(name);
        emit('update:field', 'ingredient_names', current);
    }
    ingredientInput.value = '';
}

function removeIngredient(name: string) {
    const current = props.form.ingredient_names.filter((n) => n !== name);
    emit('update:field', 'ingredient_names', current);
}

function handleIngredientKeydown(event: KeyboardEvent) {
    if (event.key === 'Enter') {
        event.preventDefault();
        addIngredient();
    }
}

// Tags
const AVAILABLE_TAGS = [
    { key: 'vegetarian', label: 'Vegetariano', emoji: '🌱' },
    { key: 'vegan', label: 'Vegano', emoji: '🌿' },
    { key: 'spicy', label: 'Picante', emoji: '🌶️' },
    { key: 'gluten_free', label: 'Sin gluten', emoji: '🚫🌾' },
];

function toggleTag(key: string) {
    const current = [...props.form.tags];
    const idx = current.indexOf(key);
    if (idx === -1) {
        current.push(key);
    } else {
        current.splice(idx, 1);
    }
    emit('update:field', 'tags', current);
}

// Ingredient suggestions: merge own list + catalog (cross-tenant popular).
// Own ingredients come first, catalog entries marked with `shared: true` so
// the UI can show a visual hint (icon / translated badge).
interface SuggestionItem {
    name: string;
    shared: boolean;
    hasTranslations: boolean;
}

const ingredientSuggestions = computed<SuggestionItem[]>(() => {
    const input = ingredientInput.value.trim().toLowerCase();
    if (!input) return [];

    const alreadyAdded = new Set(props.form.ingredient_names.map((n) => n.toLowerCase()));

    const own: SuggestionItem[] = props.ingredients
        .filter((i) => i.name.toLowerCase().includes(input) && !alreadyAdded.has(i.name.toLowerCase()))
        .map((i) => ({ name: i.name, shared: false, hasTranslations: false }));

    const ownNames = new Set(own.map((o) => o.name.toLowerCase()));

    const shared: SuggestionItem[] = props.catalogIngredients
        .filter(
            (c) =>
                c.name.toLowerCase().includes(input) &&
                !ownNames.has(c.name.toLowerCase()) &&
                !alreadyAdded.has(c.name.toLowerCase()),
        )
        .map((c) => ({ name: c.name, shared: true, hasTranslations: c.has_translations }));

    return [...own, ...shared].slice(0, 8);
});

function selectSuggestion(name: string) {
    ingredientInput.value = name;
    addIngredient();
}
</script>

<template>
    <form @submit.prevent="$emit('submit')">
        <div class="my-5 grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Columna Izquierda: Información Básica -->
            <div class="space-y-6">
                <Card>
                    <CardHeader>
                        <CardTitle>{{ isEdit ? 'Editar plato' : 'Nuevo plato' }}</CardTitle>
                        <CardDescription>Información básica del producto</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <!-- Name -->
                        <div class="space-y-2">
                            <Label for="product_name">Nombre *</Label>
                            <Input
                                id="product_name"
                                :model-value="form.name"
                                @update:model-value="$emit('update:field', 'name', $event)"
                                placeholder="Ej. Ensalada César"
                                :class="{ 'border-destructive': form.errors.name }"
                            />
                            <p v-if="form.errors.name" class="text-sm text-destructive">{{ form.errors.name }}</p>
                        </div>

                        <!-- Description -->
                        <div class="space-y-2">
                            <Label for="product_description">Descripción</Label>
                            <textarea
                                id="product_description"
                                :value="form.description"
                                @input="$emit('update:field', 'description', ($event.target as HTMLTextAreaElement).value)"
                                placeholder="Descripción del plato..."
                                rows="3"
                                :class="[
                                    'flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50',
                                    { 'border-destructive': form.errors.description },
                                ]"
                            />
                            <p v-if="form.errors.description" class="text-sm text-destructive">{{ form.errors.description }}</p>
                        </div>

                        <!-- Price + Calories -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="product_price">Precio (€) *</Label>
                                <Input
                                    id="product_price"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    :model-value="form.price"
                                    @update:model-value="$emit('update:field', 'price', $event)"
                                    placeholder="0.00"
                                    :class="{ 'border-destructive': form.errors.price }"
                                />
                                <p v-if="form.errors.price" class="text-sm text-destructive">{{ form.errors.price }}</p>
                            </div>
                            <div class="space-y-2">
                                <Label for="product_calories">Calorías (kcal)</Label>
                                <Input
                                    id="product_calories"
                                    type="number"
                                    step="1"
                                    min="0"
                                    :model-value="form.calories ?? ''"
                                    @update:model-value="$emit('update:field', 'calories', $event || null)"
                                    placeholder="Opcional"
                                    :class="{ 'border-destructive': form.errors.calories }"
                                />
                                <p v-if="form.errors.calories" class="text-sm text-destructive">{{ form.errors.calories }}</p>
                            </div>
                        </div>

                        <!-- Section -->
                        <div class="space-y-2">
                            <Label>Sección *</Label>
                            <SelectRoot
                                :model-value="form.section_id"
                                @update:model-value="$emit('update:field', 'section_id', $event)"
                            >
                                <SelectTrigger
                                    :class="cn(
                                        'flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:outline-none',
                                        { 'border-destructive': form.errors.section_id },
                                    )"
                                >
                                    <SelectValue placeholder="Selecciona una sección" />
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
                                                v-for="section in sections"
                                                :key="section.id"
                                                :value="section.id"
                                                class="relative flex w-full cursor-default items-center rounded-sm py-1.5 pr-2 pl-8 text-sm outline-none select-none focus:bg-accent focus:text-accent-foreground data-[highlighted]:bg-accent data-[highlighted]:text-accent-foreground"
                                            >
                                                <SelectItemIndicator class="absolute left-2 flex h-3.5 w-3.5 items-center justify-center">
                                                    <Check class="h-4 w-4" />
                                                </SelectItemIndicator>
                                                <SelectItemText>{{ section.name }}</SelectItemText>
                                            </SelectItem>
                                        </SelectViewport>
                                    </SelectContent>
                                </SelectPortal>
                            </SelectRoot>
                            <p v-if="form.errors.section_id" class="text-sm text-destructive">{{ form.errors.section_id }}</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Image -->
                <Card>
                    <CardHeader>
                        <CardTitle>Imagen</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div
                            v-if="imagePreview"
                            class="group relative overflow-hidden rounded-lg border bg-muted/30"
                        >
                            <img
                                :src="imagePreview"
                                alt="Preview del producto"
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
                            id="product_image"
                            type="file"
                            accept="image/jpeg,image/jpg,image/png,image/gif,image/webp"
                            @change="handleFileChange"
                        />
                        <p class="text-xs text-muted-foreground">JPG, PNG, GIF o WebP. Máx. 2 MB.</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Columna Derecha: Alérgenos, Ingredientes, Tags -->
            <div class="space-y-6">
                <!-- Tags -->
                <Card>
                    <CardHeader>
                        <CardTitle>Etiquetas</CardTitle>
                        <CardDescription>Características dietéticas del plato</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-2 gap-2">
                            <button
                                v-for="tag in AVAILABLE_TAGS"
                                :key="tag.key"
                                type="button"
                                @click="toggleTag(tag.key)"
                                :class="[
                                    'flex items-center gap-2 rounded-md border px-3 py-2 text-sm transition-colors',
                                    form.tags.includes(tag.key)
                                        ? 'border-primary bg-primary/10 text-primary font-medium'
                                        : 'border-border hover:border-primary/50',
                                ]"
                            >
                                <span>{{ tag.emoji }}</span>
                                <span>{{ tag.label }}</span>
                            </button>
                        </div>
                    </CardContent>
                </Card>

                <!-- Allergens -->
                <Card>
                    <CardHeader>
                        <CardTitle>Alérgenos</CardTitle>
                        <CardDescription>Selecciona los alérgenos presentes en el plato</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div
                            v-if="allergens.length > 0"
                            class="grid grid-cols-3 gap-2 sm:grid-cols-4 lg:grid-cols-3 xl:grid-cols-4"
                        >
                            <button
                                v-for="allergen in allergens"
                                :key="allergen.id"
                                type="button"
                                @click="toggleAllergen(allergen.id)"
                                :title="allergen.name"
                                :class="[
                                    'flex flex-col items-center gap-1 rounded-md border p-2 text-center text-xs transition-colors',
                                    form.allergen_ids.includes(allergen.id)
                                        ? 'border-primary bg-primary/10 text-primary font-medium'
                                        : 'border-border hover:border-primary/50',
                                ]"
                            >
                                <AllergenIcon :code="allergen.code" size="md" />
                                <span class="leading-tight">{{ allergen.name }}</span>
                            </button>
                        </div>
                        <p v-else class="text-sm text-muted-foreground">
                            No hay alérgenos configurados.
                        </p>
                    </CardContent>
                </Card>

                <!-- Ingredients -->
                <Card>
                    <CardHeader>
                        <CardTitle>Ingredientes</CardTitle>
                        <CardDescription>Añade los ingredientes del plato</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <!-- Tag input -->
                        <div class="relative flex gap-2">
                            <div class="relative flex-1">
                                <Input
                                    v-model="ingredientInput"
                                    placeholder="Escribir ingrediente..."
                                    @keydown="handleIngredientKeydown"
                                />
                                <!-- Suggestions -->
                                <div
                                    v-if="ingredientSuggestions.length > 0"
                                    class="absolute top-full z-10 mt-1 w-full rounded-md border bg-popover text-popover-foreground shadow-md"
                                >
                                    <button
                                        v-for="suggestion in ingredientSuggestions"
                                        :key="`${suggestion.shared ? 'c' : 'o'}:${suggestion.name}`"
                                        type="button"
                                        class="flex w-full items-center gap-2 px-3 py-2 text-sm hover:bg-accent"
                                        @click="selectSuggestion(suggestion.name)"
                                    >
                                        <span class="flex-1 text-left">{{ suggestion.name }}</span>
                                        <span
                                            v-if="suggestion.shared"
                                            class="flex items-center gap-1 rounded-full bg-primary/10 px-1.5 py-0.5 text-[10px] font-medium text-primary"
                                            title="Disponible en el catálogo compartido — se importará como copia privada"
                                        >
                                            <Users class="h-2.5 w-2.5" />
                                            Catálogo
                                        </span>
                                        <Sparkles
                                            v-if="suggestion.hasTranslations"
                                            class="h-3 w-3 text-emerald-500"
                                            title="Incluye traducciones"
                                        />
                                    </button>
                                </div>
                            </div>
                            <Button type="button" size="sm" variant="outline" @click="addIngredient">
                                <Plus class="h-4 w-4" />
                            </Button>
                        </div>

                        <!-- Tags list -->
                        <div
                            v-if="form.ingredient_names.length > 0"
                            class="flex flex-wrap gap-1.5"
                        >
                            <span
                                v-for="name in form.ingredient_names"
                                :key="name"
                                class="inline-flex items-center gap-1 rounded-full bg-secondary px-2.5 py-1 text-xs font-medium text-secondary-foreground"
                            >
                                {{ name }}
                                <button
                                    type="button"
                                    class="ml-0.5 rounded-full hover:text-destructive"
                                    @click="removeIngredient(name)"
                                    :aria-label="`Eliminar ${name}`"
                                >
                                    <X class="h-3 w-3" />
                                </button>
                            </span>
                        </div>
                        <p v-else class="text-xs text-muted-foreground">
                            Aún no hay ingredientes añadidos.
                        </p>
                    </CardContent>
                </Card>
            </div>
        </div>

        <!-- Actions -->
        <div class="my-5 flex justify-between">
            <div class="flex gap-2">
                <Button variant="outline" type="button" as-child>
                    <a :href="cancelHref">
                        <X class="mr-2 h-4 w-4" />
                        Cancelar
                    </a>
                </Button>
                <Button
                    v-if="showDeleteButton"
                    variant="destructive"
                    type="button"
                    @click="$emit('delete')"
                >
                    <Trash2 class="mr-2 h-4 w-4" />
                    Eliminar
                </Button>
            </div>
            <Button type="submit" :disabled="form.processing">
                <Save class="mr-2 h-4 w-4" />
                {{ form.processing ? 'Guardando...' : (isEdit ? 'Guardar cambios' : 'Crear plato') }}
            </Button>
        </div>
    </form>
</template>
