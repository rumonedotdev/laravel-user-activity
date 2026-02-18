<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue'
import AppHeader from './components/AppHeader.vue'
import AppFooter from './components/AppFooter.vue'
import FiltersBar from './components/FiltersBar.vue'
import LogTable from './components/LogTable.vue'
import LogPreviewModal from './components/LogPreviewModal.vue'
import Pagination from './components/Pagination.vue'
import type {
  BootConfig,
  CurrentDataResponse,
  FilterState,
  LogEntry,
  LogUser,
  PaginatedResponse,
} from './types/activity'
import { objectToQueryString } from './utils/query'
import { fetchJson } from './utils/http'

const fallbackBoot: BootConfig = {
  routePath: '',
  adminPanelPath: '',
  deleteLimit: 0,
  tables: [],
}

const boot = window.__USER_ACTIVITY_BOOT__ ?? fallbackBoot

const selected = ref<LogEntry | null>(null)
const popup = ref(false)
const activeFilter = ref(false)
const isLoading = ref(false)
const userList = ref<LogUser[]>([])
const currentData = ref<Record<string, unknown>>({})
const editHistory = ref<LogEntry[]>([])

const filter = reactive<FilterState>({
  user_id: null,
  log_type: null,
  table: null,
  from_date: null,
  to_date: null,
})

const response = ref<PaginatedResponse<LogEntry>>({
  current_page: 1,
  data: [],
  from: null,
  to: null,
  total: 0,
  per_page: 10,
})

const data = ref<LogEntry[]>([])

function statusCategory(logType: string): 'success' | 'warning' | 'error' {
  if (logType === 'delete') {
    return 'error'
  }

  if (logType === 'lockout') {
    return 'warning'
  }

  return 'success'
}

const summary = computed(() => {
  const success = data.value.filter((item) => statusCategory(item.log_type) === 'success').length
  const warning = data.value.filter((item) => statusCategory(item.log_type) === 'warning').length
  const error = data.value.filter((item) => statusCategory(item.log_type) === 'error').length
  const shownTotal = data.value.length || 1

  const percent = (value: number): string => `${Math.round((value / shownTotal) * 100)}% of shown`

  return {
    totalActivities: response.value.total,
    successful: success,
    warnings: warning,
    errors: error,
    successfulPct: percent(success),
    warningsPct: percent(warning),
    errorsPct: percent(error),
  }
})

async function init(pageNumber = 1): Promise<void> {
  let url = `${boot.routePath}?action=data&page=${pageNumber}`

  if (activeFilter.value) {
    if ((filter.from_date !== null && filter.to_date === null) || (filter.to_date !== null && filter.from_date === null)) {
      window.alert('From & To date required')
      return
    }

    const query = objectToQueryString({ ...filter })
    url = `${url}${query.replace('?', '&')}`
  }

  isLoading.value = true
  try {
    const fetched = await fetchJson<PaginatedResponse<LogEntry>>(url, {
      csrfToken: boot.csrfToken,
    })

    response.value = fetched
    data.value = fetched.data
  } catch (error) {
    console.error(error)
  } finally {
    isLoading.value = false
  }
}

function resetFilter(): void {
  filter.user_id = null
  filter.log_type = null
  filter.table = null
  filter.from_date = null
  filter.to_date = null
}

function resetParam(): void {
  activeFilter.value = false
  userList.value = []
  resetFilter()
  void init()
}

function filterData(): void {
  activeFilter.value = true
  void init()
}

async function getUsers(user: string | number | null): Promise<void> {
  if (!user) {
    userList.value = []
    return
  }

  const url = `${boot.routePath}?action=user_autocomplete&user=${encodeURIComponent(String(user))}`

  try {
    const fetched = await fetchJson<LogUser[]>(url, {
      csrfToken: boot.csrfToken,
    })
    userList.value = fetched
  } catch (error) {
    console.error(error)
  }
}

function onUserSelect(user: LogUser): void {
  filter.user_id = user.id
  userList.value = []
}

async function getCurrentData(logData: LogEntry): Promise<void> {
  const params = {
    action: 'current_data',
    table: logData.table_name,
    id: (logData.json_data as { id?: unknown }).id,
    log_id: logData.id,
  }

  currentData.value = {}
  editHistory.value = []

  try {
    const fetched = await fetchJson<CurrentDataResponse>(
      `${boot.routePath}${objectToQueryString(params as Record<string, unknown>)}`,
      {
        csrfToken: boot.csrfToken,
      },
    )

    currentData.value = fetched.current_data ?? {}
    editHistory.value = fetched.edit_history ?? []
  } catch (error) {
    console.error(error)
  }
}

function showPopup(logData: LogEntry): void {
  selected.value = logData
  popup.value = true

  if (logData.log_type === 'edit') {
    void getCurrentData(logData)
  }
}

async function deleteLog(): Promise<void> {
  if (!window.confirm('Are you sure?')) {
    return
  }

  try {
    const fetched = await fetchJson<{ success: boolean; message: string }>(boot.routePath, {
      method: 'POST',
      body: { action: 'delete' },
      csrfToken: boot.csrfToken,
    })

    if (fetched.success) {
      window.alert(fetched.message)
      void init()
    } else {
      window.alert('Something went wrong')
    }
  } catch (error) {
    console.error(error)
    window.alert('Something went wrong')
  }
}

function closePopup(): void {
  popup.value = false
}

function onPageChange(payload: { page: number }): void {
  void init(payload.page)
}

function onUserSearch(user: string | number | null): void {
  void getUsers(user)
}

onMounted(() => {
  void init()
})
</script>

<template>
  <div class="min-h-screen bg-[#05070c] p-3 font-sans sm:p-6">
    <div class="relative mx-auto max-w-[1460px] min-h-[calc(100vh-24px)] overflow-hidden rounded-[14px] border border-[#70bed1] bg-[#f7f7f8] sm:min-h-[calc(100vh-48px)]">
      <AppHeader :admin-panel-path="boot.adminPanelPath" />

      <section class="px-4 pb-4 pt-[98px] sm:px-[30px] sm:pb-[30px] sm:pt-[82px]">
        <div>
          <h1 class="m-0 text-[32px] font-bold tracking-[-0.03em] text-[#131518] sm:text-[50px]">User Activity Dashboard</h1>
          <p class="mt-2 text-base text-[#606778] sm:text-[22px]">Monitor and track all user activities in real-time</p>
        </div>

        <div class="mt-6 grid grid-cols-1 gap-3.5 md:grid-cols-2 xl:grid-cols-4 sm:mt-7">
          <div class="min-h-[138px] rounded-[14px] border border-[#d6d9df] bg-white p-5">
            <div class="mb-5 text-lg font-semibold text-[#2d3139] sm:text-[19px]">Total Activities</div>
            <div class="text-[40px] font-bold leading-none tracking-[-0.03em] text-[#15171c] sm:text-[48px]">{{ summary.totalActivities }}</div>
            <div class="mt-2 text-sm text-[#677083] sm:text-base">All matching records</div>
          </div>

          <div class="min-h-[138px] rounded-[14px] border border-[#d6d9df] bg-white p-5">
            <div class="mb-5 text-lg font-semibold text-[#2d3139] sm:text-[19px]">Successful</div>
            <div class="text-[40px] font-bold leading-none tracking-[-0.03em] text-[#159f5f] sm:text-[48px]">{{ summary.successful }}</div>
            <div class="mt-2 text-sm text-[#677083] sm:text-base">{{ summary.successfulPct }}</div>
          </div>

          <div class="min-h-[138px] rounded-[14px] border border-[#d6d9df] bg-white p-5">
            <div class="mb-5 text-lg font-semibold text-[#2d3139] sm:text-[19px]">Warnings</div>
            <div class="text-[40px] font-bold leading-none tracking-[-0.03em] text-[#c19015] sm:text-[48px]">{{ summary.warnings }}</div>
            <div class="mt-2 text-sm text-[#677083] sm:text-base">{{ summary.warningsPct }}</div>
          </div>

          <div class="min-h-[138px] rounded-[14px] border border-[#d6d9df] bg-white p-5">
            <div class="mb-5 text-lg font-semibold text-[#2d3139] sm:text-[19px]">Errors</div>
            <div class="text-[40px] font-bold leading-none tracking-[-0.03em] text-[#d44848] sm:text-[48px]">{{ summary.errors }}</div>
            <div class="mt-2 text-sm text-[#677083] sm:text-base">{{ summary.errorsPct }}</div>
          </div>
        </div>

        <section class="mt-5 rounded-[14px] border border-[#d6d9df] bg-white p-4 sm:mt-[22px] sm:px-[18px] sm:pb-4 sm:pt-[18px]">
          <div class="mb-3.5 flex flex-col items-start justify-between gap-3 sm:flex-row sm:items-end sm:gap-4">
            <div>
              <h2 class="m-0 text-2xl font-semibold text-[#171920] sm:text-[28px]">Activity Logs</h2>
              <p class="mt-1 text-sm text-[#5f6675] sm:text-lg">Detailed view of all user activities and system events</p>
            </div>
            <p class="text-sm text-[#767d8f] sm:text-base">Showing {{ response.from ?? 0 }} to {{ response.to ?? 0 }} of {{ response.total }} records</p>
          </div>

          <FiltersBar
            :filter="filter"
            :active-filter="activeFilter"
            :tables="boot.tables"
            :user-list="userList"
            @search-user="onUserSearch"
            @select-user="onUserSelect"
            @reset="resetParam"
            @filter="filterData"
          />

          <LogTable :data="data" :is-loading="isLoading" @show="showPopup">
            <template #pagination>
              <Pagination
                :page="response.current_page"
                :page-size="response.per_page"
                :total="response.total"
                :scroll-top="true"
                :hide-if-empty="true"
                :show-prev-next="true"
                :show-first-last="true"
                @change="onPageChange"
              />
            </template>
          </LogTable>

          <AppFooter :delete-limit="boot.deleteLimit" @delete="deleteLog" />
        </section>

        <LogPreviewModal
          :open="popup"
          :selected="selected"
          :current-data="currentData"
          :edit-history="editHistory"
          @close="closePopup"
        />
      </section>
    </div>
  </div>
</template>
