<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
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
  <AppHeader :admin-panel-path="boot.adminPanelPath" />

  <section class="content">
    <div class="top_content">
      <div class="top_content_left">
        <p class="text_light">Showing {{ response.from ?? '' }} to {{ response.to ?? '' }} of {{ response.total }} records</p>
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
    </div>

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

    <LogPreviewModal
      :open="popup"
      :selected="selected"
      :current-data="currentData"
      :edit-history="editHistory"
      @close="closePopup"
    />
  </section>
</template>
