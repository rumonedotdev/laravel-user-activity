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

const controlClass =
  'h-10 w-full rounded-xl border border-[#d7dbe2] bg-[#f8f9fb] px-3 text-sm text-[#20242d] outline-none transition focus:border-[#8a93a8] focus:bg-white'

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
  <div class="mb-3.5 grid grid-cols-1 items-end gap-2.5 xl:grid-cols-[minmax(250px,1.9fr)_repeat(4,minmax(130px,1fr))_auto] md:grid-cols-2">
    <div class="relative flex flex-col gap-1.5">
      <span class="pointer-events-none absolute bottom-[13px] left-3.5 h-3 w-3 rounded-full border-2 border-[#9097a6] after:absolute after:bottom-[-4px] after:right-[-7px] after:h-[2px] after:w-[7px] after:rotate-45 after:rounded after:bg-[#9097a6]"></span>
      <label class="text-xs font-semibold uppercase tracking-[0.03em] text-[#6c7486]">User</label>
      <input
        v-model="props.filter.user_id"
        type="text"
        placeholder="Search by user id or name"
        :class="`${controlClass} pl-10`"
        @input="onUserInput"
      >

      <div
        id="user_list"
        v-show="props.userList.length && props.filter.user_id"
        class="absolute top-[calc(100%+6px)] z-30 max-h-56 w-full overflow-y-auto rounded-xl border border-[#d7dbe2] bg-white shadow-[0_10px_24px_rgba(24,26,32,0.10)]"
      >
        <div
          v-for="user in props.userList"
          :key="user.id"
          class="cursor-pointer border-b border-[#eef0f4] px-3 py-2.5 last:border-b-0 hover:bg-[#f4f6fa]"
          @click="emit('select-user', user)"
        >
          <p class="m-0">
            {{ user.name }}<br>
            <span class="text-sm text-[#767d8f]">{{ user.email }}</span>
          </p>
        </div>
      </div>
    </div>

    <div class="flex flex-col gap-1.5">
      <label class="text-xs font-semibold uppercase tracking-[0.03em] text-[#6c7486]">Log Type</label>
      <select v-model="props.filter.log_type" :class="controlClass">
        <option :value="null">All Actions</option>
        <option value="create">create</option>
        <option value="edit">edit</option>
        <option value="delete">delete</option>
        <option value="login">login</option>
        <option value="lockout">lockout</option>
      </select>
    </div>

    <div class="flex flex-col gap-1.5">
      <label class="text-xs font-semibold uppercase tracking-[0.03em] text-[#6c7486]">Table</label>
      <select v-model="props.filter.table" :class="controlClass">
        <option :value="null">All Tables</option>
        <option v-for="table in props.tables" :key="table" :value="table">{{ table }}</option>
      </select>
    </div>

    <div class="flex flex-col gap-1.5">
      <label class="text-xs font-semibold uppercase tracking-[0.03em] text-[#6c7486]">From</label>
      <input v-model="props.filter.from_date" type="date" :class="controlClass">
    </div>

    <div class="flex flex-col gap-1.5">
      <label class="text-xs font-semibold uppercase tracking-[0.03em] text-[#6c7486]">To</label>
      <input v-model="props.filter.to_date" type="date" :class="controlClass">
    </div>

    <div class="flex items-end gap-2.5 pb-px md:col-span-2 xl:col-span-1">
      <button
        v-show="props.activeFilter"
        class="h-10 min-w-[92px] rounded-xl border border-[#d7dbe2] bg-white px-3 text-sm font-semibold text-[#20242d]"
        @click="emit('reset')"
      >
        Reset
      </button>

      <button
        class="h-10 min-w-[92px] rounded-xl border px-3 text-sm font-semibold text-white"
        :class="props.activeFilter ? 'border-[#df4343] bg-[#df4343]' : 'border-[#151922] bg-[#151922]'"
        @click="emit('filter')"
      >
        Apply
      </button>
    </div>
  </div>
</template>
