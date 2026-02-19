<script setup lang="ts">
defineProps<{
  adminPanelPath: string
}>()
</script>

<template>
  <header class="fixed left-0 top-0 z-[120] flex min-h-[30px] w-full items-center justify-between bg-[#2e2e2f] px-[15px] py-[15px] shadow-[0_1px_3px_rgba(0,0,0,0.12),0_1px_2px_rgba(0,0,0,0.24)]">
    <div class="relative text-[25px] font-medium text-white">
      <span class="absolute left-7 top-[-3px] text-[13px]">user</span>
      <span class="mr-[3px] rounded bg-[#FFC107] px-[3px] font-semibold text-[#333]">A</span>
      <span class="absolute left-7 top-[7px] text-[22px]">ctivity</span>
    </div>

    <div class="inline-flex items-center gap-2">
      <a class="inline-flex items-center justify-center rounded-[25px] bg-white px-3 py-[5px] text-[13px] text-[#333] no-underline" :href="adminPanelPath">Goto Admin Panel</a>
      <a class="inline-flex items-center justify-center rounded-[25px] bg-white px-3 py-[5px] text-[13px] text-[#333] no-underline" href="https://laravelarticle.com/laravel-user-activity" title="Laravel User Activity">Doc</a>
    </div>
  </header>
</template>
