<script setup lang="ts">
import { Crown, Pencil } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

interface Location {
    id: number;
    name: string;
}

interface FormShape {
    role: 'owner' | 'editor';
    location_scope: 'all' | 'locations';
    location_ids: number[];
    errors: Record<string, string>;
}

interface Props {
    form: FormShape;
    locations: Location[];
}

const props = defineProps<Props>();

const emit = defineEmits<{
    'update:field': [field: string, value: unknown];
}>();

function setRole(role: 'owner' | 'editor') {
    emit('update:field', 'role', role);
    if (role === 'owner') {
        emit('update:field', 'location_scope', 'all');
        emit('update:field', 'location_ids', []);
    }
}

function setScope(scope: 'all' | 'locations') {
    emit('update:field', 'location_scope', scope);
    if (scope === 'all') {
        emit('update:field', 'location_ids', []);
    }
}

function toggleLocation(id: number) {
    const current = new Set(props.form.location_ids);
    if (current.has(id)) current.delete(id);
    else current.add(id);
    emit('update:field', 'location_ids', Array.from(current));
}

const isEditor = computed(() => props.form.role === 'editor');
</script>

<template>
    <div class="space-y-5">
        <!-- Role picker -->
        <div class="space-y-2">
            <p class="panel-label">{{ t('panel.users.role_label') }}</p>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <button
                    type="button"
                    class="flex items-start gap-3 rounded-xl border p-4 text-left transition-colors"
                    :class="
                        form.role === 'owner'
                            ? 'border-primary bg-primary/5'
                            : 'border-border bg-card hover:bg-muted/30'
                    "
                    @click="setRole('owner')"
                >
                    <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300">
                        <Crown class="h-4 w-4" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-foreground">Owner</p>
                        <p class="mt-0.5 text-xs text-muted-foreground">
                            {{ t('panel.users.owner_description') }}
                        </p>
                    </div>
                </button>

                <button
                    type="button"
                    class="flex items-start gap-3 rounded-xl border p-4 text-left transition-colors"
                    :class="
                        form.role === 'editor'
                            ? 'border-primary bg-primary/5'
                            : 'border-border bg-card hover:bg-muted/30'
                    "
                    @click="setRole('editor')"
                >
                    <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-sky-100 text-sky-700 dark:bg-sky-950 dark:text-sky-300">
                        <Pencil class="h-4 w-4" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-foreground">Editor</p>
                        <p class="mt-0.5 text-xs text-muted-foreground">
                            {{ t('panel.users.editor_description') }}
                        </p>
                    </div>
                </button>
            </div>
            <p v-if="form.errors.role" class="text-xs text-destructive">{{ form.errors.role }}</p>
        </div>

        <!-- Scope (only for editors) -->
        <div v-if="isEditor" class="space-y-3 rounded-xl border bg-card text-card-foreground p-4">
            <p class="panel-label">{{ t('panel.users.scope_label') }}</p>

            <div class="space-y-2">
                <label class="flex cursor-pointer items-start gap-2.5 text-sm">
                    <input
                        type="radio"
                        name="location_scope"
                        value="all"
                        :checked="form.location_scope === 'all'"
                        class="mt-0.5 h-4 w-4 cursor-pointer"
                        @change="setScope('all')"
                    />
                    <div>
                        <span class="font-medium text-foreground">{{ t('panel.users.scope_all') }}</span>
                        <p class="text-xs text-muted-foreground">
                            {{ t('panel.users.scope_all_description') }}
                        </p>
                    </div>
                </label>

                <label class="flex cursor-pointer items-start gap-2.5 text-sm">
                    <input
                        type="radio"
                        name="location_scope"
                        value="locations"
                        :checked="form.location_scope === 'locations'"
                        class="mt-0.5 h-4 w-4 cursor-pointer"
                        @change="setScope('locations')"
                    />
                    <div>
                        <span class="font-medium text-foreground">{{ t('panel.users.scope_specific') }}</span>
                        <p class="text-xs text-muted-foreground">
                            {{ t('panel.users.scope_specific_description') }}
                        </p>
                    </div>
                </label>
            </div>

            <!-- Location checkboxes -->
            <div
                v-if="form.location_scope === 'locations'"
                class="mt-2 space-y-1.5 rounded-md border bg-background p-3 max-h-48 overflow-y-auto"
            >
                <div v-if="locations.length === 0" class="text-xs text-muted-foreground italic">
                    {{ t('panel.users.no_locations_yet') }}
                </div>
                <label
                    v-for="loc in locations"
                    :key="loc.id"
                    class="flex cursor-pointer items-center gap-2 rounded px-2 py-1.5 text-sm hover:bg-muted/40"
                >
                    <input
                        type="checkbox"
                        :checked="form.location_ids.includes(loc.id)"
                        class="h-4 w-4 cursor-pointer"
                        @change="toggleLocation(loc.id)"
                    />
                    <span class="text-foreground">{{ loc.name }}</span>
                </label>
            </div>
            <p v-if="form.errors.location_ids" class="text-xs text-destructive">
                {{ form.errors.location_ids }}
            </p>
        </div>
    </div>
</template>
