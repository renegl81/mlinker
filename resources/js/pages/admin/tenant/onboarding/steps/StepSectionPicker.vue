<script setup lang="ts">
import { UtensilsCrossed } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import SectionCard from '../components/SectionCard.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

const { t } = useI18n();

const emit = defineEmits<{
    selected: [sectionName: string];
    back: [];
}>();

const selectedPreset = ref<string | null>(null);
const customName = ref('');
const showCustom = ref(false);

const presets = computed(() => [
    { key: 'starters', emoji: '🥗' },
    { key: 'mains',    emoji: '🍝' },
    { key: 'meats',    emoji: '🥩' },
    { key: 'desserts', emoji: '🍰' },
    { key: 'drinks',   emoji: '🥤' },
    { key: 'wines',    emoji: '🍷' },
    { key: 'coffees',  emoji: '☕' },
    { key: 'daily',    emoji: '📋' },
]);

function selectPreset(key: string) {
    selectedPreset.value = key;
    showCustom.value = false;
    const label = t(`panel.onboarding.section_presets.${key}`);
    emit('selected', label);
}

function toggleCustom() {
    showCustom.value = true;
    selectedPreset.value = null;
}

function confirmCustom() {
    if (customName.value.trim()) {
        emit('selected', customName.value.trim());
    }
}
</script>

<template>
    <div>
        <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10">
            <UtensilsCrossed class="h-6 w-6 text-primary" />
        </div>
        <h1 class="mb-2 text-2xl font-bold text-foreground">
            {{ t('panel.onboarding.section_title') }}
        </h1>
        <p class="mb-6 text-sm text-muted-foreground">
            {{ t('panel.onboarding.section_subtitle') }}
        </p>

        <!-- Preset grid -->
        <div class="mb-4 grid grid-cols-3 gap-3 sm:grid-cols-4">
            <SectionCard
                v-for="preset in presets"
                :key="preset.key"
                :label="t(`panel.onboarding.section_presets.${preset.key}`)"
                :emoji="preset.emoji"
                :selected="selectedPreset === preset.key"
                @select="selectPreset(preset.key)"
            />
        </div>

        <!-- Custom section -->
        <div v-if="!showCustom" class="mb-6">
            <button
                type="button"
                class="inline-flex items-center gap-2 rounded-xl border-2 border-dashed border-border px-4 py-3 text-sm text-muted-foreground transition-all hover:border-border/80 hover:text-foreground"
                @click="toggleCustom"
            >
                <span>+</span>
                <span>{{ t('panel.onboarding.section_other') }}</span>
            </button>
        </div>

        <div v-else class="mb-6 flex gap-2">
            <Input
                v-model="customName"
                type="text"
                :placeholder="t('panel.onboarding.section_other_placeholder')"
                class="flex-1"
                autofocus
                @keydown.enter="confirmCustom"
            />
            <Button
                type="button"
                :disabled="!customName.trim()"
                @click="confirmCustom"
            >
                →
            </Button>
        </div>

        <Button
            type="button"
            variant="ghost"
            class="text-muted-foreground"
            @click="emit('back')"
        >
            ← {{ t('panel.onboarding.back') }}
        </Button>
    </div>
</template>
