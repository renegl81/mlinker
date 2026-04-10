<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

interface MetaProps {
    title: string;
    description: string;
    image: string | null;
    url: string;
}

interface JsonLd {
    '@context': string;
    '@type': string;
    [key: string]: unknown;
}

interface Props {
    meta: MetaProps;
    jsonLd: JsonLd;
}

const props = defineProps<Props>();

const jsonLdString = computed(() => JSON.stringify(props.jsonLd));
</script>

<template>
    <Head>
        <title>{{ meta.title }}</title>
        <meta name="description" :content="meta.description" />
        <meta property="og:title" :content="meta.title" />
        <meta property="og:description" :content="meta.description" />
        <meta v-if="meta.image" property="og:image" :content="meta.image" />
        <meta property="og:url" :content="meta.url" />
        <meta property="og:type" content="restaurant.menu" />
        <!-- eslint-disable-next-line vue/no-v-text-v-html-on-component -->
        <component :is="'script'" type="application/ld+json" v-html="jsonLdString" />
    </Head>
</template>
