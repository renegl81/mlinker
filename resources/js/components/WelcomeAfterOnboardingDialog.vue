<script setup lang="ts">
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { useI18n } from 'vue-i18n';

interface Props {
    open: boolean;
    publicUrl: string;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    (e: 'close'): void;
}>();

const { t } = useI18n();
</script>

<template>
    <Dialog :open="props.open" @update:open="(val) => !val && emit('close')">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <div class="mb-2 flex justify-center text-4xl" aria-hidden="true">🎉</div>
                <DialogTitle class="text-center text-xl font-bold">
                    {{ t('panel.onboarding.welcome.title') }}
                </DialogTitle>
                <DialogDescription class="text-center text-sm text-muted-foreground">
                    {{ t('panel.onboarding.welcome.body') }}
                </DialogDescription>
            </DialogHeader>

            <DialogFooter class="mt-4 flex-col gap-2 sm:flex-col">
                <!-- CTA primaria: ir al panel / cerrar -->
                <Button
                    class="w-full"
                    size="lg"
                    @click="emit('close')"
                >
                    {{ t('panel.onboarding.welcome.cta_customize') }}
                </Button>

                <!-- CTAs secundarias -->
                <div class="flex flex-col gap-2 sm:flex-row">
                    <Button
                        variant="outline"
                        class="w-full sm:flex-1"
                        as-child
                    >
                        <a
                            :href="publicUrl"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            {{ t('panel.onboarding.success_view_public') }}
                        </a>
                    </Button>

                    <TooltipProvider>
                        <Tooltip>
                            <TooltipTrigger as-child>
                                <span class="w-full sm:flex-1">
                                    <Button
                                        variant="outline"
                                        class="w-full pointer-events-none"
                                        disabled
                                    >
                                        {{ t('panel.onboarding.success_download_qr') }}
                                    </Button>
                                </span>
                            </TooltipTrigger>
                            <TooltipContent>
                                Próximamente
                            </TooltipContent>
                        </Tooltip>
                    </TooltipProvider>
                </div>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
