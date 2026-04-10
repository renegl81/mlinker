<template>
    <Card class="my-5">
        <form @submit.prevent="$emit('submit')">
            <CardHeader>
                <CardTitle>{{ title }}</CardTitle>
                <CardDescription>{{ description }}</CardDescription>
            </CardHeader>
            <CardContent>
                <div class="my-5 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <Label for="name">{{messages.locations.fields.name }}</Label>
                        <Input
                            id="name"
                            :model-value="form.name"
                            @update:model-value="$emit('update:field', 'name', $event)"
                            :placeholder="messages.locations.placeholders.name"
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
                        <Label for="address">{{
                            messages.locations.fields.address
                        }}</Label>
                        <Input
                            id="address"
                            :model-value="form.address"
                            @update:model-value="
                                $emit('update:field', 'address', $event)
                            "
                            :placeholder="
                                messages.locations.placeholders
                                    .address
                            "
                            :class="{
                                'border-destructive': form.errors.address,
                            }"
                        />
                        <p
                            v-if="form.errors.address"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.address }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="phone">{{
                            messages.locations.fields.phone
                        }}</Label>
                        <Input
                            id="phone"
                            :model-value="form.phone"
                            @update:model-value="$emit('update:field', 'phone', $event)"
                            :placeholder="messages.locations.placeholders.phone"
                            :class="{ 'border-destructive': form.errors.phone }"
                        />
                        <p
                            v-if="form.errors.phone"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.phone }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="city">{{
                            messages.locations.fields.city
                        }}</Label>
                        <Input
                            id="city"
                            :model-value="form.city"
                            @update:model-value="$emit('update:field', 'city', $event)"
                            :placeholder="messages.locations.placeholders.city"
                            :class="{ 'border-destructive': form.errors.city }"
                        />
                        <p
                            v-if="form.errors.city"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.city }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="province">{{
                            messages.locations.fields.province
                        }}</Label>
                        <Input
                            id="province"
                            :model-value="form.province"
                            @update:model-value="
                                $emit('update:field', 'province', $event)
                            "
                            :placeholder="
                                messages.locations.placeholders
                                    .province
                            "
                            :class="{
                                'border-destructive': form.errors.province,
                            }"
                        />
                        <p
                            v-if="form.errors.province"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.province }}
                        </p>
                    </div>
                    <div class="space-y-2">
                        <Label for="country_id">{{
                            messages.locations.fields.country
                        }}</Label>
                        <SelectRoot
                            :model-value="form.country_id"
                            @update:model-value="$emit('update:field', 'country_id', $event)"
                        >
                            <SelectTrigger
                                id="country_id"
                                :class="
                                    cn(
                                        'flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50',
                                        {
                                            'border-destructive':
                                                form.errors.country_id,
                                        },
                                    )
                                "
                            >
                                <SelectValue
                                    :placeholder="
                                        page.props.messages.countries.single
                                    "
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
                                            v-for="country in props.countries"
                                            :key="country.id"
                                            :value="country.id"
                                            class="relative flex w-full cursor-default items-center rounded-sm py-1.5 pr-2 pl-8 text-sm outline-none select-none focus:bg-accent focus:text-accent-foreground data-[highlighted]:bg-accent data-[highlighted]:text-accent-foreground"
                                        >
                                            <SelectItemIndicator
                                                class="absolute left-2 flex h-3.5 w-3.5 items-center justify-center"
                                            >
                                                <Check class="h-4 w-4" />
                                            </SelectItemIndicator>
                                            <SelectItemText>
                                                {{ country.name }}
                                            </SelectItemText>
                                        </SelectItem>
                                    </SelectViewport>
                                </SelectContent>
                            </SelectPortal>
                        </SelectRoot>

                        <p
                            v-if="form.errors.country_id"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.country_id }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="postal_code">{{
                            messages.locations.fields.postal_code
                        }}</Label>
                        <Input
                            id="postal_code"
                            :model-value="form.postal_code"
                            @update:model-value="
                                $emit('update:field', 'postal_code', $event)
                            "
                            :placeholder="
                                messages.locations.placeholders
                                    .postal_code
                            "
                            :class="{
                                'border-destructive': form.errors.postal_code,
                            }"
                        />
                        <p
                            v-if="form.errors.postal_code"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.postal_code }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="currency">{{
                            messages.locations.fields.currency
                        }}</Label>
                        <Input
                            id="currency"
                            :model-value="form.currency"
                            @update:model-value="
                                $emit('update:field', 'currency', $event)
                            "
                            :placeholder="
                               messages.locations.placeholders
                                    .currency
                            "
                            :class="{
                                'border-destructive': form.errors.currency,
                            }"
                            maxlength="3"
                        />
                        <p
                            v-if="form.errors.currency"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.currency }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="time_zone">{{
                            messages.locations.fields.time_zone
                        }}</Label>
                        <Input
                            id="time_zone"
                            :model-value="form.time_zone"
                            @update:model-value="
                                $emit('update:field', 'time_zone', $event)
                            "
                            :placeholder="
                                messages.locations.placeholders.time_zone
                            "
                            :class="{
                                'border-destructive': form.errors.time_zone,
                            }"
                        />
                        <p
                            v-if="form.errors.time_zone"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.time_zone }}
                        </p>
                    </div>

                    <!-- Location image -->
                    <div class="space-y-2 md:col-span-2">
                        <Label for="location_image">Foto del local</Label>
                        <div class="flex items-start gap-4">
                            <div v-if="imagePreview" class="relative shrink-0">
                                <img :src="imagePreview" alt="Foto del local" class="h-32 w-48 rounded-xl object-cover border" />
                                <button
                                    type="button"
                                    class="absolute -top-2 -right-2 flex h-6 w-6 items-center justify-center rounded-full bg-destructive text-destructive-foreground text-xs shadow"
                                    @click="removeImage"
                                >✕</button>
                            </div>
                            <div class="flex-1">
                                <input
                                    id="location_image"
                                    type="file"
                                    accept="image/jpeg,image/jpg,image/png,image/webp"
                                    class="block w-full text-sm text-muted-foreground file:mr-3 file:rounded-lg file:border-0 file:bg-primary file:px-4 file:py-2 file:text-sm file:font-medium file:text-primary-foreground hover:file:bg-primary/90 cursor-pointer"
                                    @change="handleFileChange"
                                />
                                <p class="mt-1.5 text-xs text-muted-foreground">JPG, PNG o WebP. Se usará en la web pública y en las cards del panel.</p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2 md:col-span-2">
                        <Label for="description">{{
                            messages.locations.fields.description
                        }}</Label>
                        <Textarea
                            id="description"
                            :model-value="form.description"
                            @update:model-value="
                                $emit('update:field', 'description', $event)
                            "
                            :placeholder="
                                messages.locations.placeholders.description
                            "
                            :class="{
                                'border-destructive': form.errors.description,
                            }"
                        />
                        <p
                            v-if="form.errors.description"
                            class="text-sm text-destructive"
                        >
                            {{ form.errors.description }}
                        </p>
                    </div>
                    <!-- Brand colors -->
                    <div class="space-y-2 md:col-span-2">
                        <Label>Colores de marca</Label>
                        <p class="text-xs text-muted-foreground mb-2">Se usarán en la página pública de tu negocio para personalizar la apariencia.</p>
                        <div class="flex flex-wrap gap-4">
                            <div class="flex items-center gap-2">
                                <input
                                    type="color"
                                    :value="form.primary_color || '#0d9488'"
                                    class="h-10 w-14 cursor-pointer rounded-lg border border-input p-0.5"
                                    @input="$emit('update:field', 'primary_color', ($event.target as HTMLInputElement).value)"
                                />
                                <div>
                                    <span class="text-sm font-medium">Primario</span>
                                    <span class="block text-xs text-muted-foreground">{{ form.primary_color || '#0d9488' }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <input
                                    type="color"
                                    :value="form.secondary_color || '#f59e0b'"
                                    class="h-10 w-14 cursor-pointer rounded-lg border border-input p-0.5"
                                    @input="$emit('update:field', 'secondary_color', ($event.target as HTMLInputElement).value)"
                                />
                                <div>
                                    <span class="text-sm font-medium">Secundario</span>
                                    <span class="block text-xs text-muted-foreground">{{ form.secondary_color || '#f59e0b' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </CardContent>

            <CardFooter class="my-5 flex justify-between">
                <Button variant="outline" type="button" as-child>
                    <Link :href="locationsRoute().url">
                        <X class="mr-2 h-4 w-4" />
                        {{ messages.locations.actions.cancel }}
                    </Link>
                </Button>
                <Button type="submit" :disabled="form.processing">
                    <Save class="mr-2 h-4 w-4" />
                    {{ form.processing ? processingText : submitText }}
                </Button>
            </CardFooter>
        </form>
    </Card>
</template>

<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Check, ChevronDown, ImageIcon, Save, X } from 'lucide-vue-next';
import { index as locationsRoute } from '@/routes/tenant/locations';
import { Country } from '@/types';
import { cn } from '@/lib/utils';
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

interface Props {
    form: {
        name: string;
        address: string;
        phone: string;
        city: string;
        province: string;
        postal_code: string;
        currency: string;
        time_zone: string;
        description?: string;
        country_id?: number;
        latitude?: string;
        longitude?: string;
        image_url?: string | null;
        errors: Record<string, string>;
        processing: boolean;
    };
    title?: string;
    description?: string;
    submitText?: string;
    processingText?: string;
    isEdit?: boolean;
    countries: Country[];
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

// Image preview logic
const imagePreview = computed(() => {
    const val = props.form.image_url;
    if (!val || typeof val !== 'string') return null;
    if (val.startsWith('data:') || val.startsWith('http')) return val;
    return null;
});

function handleFileChange(event: Event) {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = () => {
        emit('update:field', 'image_url', reader.result as string);
    };
    reader.readAsDataURL(file);
}

function removeImage() {
    emit('update:field', 'image_url', null);
    const input = document.getElementById('location_image') as HTMLInputElement | null;
    if (input) input.value = '';
}

const title = computed(() => {
    if (props.title) return props.title;
    return props.isEdit
        ? messages.locations.form.title_edit
        : messages.locations.form.title_create;
});

const description = computed(() => {
    if (props.description) return props.description;
    return props.isEdit
        ? messages.locations.form.description_edit
        : messages.locations.form.description_create;
});

const submitText = computed(() => {
    return props.submitText || messages.locations.actions.save;
});

const processingText = computed(() => {
    return props.processingText || messages.locations.actions.saving;
});
</script>
