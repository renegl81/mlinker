<script setup lang="ts">
import { computed } from 'vue';
import { formatOpeningHours, type OpeningHour } from '@/composables/useTenantHome';

const props = defineProps<{
    hours: OpeningHour[];
    compact?: boolean;
}>();

const formatted = computed(() => formatOpeningHours(props.hours));
const today = new Date().getDay(); // 0=Sun … 6=Sat
</script>

<template>
    <ul class="oh-list" :class="{ 'oh-compact': compact }">
        <li
            v-for="(day, i) in formatted"
            :key="i"
            class="oh-row"
            :class="{
                'oh-today': [1, 2, 3, 4, 5, 6, 0][i] === today,
                'oh-closed': day.isClosed,
            }"
        >
            <span class="oh-day">{{ day.label }}</span>
            <span class="oh-dots" aria-hidden="true" />
            <span class="oh-hours">{{ day.hours }}</span>
        </li>
    </ul>
</template>

<style scoped>
.oh-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 0.45rem;
}

.oh-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.88rem;
    line-height: 1.4;
    opacity: 0.85;
}

.oh-today {
    opacity: 1;
    font-weight: 600;
}

.oh-closed .oh-hours {
    opacity: 0.5;
    font-style: italic;
}

.oh-day {
    min-width: 7.5rem;
    flex-shrink: 0;
}

.oh-compact .oh-day {
    min-width: 3.5rem;
}

.oh-dots {
    flex: 1;
    height: 0;
    border-bottom: 1px dotted currentColor;
    opacity: 0.25;
    margin-bottom: 0.1em;
}

.oh-hours {
    flex-shrink: 0;
    text-align: right;
    font-variant-numeric: tabular-nums;
}
</style>
