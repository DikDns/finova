<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'
import { Primitive, type PrimitiveProps } from 'reka-ui'

interface Props extends PrimitiveProps {
  size?: 'sm' | 'md' | 'lg'
  class?: HTMLAttributes['class']
}

const props = withDefaults(defineProps<Props>(), {
  as: 'button',
  size: 'md'
})

// Size variants untuk AI button
const aiButtonSizes = {
  sm: 'h-8 px-3 text-sm',
  md: 'h-9 px-4 text-sm',
  lg: 'h-11 px-6 text-base'
}

// Base classes untuk AI button dengan gradient
const aiButtonClasses = [
  // Layout & spacing
  'inline-flex items-center justify-center',
  'rounded-full font-medium',
  'transition-all duration-300 ease-in-out',
  
  // Gradient background
  'bg-gradient-to-r from-pink-500 via-purple-600 to-blue-400',
  'hover:from-pink-600 hover:via-purple-700 hover:to-blue-500',
  
  // Text & border
  'text-white border-0',
  
  // Effects
  'hover:shadow-lg hover:shadow-purple-500/25',
  'hover:-translate-y-0.5',
  'active:translate-y-0 active:shadow-md',
  
  // Focus states
  'focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2',
  
  // Disabled state
  'disabled:opacity-50 disabled:cursor-not-allowed',
  'disabled:hover:translate-y-0 disabled:hover:shadow-none'
].join(' ')
</script>

<template>
  <Primitive
    data-slot="ai-button"
    :as="as"
    :as-child="asChild"
    :class="cn(aiButtonClasses, aiButtonSizes[size], props.class)"
  >
    <slot />
  </Primitive>
</template>

<style scoped>
/* Additional gradient styles */
[data-slot="ai-button"] {
  background: linear-gradient(
    227deg,
    #f72585 0%,
    rgba(114, 9, 183, 0.85) 25%,
    rgba(58, 12, 163, 0.85) 50%,
    rgba(67, 97, 238, 0.85) 75%,
    #4cc9f0 100%
  );
}

[data-slot="ai-button"]:hover {
  background: linear-gradient(
    227deg,
    #e91e63 0%,
    rgba(124, 19, 193, 0.9) 25%,
    rgba(68, 22, 173, 0.9) 50%,
    rgba(77, 107, 248, 0.9) 75%,
    #3bb9f0 100%
  );
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(156, 39, 176, 0.3);
}

[data-slot="ai-button"]:active {
  transform: translateY(0);
  box-shadow: 0 4px 12px rgba(156, 39, 176, 0.2);
}

/* Tambahan untuk efek glow */
[data-slot="ai-button"]::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  border-radius: inherit;
  padding: 1px;
  background: linear-gradient(
    227deg,
    #f72585,
    rgba(114, 9, 183, 0.85),
    rgba(58, 12, 163, 0.85),
    rgba(67, 97, 238, 0.85),
    #4cc9f0
  );
  mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
  mask-composite: subtract;
  pointer-events: none;
  opacity: 0;
  transition: opacity 0.3s ease;
}

[data-slot="ai-button"]:hover::before {
  opacity: 1;
}
</style>