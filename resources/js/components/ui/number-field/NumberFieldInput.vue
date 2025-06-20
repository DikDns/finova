<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { NumberFieldInput } from 'reka-ui'
import { cn } from '@/lib/utils'

const props = defineProps<{
  class?: HTMLAttributes['class']
}>()
</script>

<template>
  <NumberFieldInput
    data-slot="input"
    :class="cn('flex border-input placeholder:text-muted-foreground selection:bg-primary selection:text-primary-foreground dark:bg-input/30 text-center focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none bg-transparent disabled:cursor-not-allowed disabled:opacity-50 md:text-sm outline-none h-9 w-full min-w-0 rounded-md border px-3 py-1 text-base shadow-xs transition-[color,box-shadow] ',
      'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
      'aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive', props.class)"
  />
</template>
