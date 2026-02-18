<script setup lang="ts">
import type { LogEntry } from '../types/activity'

defineProps<{
  data: LogEntry[]
  isLoading: boolean
}>()

const emit = defineEmits<{
  (event: 'show', log: LogEntry): void
}>()

function actionLabel(logType: string): string {
  if (!logType) {
    return 'Unknown'
  }

  return `${logType.charAt(0).toUpperCase()}${logType.slice(1)}`
}

function statusLabel(logType: string): 'success' | 'warning' | 'error' {
  if (logType === 'delete') {
    return 'error'
  }

  if (logType === 'lockout') {
    return 'warning'
  }

  return 'success'
}

function detailsText(log: LogEntry): string {
  if (log.log_type === 'create') {
    return `Created record in ${log.table_name}`
  }

  if (log.log_type === 'edit') {
    const updatedFields = Object.keys(log.json_data ?? {}).filter((field) => field !== 'id')
    if (updatedFields.length > 0) {
      return `Updated ${updatedFields.slice(0, 2).join(', ')}`
    }

    return `Updated record in ${log.table_name}`
  }

  if (log.log_type === 'delete') {
    return `Deleted record from ${log.table_name}`
  }

  if (log.log_type === 'login') {
    return 'Authenticated successfully'
  }

  if (log.log_type === 'lockout') {
    return 'Too many login attempts'
  }

  return `${actionLabel(log.log_type)} activity`
}
</script>

<template>
  <div class="relative overflow-hidden rounded-xl border border-[#e2e5eb] bg-[#fbfcfe]">
    <div
      v-show="isLoading"
      class="absolute inset-0 z-40 flex items-center justify-center bg-[rgba(250,250,252,0.88)]"
    >
      <div class="h-8 w-8 animate-spin rounded-full border-4 border-[#c8ceda] border-t-[#222838]"></div>
    </div>

    <div class="w-full overflow-x-auto">
      <table class="min-w-[980px] w-full border-collapse">
        <thead>
          <tr>
            <th class="border-b border-[#dfe3ea] bg-[#f2f4f8] px-3 py-3 text-left text-[13px] font-semibold text-[#525a6c]">User</th>
            <th class="border-b border-[#dfe3ea] bg-[#f2f4f8] px-3 py-3 text-left text-[13px] font-semibold text-[#525a6c]">Action</th>
            <th class="border-b border-[#dfe3ea] bg-[#f2f4f8] px-3 py-3 text-left text-[13px] font-semibold text-[#525a6c]">Details</th>
            <th class="border-b border-[#dfe3ea] bg-[#f2f4f8] px-3 py-3 text-left text-[13px] font-semibold text-[#525a6c]">Timestamp</th>
            <th class="border-b border-[#dfe3ea] bg-[#f2f4f8] px-3 py-3 text-left text-[13px] font-semibold text-[#525a6c]">Table</th>
            <th class="border-b border-[#dfe3ea] bg-[#f2f4f8] px-3 py-3 text-left text-[13px] font-semibold text-[#525a6c]">Status</th>
            <th class="border-b border-[#dfe3ea] bg-[#f2f4f8] px-3 py-3 text-right text-[13px] font-semibold text-[#525a6c]">View</th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="(log, index) in data" :key="`${log.id}-${index}`" class="hover:bg-[#f8faff]">
            <td class="border-b border-[#e8ebf1] px-3 py-2.5 align-top text-sm text-[#1f2330]">
              <strong>{{ log.user?.name }}</strong><br>
              <span class="text-sm text-[#767d8f]">ID: {{ log.user?.id }}</span>
            </td>
            <td class="border-b border-[#e8ebf1] px-3 py-2.5 align-top text-sm text-[#1f2330]">{{ actionLabel(log.log_type) }}</td>
            <td class="border-b border-[#e8ebf1] px-3 py-2.5 align-top text-sm text-[#1f2330]">{{ detailsText(log) }}</td>
            <td class="border-b border-[#e8ebf1] px-3 py-2.5 align-top text-sm text-[#1f2330]">
              {{ log.log_date }}<br>
              <span class="text-sm text-[#767d8f]">{{ log.dateHumanize }}</span>
            </td>
            <td class="border-b border-[#e8ebf1] px-3 py-2.5 align-top text-sm text-[#1f2330]">{{ log.table_name || '-' }}</td>
            <td class="border-b border-[#e8ebf1] px-3 py-2.5 align-top text-sm text-[#1f2330]">
              <span
                class="inline-flex min-w-[78px] items-center justify-center rounded-full px-2.5 py-1 text-xs font-bold lowercase"
                :class="{
                  'bg-[#dff5e7] text-[#109a58]': statusLabel(log.log_type) === 'success',
                  'bg-[#fff2cf] text-[#b37d00]': statusLabel(log.log_type) === 'warning',
                  'bg-[#ffe0e0] text-[#dc3f3f]': statusLabel(log.log_type) === 'error',
                }"
              >
                {{ statusLabel(log.log_type) }}
              </span>
            </td>
            <td class="border-b border-[#e8ebf1] px-3 py-2.5 align-top text-right text-sm text-[#1f2330]">
              <button
                class="inline-flex h-8 items-center justify-center rounded-lg border border-[#ced4df] bg-white px-2.5 text-sm font-semibold text-[#1f2330] hover:bg-[#edf1f8]"
                @click="emit('show', log)"
              >
                Open
              </button>
            </td>
          </tr>
        </tbody>
      </table>

      <div class="flex justify-end px-3 pb-3.5 pt-3">
        <slot name="pagination"></slot>
      </div>
    </div>
  </div>
</template>
