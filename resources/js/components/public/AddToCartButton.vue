<script setup lang="ts">
defineProps<{
    quantity: number;
}>();

const emit = defineEmits<{
    add: [];
    remove: [];
}>();
</script>

<template>
    <div class="cart-btn-wrap">
        <!-- Simple + button when not in cart -->
        <button
            v-if="quantity === 0"
            type="button"
            class="cart-btn-add"
            @click.stop="emit('add')"
        >
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M8 3v10M3 8h10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        </button>

        <!-- Expanded quantity control when in cart -->
        <div v-else class="cart-btn-qty">
            <button type="button" class="cart-btn-minus" @click.stop="emit('remove')">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M3 7h8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            </button>
            <span class="cart-btn-count">{{ quantity }}</span>
            <button type="button" class="cart-btn-plus" @click.stop="emit('add')">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none"><path d="M7 3v8M3 7h8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            </button>
        </div>
    </div>
</template>

<style scoped>
.cart-btn-wrap {
    flex-shrink: 0;
}

.cart-btn-add {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 999px;
    border: none;
    background: var(--ml-accent, oklch(0.55 0.15 165));
    color: #fff;
    cursor: pointer;
    transition: transform 150ms, opacity 150ms;
    -webkit-tap-highlight-color: transparent;
    touch-action: manipulation;
}

.cart-btn-add:active {
    transform: scale(0.9);
}

.cart-btn-qty {
    display: inline-flex;
    align-items: center;
    gap: 0;
    border-radius: 999px;
    background: var(--ml-accent, oklch(0.55 0.15 165));
    color: #fff;
    overflow: hidden;
    transition: width 200ms ease;
}

.cart-btn-minus,
.cart-btn-plus {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border: none;
    background: transparent;
    color: inherit;
    cursor: pointer;
    -webkit-tap-highlight-color: transparent;
    touch-action: manipulation;
}

.cart-btn-minus:active,
.cart-btn-plus:active {
    opacity: 0.7;
}

.cart-btn-count {
    min-width: 24px;
    text-align: center;
    font-size: 0.85rem;
    font-weight: 600;
    font-variant-numeric: tabular-nums;
    line-height: 1;
    user-select: none;
}
</style>
