<script setup lang="ts">
import { dashboard, documentation } from '@/routes';
import { dashboard as panel } from '@/routes/tenant/index';
import { index as locations } from '@/routes/tenant/locations';
import { index as tenantUsers } from '@/routes/tenant/users';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import LanguageSelector from '@/components/ui/LanguageSelector.vue';
import { toUrl } from '@/lib/utils';
import { Link, usePage } from '@inertiajs/vue3';
import {
    BookOpen,
    Building2,
    LayoutGrid,
    Leaf,
    MoreHorizontal,
    Users,
    Utensils,
} from 'lucide-vue-next';
import type { Component } from 'vue';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const page = usePage();
const sheetOpen = ref(false);

const user = computed(() => page.props.auth?.user);
const tenantProps = computed(
    () =>
        page.props.tenant as
            | { plan_features?: { catalog?: boolean }; user_role?: string | null }
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
    icon: Component;
}

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

interface SecondaryNavItem {
    title: string;
    href: string;
    icon: Component;
    external?: boolean;
}

const secondaryItems = computed((): SecondaryNavItem[] => [
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
    {
        title: t('nav.documentation'),
        href: toUrl(documentation()),
        icon: BookOpen,
        external: true,
    },
]);

function isActive(href: string): boolean {
    return href === page.url;
}
</script>

<template>
    <!-- Visible only on mobile (<768px) — hidden on md+ via parent layout -->
    <nav
        class="fixed inset-x-0 bottom-0 z-40 flex h-[60px] items-center justify-around border-t border-border bg-card/90 backdrop-blur-md"
        style="padding-bottom: env(safe-area-inset-bottom, 0)"
    >
        <Link
            v-for="item in primaryItems"
            :key="item.href"
            :href="item.href"
            class="flex min-w-0 flex-1 flex-col items-center justify-center gap-0.5 py-1 text-[10px] font-medium leading-tight transition-colors"
            :class="isActive(item.href) ? 'text-primary' : 'text-muted-foreground'"
        >
            <component
                :is="item.icon"
                class="size-5 transition-transform"
                :class="isActive(item.href) ? 'scale-110' : ''"
            />
            <span class="max-w-[56px] truncate text-center">{{ item.title }}</span>
        </Link>

        <!-- "Más" button — opens bottom sheet -->
        <Sheet v-if="secondaryItems.length > 0" v-model:open="sheetOpen">
            <SheetTrigger
                class="flex min-w-0 flex-1 flex-col items-center justify-center gap-0.5 py-1 text-[10px] font-medium leading-tight text-muted-foreground transition-colors hover:text-foreground"
            >
                <MoreHorizontal class="size-5" />
                <span>{{ t('nav.more') }}</span>
            </SheetTrigger>
            <SheetContent
                side="bottom"
                class="rounded-t-2xl"
                style="padding-bottom: env(safe-area-inset-bottom, 1rem)"
            >
                <SheetHeader class="mb-4">
                    <SheetTitle class="text-left">{{ t('nav.more') }}</SheetTitle>
                </SheetHeader>

                <nav class="flex flex-col gap-1">
                    <template v-for="item in secondaryItems" :key="item.href">
                        <a
                            v-if="item.external"
                            :href="item.href"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="flex min-h-[44px] items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-foreground hover:bg-accent"
                            @click="sheetOpen = false"
                        >
                            <component :is="item.icon" class="size-5 text-muted-foreground" />
                            {{ item.title }}
                        </a>
                        <Link
                            v-else
                            :href="item.href"
                            class="flex min-h-[44px] items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-foreground hover:bg-accent"
                            @click="sheetOpen = false"
                        >
                            <component :is="item.icon" class="size-5 text-muted-foreground" />
                            {{ item.title }}
                        </Link>
                    </template>
                </nav>

                <div class="mt-4 border-t border-border pt-4">
                    <LanguageSelector />
                </div>
            </SheetContent>
        </Sheet>
    </nav>
</template>
