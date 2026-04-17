<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { Check } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import StepBasics from './steps/StepBasics.vue';
import StepProducts from './steps/StepProducts.vue';
import StepSectionPicker from './steps/StepSectionPicker.vue';
import StepSuccess from './steps/StepSuccess.vue';
import StepTemplate from './steps/StepTemplate.vue';

const { t } = useI18n();
const appName = usePage().props.name as string;

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

interface Location {
    id: number;
    name: string;
}

interface Menu {
    id: number;
    name: string;
}

const props = defineProps<{
    step: number;
    tenantName: string;
    userName: string;
    location?: Location | null;
    menu?: Menu | null;
    templates: Template[];
}>();

// ─── Local navigation state ───────────────────────────────────────────────────
// The backend drives the step on hard navigations; within a section+products
// sub-flow we use a client-side sub-step to avoid extra round-trips.
type SubStep = 'basics' | 'template' | 'section' | 'products' | 'success';

function mapStep(serverStep: number): SubStep {
    // server step: 0=basics, 1=template, 2=products, 3=success
    if (serverStep === 0) return 'basics';
    if (serverStep === 1) return 'template';
    if (serverStep === 2) return 'section';
    if (serverStep === 3) return 'success';
    return 'success';
}

const currentSubStep = ref<SubStep>(mapStep(props.step));

// For "add another section" flow we keep client state
const selectedSection = ref<string>('');
const completedSections = ref<Array<{ name: string; products: Array<{ name: string; price: string }> }>>([]);

// ─── Progress bar (3 visible steps) ──────────────────────────────────────────
const visibleSteps = computed(() => [
    t('panel.onboarding.step_basics'),
    t('panel.onboarding.step_template'),
    t('panel.onboarding.step_products'),
]);

const progressIndex = computed(() => {
    const map: Record<SubStep, number> = {
        basics: 0,
        template: 1,
        section: 2,
        products: 2,
        success: 3,
    };
    return map[currentSubStep.value] ?? 0;
});

// ─── Navigation handlers ──────────────────────────────────────────────────────
function goBack() {
    if (currentSubStep.value === 'template') currentSubStep.value = 'basics';
    else if (currentSubStep.value === 'section') currentSubStep.value = 'template';
    else if (currentSubStep.value === 'products') currentSubStep.value = 'section';
}

function onSectionSelected(name: string) {
    selectedSection.value = name;
    currentSubStep.value = 'products';
}

function onAddSection(rows: Array<{ name: string; price: string }>) {
    completedSections.value.push({ name: selectedSection.value, products: rows });
    selectedSection.value = '';
    currentSubStep.value = 'section';
}

// When backend redirects back with a new step, re-sync our sub-step
// (Inertia will re-render the component with new props)
// This handles the case where location or menu are already set from a previous visit
if (props.step === 1 && props.location) {
    // Already have location, move to template selection
    currentSubStep.value = 'template';
}
if (props.step === 2 && props.menu) {
    currentSubStep.value = 'section';
}
if (props.step === 3) {
    currentSubStep.value = 'success';
}

// Inertia reuses the same component on navigation — sync sub-step when
// the server-side step advances (e.g. after storeBasics → step=1).
watch(
    () => props.step,
    (s) => {
        currentSubStep.value = mapStep(s);
        if (s === 1 && props.location) currentSubStep.value = 'template';
        if (s === 2 && props.menu) currentSubStep.value = 'section';
        if (s === 3) currentSubStep.value = 'success';
    },
);
</script>

<template>
    <div class="min-h-svh bg-background text-foreground">
        <div class="flex min-h-svh flex-col items-center justify-start px-4 py-10">
            <!-- Logo -->
            <div class="mb-10 flex items-center">
                <img
                    src="/images/logo-name.png"
                    :alt="appName"
                    class="h-10 object-contain"
                />
            </div>

            <!-- Progress bar (only for non-success steps) -->
            <div v-if="currentSubStep !== 'success'" class="mb-8 w-full max-w-xl">
                <div class="mb-3 flex justify-between">
                    <div
                        v-for="(label, i) in visibleSteps"
                        :key="i"
                        class="flex flex-1 flex-col items-center gap-2"
                    >
                        <div
                            class="flex h-8 w-8 items-center justify-center rounded-full border-2 text-xs font-bold transition-all"
                            :class="
                                i < progressIndex
                                    ? 'border-primary bg-primary text-primary-foreground'
                                    : i === progressIndex
                                      ? 'border-primary bg-primary/15 text-primary'
                                      : 'border-border bg-card text-muted-foreground'
                            "
                        >
                            <Check v-if="i < progressIndex" class="h-4 w-4" />
                            <span v-else>{{ i + 1 }}</span>
                        </div>
                        <span
                            class="text-[11px] font-medium"
                            :class="i <= progressIndex ? 'text-primary' : 'text-muted-foreground'"
                        >
                            {{ label }}
                        </span>
                    </div>
                </div>
                <div class="relative mt-2 h-1.5 w-full overflow-hidden rounded-full bg-muted">
                    <div
                        class="absolute left-0 top-0 h-full rounded-full bg-primary transition-all duration-500"
                        :style="{ width: Math.round((progressIndex / visibleSteps.length) * 100) + '%' }"
                    />
                </div>
            </div>

            <!-- Card -->
            <div
                class="w-full rounded-2xl border border-border bg-card shadow-sm"
                :class="currentSubStep === 'template' ? 'max-w-3xl p-6 sm:p-8' : 'max-w-xl p-8'"
            >
                <Transition
                    mode="out-in"
                    enter-active-class="transition-all duration-200"
                    enter-from-class="opacity-0 translate-y-2"
                    enter-to-class="opacity-100 translate-y-0"
                    leave-active-class="transition-all duration-150"
                    leave-from-class="opacity-100 translate-y-0"
                    leave-to-class="opacity-0 -translate-y-2"
                >
                    <StepBasics
                        v-if="currentSubStep === 'basics'"
                        :tenant-name="props.tenantName"
                        :user-name="props.userName"
                        @skip="currentSubStep = 'basics'"
                    />

                    <StepTemplate
                        v-else-if="currentSubStep === 'template'"
                        :templates="props.templates"
                        :location-id="props.location?.id ?? 0"
                        @back="goBack"
                    />

                    <StepSectionPicker
                        v-else-if="currentSubStep === 'section'"
                        @selected="onSectionSelected"
                        @back="goBack"
                    />

                    <StepProducts
                        v-else-if="currentSubStep === 'products'"
                        :section-name="selectedSection"
                        :menu-id="props.menu?.id ?? 0"
                        :all-sections="completedSections"
                        @add-section="onAddSection"
                        @back="currentSubStep = 'section'"
                        @done="currentSubStep = 'success'"
                    />

                    <StepSuccess
                        v-else-if="currentSubStep === 'success'"
                        :menu="props.menu ?? null"
                    />
                </Transition>
            </div>

            <!-- Footer -->
            <p v-if="currentSubStep !== 'success'" class="mt-8 text-xs text-muted-foreground">
                {{ t('panel.onboarding.step_counter', { current: progressIndex + 1, total: visibleSteps.length }) }}
            </p>
        </div>
    </div>
</template>
