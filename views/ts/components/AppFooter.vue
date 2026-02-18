<script setup lang="ts">
defineProps<{
  deleteLimit: number
}>()

const emit = defineEmits<{
  (event: 'delete'): void
}>()
</script>

<template>
  <footer>
    <div></div>
    <div class="footer_right">
      <span class="text_light">Delete data older than {{ deleteLimit }} days</span>
      <button class="btn" @click="emit('delete')">DELETE</button>
    </div>
  </footer>
</template>
