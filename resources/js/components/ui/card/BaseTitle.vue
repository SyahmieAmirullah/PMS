<template>
  <h2 :class="['font-semibold my-5 ml-3', sizeClass]">
    <slot />
  </h2>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  size: {
    type: String,
    default: 'xl', 
  },
})

const sizeClass = computed(() => {
  switch (props.size) {
    case 'lg':
      return 'text-2xl'
    case '2xl':
      return 'text-3xl'
    case '3xl':
      return 'text-4xl'
    default:
      return 'text-xl'
  }
})
</script>
