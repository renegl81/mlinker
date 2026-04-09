<script setup lang="ts">
import { ref, computed } from 'vue';

const props = withDefaults(
    defineProps<{
        modelValue: string | null;
        uploadUrl: string;
        maxSizeKb?: number;
    }>(),
    {
        maxSizeKb: 2048,
    },
);

const emit = defineEmits<{
    (e: 'update:modelValue', value: string | null): void;
}>();

const fileInput = ref<HTMLInputElement | null>(null);
const isDragging = ref(false);
const isUploading = ref(false);
const uploadProgress = ref(0);
const errorMessage = ref<string | null>(null);

const previewUrl = computed<string | null>(() => {
    if (!props.modelValue) return null;
    if (props.modelValue.startsWith('data:') || props.modelValue.startsWith('http')) {
        return props.modelValue;
    }
    // Path relativo — construir URL pública
    return `/storage/${props.modelValue}`;
});

function openFilePicker(): void {
    fileInput.value?.click();
}

function onFileInputChange(event: Event): void {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0] ?? null;
    if (file) {
        processFile(file);
    }
    // Reset para permitir seleccionar el mismo archivo de nuevo
    input.value = '';
}

function onDragover(event: DragEvent): void {
    event.preventDefault();
    isDragging.value = true;
}

function onDragleave(): void {
    isDragging.value = false;
}

function onDrop(event: DragEvent): void {
    event.preventDefault();
    isDragging.value = false;
    const file = event.dataTransfer?.files?.[0] ?? null;
    if (file) {
        processFile(file);
    }
}

function processFile(file: File): void {
    errorMessage.value = null;

    if (!file.type.startsWith('image/')) {
        errorMessage.value = 'El archivo debe ser una imagen (JPG, PNG o WebP).';
        return;
    }

    const maxBytes = props.maxSizeKb * 1024;
    if (file.size > maxBytes) {
        errorMessage.value = `El archivo no debe superar ${props.maxSizeKb} KB.`;
        return;
    }

    uploadFile(file);
}

async function uploadFile(file: File): Promise<void> {
    isUploading.value = true;
    uploadProgress.value = 0;
    errorMessage.value = null;

    const formData = new FormData();
    formData.append('image', file);

    try {
        const csrfToken = (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement | null)?.content ?? '';

        const response = await fetch(props.uploadUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                Accept: 'application/json',
            },
            body: formData,
        });

        if (!response.ok) {
            const data = (await response.json().catch(() => ({}))) as Record<string, unknown>;
            const message =
                typeof data.message === 'string'
                    ? data.message
                    : 'Error al subir la imagen. Inténtalo de nuevo.';
            throw new Error(message);
        }

        const data = (await response.json()) as { url: string; thumbnail_url: string };
        emit('update:modelValue', data.url);
    } catch (err) {
        errorMessage.value = err instanceof Error ? err.message : 'Error desconocido al subir la imagen.';
    } finally {
        isUploading.value = false;
        uploadProgress.value = 100;
    }
}

function removeImage(): void {
    emit('update:modelValue', null);
    errorMessage.value = null;
}
</script>

<template>
    <div class="w-full">
        <!-- Zona de drag & drop -->
        <div
            class="relative flex min-h-40 w-full cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed transition-colors"
            :class="{
                'border-primary bg-primary/5': isDragging,
                'border-border bg-muted/30 hover:bg-muted/50': !isDragging && !previewUrl,
                'border-border': !isDragging && previewUrl,
                'overflow-hidden': previewUrl,
            }"
            @click="!previewUrl ? openFilePicker() : undefined"
            @dragover="onDragover"
            @dragleave="onDragleave"
            @drop="onDrop"
        >
            <!-- Preview de imagen existente -->
            <template v-if="previewUrl && !isUploading">
                <img
                    :src="previewUrl"
                    alt="Vista previa"
                    class="h-40 w-full object-cover"
                />
                <button
                    type="button"
                    class="absolute right-2 top-2 flex size-7 items-center justify-center rounded-full bg-black/60 text-white transition-opacity hover:bg-black/80"
                    title="Eliminar imagen"
                    @click.stop="removeImage"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="size-4"
                        viewBox="0 0 20 20"
                        fill="currentColor"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                            clip-rule="evenodd"
                        />
                    </svg>
                </button>
                <!-- Overlay al hacer hover para cambiar imagen -->
                <button
                    type="button"
                    class="absolute inset-0 flex items-center justify-center bg-black/0 transition-all hover:bg-black/30"
                    title="Cambiar imagen"
                    @click.stop="openFilePicker"
                >
                    <span
                        class="rounded-md bg-black/60 px-3 py-1.5 text-xs font-medium text-white opacity-0 transition-opacity group-hover:opacity-100 hover:opacity-100"
                    >
                        Cambiar imagen
                    </span>
                </button>
            </template>

            <!-- Estado de carga -->
            <template v-else-if="isUploading">
                <div class="flex flex-col items-center gap-3 p-6">
                    <svg
                        class="size-8 animate-spin text-primary"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                    >
                        <circle
                            class="opacity-25"
                            cx="12"
                            cy="12"
                            r="10"
                            stroke="currentColor"
                            stroke-width="4"
                        />
                        <path
                            class="opacity-75"
                            fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
                        />
                    </svg>
                    <span class="text-sm text-muted-foreground">Subiendo imagen...</span>
                </div>
            </template>

            <!-- Estado vacío / placeholder -->
            <template v-else>
                <div class="flex flex-col items-center gap-3 p-6">
                    <svg
                        class="size-10 text-muted-foreground"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.5"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                        />
                    </svg>
                    <div class="text-center">
                        <p class="text-sm font-medium text-foreground">
                            Arrastra una imagen o haz clic
                        </p>
                        <p class="mt-1 text-xs text-muted-foreground">
                            JPG, PNG o WebP — máx. {{ maxSizeKb }} KB
                        </p>
                    </div>
                </div>
            </template>
        </div>

        <!-- Mensaje de error -->
        <p v-if="errorMessage" class="mt-2 text-sm text-destructive">
            {{ errorMessage }}
        </p>

        <!-- Input oculto -->
        <input
            ref="fileInput"
            type="file"
            accept="image/jpeg,image/png,image/webp"
            class="hidden"
            @change="onFileInputChange"
        />
    </div>
</template>
