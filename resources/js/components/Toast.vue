<script setup lang="ts">
import { ref, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'
import {
    ToastProvider,
    ToastRoot,
    ToastDescription,
    ToastViewport,
} from 'reka-ui'
import { CheckCircle2, XCircle } from 'lucide-vue-next'

const page = usePage()
const open = ref(false)
const message = ref('')
const type = ref<'success' | 'error'>('success')

watch(
    () => page.props.flash,
    (flash) => {
        if (flash.success) {
            message.value = flash.success
            type.value = 'success'
            open.value = true
        }
        if (flash.error) {
            message.value = flash.error
            type.value = 'error'
            open.value = true
        }
    },
    { deep: true, immediate: true }
)
</script>

<template>
    <ToastProvider>
        <ToastRoot
            v-model:open="open"
            :duration="3000"
            class="data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-80 data-[state=closed]:slide-out-to-right-full data-[state=open]:slide-in-from-top-full"
            :class="[
                'fixed top-4 right-4 z-[100] flex w-full max-w-md items-center gap-3 rounded-lg border p-4 shadow-lg sm:w-auto',
                type === 'success' ? 'border-green-200 bg-green-50 text-green-900' : 'border-red-200 bg-red-50 text-red-900'
            ]"
        >
            <CheckCircle2 v-if="type === 'success'" class="h-5 w-5 flex-shrink-0 text-green-600" />
            <XCircle v-else class="h-5 w-5 flex-shrink-0 text-red-600" />
            <ToastDescription class="text-sm font-medium">
                {{ message }}
            </ToastDescription>
        </ToastRoot>
        <ToastViewport class="fixed top-0 right-0 z-[100] flex max-h-screen w-full flex-col p-4 md:max-w-[420px]" />
    </ToastProvider>
</template>
