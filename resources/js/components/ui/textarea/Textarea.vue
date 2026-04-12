<script setup lang="ts">
import { cn } from '@/lib/utils'

interface TextareaProps {
    class?: string
    modelValue?: string
    placeholder?: string
}

const props = defineProps<TextareaProps>()

const emit = defineEmits<{
    'update:modelValue': [value: string]
}>()

const handleInput = (event: Event) => {
    const target = event.target as HTMLTextAreaElement
    emit('update:modelValue', target.value)
}
</script>

<template>
  <textarea
      :value="modelValue"
      @input="handleInput"
      :placeholder="placeholder"
      :class="cn(
      'flex min-h-20 w-full rounded-lg border border-input bg-background px-3 py-2 text-base ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
      props.class
    )"
  />
</template>
