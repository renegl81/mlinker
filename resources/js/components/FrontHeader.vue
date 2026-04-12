<script setup lang="ts">
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Sheet,
    SheetContent,
    SheetTrigger,
} from '@/components/ui/sheet';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { getInitials } from '@/composables/useInitials';
import { Link, router, usePage } from '@inertiajs/vue3';
import LocaleFlag from '@/components/ui/LocaleFlag.vue';
import { Globe, Menu } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const page = usePage();
const auth = computed(() => page.props.auth);
const scrolled = ref(false);

const { t, locale: i18nLocale } = useI18n();
const appName = page.props.name as string;

function onScroll() {
    scrolled.value = window.scrollY > 12;
}

onMounted(() => window.addEventListener('scroll', onScroll, { passive: true }));
onUnmounted(() => window.removeEventListener('scroll', onScroll));

interface NavLink {
    label: string;
    href: string;
}

const links = computed<NavLink[]>(() => [
    { label: t('home.header.nav.features'), href: '/#features' },
    { label: t('home.header.nav.pricing'), href: '/#pricing' },
    { label: t('home.header.nav.faq'), href: '/faq' },
    { label: t('home.header.nav.contact'), href: '/contact' },
]);

const UI_LOCALES = [
    { code: 'es', label: 'Español' },
    { code: 'en', label: 'English' },
    { code: 'ca', label: 'Català' },
    { code: 'gl', label: 'Galego' },
    { code: 'eu', label: 'Euskara' },
];

const currentLocaleLabel = computed(() => {
    const found = UI_LOCALES.find((l) => l.code === i18nLocale.value);
    return found ? found.code.toUpperCase() : 'ES';
});

function switchLocale(code: string) {
    i18nLocale.value = code;
    if (typeof localStorage !== 'undefined') {
        localStorage.setItem('locale', code);
    }
    const currentPath = window.location.pathname;
    // Remove existing locale prefix if any
    const cleanPath = currentPath.replace(/^\/(en|ca|gl|eu)(\/|$)/, '/');
    const newPath = code === 'es' ? cleanPath : `/${code}${cleanPath === '/' ? '' : cleanPath}`;
    router.visit(newPath);
}
</script>

<template>
    <header
        class="sticky top-0 z-50 transition-all duration-200"
        :class="scrolled
            ? 'bg-white/95 backdrop-blur-md border-b border-slate-200/80 shadow-sm'
            : 'bg-white/80 backdrop-blur-sm'"
    >
        <div class="mx-auto flex h-16 items-center px-4 md:max-w-7xl">
            <!-- Mobile hamburger -->
            <div class="lg:hidden">
                <Sheet>
                    <SheetTrigger :as-child="true">
                        <Button
                            variant="ghost"
                            size="icon"
                            class="mr-2 h-9 w-9 text-slate-600 hover:text-slate-900 hover:bg-slate-100"
                            :aria-label="t('home.header.open_menu')"
                        >
                            <Menu class="h-5 w-5" />
                        </Button>
                    </SheetTrigger>
                    <SheetContent side="left" class="w-[280px] p-6 bg-white border-r border-slate-200">
                        <!-- Mobile logo -->
                        <Link href="/" class="flex items-center mb-8">
                            <img src="/images/logo-name.png" :alt="appName" class="h-10 object-contain" />
                        </Link>
                        <nav class="space-y-1">
                            <Link
                                v-for="link in links"
                                :key="link.href"
                                :href="link.href"
                                class="flex items-center px-3 py-2.5 rounded-lg text-slate-700 hover:text-teal-600 hover:bg-teal-50 font-medium transition-colors"
                            >
                                {{ link.label }}
                            </Link>
                            <template v-if="!auth?.user">
                                <div class="pt-5 mt-5 border-t border-slate-100 space-y-2.5">
                                    <Link
                                        href="/login"
                                        class="flex items-center justify-center px-4 py-2.5 rounded-lg border border-slate-200 text-slate-700 hover:border-teal-300 hover:text-teal-600 font-medium transition-colors text-sm"
                                    >
                                        {{ t('home.header.login') }}
                                    </Link>
                                    <Link
                                        href="/register"
                                        class="flex items-center justify-center px-4 py-2.5 rounded-full bg-teal-500 hover:bg-teal-600 text-white font-bold transition-colors text-sm"
                                    >
                                        {{ t('home.header.get_started') }}
                                    </Link>
                                </div>
                            </template>

                            <!-- Selector de idioma en mobile -->
                            <div class="pt-5 mt-5 border-t border-slate-100">
                                <p class="px-3 text-xs font-bold uppercase tracking-widest text-slate-400 mb-2">{{ t('home.header.language') }}</p>
                                <div class="space-y-0.5">
                                    <button
                                        v-for="loc in UI_LOCALES"
                                        :key="loc.code"
                                        class="flex w-full items-center gap-2.5 px-3 py-2 rounded-lg text-sm transition-colors"
                                        :class="i18nLocale === loc.code
                                            ? 'bg-teal-50 text-teal-700 font-semibold'
                                            : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'"
                                        @click="switchLocale(loc.code)"
                                    >
                                        <LocaleFlag :code="loc.code" />
                                        <span>{{ loc.label }}</span>
                                    </button>
                                </div>
                            </div>
                        </nav>
                    </SheetContent>
                </Sheet>
            </div>

            <!-- Logo -->
            <Link href="/" class="flex items-center group">
                <img src="/images/logo-name.png" :alt="appName" class="hidden sm:block h-10 object-contain" />
                <img src="/images/logo.png" :alt="appName" class="block sm:hidden h-9 w-9 object-contain" />
            </Link>

            <!-- Desktop nav -->
            <nav class="hidden lg:flex lg:flex-1 ml-10 items-center gap-1">
                <Link
                    v-for="link in links"
                    :key="link.href"
                    :href="link.href"
                    class="px-3 py-2 rounded-lg text-sm font-medium text-slate-600 hover:text-teal-600 hover:bg-teal-50 transition-colors"
                >
                    {{ link.label }}
                </Link>
            </nav>

            <!-- Right side -->
            <div class="ml-auto flex items-center gap-2">
                <!-- Selector de idioma desktop -->
                <DropdownMenu>
                    <DropdownMenuTrigger :as-child="true">
                        <Button
                            variant="ghost"
                            size="sm"
                            class="hidden lg:inline-flex items-center gap-1.5 text-slate-500 hover:text-slate-800 hover:bg-slate-100 px-2.5"
                        >
                            <Globe class="h-4 w-4" />
                            <span class="text-xs font-semibold">{{ currentLocaleLabel }}</span>
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" class="w-44">
                        <DropdownMenuItem
                            v-for="loc in UI_LOCALES"
                            :key="loc.code"
                            class="flex items-center gap-2.5 cursor-pointer"
                            :class="i18nLocale === loc.code ? 'font-semibold text-teal-700 bg-teal-50' : ''"
                            @click="switchLocale(loc.code)"
                        >
                            <LocaleFlag :code="loc.code" />
                            <span>{{ loc.label }}</span>
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>

                <template v-if="!auth?.user">
                    <Link
                        href="/login"
                        class="hidden lg:inline-flex items-center px-4 py-2 text-sm font-medium text-slate-600 hover:text-slate-900 transition-colors"
                    >
                        {{ t('home.header.login') }}
                    </Link>
                    <Link
                        href="/register"
                        class="inline-flex items-center px-5 py-2 rounded-full bg-teal-500 hover:bg-teal-600 text-white text-sm font-bold transition-colors shadow-sm"
                    >
                        {{ t('home.header.get_started') }}
                    </Link>
                </template>

                <DropdownMenu v-if="auth?.user">
                    <DropdownMenuTrigger :as-child="true">
                        <Button variant="ghost" class="relative size-10 w-auto rounded-full p-1 border border-slate-200 hover:border-teal-300">
                            <Avatar class="h-8 w-8 overflow-hidden rounded-lg">
                                <AvatarFallback class="rounded-lg text-slate-700 bg-teal-50 text-xs font-semibold">
                                    {{ getInitials(auth?.user?.name) }}
                                </AvatarFallback>
                            </Avatar>
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" class="w-56">
                        <UserMenuContent :user="auth.user" />
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        </div>
    </header>
</template>
