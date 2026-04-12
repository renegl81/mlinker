<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';

const appName = usePage().props.name as string;
</script>

<template>
    <img class="logo" src="/images/logo.png" :alt="appName" />
</template>
