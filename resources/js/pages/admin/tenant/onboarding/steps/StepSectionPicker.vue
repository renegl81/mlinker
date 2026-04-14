<script setup lang="ts">
import { UtensilsCrossed } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import SectionCard from '../components/SectionCard.vue';

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
        <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-teal-500/15">
            <UtensilsCrossed class="h-6 w-6 text-teal-400" />
        </div>
        <h1 class="mb-2 text-2xl font-bold text-white">
            {{ t('panel.onboarding.section_title') }}
        </h1>
        <p class="mb-6 text-sm text-slate-400">
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
                class="inline-flex items-center gap-2 rounded-xl border-2 border-dashed border-slate-700 px-4 py-3 text-sm text-slate-400 transition-all hover:border-slate-500 hover:text-white"
                @click="toggleCustom"
            >
                <span>+</span>
                <span>{{ t('panel.onboarding.section_other') }}</span>
            </button>
        </div>

        <div v-else class="mb-6 flex gap-2">
            <input
                v-model="customName"
                type="text"
                :placeholder="t('panel.onboarding.section_other_placeholder')"
                class="flex h-11 flex-1 rounded-lg border border-slate-700 bg-slate-900/60 px-4 py-2 text-sm text-white placeholder:text-slate-500 transition-colors focus:border-teal-500 focus:outline-none focus:ring-2 focus:ring-teal-500/30"
                autofocus
                @keydown.enter="confirmCustom"
            />
            <button
                type="button"
                :disabled="!customName.trim()"
                class="h-11 rounded-lg bg-teal-600 px-4 font-semibold text-white transition-colors hover:bg-teal-500 disabled:cursor-not-allowed disabled:opacity-50"
                @click="confirmCustom"
            >
                →
            </button>
        </div>

        <button
            type="button"
            class="text-sm text-slate-500 transition-colors hover:text-slate-300"
            @click="emit('back')"
        >
            ← {{ t('panel.onboarding.back') }}
        </button>
    </div>
</template>
