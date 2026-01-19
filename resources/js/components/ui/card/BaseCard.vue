<template>
  <div
    class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-6"
    :class="customClass"
  >
    <slot />
  </div>
</template>

<script setup>
const props = defineProps({
  customClass: {
    type: String,
    default: '',
    
  },
});
</script>
