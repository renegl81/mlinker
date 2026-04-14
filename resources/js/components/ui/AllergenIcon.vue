<script setup lang="ts">
interface Props {
    code: string | null | undefined;
    size?: 'sm' | 'md' | 'lg';
}

const props = withDefaults(defineProps<Props>(), {
    size: 'md',
});

const allergenEmojis: Record<string, string> = {
    gluten: '🌾',
    crustaceans: '🦐',
    eggs: '🥚',
    fish: '🐟',
    peanuts: '🥜',
    soy: '🌱',
    dairy: '🥛',
    nuts: '🌰',
    celery: '🥬',
    mustard: '🌿',
    sesame: '🫘',
    sulfites: '⚠️',
    lupin: '🌸',
    molluscs: '🐚',
};

const emoji = props.code ? (allergenEmojis[props.code] ?? '⚠️') : '⚠️';

const sizeClasses: Record<string, string> = {
    sm: 'text-base',
    md: 'text-xl',
    lg: 'text-3xl',
};
</script>

<template>
    <span
        :class="sizeClasses[size]"
        role="img"
        :title="code ?? ''"
    >{{ emoji }}</span>
</template>
