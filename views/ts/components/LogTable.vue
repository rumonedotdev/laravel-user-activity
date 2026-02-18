<script setup lang="ts">
import type { LogEntry, LogType } from '../types/activity'

defineProps<{
  data: LogEntry[]
  isLoading: boolean
}>()

const emit = defineEmits<{
  (event: 'show', log: LogEntry): void
}>()

function isDeleteOrLockout(type: LogType): boolean {
  return type === 'delete' || type === 'lockout'
}

function isEditOrDelete(type: LogType): boolean {
  return type === 'edit' || type === 'delete'
}
</script>

<template>
  <div class="log_data_wrapper">
    <div class="loader" v-show="isLoading">
      <div class="spinner">
        <div class="bounce1"></div>
        <div class="bounce2"></div>
        <div class="bounce3"></div>
      </div>
    </div>

    <div class="responsive_table">
      <table>
        <thead>
          <tr>
            <td width="30">ID</td>
            <td width="260">DATE</td>
            <td width="170">LOG TYPE</td>
            <td>DONE BY</td>
            <td class="text_right" style="padding-right: 10px;">ACTION</td>
          </tr>
        </thead>

        <tr v-for="(log, index) in data" :key="`${log.id}-${index}`">
          <td style="border-right: 1px solid #ddd;">{{ log.id }}</td>
          <td>{{ log.log_date }} - {{ log.dateHumanize }}</td>
          <td>
            <template v-if="isDeleteOrLockout(log.log_type)">
              <span class="badge emergency">{{ log.log_type }}</span>
            </template>
            <template v-else-if="log.log_type === 'create'">
              <span class="badge info">{{ log.log_type }}</span>
              <span class="lbl_table">to {{ log.table_name }}</span>
            </template>
            <template v-else-if="log.log_type === 'edit'">
              <span class="badge warning edit_badge">{{ log.log_type }}</span>
            </template>
            <template v-else>
              <span class="badge debug">{{ log.log_type }}</span>
            </template>

            <span v-if="isEditOrDelete(log.log_type)" class="lbl_table">from {{ log.table_name }}</span>
          </td>

          <td>
            <strong>{{ log.user?.name }}</strong><br>
            <span class="text_light">{{ log.user?.email }}</span>
          </td>

          <td class="action_column text_right">
            <button class="btn_show" @click="emit('show', log)">SHOW</button>
          </td>
        </tr>
      </table>

      <div class="pagination_wrapper">
        <slot name="pagination"></slot>
      </div>
    </div>
  </div>
</template>
