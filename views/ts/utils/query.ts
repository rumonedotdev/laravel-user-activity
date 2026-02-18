export function objectToQueryString(params: Record<string, unknown>): string {
  return `?${Object.keys(params)
    .map((key) => {
      const value = params[key]
      if (value) {
        return `${encodeURIComponent(key)}=${encodeURIComponent(String(value))}`
      }

      return ''
    })
    .filter((item) => Boolean(item))
    .join('&')}`
}
