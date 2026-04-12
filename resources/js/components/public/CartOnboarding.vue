<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps<{
    menuId: number;
}>();

const visible = ref(false);

const STORAGE_KEY = 'ml-cart-onboarding-seen';

onMounted(() => {
    try {
        const seen = localStorage.getItem(STORAGE_KEY);
        if (!seen) {
            setTimeout(() => {
                visible.value = true;
            }, 800);
        }
    } catch {
        // localStorage unavailable
    }
});

function dismiss() {
    visible.value = false;
    try {
        localStorage.setItem(STORAGE_KEY, '1');
    } catch {
        // ignore
    }
}
</script>

<template>
    <Teleport to="body">
        <Transition name="cart-onboarding">
            <div v-if="visible" class="cart-onboarding" @click="dismiss">
                <div class="cart-onboarding-card" @click.stop>
                    <div class="cart-onboarding-icon">🛒</div>
                    <div class="cart-onboarding-text">
                        <p class="cart-onboarding-title">{{ t('public_menu.cart.onboarding_title') }}</p>
                        <p class="cart-onboarding-body">{{ t('public_menu.cart.onboarding_body') }}</p>
                    </div>
                    <button type="button" class="cart-onboarding-close" @click="dismiss">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M4 4l8 8M12 4L4 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                        </svg>
                    </button>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.cart-onboarding {
    position: fixed;
    bottom: 5.5rem;
    left: 1rem;
    right: 1rem;
    z-index: 45;
    display: flex;
    justify-content: center;
    pointer-events: none;
}

.cart-onboarding-card {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    max-width: 400px;
    width: 100%;
    padding: 0.85rem 1rem;
    border-radius: 14px;
    background: var(--ml-bg, #fff);
    color: var(--ml-ink, #222);
    border: 1px solid color-mix(in oklch, var(--ml-rule, #ddd) 80%, transparent);
    box-shadow:
        0 4px 24px -6px oklch(0 0 0 / 0.15),
        0 1px 3px oklch(0 0 0 / 0.06);
    font-family: var(--ml-font-body, system-ui, sans-serif);
    pointer-events: auto;
    animation: cart-onboarding-float 3s ease-in-out infinite;
}

.cart-onboarding-icon {
    font-size: 1.5rem;
    line-height: 1;
    flex-shrink: 0;
    margin-top: 0.1rem;
}

.cart-onboarding-text {
    flex: 1;
    min-width: 0;
}

.cart-onboarding-title {
    font-size: 0.82rem;
    font-weight: 600;
    margin: 0 0 0.2rem;
    color: var(--ml-ink, #222);
}

.cart-onboarding-body {
    font-size: 0.75rem;
    line-height: 1.45;
    margin: 0;
    color: var(--ml-ink-soft, #666);
}

.cart-onboarding-close {
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border: none;
    border-radius: 999px;
    background: transparent;
    color: var(--ml-ink-soft, #666);
    cursor: pointer;
    transition: background 150ms;
    -webkit-tap-highlight-color: transparent;
}

.cart-onboarding-close:hover {
    background: color-mix(in oklch, var(--ml-rule, #ddd) 50%, transparent);
}

@keyframes cart-onboarding-float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-3px); }
}

.cart-onboarding-enter-active {
    transition: opacity 350ms ease, transform 350ms cubic-bezier(0.2, 0.65, 0.2, 1);
}
.cart-onboarding-leave-active {
    transition: opacity 200ms ease, transform 200ms ease;
}
.cart-onboarding-enter-from {
    opacity: 0;
    transform: translateY(20px);
}
.cart-onboarding-leave-to {
    opacity: 0;
    transform: translateY(10px);
}
</style>
