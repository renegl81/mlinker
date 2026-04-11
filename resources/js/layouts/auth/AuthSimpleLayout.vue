<script setup lang="ts">
import { tenant_home as home } from '@/routes/tenant_public/index';
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="grid min-h-svh bg-white lg:grid-cols-2">
        <!-- Left brand panel (desktop only) -->
        <aside
            class="relative hidden flex-col justify-between overflow-hidden bg-slate-900 p-12 text-white lg:flex"
        >
            <!-- decorative blobs -->
            <div
                class="pointer-events-none absolute -top-32 -left-32 h-[28rem] w-[28rem] rounded-full bg-teal-500/20 blur-[140px]"
            />
            <div
                class="pointer-events-none absolute -right-24 bottom-0 h-[28rem] w-[28rem] rounded-full bg-cyan-500/15 blur-[140px]"
            />
            <!-- subtle grid -->
            <div
                class="pointer-events-none absolute inset-0 opacity-[0.04]"
                style="
                    background-image: linear-gradient(
                            white 1px,
                            transparent 1px
                        ),
                        linear-gradient(90deg, white 1px, transparent 1px);
                    background-size: 44px 44px;
                "
            />

            <!-- brand -->
            <Link
                :href="home()"
                class="relative z-10 flex items-center gap-3 group"
            >
                <img
                    src="/images/logo.png"
                    alt="MenuLinker"
                    class="h-10 w-10 object-contain transition-transform group-hover:scale-105"
                />
                <span class="text-xl font-bold tracking-tight">MenuLinker</span>
            </Link>

            <!-- quote/tagline -->
            <div class="relative z-10 max-w-md space-y-5">
                <div
                    class="inline-flex items-center rounded-full border border-teal-500/25 bg-teal-500/10 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-teal-300"
                >
                    {{ t('auth.brand.badge') }}
                </div>
                <blockquote
                    class="text-3xl font-bold leading-[1.15] text-white xl:text-4xl"
                >
                    {{ t('auth.brand.headline') }}
                </blockquote>
                <p class="text-sm leading-relaxed text-slate-400">
                    {{ t('auth.brand.subline') }}
                </p>
            </div>

            <!-- footer -->
            <p class="relative z-10 text-xs text-slate-500">
                © {{ new Date().getFullYear() }} MenuLinker
            </p>
        </aside>

        <!-- Right form area -->
        <main
            class="flex flex-col items-center justify-center px-6 py-10 md:px-12"
        >
            <!-- mobile brand -->
            <Link
                :href="home()"
                class="mb-10 flex items-center gap-2.5 lg:hidden"
            >
                <img
                    src="/images/logo.png"
                    alt="MenuLinker"
                    class="h-9 w-9 object-contain"
                />
                <span class="text-lg font-bold tracking-tight text-slate-900"
                    >MenuLinker</span
                >
            </Link>

            <div class="w-full max-w-sm">
                <div class="mb-8 space-y-2">
                    <h1
                        class="text-3xl font-bold leading-tight tracking-tight text-slate-900"
                    >
                        {{ title }}
                    </h1>
                    <p v-if="description" class="text-sm text-slate-500">
                        {{ description }}
                    </p>
                </div>
                <slot />
            </div>
        </main>
    </div>
</template>
