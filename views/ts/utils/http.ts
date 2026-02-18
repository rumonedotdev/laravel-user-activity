interface FetchJsonOptions extends Omit<RequestInit, 'body'> {
  body?: unknown
  csrfToken?: string
}

function getCookie(name: string): string | null {
  const pattern = new RegExp(`(?:^|; )${name}=([^;]*)`)
  const match = document.cookie.match(pattern)
  if (!match || !match[1]) {
    return null
  }

  return decodeURIComponent(match[1])
}

function resolveCsrfToken(explicitToken?: string): string | null {
  if (explicitToken) {
    return explicitToken
  }

  const metaToken = document
    .querySelector('meta[name="csrf-token"]')
    ?.getAttribute('content')
  if (metaToken) {
    return metaToken
  }

  return getCookie('XSRF-TOKEN')
}

export async function fetchJson<T>(url: string, options: FetchJsonOptions = {}): Promise<T> {
  const method = (options.method ?? 'GET').toUpperCase()
  const headers = new Headers(options.headers ?? {})

  headers.set('Accept', 'application/json')

  let body: BodyInit | undefined
  if (options.body !== undefined) {
    if (typeof options.body === 'string' || options.body instanceof FormData) {
      body = options.body
    } else {
      body = JSON.stringify(options.body)
      headers.set('Content-Type', 'application/json')
    }
  }

  if (method !== 'GET' && method !== 'HEAD') {
    const csrfToken = resolveCsrfToken(options.csrfToken)
    if (csrfToken) {
      headers.set('X-CSRF-TOKEN', csrfToken)
    }
    headers.set('X-Requested-With', 'XMLHttpRequest')
  }

  const response = await fetch(url, {
    ...options,
    method,
    headers,
    body,
    credentials: 'same-origin',
  })

  if (!response.ok) {
    throw new Error(`Request failed with status ${response.status}`)
  }

  return (await response.json()) as T
}
