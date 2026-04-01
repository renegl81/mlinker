<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { route } from 'ziggy-js';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger
} from '@/components/ui/dropdown-menu';
import {
    NavigationMenu,
    NavigationMenuItem,
    NavigationMenuList,
    navigationMenuTriggerStyle,
} from '@/components/ui/navigation-menu';
import {
    Sheet,
    SheetContent,
    SheetTrigger,
} from '@/components/ui/sheet';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { getInitials } from '@/composables/useInitials';
import { urlIsActive } from '@/lib/utils';
import type { NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { Menu } from 'lucide-vue-next';
import { computed } from 'vue';

const page = usePage();
const auth = computed(() => page.props.auth);

const mainNavItems: NavItem[] = [];

</script>

<template>
    <div class="sticky top-0 z-50">
        <div class="border-b border-white/5 bg-slate-950/80 backdrop-blur-md supports-[backdrop-filter]:bg-slate-950/50">
            <div class="mx-auto flex h-16 items-center px-4 md:max-w-7xl">
                <div class="lg:hidden">
                    <Sheet>
                        <SheetTrigger :as-child="true">
                            <Button variant="ghost" size="icon" class="mr-2 h-9 w-9 text-slate-300 hover:text-white hover:bg-slate-800">
                                <Menu class="h-5 w-5" />
                            </Button>
                        </SheetTrigger>
                        <SheetContent side="left" class="w-[300px] p-6 bg-slate-950 border-r border-slate-800 text-white">
                        </SheetContent>
                    </Sheet>
                </div>

                <Link :href="route('tenant_public.tenant_home')" class="flex items-center gap-x-2">
                    <AppLogo class="text-white fill-white" /> </Link>

                <div class="hidden h-full lg:flex lg:flex-1">
                    <NavigationMenu class="ml-10 flex h-full items-stretch">
                        <NavigationMenuList class="flex h-full items-stretch space-x-2">
                            <NavigationMenuItem v-for="(item, index) in mainNavItems" :key="index" class="relative flex h-full items-center">
                                <Link
                                    :class="[
                                        navigationMenuTriggerStyle(),
                                        // Estilos para links activos y normales en modo oscuro
                                        urlIsActive(item.href, page.url) ? 'text-white bg-slate-800' : 'text-slate-400 hover:text-white hover:bg-slate-800/50',
                                        'h-9 cursor-pointer px-3 bg-transparent'
                                    ]"
                                    :href="item.href"
                                >
                                    <component v-if="item.icon" :is="item.icon" class="mr-2 h-4 w-4" />
                                    {{ item.title }}
                                </Link>
                            </NavigationMenuItem>
                        </NavigationMenuList>
                    </NavigationMenu>
                </div>

                <div class="ml-auto flex items-center space-x-2">
                    <div class="hidden space-x-4 lg:flex items-center">
                        <template v-if="!auth?.user">
                            <Link :href="route('login')" class="text-sm font-medium text-slate-300 hover:text-white transition">
                                Log in
                            </Link>
                            <Link :href="route('register')" class="text-sm font-bold bg-white text-slate-950 px-4 py-2 rounded-full hover:bg-slate-200 transition">
                                Sign up
                            </Link>
                        </template>
                    </div>

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
