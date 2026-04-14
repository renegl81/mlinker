<script setup lang="ts">
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import LocaleFlag from '@/components/ui/LocaleFlag.vue';
import { Globe } from 'lucide-vue-next';
import { useI18n } from 'vue-i18n';

const { locale, t } = useI18n();

const SUPPORTED_LOCALES = ['es', 'en', 'ca', 'gl', 'eu'] as const;
type SupportedLocale = (typeof SUPPORTED_LOCALES)[number];

const locales: { code: SupportedLocale; label: string }[] = [
    { code: 'es', label: 'Español' },
    { code: 'en', label: 'English' },
    { code: 'ca', label: 'Català / Valencià' },
    { code: 'gl', label: 'Galego' },
    { code: 'eu', label: 'Euskara' },
];

function changeLocale(code: SupportedLocale) {
    locale.value = code;
    if (typeof localStorage !== 'undefined') {
        localStorage.setItem('locale', code);
    }
}

const currentLocaleEntry = () => locales.find((l) => l.code === (locale.value as SupportedLocale)) ?? locales[0];
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger
            class="flex items-center gap-1.5 rounded-md px-2 py-1.5 text-sm text-muted-foreground transition-colors hover:bg-accent hover:text-foreground"
            :title="t('languages.select')"
        >
            <Globe class="h-4 w-4" />
            <span class="text-xs font-medium uppercase">{{ locale }}</span>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" class="min-w-[160px]">
            <DropdownMenuItem
                v-for="loc in locales"
                :key="loc.code"
                class="flex cursor-pointer items-center gap-2"
                :class="{ 'font-semibold text-primary': loc.code === locale.value }"
                @click="changeLocale(loc.code)"
            >
                <LocaleFlag :code="loc.code" />
                <span>{{ loc.label }}</span>
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
