<script setup lang="ts">
import { ref } from 'vue';
import { Share2, Link, MessageCircle, Twitter, Facebook, X } from 'lucide-vue-next';

interface Props {
    url: string;
    menuName: string;
    locationName: string;
}

const props = defineProps<Props>();

const isOpen = ref(false);
const copied = ref(false);

function toggle() {
    isOpen.value = !isOpen.value;
}

function close() {
    isOpen.value = false;
}

function shareText(): string {
    return encodeURIComponent(`Mira la carta de ${props.locationName}: ${props.url}`);
}

function openWhatsApp() {
    window.open(`https://wa.me/?text=${shareText()}`, '_blank', 'noopener,noreferrer');
    close();
}

function openTwitter() {
    const text = encodeURIComponent(`Mira la carta de ${props.locationName}`);
    window.open(
        `https://twitter.com/intent/tweet?url=${encodeURIComponent(props.url)}&text=${text}`,
        '_blank',
        'noopener,noreferrer',
    );
    close();
}

function openFacebook() {
    window.open(
        `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(props.url)}`,
        '_blank',
        'noopener,noreferrer',
    );
    close();
}

async function copyLink() {
    try {
        await navigator.clipboard.writeText(props.url);
        copied.value = true;
        setTimeout(() => {
            copied.value = false;
        }, 2000);
    } catch {
        // Fallback silencioso si el navegador no soporta clipboard API
    }
}
</script>

<template>
    <!-- Overlay para cerrar al hacer click fuera -->
    <div v-if="isOpen" class="fixed inset-0 z-40" @click="close" />

    <!-- FAB + Popover -->
    <div class="fixed bottom-6 right-6 z-50 flex flex-col items-end gap-2">
        <!-- Opciones del popover (aparecen encima del FAB) -->
        <Transition
            enter-active-class="transition duration-150 ease-out"
            enter-from-class="opacity-0 translate-y-2"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-100 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 translate-y-2"
        >
            <div
                v-if="isOpen"
                class="mb-2 flex flex-col gap-1 rounded-xl border bg-card p-2 shadow-lg"
            >
                <!-- WhatsApp -->
                <button
                    class="flex items-center gap-3 rounded-lg px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted"
                    @click="openWhatsApp"
                >
                    <MessageCircle class="h-4 w-4 text-green-500" />
                    WhatsApp
                </button>

                <!-- Copiar enlace -->
                <button
                    class="flex items-center gap-3 rounded-lg px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted"
                    @click="copyLink"
                >
                    <Link class="h-4 w-4 text-blue-500" />
                    {{ copied ? '¡Copiado!' : 'Copiar enlace' }}
                </button>

                <!-- Twitter / X -->
                <button
                    class="flex items-center gap-3 rounded-lg px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted"
                    @click="openTwitter"
                >
                    <Twitter class="h-4 w-4 text-sky-500" />
                    Twitter
                </button>

                <!-- Facebook -->
                <button
                    class="flex items-center gap-3 rounded-lg px-4 py-2 text-sm font-medium text-foreground transition-colors hover:bg-muted"
                    @click="openFacebook"
                >
                    <Facebook class="h-4 w-4 text-blue-700" />
                    Facebook
                </button>
            </div>
        </Transition>

        <!-- Botón FAB -->
        <button
            class="flex h-14 w-14 items-center justify-center rounded-full bg-primary text-primary-foreground shadow-lg transition-all hover:bg-primary/90 hover:shadow-xl active:scale-95"
            :aria-label="isOpen ? 'Cerrar opciones de compartir' : 'Compartir menú'"
            @click="toggle"
        >
            <X v-if="isOpen" class="h-6 w-6" />
            <Share2 v-else class="h-6 w-6" />
        </button>
    </div>
</template>
