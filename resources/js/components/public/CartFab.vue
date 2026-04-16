<script setup lang="ts">
defineProps<{
    itemCount: number;
}>();

defineEmits<{
    click: [];
}>();
</script>

<template>
    <button
        type="button"
        class="cart-fab"
        @click="$emit('click')"
    >
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/>
            <line x1="3" y1="6" x2="21" y2="6"/>
            <path d="M16 10a4 4 0 01-8 0"/>
        </svg>
        <span v-if="itemCount > 0" class="cart-fab-badge">
            {{ itemCount > 99 ? '99+' : itemCount }}
        </span>
    </button>
</template>

<style scoped>
.cart-fab {
    position: fixed;
    bottom: 1.5rem;
    left: 1.5rem;
    z-index: 40;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 56px;
    height: 56px;
    border-radius: 999px;
    border: none;
    background: var(--ml-accent, oklch(0.55 0.15 165));
    color: #fff;
    cursor: pointer;
    box-shadow: 0 4px 20px -4px oklch(0 0 0 / 0.3);
    transition: transform 150ms;
    -webkit-tap-highlight-color: transparent;
    touch-action: manipulation;
}

.cart-fab:active {
    transform: scale(0.92);
}

.cart-fab-badge {
    position: absolute;
    top: -2px;
    right: -2px;
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 22px;
    height: 22px;
    padding: 0 5px;
    border-radius: 999px;
    background: var(--ml-ink, #222);
    color: var(--ml-bg, #fff);
    font-size: 0.7rem;
    font-weight: 700;
    font-variant-numeric: tabular-nums;
    line-height: 1;
    animation: cart-badge-pop 200ms cubic-bezier(0.2, 0.65, 0.2, 1);
}

@keyframes cart-badge-pop {
    0% { transform: scale(0.5); }
    70% { transform: scale(1.15); }
    100% { transform: scale(1); }
}
</style>
