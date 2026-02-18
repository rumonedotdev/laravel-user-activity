<script setup lang="ts">
defineProps<{
  adminPanelPath: string
}>()
</script>

<template>
  <header>
    <div class="name">
      <span>user</span>
      <span class="letter_a">A</span>
      <span>ctivity</span>
    </div>
    <div class="actions">
      <a class="btn" :href="adminPanelPath">Goto Admin Panel</a>
      <a class="btn" href="https://laravelarticle.com/laravel-user-activity" title="Laravel User Activity">Doc</a>
    </div>
  </header>
</template>
