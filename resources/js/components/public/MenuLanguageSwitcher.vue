<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { useI18n } from 'vue-i18n';

interface LocaleMeta {
    native: string;
    flag: string;
}

interface Props {
    current: string;
    available?: string[];
    localesMeta?: Record<string, LocaleMeta>;
}

const props = withDefaults(defineProps<Props>(), {
    available: () => ['es', 'en'],
    localesMeta: () => ({
        es: { native: 'Español', flag: '🇪🇸' },
        en: { native: 'English', flag: '🇬🇧' },
    }),
});

const { locale } = useI18n();

function label(code: string): LocaleMeta {
    return props.localesMeta[code] ?? { native: code.toUpperCase(), flag: '🏳️' };
}

const open = ref(false);
const rootRef = ref<HTMLElement | null>(null);

function toggle() {
    open.value = !open.value;
}

function choose(code: string) {
    open.value = false;
    if (code === props.current) return;

    // Sync vue-i18n (UI strings) instantly
    locale.value = code;
    if (typeof localStorage !== 'undefined') {
        localStorage.setItem('locale', code);
    }

    // Full reload so backend re-applies DB translations (menu/section/product)
    const url = new URL(window.location.href);
    url.searchParams.set('lang', code);
    router.visit(url.pathname + url.search, {
        preserveState: false,
        preserveScroll: false,
    });
}

function handleClickOutside(e: MouseEvent) {
    if (!rootRef.value) return;
    if (!rootRef.value.contains(e.target as Node)) {
        open.value = false;
    }
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    // Sync vue-i18n to match the backend-rendered locale on first paint
    if (locale.value !== props.current) {
        locale.value = props.current;
    }
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <div ref="rootRef" class="lang-switcher">
        <button
            type="button"
            class="lang-trigger"
            :aria-expanded="open"
            aria-haspopup="listbox"
            @click="toggle"
        >
            <span class="lang-flag" aria-hidden="true">{{ label(current).flag }}</span>
            <span class="lang-code">{{ current }}</span>
            <svg
                class="lang-caret"
                :class="{ 'is-open': open }"
                width="10"
                height="10"
                viewBox="0 0 10 10"
                fill="none"
            >
                <path d="M2 3.5L5 6.5L8 3.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
        </button>

        <ul
            v-if="open"
            class="lang-menu"
            role="listbox"
        >
            <li
                v-for="code in available"
                :key="code"
                role="option"
                :aria-selected="code === current"
            >
                <button
                    type="button"
                    class="lang-option"
                    :class="{ 'is-active': code === current }"
                    @click="choose(code)"
                >
                    <span class="lang-flag" aria-hidden="true">{{ label(code).flag }}</span>
                    <span class="lang-native">{{ label(code).native }}</span>
                </button>
            </li>
        </ul>
    </div>
</template>

<style scoped>
.lang-switcher {
    position: relative;
    font-family: inherit;
}

.lang-trigger {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.5rem 0.8rem;
    background: color-mix(in oklch, var(--menu-paper, #fff) 88%, transparent);
    backdrop-filter: blur(12px);
    border: 1px solid color-mix(in oklch, var(--menu-rule, #ccc) 70%, transparent);
    border-radius: 999px;
    font-size: 0.74rem;
    font-weight: 500;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    color: var(--menu-ink, inherit);
    cursor: pointer;
    transition: background 180ms, border-color 180ms;
}

.lang-trigger:hover {
    background: var(--menu-paper, #fff);
    border-color: var(--menu-accent, currentColor);
}

.lang-flag { font-size: 0.95rem; line-height: 1; }
.lang-code { font-variant-caps: all-small-caps; }

.lang-caret {
    transition: transform 200ms;
    opacity: 0.6;
}
.lang-caret.is-open { transform: rotate(180deg); }

.lang-menu {
    position: absolute;
    top: calc(100% + 0.4rem);
    right: 0;
    min-width: 160px;
    padding: 0.35rem;
    margin: 0;
    list-style: none;
    background: var(--menu-paper, #fff);
    border: 1px solid color-mix(in oklch, var(--menu-rule, #ccc) 60%, transparent);
    border-radius: 14px;
    box-shadow:
        0 1px 2px oklch(0 0 0 / 0.04),
        0 12px 40px -12px oklch(0 0 0 / 0.22);
    z-index: 40;
    animation: lang-pop 180ms cubic-bezier(.2,.65,.2,1);
}

.lang-option {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    width: 100%;
    padding: 0.55rem 0.75rem;
    background: transparent;
    border: none;
    border-radius: 10px;
    font-size: 0.85rem;
    color: var(--menu-ink, inherit);
    cursor: pointer;
    text-align: left;
    transition: background 150ms;
}

.lang-option:hover { background: color-mix(in oklch, var(--menu-accent, #000) 8%, transparent); }
.lang-option.is-active {
    color: var(--menu-accent, inherit);
    font-weight: 500;
}

.lang-native {
    font-family: inherit;
    letter-spacing: 0;
    text-transform: none;
}

@keyframes lang-pop {
    from { opacity: 0; transform: translateY(-4px) scale(0.98); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}
</style>
