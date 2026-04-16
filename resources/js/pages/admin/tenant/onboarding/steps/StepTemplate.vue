<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Loader2, Sparkles, X } from 'lucide-vue-next';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import TemplateCard from '../components/TemplateCard.vue';
import TemplatePreview from '@/components/template-bodies/TemplatePreview.vue';

const { t } = useI18n();

interface TemplateConfig {
    color_scheme?: string;
    vibe?: string;
}

interface Template {
    id: number;
    name: string;
    component_name: string;
    description: string;
    preview_image_url: string | null;
    config: TemplateConfig;
}

const props = defineProps<{
    templates: Template[];
    locationId: number;
}>();

const emit = defineEmits<{
    back: [];
}>();

const selectedTemplateId = ref<number | null>(null);
const previewTemplate = ref<Template | null>(null);

const form = useForm({
    template_id: 0,
    location_id: props.locationId,
});

function selectTemplate(id: number) {
    selectedTemplateId.value = id;
    form.template_id = id;
    form.location_id = props.locationId;
}

function submit() {
    if (!selectedTemplateId.value) return;
    form.post(route('tenant.onboarding.menu'));
}

function openPreview(template: Template) {
    previewTemplate.value = template;
}

function closePreview() {
    previewTemplate.value = null;
}
</script>

<template>
    <div>
    <div>
        <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-teal-500/15">
            <Sparkles class="h-6 w-6 text-teal-400" />
        </div>
        <h1 class="mb-2 text-2xl font-bold text-white">
            {{ t('panel.onboarding.template_title') }}
        </h1>
        <p class="mb-6 text-sm text-slate-400">
            {{ t('panel.onboarding.template_subtitle') }}
        </p>

        <!-- Template grid -->
        <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
            <TemplateCard
                v-for="tpl in props.templates"
                :key="tpl.id"
                :template="tpl"
                :selected="selectedTemplateId === tpl.id"
                @select="selectTemplate"
                @preview="openPreview"
            />
        </div>

        <!-- Actions -->
        <div class="flex flex-col gap-3">
            <button
                type="button"
                :disabled="!selectedTemplateId || form.processing"
                class="flex h-11 w-full items-center justify-center gap-2 rounded-lg bg-gradient-to-r from-teal-600 to-cyan-600 px-6 font-semibold text-white shadow-lg shadow-teal-500/20 transition-all hover:from-teal-500 hover:to-cyan-500 disabled:cursor-not-allowed disabled:opacity-50"
                @click="submit"
            >
                <Loader2 v-if="form.processing" class="h-4 w-4 animate-spin" />
                <span>{{ t('panel.onboarding.template_use_cta') }}</span>
                <span v-if="!form.processing">→</span>
            </button>
            <button
                type="button"
                class="text-sm text-slate-500 transition-colors hover:text-slate-300"
                @click="emit('back')"
            >
                ← {{ t('panel.onboarding.back') }}
            </button>
        </div>
    </div>

    <!-- Preview modal -->
    <Teleport to="body">
        <Transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="previewTemplate"
                class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/90 p-4 backdrop-blur-sm"
                @click.self="closePreview"
            >
                <div class="relative max-h-[90vh] w-full max-w-2xl overflow-hidden rounded-2xl border border-slate-700 bg-slate-900 shadow-2xl">
                    <div class="flex items-center justify-between border-b border-slate-800 px-5 py-4">
                        <div>
                            <p class="font-semibold text-white">{{ previewTemplate.name }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">{{ previewTemplate.description }}</p>
                        </div>
                        <button
                            type="button"
                            class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-800 hover:text-white"
                            @click="closePreview"
                        >
                            <X class="h-5 w-5" />
                        </button>
                    </div>
                    <div class="bg-slate-800/50 p-4 overflow-hidden rounded-b-xl" aria-hidden="true">
                        <div class="relative w-full overflow-hidden rounded-lg" style="height: 480px;">
                            <div
                                class="pointer-events-none absolute left-0 top-0 origin-top-left"
                                style="width: 600px; height: 800px; transform: scale(0.8); transform-origin: top left;"
                            >
                                <TemplatePreview :component-name="previewTemplate.component_name" />
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 border-t border-slate-800 px-5 py-4">
                        <button
                            type="button"
                            class="rounded-lg px-4 py-2 text-sm text-slate-400 transition-colors hover:text-white"
                            @click="closePreview"
                        >
                            {{ t('common.cancel') }}
                        </button>
                        <button
                            type="button"
                            class="rounded-lg bg-teal-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-teal-500"
                            @click="() => { selectTemplate(previewTemplate!.id); closePreview(); }"
                        >
                            {{ t('panel.onboarding.template_use_cta') }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
    </div>
</template>
