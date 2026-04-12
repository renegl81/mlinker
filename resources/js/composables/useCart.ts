import { computed, ref, watch } from 'vue';

export interface CartItem {
    productId: number;
    name: string;
    price: number;
    priceDisplay: string;
    quantity: number;
    imageUrl: string | null;
}

export interface CartState {
    menuId: number;
    items: CartItem[];
    customerName: string;
    updatedAt: number;
}

function storageKey(menuId: number): string {
    return `ml-cart-${menuId}`;
}

function loadCartState(menuId: number): { items: CartItem[]; customerName: string } {
    try {
        const raw = localStorage.getItem(storageKey(menuId));
        if (!raw) return { items: [], customerName: '' };
        const state: CartState = JSON.parse(raw);
        if (state.menuId !== menuId) return { items: [], customerName: '' };
        return {
            items: state.items ?? [],
            customerName: state.customerName ?? '',
        };
    } catch {
        return { items: [], customerName: '' };
    }
}

function saveCart(menuId: number, items: CartItem[], customerName: string): void {
    try {
        const state: CartState = { menuId, items, customerName, updatedAt: Date.now() };
        localStorage.setItem(storageKey(menuId), JSON.stringify(state));
    } catch {
        // localStorage unavailable (Safari private mode)
    }
}

export function useCart(menuId: number) {
    const { items: savedItems, customerName: savedName } = loadCartState(menuId);
    const items = ref<CartItem[]>(savedItems);
    const customerName = ref<string>(savedName);

    watch(
        [items, customerName],
        ([newItems, newName]) => saveCart(menuId, newItems, newName),
        { deep: true },
    );

    const totalItems = computed(() => items.value.reduce((sum, i) => sum + i.quantity, 0));
    const totalPrice = computed(() => items.value.reduce((sum, i) => sum + i.price * i.quantity, 0));

    function addItem(product: { id: number; name: string; price: number | string; image_path?: string | null }, priceDisplay: string) {
        const existing = items.value.find(i => i.productId === product.id);
        if (existing) {
            existing.quantity++;
        } else {
            items.value.push({
                productId: product.id,
                name: product.name,
                price: typeof product.price === 'string' ? parseFloat(product.price) : product.price,
                priceDisplay,
                quantity: 1,
                imageUrl: product.image_path ?? null,
            });
        }
    }

    function incrementItem(productId: number) {
        const existing = items.value.find(i => i.productId === productId);
        if (existing) existing.quantity++;
    }

    function removeItem(productId: number) {
        const idx = items.value.findIndex(i => i.productId === productId);
        if (idx === -1) return;
        if (items.value[idx].quantity > 1) {
            items.value[idx].quantity--;
        } else {
            items.value.splice(idx, 1);
        }
    }

    function deleteItem(productId: number) {
        items.value = items.value.filter(i => i.productId !== productId);
    }

    function clearCart() {
        items.value = [];
        customerName.value = '';
    }

    function getQuantity(productId: number): number {
        return items.value.find(i => i.productId === productId)?.quantity ?? 0;
    }

    return {
        items,
        customerName,
        totalItems,
        totalPrice,
        addItem,
        incrementItem,
        removeItem,
        deleteItem,
        clearCart,
        getQuantity,
    };
}
