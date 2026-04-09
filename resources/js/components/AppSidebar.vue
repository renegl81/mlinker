<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard, documentation } from '@/routes';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import {
    BookOpen,
    Building2Icon,
    Folder,
    LayoutGrid,
} from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import LanguageSelector from '@/components/ui/LanguageSelector.vue';
import { index as users } from '@/routes/users';
import { index as tenantUsers } from '@/routes/tenant/users';
import { dashboard as panel } from '@/routes/tenant/index';
import { UserGroupIcon } from '@heroicons/vue/16/solid';
import { index as locations } from '@/routes/tenant/locations';

const page = usePage();
const messages = page.props.messages as any;
const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: page.props.auth?.user?.is_admin ? dashboard() : panel(),
        icon: LayoutGrid,
        role: 'owner, admin',
    },
    {
        title: messages.locations.plural,
        href: locations(),
        icon: Building2Icon,
        role: 'owner',
    },
    {
        title: messages.users.plural,
        href: page.props.auth?.user?.is_admin ? users() : tenantUsers(),
        icon: UserGroupIcon,
        role: 'owner, admin',
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentación',
        href: documentation(),
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <div class="px-2 py-1">
                <LanguageSelector />
            </div>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
