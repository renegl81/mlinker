<script setup lang="ts">
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Sheet,
    SheetContent,
    SheetTrigger,
} from '@/components/ui/sheet';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { getInitials } from '@/composables/useInitials';
import { Link, usePage } from '@inertiajs/vue3';
import { Menu } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';

const page = usePage();
const auth = computed(() => page.props.auth);
const scrolled = ref(false);

function onScroll() {
    scrolled.value = window.scrollY > 12;
}

onMounted(() => window.addEventListener('scroll', onScroll, { passive: true }));
onUnmounted(() => window.removeEventListener('scroll', onScroll));

interface NavLink {
    label: string;
    href: string;
}

const links: NavLink[] = [
    { label: 'Funcionalidades', href: '/#features' },
    { label: 'Precios', href: '/#pricing' },
    { label: 'FAQ', href: '/faq' },
    { label: 'Contacto', href: '/contact' },
];
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
                            aria-label="Abrir menú de navegación"
                        >
                            <Menu class="h-5 w-5" />
                        </Button>
                    </SheetTrigger>
                    <SheetContent side="left" class="w-[280px] p-6 bg-white border-r border-slate-200">
                        <!-- Mobile logo -->
                        <Link href="/" class="flex items-center gap-2.5 mb-8">
                            <div class="w-8 h-8 rounded-lg bg-teal-500 flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <span class="font-bold text-slate-900 text-lg">MenuLinker</span>
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
                                        Iniciar sesión
                                    </Link>
                                    <Link
                                        href="/register"
                                        class="flex items-center justify-center px-4 py-2.5 rounded-full bg-teal-500 hover:bg-teal-600 text-white font-bold transition-colors text-sm"
                                    >
                                        Empezar gratis
                                    </Link>
                                </div>
                            </template>
                        </nav>
                    </SheetContent>
                </Sheet>
            </div>

            <!-- Logo -->
            <Link href="/" class="flex items-center gap-2.5 group">
                <div class="w-8 h-8 rounded-lg bg-teal-500 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <span class="font-bold text-slate-900 text-lg tracking-tight">MenuLinker</span>
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
                <template v-if="!auth?.user">
                    <Link
                        href="/login"
                        class="hidden lg:inline-flex items-center px-4 py-2 text-sm font-medium text-slate-600 hover:text-slate-900 transition-colors"
                    >
                        Iniciar sesión
                    </Link>
                    <Link
                        href="/register"
                        class="inline-flex items-center px-5 py-2 rounded-full bg-teal-500 hover:bg-teal-600 text-white text-sm font-bold transition-colors shadow-sm"
                    >
                        Empezar gratis
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
