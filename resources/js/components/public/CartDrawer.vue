<script setup lang="ts">
import { onUnmounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

interface CartItem {
    productId: number;
    name: string;
    price: number;
    priceDisplay: string;
    quantity: number;
    imageUrl: string | null;
}

const props = defineProps<{
    open: boolean;
    items: CartItem[];
    totalPrice: number;
    formatPrice: (n: number) => string;
    customerName: string;
    orderEmail: string | null;
    orderWhatsapp: string | null;
}>();

const emit = defineEmits<{
    close: [];
    increment: [productId: number];
    decrement: [productId: number];
    delete: [productId: number];
    clear: [];
    'update:customerName': [name: string];
    sendEmail: [];
    sendWhatsapp: [];
}>();

// ── Drag to dismiss ──
const panelRef = ref<HTMLElement | null>(null);
const dragY = ref(0);
const isDragging = ref(false);
let startY = 0;

function onTouchStart(e: TouchEvent) {
    const el = panelRef.value;
    if (!el) return;
    // Only start drag from the top area (handle)
    const touch = e.touches[0];
    const rect = el.getBoundingClientRect();
    if (touch.clientY - rect.top > 48) return;
    startY = touch.clientY;
    isDragging.value = true;
}

function onTouchMove(e: TouchEvent) {
    if (!isDragging.value) return;
    const dy = e.touches[0].clientY - startY;
    if (dy > 0) {
        dragY.value = dy;
        e.preventDefault();
    }
}

function onTouchEnd() {
    if (!isDragging.value) return;
    isDragging.value = false;
    if (dragY.value > 100) {
        emit('close');
    }
    dragY.value = 0;
}

// ── Body scroll lock ──
let savedOverflow = '';
watch(() => props.open, (open) => {
    if (open) {
        savedOverflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = savedOverflow;
    }
});

onUnmounted(() => {
    document.body.style.overflow = savedOverflow;
});
</script>

<template>
    <Teleport to="body">
        <Transition name="cart-drawer">
            <div v-if="open" class="cart-overlay" @click.self="$emit('close')">
                <div
                    ref="panelRef"
                    class="cart-panel"
                    :style="dragY > 0 ? { transform: `translateY(${dragY}px)` } : undefined"
                    @touchstart.passive="onTouchStart"
                    @touchmove="onTouchMove"
                    @touchend="onTouchEnd"
                >
                    <!-- Drag handle -->
                    <div class="cart-handle-area">
                        <div class="cart-handle" />
                    </div>

                    <!-- Header -->
                    <div class="cart-header">
                        <div>
                            <h2 class="cart-title">{{ t('public_menu.cart.title') }}</h2>
                            <p v-if="items.length" class="cart-subtitle">
                                {{ items.reduce((s, i) => s + i.quantity, 0) }}
                                {{ items.reduce((s, i) => s + i.quantity, 0) === 1 ? t('public_menu.cart.item_singular') : t('public_menu.cart.item_plural') }}
                            </p>
                        </div>
                        <button type="button" class="cart-close" @click="$emit('close')">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M5 5l10 10M15 5L5 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                        </button>
                    </div>

                    <!-- Customer name -->
                    <div class="cart-name-section">
                        <label class="cart-name-label" for="cart-name">{{ t('public_menu.cart.your_name') }}</label>
                        <input
                            id="cart-name"
                            type="text"
                            class="cart-name-input"
                            :value="customerName"
                            @input="$emit('update:customerName', ($event.target as HTMLInputElement).value)"
                            :placeholder="t('public_menu.cart.name_placeholder')"
                            autocomplete="given-name"
                        />
                    </div>

                    <!-- Items list -->
                    <div class="cart-body">
                        <div v-if="items.length === 0" class="cart-empty">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" style="opacity: 0.3">
                                <path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/>
                            </svg>
                            <p class="cart-empty-text">{{ t('public_menu.cart.empty') }}</p>
                            <p class="cart-empty-hint">{{ t('public_menu.cart.empty_hint') }}</p>
                        </div>

                        <ul v-else class="cart-list">
                            <li v-for="item in items" :key="item.productId" class="cart-item">
                                <div class="cart-item-info">
                                    <span class="cart-item-name">{{ item.name }}</span>
                                    <span class="cart-item-price">{{ item.priceDisplay }}</span>
                                </div>
                                <div class="cart-item-actions">
                                    <div class="cart-item-qty">
                                        <button type="button" class="cart-qty-btn" @click="item.quantity === 1 ? $emit('delete', item.productId) : $emit('decrement', item.productId)">
                                            <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                                                <path v-if="item.quantity === 1" d="M3 3l6 6M9 3L3 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                                <path v-else d="M2.5 6h7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                                            </svg>
                                        </button>
                                        <span class="cart-qty-num">{{ item.quantity }}</span>
                                        <button type="button" class="cart-qty-btn" @click="$emit('increment', item.productId)">
                                            <svg width="12" height="12" viewBox="0 0 12 12" fill="none"><path d="M6 2.5v7M2.5 6h7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                                        </button>
                                    </div>
                                    <span class="cart-item-total">{{ formatPrice(item.price * item.quantity) }}</span>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <!-- Footer -->
                    <div v-if="items.length > 0" class="cart-footer">
                        <div class="cart-total-row">
                            <span class="cart-total-label">{{ t('public_menu.cart.total') }}</span>
                            <span class="cart-total-price">{{ formatPrice(totalPrice) }}</span>
                        </div>

                        <!-- Send buttons -->
                        <div class="cart-send-actions">
                            <button
                                v-if="orderWhatsapp"
                                type="button"
                                class="cart-send-btn cart-send-whatsapp"
                                @click="$emit('sendWhatsapp')"
                            >
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                {{ t('public_menu.cart.send_whatsapp') }}
                            </button>
                            <button
                                v-if="orderEmail"
                                type="button"
                                class="cart-send-btn cart-send-email"
                                @click="$emit('sendEmail')"
                            >
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                                {{ t('public_menu.cart.send_email') }}
                            </button>
                        </div>

                        <p class="cart-waiter-hint">{{ t('public_menu.cart.show_to_waiter') }}</p>
                        <button type="button" class="cart-clear" @click="$emit('clear')">
                            {{ t('public_menu.cart.clear') }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.cart-overlay {
    position: fixed;
    inset: 0;
    z-index: 50;
    display: flex;
    align-items: flex-end;
    background: oklch(0 0 0 / 0.45);
    backdrop-filter: blur(2px);
}

.cart-panel {
    width: 100%;
    max-height: 85vh;
    display: flex;
    flex-direction: column;
    background: var(--ml-bg, #fff);
    color: var(--ml-ink, #222);
    border-radius: 20px 20px 0 0;
    box-shadow: 0 -8px 40px -12px oklch(0 0 0 / 0.25);
    font-family: var(--ml-font-body, system-ui, sans-serif);
    will-change: transform;
    touch-action: pan-y;
}

@media (min-width: 640px) {
    .cart-panel {
        max-width: 480px;
        margin: 0 auto;
        border-radius: 20px 20px 0 0;
    }
}

.cart-handle-area {
    display: flex;
    justify-content: center;
    padding: 10px 0 4px;
    cursor: grab;
}

.cart-handle {
    width: 36px;
    height: 4px;
    border-radius: 999px;
    background: var(--ml-rule, #ddd);
}

.cart-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.5rem 1.25rem 0.75rem;
}

.cart-title {
    font-family: var(--ml-font-display, var(--ml-font-body, system-ui));
    font-size: 1.15rem;
    font-weight: 600;
    margin: 0;
    color: var(--ml-ink, #222);
}

.cart-subtitle {
    font-size: 0.75rem;
    color: var(--ml-ink-soft, #666);
    margin: 0.15rem 0 0;
}

.cart-close {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border: none;
    border-radius: 999px;
    background: transparent;
    color: var(--ml-ink-soft, #666);
    cursor: pointer;
    transition: background 150ms;
    -webkit-tap-highlight-color: transparent;
}

.cart-close:hover {
    background: color-mix(in oklch, var(--ml-rule, #ddd) 50%, transparent);
}

.cart-body {
    flex: 1;
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
    padding: 0 1.25rem;
    min-height: 80px;
}

.cart-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2.5rem 1rem;
    gap: 0.5rem;
    color: var(--ml-ink-soft, #666);
}

.cart-empty-text {
    font-size: 0.9rem;
    font-weight: 500;
    margin: 0;
}

.cart-empty-hint {
    font-size: 0.78rem;
    opacity: 0.7;
    margin: 0;
}

.cart-list {
    list-style: none;
    margin: 0;
    padding: 0;
}

.cart-item {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    padding: 0.85rem 0;
    border-bottom: 1px solid color-mix(in oklch, var(--ml-rule, #ddd) 60%, transparent);
}

.cart-item:last-child {
    border-bottom: none;
}

.cart-item-info {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 0.75rem;
}

.cart-item-name {
    font-size: 0.88rem;
    font-weight: 500;
    line-height: 1.3;
    color: var(--ml-ink, #222);
}

.cart-item-price {
    font-size: 0.78rem;
    color: var(--ml-ink-soft, #666);
    flex-shrink: 0;
}

.cart-item-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.cart-item-qty {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    border: 1px solid var(--ml-rule, #ddd);
    overflow: hidden;
}

.cart-qty-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 28px;
    border: none;
    background: transparent;
    color: var(--ml-ink, #222);
    cursor: pointer;
    -webkit-tap-highlight-color: transparent;
    touch-action: manipulation;
}

.cart-qty-btn:active {
    background: color-mix(in oklch, var(--ml-rule, #ddd) 50%, transparent);
}

.cart-qty-num {
    min-width: 28px;
    text-align: center;
    font-size: 0.82rem;
    font-weight: 600;
    font-variant-numeric: tabular-nums;
    user-select: none;
}

.cart-item-total {
    font-size: 0.88rem;
    font-weight: 600;
    color: var(--ml-accent, oklch(0.55 0.15 165));
}

.cart-footer {
    flex-shrink: 0;
    padding: 0.85rem 1.25rem;
    padding-bottom: max(0.85rem, env(safe-area-inset-bottom, 0.85rem));
    border-top: 1px solid var(--ml-rule, #ddd);
}

.cart-total-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.cart-total-label {
    font-family: var(--ml-font-display, var(--ml-font-body, system-ui));
    font-size: 1rem;
    font-weight: 600;
    color: var(--ml-ink, #222);
}

.cart-total-price {
    font-family: var(--ml-font-display, var(--ml-font-body, system-ui));
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--ml-accent, oklch(0.55 0.15 165));
}

.cart-waiter-hint {
    font-size: 0.72rem;
    color: var(--ml-ink-soft, #666);
    text-align: center;
    margin: 0.25rem 0 0.75rem;
    opacity: 0.8;
}

.cart-clear {
    display: block;
    width: 100%;
    padding: 0.5rem;
    border: none;
    border-radius: 10px;
    background: transparent;
    color: var(--ml-ink-soft, #666);
    font-size: 0.78rem;
    cursor: pointer;
    transition: background 150ms, color 150ms;
    -webkit-tap-highlight-color: transparent;
}

.cart-clear:hover {
    background: oklch(0.55 0.2 25 / 0.08);
    color: oklch(0.55 0.2 25);
}

/* Transitions */
.cart-drawer-enter-active {
    transition: opacity 250ms ease-out;
}
.cart-drawer-enter-active .cart-panel {
    transition: transform 300ms cubic-bezier(0.2, 0.65, 0.2, 1);
}
.cart-drawer-leave-active {
    transition: opacity 200ms ease-in;
}
.cart-drawer-leave-active .cart-panel {
    transition: transform 200ms ease-in;
}
.cart-drawer-enter-from {
    opacity: 0;
}
.cart-drawer-enter-from .cart-panel {
    transform: translateY(100%);
}
.cart-drawer-leave-to {
    opacity: 0;
}
.cart-drawer-leave-to .cart-panel {
    transform: translateY(100%);
}

/* Customer name input */
.cart-name-section {
    padding: 0 1.25rem 0.75rem;
    border-bottom: 1px solid color-mix(in oklch, var(--ml-rule, #ddd) 60%, transparent);
}

.cart-name-label {
    display: block;
    font-size: 0.72rem;
    font-weight: 500;
    color: var(--ml-ink-soft, #666);
    margin-bottom: 0.35rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.cart-name-input {
    width: 100%;
    padding: 0.6rem 0.85rem;
    border: 1px solid var(--ml-rule, #ddd);
    border-radius: 10px;
    background: transparent;
    color: var(--ml-ink, #222);
    font-family: var(--ml-font-body, system-ui, sans-serif);
    font-size: 0.9rem;
    outline: none;
    transition: border-color 150ms;
    box-sizing: border-box;
}

.cart-name-input:focus {
    border-color: var(--ml-accent, oklch(0.55 0.15 165));
}

.cart-name-input::placeholder {
    color: color-mix(in oklch, var(--ml-ink-soft, #666) 60%, transparent);
}

/* Send buttons */
.cart-send-actions {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin: 0.75rem 0;
}

.cart-send-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    width: 100%;
    padding: 0.7rem 1rem;
    border: none;
    border-radius: 12px;
    font-family: var(--ml-font-body, system-ui, sans-serif);
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: opacity 150ms;
    -webkit-tap-highlight-color: transparent;
}

.cart-send-btn:active {
    opacity: 0.8;
}

.cart-send-whatsapp {
    background: #25D366;
    color: #fff;
}

.cart-send-email {
    background: var(--ml-accent, oklch(0.55 0.15 165));
    color: #fff;
}
</style>
