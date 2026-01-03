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
                        <Label for="name">{{
                            page.props.messages.locations.fields.name
                        }}</Label>
                        <Input
                            id="name"
                            :model-value="form.name"
                            @update:model-value="
                                $emit('update:field', 'name', $event)
                            "
                            :placeholder="
                                page.props.messages.locations.placeholders.name
                            "
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
                            page.props.messages.locations.fields.address
                        }}</Label>
                        <Input
                            id="address"
                            :model-value="form.address"
                            @update:model-value="
                                $emit('update:field', 'address', $event)
                            "
                            :placeholder="
                                page.props.messages.locations.placeholders
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
                            page.props.messages.locations.fields.phone
                        }}</Label>
                        <Input
                            id="phone"
                            :model-value="form.phone"
                            @update:model-value="
                                $emit('update:field', 'phone', $event)
                            "
                            :placeholder="
                                page.props.messages.locations.placeholders.phone
                            "
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
                            page.props.messages.locations.fields.city
                        }}</Label>
                        <Input
                            id="city"
                            :model-value="form.city"
                            @update:model-value="
                                $emit('update:field', 'city', $event)
                            "
                            :placeholder="
                                page.props.messages.locations.placeholders.city
                            "
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
                            page.props.messages.locations.fields.province
                        }}</Label>
                        <Input
                            id="province"
                            :model-value="form.province"
                            @update:model-value="
                                $emit('update:field', 'province', $event)
                            "
                            :placeholder="
                                page.props.messages.locations.placeholders
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
                        <Label for="postal_code">{{
                            page.props.messages.locations.fields.postal_code
                        }}</Label>
                        <Input
                            id="postal_code"
                            :model-value="form.postal_code"
                            @update:model-value="
                                $emit('update:field', 'postal_code', $event)
                            "
                            :placeholder="
                                page.props.messages.locations.placeholders
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
                            page.props.messages.locations.fields.currency
                        }}</Label>
                        <Input
                            id="currency"
                            :model-value="form.currency"
                            @update:model-value="
                                $emit('update:field', 'currency', $event)
                            "
                            :placeholder="
                                page.props.messages.locations.placeholders
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

                    <div v-if="isEdit" class="flex items-center space-x-2">
                        <Switch
                            id="is_active"
                            :checked="form.is_active"
                            @update:checked="
                                $emit('update:field', 'is_active', $event)
                            "
                        />
                        <Label for="is_active">{{
                            messages.locations.fields.is_active
                        }}</Label>
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
import { Switch } from '@/components/ui/switch';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Save, X } from 'lucide-vue-next';
import { index as locationsRoute } from '@/routes/tenant/locations';

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
        is_active?: boolean;
        errors: Record<string, string>;
        processing: boolean;
    };
    title?: string;
    description?: string;
    submitText?: string;
    processingText?: string;
    isEdit?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    isEdit: false,
});

const page = usePage();
const messages = page.props.messages;
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

defineEmits<{
    submit: [];
    'update:field': [field: string, value: any];
}>();
</script>
