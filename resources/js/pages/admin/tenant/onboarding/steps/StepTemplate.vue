<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Loader2, Sparkles } from 'lucide-vue-next';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import TemplateCard from '../components/TemplateCard.vue';
import TemplatePreview from '@/components/template-bodies/TemplatePreview.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    DialogFooter,
} from '@/components/ui/dialog';

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
        <div class="mb-5 flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10">
            <Sparkles class="h-6 w-6 text-primary" />
        </div>
        <h1 class="mb-2 text-2xl font-bold text-foreground">
            {{ t('panel.onboarding.template_title') }}
        </h1>
        <p class="mb-6 text-sm text-muted-foreground">
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
            <Button
                type="button"
                :disabled="!selectedTemplateId || form.processing"
                class="w-full"
                size="lg"
                @click="submit"
            >
                <Loader2 v-if="form.processing" class="h-4 w-4 animate-spin" />
                <span>{{ t('panel.onboarding.template_use_cta') }}</span>
                <span v-if="!form.processing">→</span>
            </Button>
            <Button
                type="button"
                variant="ghost"
                class="text-muted-foreground"
                @click="emit('back')"
            >
                ← {{ t('panel.onboarding.back') }}
            </Button>
        </div>
    </div>

    <!-- Preview modal usando Dialog del UI kit -->
    <Dialog :open="!!previewTemplate" @update:open="(val) => !val && closePreview()">
        <DialogContent class="sm:max-w-2xl overflow-hidden p-0">
            <DialogHeader class="border-b border-border px-5 py-4">
                <DialogTitle>{{ previewTemplate?.name }}</DialogTitle>
                <DialogDescription class="mt-0.5">{{ previewTemplate?.description }}</DialogDescription>
            </DialogHeader>

            <div class="bg-muted/40 p-4 overflow-hidden" aria-hidden="true">
                <div class="relative w-full overflow-hidden rounded-lg" style="height: 480px;">
                    <div
                        class="pointer-events-none absolute left-0 top-0 origin-top-left"
                        style="width: 600px; height: 800px; transform: scale(0.8); transform-origin: top left;"
                    >
                        <TemplatePreview
                            v-if="previewTemplate"
                            :component-name="previewTemplate.component_name"
                        />
                    </div>
                </div>
            </div>

            <DialogFooter class="border-t border-border px-5 py-4">
                <Button variant="ghost" @click="closePreview">
                    {{ t('common.cancel') }}
                </Button>
                <Button
                    @click="() => { selectTemplate(previewTemplate!.id); closePreview(); }"
                >
                    {{ t('panel.onboarding.template_use_cta') }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
    </div>
</template>
