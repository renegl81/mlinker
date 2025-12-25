<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'

const props = defineProps<{
  class?: HTMLAttributes['class']
}>()
</script>

<template>
  <div
    data-slot="sidebar-menu-badge"
    data-sidebar="menu-badge"
    :class="cn(
      'text-sidebar-foreground pointer-events-none absolute right-1 flex h-5 min-w-5 items-center justify-center rounded-md px-1 text-xs font-medium tabular-nums select-none',
      'peer-hover/Menu-button:text-sidebar-accent-foreground peer-data-[active=true]/Menu-button:text-sidebar-accent-foreground',
      'peer-data-[size=sm]/Menu-button:top-1',
      'peer-data-[size=default]/Menu-button:top-1.5',
      'peer-data-[size=lg]/Menu-button:top-2.5',
      'group-data-[collapsible=icon]:hidden',
      props.class,
    )"
  >
    <slot />
  </div>
</template>
