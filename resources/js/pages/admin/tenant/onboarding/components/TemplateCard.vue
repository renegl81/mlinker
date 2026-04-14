<script setup lang="ts">
import { Check } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';
import TemplatePreview from '@/components/template-bodies/TemplatePreview.vue';

const { t } = useI18n();

interface TemplateConfig {
    color_scheme?: string;
    vibe?: string;
    font_style?: string;
    layout?: string;
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
    template: Template;
    selected: boolean;
}>();

const emit = defineEmits<{
    select: [id: number];
    preview: [template: Template];
}>();

// Visual chip derived from config
function vibeChip(vibe?: string): string {
    const map: Record<string, string> = {
        editorial: 'Clásico',
        contemporary: 'Moderno',
        gastronomic: 'Gastronómico',
        'italian-rustic': 'Italiano',
        izakaya: 'Izakaya',
        organic: 'Vegano',
        mediterranean: 'Mediterráneo',
        'tasting-menu': 'Alta cocina',
    };
    return vibe ? (map[vibe] ?? vibe) : '';
}

function colorChip(scheme?: string): string {
    const map: Record<string, string> = {
        dark: 'Oscuro',
        'dark-neon': 'Neon',
        light: 'Claro',
        warm: 'Cálido',
        earthy: 'Natural',
        coastal: 'Costero',
        refined: 'Refinado',
    };
    return scheme ? (map[scheme] ?? scheme) : '';
}

</script>

<template>
    <button
        type="button"
        :aria-pressed="props.selected"
        class="group relative flex flex-col overflow-hidden rounded-xl border-2 text-left transition-all focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-500 focus-visible:ring-offset-2 focus-visible:ring-offset-slate-900"
        :class="
            props.selected
                ? 'border-teal-500 bg-teal-500/10 shadow-lg shadow-teal-500/20'
                : 'border-slate-700 bg-slate-800/60 hover:border-slate-500 hover:bg-slate-800'
        "
        @click="emit('select', props.template.id)"
    >
        <!-- Preview: miniatura viva de la plantilla real escalada -->
        <div class="relative h-32 w-full overflow-hidden bg-slate-700/40 sm:h-36" aria-hidden="true">
            <div
                class="pointer-events-none absolute left-0 top-0 origin-top-left"
                style="width: 600px; height: 800px; transform: scale(0.24); transform-origin: top left;"
            >
                <TemplatePreview :component-name="props.template.component_name" />
            </div>

            <!-- Hover overlay with preview button -->
            <div
                class="absolute inset-0 flex items-center justify-center bg-slate-900/60 opacity-0 transition-opacity duration-200 group-hover:opacity-100"
                @click.stop="emit('preview', props.template)"
            >
                <span class="rounded-lg bg-white/10 px-3 py-1.5 text-xs font-semibold text-white backdrop-blur-sm">
                    {{ t('panel.onboarding.template_preview') }}
                </span>
            </div>

            <!-- Selected check -->
            <div
                v-if="props.selected"
                class="absolute right-2 top-2 flex h-6 w-6 items-center justify-center rounded-full bg-teal-500 shadow-md"
            >
                <Check class="h-3.5 w-3.5 text-white" />
            </div>
        </div>

        <!-- Info -->
        <div class="flex flex-1 flex-col gap-1.5 p-3">
            <p class="font-semibold text-sm text-white leading-tight">{{ props.template.name }}</p>
            <div class="flex flex-wrap gap-1">
                <span
                    v-if="vibeChip(props.template.config.vibe)"
                    class="rounded-full bg-slate-700/70 px-2 py-0.5 text-[10px] font-medium text-slate-300"
                >
                    {{ vibeChip(props.template.config.vibe) }}
                </span>
                <span
                    v-if="colorChip(props.template.config.color_scheme)"
                    class="rounded-full bg-slate-700/70 px-2 py-0.5 text-[10px] font-medium text-slate-300"
                >
                    {{ colorChip(props.template.config.color_scheme) }}
                </span>
            </div>
        </div>
    </button>
</template>
