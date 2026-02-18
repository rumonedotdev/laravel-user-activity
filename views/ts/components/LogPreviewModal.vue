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
  <div class="popup_wrapper" v-if="open && selected">
    <div class="popup" style="width: 60%">
      <div class="header">
        <div class="title">Log Preview</div>
        <div class="close" @click="emit('close')">x</div>
      </div>

      <div class="popup_content">
        <table style="width: 96%;">
          <thead>
            <tr>
              <td colspan="2">INFO</td>
            </tr>
          </thead>
          <tr>
            <td class="field_cell">Type</td>
            <td>
              <span v-if="isDeleteOrLockout(selected.log_type)" class="badge emergency">{{ selected.log_type }}</span>
              <span v-else-if="selected.log_type === 'create'" class="badge info">{{ selected.log_type }}</span>
              <span v-else-if="selected.log_type === 'edit'" class="badge warning edit_badge">{{ selected.log_type }}</span>
              <span v-else class="badge debug">{{ selected.log_type }}</span>
            </td>
          </tr>
          <tr v-show="isCreateEditDelete(selected.log_type)">
            <td class="field_cell">Table</td>
            <td>{{ selected.table_name }}</td>
          </tr>
          <tr>
            <td class="field_cell">Time</td>
            <td>{{ selected.dateHumanize }} - {{ selected.log_date }}</td>
          </tr>
          <tr>
            <td class="field_cell">Done by</td>
            <td>{{ selected.user?.name }} - <span class="text_light">{{ selected.user?.email }}</span></td>
          </tr>
        </table>

        <br>

        <div class="responsive_table">
          <table style="width: 96%;">
            <thead>
              <tr>
                <td>{{ ['edit', 'delete'].includes(selected.log_type) ? 'FIELD' : '' }}</td>
                <td>{{ selected.log_type === 'edit' ? 'PREVIOUS' : 'DATA' }}</td>
                <td v-show="selected.log_type === 'edit'">CURRENT</td>
              </tr>
            </thead>
            <tbody>
              <tr v-for="([field, value], index) in jsonEntries" :key="`${field}-${index}`">
                <td class="field_cell">{{ field }}</td>
                <td>{{ value }}</td>
                <td v-show="selected.log_type === 'edit'" :class="isChanged(field, value) ? 'changed' : ''">
                  {{ currentData[field] }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <br>

        <div class="responsive_table" v-if="selected.log_type === 'edit' && editHistory.length > 0">
          <p style="color: #666;">Another <strong>{{ editHistory.length }}</strong> edit history found!</p>
          <table style="width: 96%;">
            <thead>
              <tr>
                <td>Time</td>
                <td>Edit By</td>
                <td>Data</td>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(history, index) in editHistory" :key="`${history.id}-${index}`">
                <td>{{ history.dateHumanize }}</td>
                <td>{{ history.user?.name }}</td>
                <td style="overflow: hidden">{{ history.data }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="footer">
        <div></div>
        <div></div>
      </div>
    </div>
  </div>
</template>
