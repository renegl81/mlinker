<script setup lang="ts">
import { getLocaleFlag } from '@/composables/useLocaleFlags';
import { computed } from 'vue';

const props = defineProps<{ code: string; class?: string }>();

const flag = computed(() => getLocaleFlag(props.code));
</script>

<template>
    <img
        v-if="flag.type === 'svg'"
        :src="flag.value"
        :alt="code"
        :class="[$props.class ?? 'inline-block h-3.5 w-5 rounded-sm object-cover']"
    />
    <span v-else :class="$props.class">{{ flag.value }}</span>
</template>
