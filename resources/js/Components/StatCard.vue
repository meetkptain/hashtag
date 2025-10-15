<template>
  <div class="card">
    <div class="flex items-center justify-between">
      <div>
        <p class="text-sm font-medium text-gray-600">{{ title }}</p>
        <p class="text-3xl font-bold text-gray-900 mt-2">{{ formattedValue }}</p>
      </div>
      <div :class="`w-12 h-12 rounded-lg bg-${color}-100 flex items-center justify-center`">
        <span class="text-2xl">{{ icon }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  title: String,
  value: [Number, String],
  icon: String,
  color: {
    type: String,
    default: 'blue'
  }
});

const formattedValue = computed(() => {
  if (typeof props.value === 'number') {
    if (props.value >= 1000000) return (props.value / 1000000).toFixed(1) + 'M';
    if (props.value >= 1000) return (props.value / 1000).toFixed(1) + 'K';
    return props.value.toString();
  }
  return props.value;
});
</script>

