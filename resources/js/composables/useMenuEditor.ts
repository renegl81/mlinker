import { ref } from 'vue';
import axios from 'axios';

type SaveStatus = 'idle' | 'saving' | 'saved' | 'error';

export function useMenuEditor(menuId: number) {
    const saveStatus = ref<SaveStatus>('idle');
    const saveError = ref<string>('');
    let savedTimer: ReturnType<typeof setTimeout> | null = null;

    function markSaved() {
        saveStatus.value = 'saved';
        if (savedTimer) clearTimeout(savedTimer);
        savedTimer = setTimeout(() => {
            saveStatus.value = 'idle';
        }, 1800);
    }

    function markError(message?: string) {
        saveStatus.value = 'error';
        saveError.value = message ?? 'Error al guardar';
        if (savedTimer) clearTimeout(savedTimer);
        savedTimer = setTimeout(() => {
            saveStatus.value = 'idle';
            saveError.value = '';
        }, 4000);
    }

    async function patchMenu(data: Record<string, unknown>) {
        saveStatus.value = 'saving';
        try {
            const res = await axios.patch(`/panel/menus/${menuId}/inline`, data);
            markSaved();
            return res.data.menu;
        } catch (err: unknown) {
            const message = extractErrorMessage(err);
            markError(message);
            throw err;
        }
    }

    async function patchSection(sectionId: number, data: Record<string, unknown>) {
        saveStatus.value = 'saving';
        try {
            const res = await axios.patch(`/panel/sections/${sectionId}`, data);
            markSaved();
            return res.data.section;
        } catch (err: unknown) {
            const message = extractErrorMessage(err);
            markError(message);
            throw err;
        }
    }

    async function patchProduct(productId: number, data: Record<string, unknown>) {
        saveStatus.value = 'saving';
        try {
            const res = await axios.patch(`/panel/products/${productId}`, data);
            markSaved();
            return res.data.product;
        } catch (err: unknown) {
            const message = extractErrorMessage(err);
            markError(message);
            throw err;
        }
    }

    return {
        saveStatus,
        saveError,
        patchMenu,
        patchSection,
        patchProduct,
    };
}

function extractErrorMessage(err: unknown): string {
    if (err && typeof err === 'object' && 'response' in err) {
        const response = (err as { response?: { data?: { message?: string; errors?: Record<string, string[]> } } }).response;
        if (response?.data?.message) return response.data.message;
        if (response?.data?.errors) {
            const firstKey = Object.keys(response.data.errors)[0];
            return response.data.errors[firstKey]?.[0] ?? 'Error de validación';
        }
    }
    return 'Error al guardar';
}
