export type LogType = 'create' | 'edit' | 'delete' | 'login' | 'lockout' | string

export interface LogUser {
  id: number | string
  name: string
  email: string
}

export interface LogEntry {
  id: number
  log_date: string
  dateHumanize: string
  log_type: LogType
  table_name: string
  user: LogUser
  json_data: Record<string, unknown>
  data?: string
}

export interface PaginatedResponse<T> {
  current_page: number
  data: T[]
  from: number | null
  to: number | null
  total: number
  per_page: number
}

export interface FilterState {
  user_id: string | number | null
  log_type: string | null
  table: string | null
  from_date: string | null
  to_date: string | null
}

export interface CurrentDataResponse {
  current_data: Record<string, unknown>
  edit_history: LogEntry[]
}

export interface BootConfig {
  routePath: string
  adminPanelPath: string
  deleteLimit: number
  tables: string[]
  csrfToken?: string
}

declare global {
  interface Window {
    __USER_ACTIVITY_BOOT__?: BootConfig
  }
}
