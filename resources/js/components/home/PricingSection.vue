<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { CheckIcon } from '@heroicons/vue/24/solid';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

interface Plan {
    name: string;
    slug: string;
    price: string | null;
    period: string;
    description: string;
    max_locations: number;
    max_menus_per_location: number;
    max_products: number;
    has_analytics: boolean;
    has_custom_qr: boolean;
    has_multilang: boolean;
    has_catalog: boolean;
    has_team: boolean;
    has_api_access: boolean;
    has_custom_domain: boolean;
    show_branding: boolean;
    trial_days: number;
}

interface Props {
    plans: Plan[];
}

defineProps<Props>();

function features(plan: Plan): string[] {
    const f: string[] = [];

    if (plan.max_locations === 0) f.push(t('home.pricing.features.unlimited_locations'));
    else f.push(`${plan.max_locations} ${plan.max_locations === 1 ? t('home.pricing.features.location_singular') : t('home.pricing.features.location_plural')}`);

    if (plan.max_menus_per_location === 0) f.push(t('home.pricing.features.unlimited_menus'));
    else f.push(`${plan.max_menus_per_location} ${plan.max_menus_per_location === 1 ? t('home.pricing.features.menu_singular') : t('home.pricing.features.menu_plural')} ${t('home.pricing.features.per_location')}`);

    if (plan.max_products === 0 && plan.slug !== 'free') f.push(t('home.pricing.features.unlimited_products'));
    else if (plan.max_products > 0) f.push(`${plan.max_products} ${t('home.pricing.features.products')}`);

    if (plan.has_custom_qr) f.push(t('home.pricing.features.custom_qr'));
    else f.push(t('home.pricing.features.basic_qr'));

    if (plan.has_analytics) f.push(t('home.pricing.features.analytics'));
    if (plan.has_multilang) f.push(t('home.pricing.features.multilang'));
    if (plan.has_catalog) f.push(t('home.pricing.features.catalog'));
    if (plan.has_team) f.push(t('home.pricing.features.team'));
    if (plan.has_custom_domain) f.push(t('home.pricing.features.custom_domain'));
    if (plan.has_api_access) f.push(t('home.pricing.features.api_access'));
    if (plan.show_branding) f.push(t('home.pricing.features.branding_visible'));
    if (plan.trial_days > 0) f.push(`${plan.trial_days} ${t('home.pricing.features.trial_days')}`);

    return f;
}

function isHighlighted(plan: Plan): boolean {
    return plan.slug === 'pro';
}

function ctaText(plan: Plan): string {
    if (plan.slug === 'free') return t('home.pricing.cta.free');
    if (plan.slug === 'enterprise') return t('home.pricing.cta.enterprise');
    if (plan.trial_days > 0) return t('home.pricing.cta.trial');
    return t('home.pricing.cta.subscribe');
}

function ctaHref(plan: Plan): string {
    if (plan.slug === 'enterprise') return '/contact';
    if (plan.slug === 'free') return '/register';
    return `/register?plan=${plan.slug}`;
}
</script>

<template>
    <section id="pricing" class="py-24 bg-slate-50">
        <div class="container mx-auto px-4 max-w-7xl">
            <!-- Header -->
            <div class="text-center mb-14">
                <span class="inline-block px-3 py-1 rounded-full bg-teal-50 text-teal-600 text-xs font-bold uppercase tracking-wider mb-4">{{ t('home.pricing.badge') }}</span>
                <h2 class="text-3xl md:text-5xl font-bold text-slate-900 mb-4">{{ t('home.pricing.title') }}</h2>
                <p class="text-lg text-slate-500">{{ t('home.pricing.subtitle') }}</p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-5 max-w-7xl mx-auto items-start">
                <div
                    v-for="plan in plans"
                    :key="plan.slug"
                    :class="[
                        'relative rounded-2xl p-7 border transition-all duration-300 flex flex-col',
                        isHighlighted(plan)
                            ? 'bg-slate-900 border-teal-500 shadow-xl shadow-teal-500/10 lg:-translate-y-3 ring-1 ring-teal-500/30'
                            : 'bg-white border-slate-200 hover:border-slate-300 hover:shadow-md',
                    ]"
                >
                    <!-- Popular badge -->
                    <div
                        v-if="isHighlighted(plan)"
                        class="absolute -top-3.5 left-1/2 -translate-x-1/2 bg-teal-500 text-white text-[11px] font-bold px-4 py-1 rounded-full whitespace-nowrap uppercase tracking-wide"
                    >
                        {{ t('home.pricing.popular') }}
                    </div>

                    <!-- Plan name + desc -->
                    <div class="mb-6">
                        <h3 :class="['text-lg font-bold mb-1.5', isHighlighted(plan) ? 'text-white' : 'text-slate-900']">
                            {{ plan.name }}
                        </h3>
                        <p :class="['text-sm leading-relaxed', isHighlighted(plan) ? 'text-slate-400' : 'text-slate-500']">
                            {{ plan.description }}
                        </p>
                    </div>

                    <!-- Price -->
                    <div class="mb-7 flex items-end gap-1">
                        <template v-if="plan.price !== null && Number(plan.price) > 0">
                            <span :class="['text-4xl font-bold leading-none', isHighlighted(plan) ? 'text-white' : 'text-slate-900']">
                                €{{ plan.price }}
                            </span>
                            <span :class="['text-sm mb-1', isHighlighted(plan) ? 'text-slate-400' : 'text-slate-400']">
                                /{{ plan.period }}
                            </span>
                        </template>
                        <template v-else-if="plan.slug === 'enterprise'">
                            <span :class="['text-2xl font-bold', isHighlighted(plan) ? 'text-white' : 'text-slate-900']">
                                {{ t('home.pricing.price_custom') }}
                            </span>
                        </template>
                        <template v-else>
                            <span :class="['text-4xl font-bold leading-none', isHighlighted(plan) ? 'text-white' : 'text-slate-900']">
                                {{ t('home.pricing.price_free') }}
                            </span>
                        </template>
                    </div>

                    <!-- Features list -->
                    <ul class="space-y-3 mb-8 flex-1">
                        <li v-for="feature in features(plan)" :key="feature" class="flex items-start gap-2.5">
                            <CheckIcon
                                :class="['w-4 h-4 mt-0.5 flex-shrink-0', isHighlighted(plan) ? 'text-teal-400' : 'text-teal-500']"
                            />
                            <span :class="['text-sm', isHighlighted(plan) ? 'text-slate-300' : 'text-slate-600']">
                                {{ feature }}
                            </span>
                        </li>
                    </ul>

                    <!-- CTA -->
                    <Link
                        :href="ctaHref(plan)"
                        :class="[
                            'block w-full text-center py-3 px-6 rounded-xl font-bold text-sm transition-all',
                            isHighlighted(plan)
                                ? 'bg-teal-500 text-white hover:bg-teal-400'
                                : plan.slug === 'enterprise'
                                    ? 'border border-slate-200 text-slate-700 hover:border-teal-300 hover:text-teal-600'
                                    : 'bg-slate-900 text-white hover:bg-slate-800',
                        ]"
                    >
                        {{ ctaText(plan) }}
                    </Link>
                </div>
            </div>

            <!-- Bottom note -->
            <p class="text-center text-slate-400 text-sm mt-10">
                {{ t('home.pricing.bottom_note') }}
                <Link href="/faq" class="text-teal-600 hover:text-teal-700 underline underline-offset-2 ml-1">
                    {{ t('home.pricing.faq_link') }}
                </Link>
            </p>
        </div>
    </section>
</template>
