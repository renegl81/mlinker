<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { useConfirmDialog } from '@/composables/useConfirmDialog';
import { AlertTriangle } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const { isOpen, options, handleConfirm, handleCancel } = useConfirmDialog();
</script>

<template>
    <Dialog :open="isOpen" @update:open="(v) => { if (!v) handleCancel(); }">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle class="flex items-center gap-2">
                    <AlertTriangle v-if="options.variant === 'destructive'" class="h-5 w-5 text-destructive" />
                    {{ options.title || t('common.confirm') }}
                </DialogTitle>
                <DialogDescription>
                    {{ options.description }}
                </DialogDescription>
            </DialogHeader>
            <DialogFooter class="gap-2 sm:gap-0">
                <Button variant="outline" @click="handleCancel">
                    {{ options.cancelLabel || t('common.cancel') }}
                </Button>
                <Button
                    :variant="options.variant === 'destructive' ? 'destructive' : 'default'"
                    @click="handleConfirm"
                >
                    {{ options.confirmLabel || t('common.confirm') }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
