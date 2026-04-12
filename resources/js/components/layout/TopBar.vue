<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import LanguageSelector from '@/components/ui/LanguageSelector.vue';
import UserMenuContent from '@/components/UserMenuContent.vue';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { dashboard } from '@/routes';
import { dashboard as panel } from '@/routes/tenant/index';
import { index as locations } from '@/routes/tenant/locations';
import { index as tenantUsers } from '@/routes/tenant/users';
import { toUrl } from '@/lib/utils';
import { Link, usePage } from '@inertiajs/vue3';
import {
    BookOpen,
    Building2,
    CreditCard,
    LayoutGrid,
    Leaf,
    MoreHorizontal,
    Utensils,
    Users,
} from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import MoreSheet from '@/components/layout/MoreSheet.vue';

const { t } = useI18n();
const appName = usePage().props.name as string;
const page = usePage();

const user = computed(() => page.props.auth?.user);
const tenantProps = computed(
    () =>
        page.props.tenant as
            | {
                  name?: string;
                  plan_features?: { catalog?: boolean; team?: boolean };
                  user_role?: string | null;
              }
            | null,
);
const hasCatalog = computed(() => !!tenantProps.value?.plan_features?.catalog);
const isOwner = computed(
    () => tenantProps.value?.user_role === 'owner' || !!user.value?.is_admin,
);
const homeHref = computed((): string =>
    toUrl(user.value?.is_admin ? dashboard() : panel()),
);

interface NavItem {
    title: string;
    href: string;
    icon: typeof LayoutGrid;
    external?: boolean;
}

// Primary nav items — shown inline on desktop, as bottom tabs on mobile
const primaryItems = computed((): NavItem[] => [
    { title: t('nav.dashboard'), href: homeHref.value, icon: LayoutGrid },
    { title: t('nav.locations'), href: toUrl(locations()), icon: Building2 },
    ...(hasCatalog.value && isOwner.value
        ? [
              {
                  title: t('catalog.products.title'),
                  href: '/panel/catalog/products',
                  icon: Utensils,
              },
          ]
        : []),
]);

// Secondary nav items — desktop dropdown / mobile MoreSheet
const secondaryItems = computed((): NavItem[] => [
    ...(hasCatalog.value && isOwner.value
        ? [
              {
                  title: t('catalog.ingredients.title'),
                  href: '/panel/catalog/ingredients',
                  icon: Leaf,
              },
          ]
        : []),
    ...(isOwner.value
        ? [
              {
                  title: t('nav.users'),
                  href: toUrl(tenantUsers()),
                  icon: Users,
              },
          ]
        : []),
    ...(isOwner.value
        ? [
              {
                  title: t('nav.billing'),
                  href: '/panel/billing/plans',
                  icon: CreditCard,
              },
          ]
        : []),
    {
        title: t('nav.documentation'),
        href: '/panel/docs',
        icon: BookOpen,
    },
]);

function isActive(href: string): boolean {
    return href === page.url;
}
</script>

<template>
    <header
        class="fixed inset-x-0 top-0 z-40 h-[52px] border-b border-border bg-card/80 backdrop-blur-md md:h-14"
    >
        <div class="mx-auto flex h-full max-w-7xl items-center gap-4 px-4">
            <!-- Logo -->
            <Link :href="homeHref" class="flex shrink-0 items-center">
                <img src="/images/logo-name.png" :alt="appName" class="hidden sm:block h-8 object-contain" />
                <img src="/images/logo.png" :alt="appName" class="block sm:hidden h-8 w-8 object-contain" />
            </Link>

            <!-- Desktop: primary nav inline -->
            <nav class="hidden flex-1 items-center gap-0.5 md:flex">
                <Link
                    v-for="item in primaryItems"
                    :key="item.href"
                    :href="item.href"
                    class="relative flex items-center gap-1.5 rounded-md px-3 py-1.5 text-sm font-medium transition-colors hover:bg-accent hover:text-accent-foreground"
                    :class="isActive(item.href) ? 'text-primary' : 'text-muted-foreground'"
                >
                    <component :is="item.icon" class="size-4" />
                    {{ item.title }}
                    <span
                        v-if="isActive(item.href)"
                        class="absolute inset-x-2 -bottom-px h-0.5 rounded-full bg-primary"
                    />
                </Link>

                <!-- Desktop: "More" dropdown -->
                <DropdownMenu v-if="secondaryItems.length > 0">
                    <DropdownMenuTrigger
                        class="flex items-center gap-1.5 rounded-md px-3 py-1.5 text-sm font-medium text-muted-foreground transition-colors hover:bg-accent hover:text-accent-foreground"
                    >
                        <MoreHorizontal class="size-4" />
                        {{ t('nav.more') }}
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="start" class="w-48">
                        <template v-for="item in secondaryItems" :key="item.href">
                            <a
                                v-if="item.external"
                                :href="item.href"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="flex w-full items-center gap-2 rounded-sm px-2 py-1.5 text-sm text-foreground hover:bg-accent"
                            >
                                <component
                                    :is="item.icon"
                                    class="size-4 text-muted-foreground"
                                />
                                {{ item.title }}
                            </a>
                            <Link
                                v-else
                                :href="item.href"
                                class="flex w-full items-center gap-2 rounded-sm px-2 py-1.5 text-sm text-foreground hover:bg-accent"
                            >
                                <component
                                    :is="item.icon"
                                    class="size-4 text-muted-foreground"
                                />
                                {{ item.title }}
                            </Link>
                        </template>
                    </DropdownMenuContent>
                </DropdownMenu>
            </nav>

            <!-- Right side -->
            <div class="ml-auto flex items-center gap-2">
                <!-- Language selector (desktop only) -->
                <div class="hidden md:block">
                    <LanguageSelector />
                </div>

                <!-- User avatar dropdown -->
                <DropdownMenu v-if="user">
                    <DropdownMenuTrigger
                        class="flex h-9 w-9 cursor-pointer items-center justify-center rounded-full bg-primary/10 text-sm font-semibold text-primary outline-none ring-ring transition-colors hover:bg-primary/20 focus-visible:ring-2"
                        :title="user.name"
                    >
                        {{ user.name?.charAt(0)?.toUpperCase() }}
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" class="w-56" :side-offset="8">
                        <UserMenuContent :user="user" />
                    </DropdownMenuContent>
                </DropdownMenu>

                <!-- Mobile: More sheet trigger -->
                <MoreSheet
                    v-if="secondaryItems.length > 0"
                    :items="secondaryItems"
                    class="md:hidden"
                />
            </div>
        </div>
    </header>
</template>
