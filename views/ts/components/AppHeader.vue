<script setup lang="ts">
defineProps<{
  adminPanelPath: string
}>()
</script>

<template>
  <header class="fixed left-1/2 top-3 z-[120] grid h-12 w-[calc(100%-1.5rem)] max-w-[1460px] -translate-x-1/2 grid-cols-[1fr_auto] items-center border-b border-[#dddddf] bg-[#f7f7f8] px-3 sm:top-6 sm:h-[52px] sm:w-[calc(100%-3rem)] sm:grid-cols-[1fr_auto_1fr] sm:px-4">
    <div class="inline-flex items-center gap-2 text-[13px] text-[#6e7480]">
      <span class="inline-flex h-[18px] w-[18px] items-center justify-center rounded-md border border-[#b6bac4] text-[10px]">[]</span>
      <span>AI</span>
    </div>

    <div class="col-span-2 row-start-2 mt-[-2px] justify-self-start text-base font-semibold text-[#1a1b1f] sm:col-span-1 sm:row-start-1 sm:mt-0 sm:justify-self-center sm:text-lg">
      <span>User Activity Dashboard</span>
    </div>

    <div class="justify-self-end inline-flex items-center gap-2">
      <a class="inline-flex items-center justify-center rounded-lg border border-[#d0d5dd] bg-[#fcfcfd] px-3 py-[7px] text-[13px] font-semibold text-[#495268] no-underline" :href="adminPanelPath">Admin</a>
      <a class="inline-flex items-center justify-center rounded-lg bg-[#5b52f1] px-3 py-[7px] text-[13px] font-semibold text-white no-underline" href="https://laravelarticle.com/laravel-user-activity" title="Laravel User Activity">Share</a>
    </div>
  </header>
</template>
