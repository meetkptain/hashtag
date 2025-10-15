<template>
  <Link 
    :href="href" 
    :class="[
      'flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg transition-colors',
      active 
        ? 'bg-primary-50 text-primary-600' 
        : 'text-gray-700 hover:bg-gray-100'
    ]"
  >
    <slot></slot>
  </Link>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
  href: String,
  active: Boolean,
});
</script>

