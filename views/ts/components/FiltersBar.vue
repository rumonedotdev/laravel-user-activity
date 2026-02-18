<script setup lang="ts">
import { onBeforeUnmount, ref } from 'vue'
import type { FilterState, LogUser } from '../types/activity'

const props = defineProps<{
  filter: FilterState
  activeFilter: boolean
  tables: string[]
  userList: LogUser[]
}>()

const emit = defineEmits<{
  (event: 'search-user', value: string | number | null): void
  (event: 'select-user', user: LogUser): void
  (event: 'reset'): void
  (event: 'filter'): void
}>()

const debounceId = ref<number | null>(null)

function onUserInput(): void {
  if (debounceId.value !== null) {
    window.clearTimeout(debounceId.value)
  }

  debounceId.value = window.setTimeout(() => {
    emit('search-user', props.filter.user_id)
  }, 500)
}

onBeforeUnmount(() => {
  if (debounceId.value !== null) {
    window.clearTimeout(debounceId.value)
  }
})
</script>

<template>
  <div class="top_content_right">
    <div class="filter_item full_width_param user_list_box">
      <label>USER</label>
      <input
        v-model="props.filter.user_id"
        type="text"
        placeholder="Type name or id"
        @input="onUserInput"
      >

      <div id="user_list" v-show="props.userList.length && props.filter.user_id">
        <div
          v-for="user in props.userList"
          :key="user.id"
          class="single_user"
          @click="emit('select-user', user)"
        >
          <p>
            {{ user.name }}<br>
            <span class="text_light">{{ user.email }}</span>
          </p>
        </div>
      </div>
    </div>

    <div class="filter_item">
      <label>LOG TYPE</label>
      <select v-model="props.filter.log_type">
        <option :value="null"></option>
        <option value="create">create</option>
        <option value="edit">edit</option>
        <option value="delete">delete</option>
        <option value="login">login</option>
        <option value="lockout">lockout</option>
      </select>
    </div>

    <div class="filter_item">
      <label>TABLE</label>
      <select v-model="props.filter.table">
        <option :value="null"></option>
        <option v-for="table in props.tables" :key="table" :value="table">{{ table }}</option>
      </select>
    </div>

    <div class="filter_item">
      <label>FROM DATE</label>
      <input v-model="props.filter.from_date" type="date">
    </div>

    <div class="filter_item">
      <label>TO DATE</label>
      <input v-model="props.filter.to_date" type="date">
    </div>

    <div class="filter_item" style="justify-content: flex-end;">
      <button v-show="props.activeFilter" class="btn_reset" @click="emit('reset')">RESET</button>
    </div>

    <div class="filter_item" style="justify-content: flex-end;">
      <button class="btn_filter" :class="{ btn_filter_active: props.activeFilter }" @click="emit('filter')">FILTER</button>
    </div>
  </div>
</template>
