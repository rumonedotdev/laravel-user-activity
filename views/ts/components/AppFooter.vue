<script setup lang="ts">
defineProps<{
  deleteLimit: number
}>()

const emit = defineEmits<{
  (event: 'delete'): void
}>()
</script>

<template>
  <footer class="mt-3 flex flex-col items-start justify-between gap-3 border-t border-[#e2e5eb] pt-3 sm:flex-row sm:items-center">
    <div class="text-sm text-[#6b7383]">Delete data older than {{ deleteLimit }} days</div>
    <div>
      <button
        class="inline-flex h-9 items-center justify-center rounded-xl border border-[#131822] bg-[#131822] px-3.5 text-sm font-semibold text-white"
        @click="emit('delete')"
      >
        Delete Old Logs
      </button>
    </div>
  </footer>
</template>
