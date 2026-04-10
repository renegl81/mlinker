<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
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
import { computed } from 'vue';

const page = usePage();
const auth = computed(() => page.props.auth);

interface NavLink {
    label: string;
    href: string;
}

const links: NavLink[] = [
    { label: 'Funcionalidades', href: '#features' },
    { label: 'Precios', href: '#pricing' },
];
</script>

<template>
    <div class="sticky top-0 z-50">
        <div class="border-b border-white/5 bg-slate-950/80 backdrop-blur-md supports-[backdrop-filter]:bg-slate-950/50">
            <div class="mx-auto flex h-16 items-center px-4 md:max-w-7xl">
                <!-- Mobile hamburger -->
                <div class="lg:hidden">
                    <Sheet>
                        <SheetTrigger :as-child="true">
                            <Button variant="ghost" size="icon" class="mr-2 h-9 w-9 text-slate-300 hover:text-white hover:bg-slate-800">
                                <Menu class="h-5 w-5" />
                            </Button>
                        </SheetTrigger>
                        <SheetContent side="left" class="w-[280px] p-6 bg-slate-950 border-r border-slate-800 text-white">
                            <nav class="mt-8 space-y-4">
                                <a
                                    v-for="link in links"
                                    :key="link.href"
                                    :href="link.href"
                                    class="block text-lg font-medium text-slate-300 hover:text-white transition"
                                >
                                    {{ link.label }}
                                </a>
                                <template v-if="!auth?.user">
                                    <div class="pt-6 border-t border-slate-800 space-y-3">
                                        <Link href="/login" class="block text-slate-300 hover:text-white transition">
                                            Iniciar sesión
                                        </Link>
                                        <Link href="/register" class="block bg-white text-slate-950 font-bold text-center py-3 px-6 rounded-full">
                                            Crear cuenta gratis
                                        </Link>
                                    </div>
                                </template>
                            </nav>
                        </SheetContent>
                    </Sheet>
                </div>

                <!-- Logo -->
                <Link href="/" class="flex items-center gap-x-2">
                    <AppLogo class="text-white fill-white" />
                </Link>

                <!-- Desktop nav -->
                <nav class="hidden lg:flex lg:flex-1 ml-10 items-center gap-6">
                    <a
                        v-for="link in links"
                        :key="link.href"
                        :href="link.href"
                        class="text-sm font-medium text-slate-400 hover:text-white transition"
                    >
                        {{ link.label }}
                    </a>
                </nav>

                <!-- Right side -->
                <div class="ml-auto flex items-center space-x-3">
                    <template v-if="!auth?.user">
                        <Link href="/login" class="hidden lg:inline text-sm font-medium text-slate-300 hover:text-white transition">
                            Iniciar sesión
                        </Link>
                        <Link href="/register" class="text-sm font-bold bg-white text-slate-950 px-5 py-2 rounded-full hover:bg-slate-200 transition">
                            Empezar gratis
                        </Link>
                    </template>

                    <DropdownMenu v-if="auth?.user">
                        <DropdownMenuTrigger :as-child="true">
                            <Button variant="ghost" class="relative size-10 w-auto rounded-full p-1 border border-slate-700">
                                <Avatar class="h-8 w-8 overflow-hidden rounded-lg">
                                    <AvatarFallback class="rounded-lg text-white">
                                        {{ getInitials(auth?.user?.name) }}
                                    </AvatarFallback>
                                </Avatar>
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-56 bg-slate-900 border-slate-800 text-slate-200">
                            <UserMenuContent :user="auth.user" />
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </div>
        </div>
    </div>
</template>
