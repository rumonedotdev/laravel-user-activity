<script setup lang="ts">
import { computed } from 'vue'
import type { LogEntry, LogType } from '../types/activity'

const props = defineProps<{
  open: boolean
  selected: LogEntry | null
  currentData: Record<string, unknown>
  editHistory: LogEntry[]
}>()

const emit = defineEmits<{
  (event: 'close'): void
}>()

const jsonEntries = computed(() => Object.entries(props.selected?.json_data ?? {}))

function isCreateEditDelete(type: LogType | undefined): boolean {
  return type === 'create' || type === 'edit' || type === 'delete'
}

function isDeleteOrLockout(type: LogType | undefined): boolean {
  return type === 'delete' || type === 'lockout'
}

function isChanged(field: string, value: unknown): boolean {
  if (!props.selected || props.selected.log_type !== 'edit') {
    return false
  }

  return value != props.currentData[field]
}
</script>

<template>
  <div v-if="open && selected" class="fixed inset-0 z-[150] bg-[rgba(12,14,18,0.52)]">
    <div class="absolute left-1/2 top-[12%] z-[200] max-h-[76vh] w-[95%] -translate-x-1/2 overflow-y-auto rounded-xl bg-white shadow-[0_24px_48px_rgba(15,19,29,0.22)] sm:w-[60%]">
      <div class="flex h-[42px] items-center justify-between border-b border-[#dde2ea] bg-[#f4f6fa] pl-3.5">
        <div class="text-sm font-bold text-[#1f2430]">Log Preview</div>
        <button
          type="button"
          class="h-[42px] w-[42px] bg-[#1b2230] text-lg leading-[42px] text-white"
          @click="emit('close')"
        >
          x
        </button>
      </div>

      <div class="p-4">
        <table class="w-full border-collapse border border-[#dde1e9] text-sm text-[#404754] sm:w-[96%]">
          <thead>
            <tr>
              <td colspan="2" class="border border-[#dde1e9] bg-[#f0f3f8] p-2 font-bold text-[#334]">INFO</td>
            </tr>
          </thead>
          <tr>
            <td class="w-[150px] border border-[#dde1e9] bg-[#f4f6fa] p-2">Type</td>
            <td class="border border-[#dde1e9] p-2">
              <span
                class="inline-flex rounded-full px-2 py-1 text-xs"
                :class="{
                  'bg-[#ffd2d2]': isDeleteOrLockout(selected.log_type),
                  'bg-[#bcebe0]': selected.log_type === 'create',
                  'bg-[#ffe6b8]': selected.log_type === 'edit',
                  'bg-[#d8dce3]': !isDeleteOrLockout(selected.log_type) && selected.log_type !== 'create' && selected.log_type !== 'edit',
                }"
              >
                {{ selected.log_type }}
              </span>
            </td>
          </tr>
          <tr v-show="isCreateEditDelete(selected.log_type)">
            <td class="border border-[#dde1e9] bg-[#f4f6fa] p-2">Table</td>
            <td class="border border-[#dde1e9] p-2">{{ selected.table_name }}</td>
          </tr>
          <tr>
            <td class="border border-[#dde1e9] bg-[#f4f6fa] p-2">Time</td>
            <td class="border border-[#dde1e9] p-2">{{ selected.dateHumanize }} - {{ selected.log_date }}</td>
          </tr>
          <tr>
            <td class="border border-[#dde1e9] bg-[#f4f6fa] p-2">Done by</td>
            <td class="border border-[#dde1e9] p-2">
              {{ selected.user?.name }} -
              <span class="text-[#767d8f]">{{ selected.user?.email }}</span>
            </td>
          </tr>
        </table>

        <div class="mt-4 w-full overflow-x-auto">
          <table class="w-full border-collapse border border-[#dde1e9] text-sm text-[#404754] sm:w-[96%]">
            <thead>
              <tr>
                <td class="border border-[#dde1e9] bg-[#f0f3f8] p-2 font-bold text-[#334]">{{ ['edit', 'delete'].includes(selected.log_type) ? 'FIELD' : '' }}</td>
                <td class="border border-[#dde1e9] bg-[#f0f3f8] p-2 font-bold text-[#334]">{{ selected.log_type === 'edit' ? 'PREVIOUS' : 'DATA' }}</td>
                <td v-show="selected.log_type === 'edit'" class="border border-[#dde1e9] bg-[#f0f3f8] p-2 font-bold text-[#334]">CURRENT</td>
              </tr>
            </thead>
            <tbody>
              <tr v-for="([field, value], index) in jsonEntries" :key="`${field}-${index}`">
                <td class="border border-[#dde1e9] bg-[#f4f6fa] p-2">{{ field }}</td>
                <td class="border border-[#dde1e9] p-2">{{ value }}</td>
                <td
                  v-show="selected.log_type === 'edit'"
                  class="border border-[#dde1e9] p-2"
                  :class="isChanged(field, value) ? 'bg-[#fff1d9]' : ''"
                >
                  {{ currentData[field] }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="selected.log_type === 'edit' && editHistory.length > 0" class="mt-4 w-full overflow-x-auto">
          <p class="mb-2 text-sm text-[#666]">Another <strong>{{ editHistory.length }}</strong> edit history found!</p>
          <table class="w-full border-collapse border border-[#dde1e9] text-sm text-[#404754] sm:w-[96%]">
            <thead>
              <tr>
                <td class="border border-[#dde1e9] bg-[#f0f3f8] p-2 font-bold text-[#334]">Time</td>
                <td class="border border-[#dde1e9] bg-[#f0f3f8] p-2 font-bold text-[#334]">Edit By</td>
                <td class="border border-[#dde1e9] bg-[#f0f3f8] p-2 font-bold text-[#334]">Data</td>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(history, index) in editHistory" :key="`${history.id}-${index}`">
                <td class="border border-[#dde1e9] p-2">{{ history.dateHumanize }}</td>
                <td class="border border-[#dde1e9] p-2">{{ history.user?.name }}</td>
                <td class="border border-[#dde1e9] p-2">{{ history.data }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="h-7 border-t border-[#dde2ea] bg-[#f4f6fa]"></div>
    </div>
  </div>
</template>
