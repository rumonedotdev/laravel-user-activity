<script setup lang="ts">
import { computed } from 'vue'

interface PaginationItem {
  title?: string
  value: string | number
  action: () => void
  active?: boolean
  disabled?: boolean
}

const props = withDefaults(
  defineProps<{
    page?: number
    pageSize?: number
    total?: number
    disabled?: boolean
    dots?: string
    adjacent?: number
    hideIfEmpty?: boolean
    showPrevNext?: boolean
    showFirstLast?: boolean
    scrollTop?: boolean
    textFirst?: string
    textLast?: string
    textNext?: string
    textPrev?: string
    textTitlePage?: string
    textTitleFirst?: string
    textTitleLast?: string
    textTitleNext?: string
    textTitlePrev?: string
  }>(),
  {
    page: 1,
    pageSize: 1,
    total: 0,
    disabled: false,
    dots: '...',
    adjacent: 2,
    hideIfEmpty: false,
    showPrevNext: false,
    showFirstLast: false,
    scrollTop: false,
    textFirst: 'First',
    textLast: 'Last',
    textNext: 'Next',
    textPrev: 'Prev',
    textTitlePage: 'Page {page}',
    textTitleFirst: 'First Page',
    textTitleLast: 'Last Page',
    textTitleNext: 'Next Page',
    textTitlePrev: 'Previous Page',
  },
)

const emit = defineEmits<{
  (event: 'change', payload: { page: number; pageSize: number; total: number }): void
}>()

function replacePageToken(text: string, page: number): string {
  return text.replace(/\{page\}/g, String(page))
}

const state = computed(() => {
  const list: PaginationItem[] = []

  const pageSize = Number.parseInt(String(props.pageSize ?? 1), 10)
  const normalizedPageSize = pageSize > 0 ? pageSize : 1

  const total = Number.parseInt(String(props.total ?? 0), 10)
  const normalizedTotal = Number.isFinite(total) ? total : 0

  let page = Number.parseInt(String(props.page ?? 1), 10)
  if (!Number.isFinite(page)) {
    page = 1
  }

  const pageCount = Math.ceil(normalizedTotal / normalizedPageSize)

  if (page > pageCount) {
    page = pageCount
  }

  if (page <= 0) {
    page = 1
  }

  let adjacent = Number.parseInt(String(props.adjacent ?? 2), 10)
  if (!Number.isFinite(adjacent) || adjacent <= 0) {
    adjacent = 2
  }

  const hide = pageCount <= 1 ? props.hideIfEmpty : false
  const isDisabled = Boolean(props.disabled)

  const internalAction = (targetPage: number) => {
    if (page === targetPage || isDisabled) {
      return
    }

    emit('change', {
      page: targetPage,
      pageSize: normalizedPageSize,
      total: normalizedTotal,
    })

    if (props.scrollTop) {
      window.scrollTo(0, 0)
    }
  }

  const addRange = (start: number, finish: number) => {
    for (let i = start; i <= finish; i += 1) {
      if (i < 1) {
        continue
      }

      list.push({
        value: i,
        title: replacePageToken(props.textTitlePage, i),
        active: !isDisabled && page === i,
        disabled: isDisabled,
        action: () => internalAction(i),
      })
    }
  }

  const addDots = () => {
    list.push({
      value: props.dots,
      disabled: true,
      action: () => undefined,
    })
  }

  const addFirst = (next: number) => {
    addRange(1, 2)

    if (next !== 3) {
      addDots()
    }
  }

  const addLast = (prev: number) => {
    if (prev !== pageCount - 2) {
      addDots()
    }

    addRange(pageCount - 1, pageCount)
  }

  const addPrevNext = (mode: 'prev' | 'next') => {
    if ((!props.showPrevNext && !props.showFirstLast) || pageCount < 1) {
      return
    }

    let disabled = false
    let alpha: { value: string; title: string; page: number } | null = null
    let beta: { value: string; title: string; page: number } | null = null

    if (mode === 'prev') {
      disabled = page - 1 <= 0
      const prevPage = page - 1 <= 0 ? 1 : page - 1

      if (props.showFirstLast) {
        alpha = {
          value: props.textFirst,
          title: props.textTitleFirst,
          page: 1,
        }
      }

      if (props.showPrevNext) {
        beta = {
          value: props.textPrev,
          title: props.textTitlePrev,
          page: prevPage,
        }
      }
    } else {
      disabled = page + 1 > pageCount
      const nextPage = page + 1 >= pageCount ? pageCount : page + 1

      if (props.showPrevNext) {
        alpha = {
          value: props.textNext,
          title: props.textTitleNext,
          page: nextPage,
        }
      }

      if (props.showFirstLast) {
        beta = {
          value: props.textLast,
          title: props.textTitleLast,
          page: pageCount,
        }
      }
    }

    if (isDisabled) {
      disabled = true
    }

    const buildItem = (item: { value: string; title: string; page: number }): PaginationItem => ({
      title: item.title,
      value: item.value,
      disabled,
      action: () => {
        if (!disabled) {
          internalAction(item.page)
        }
      },
    })

    if (alpha) {
      list.push(buildItem(alpha))
    }

    if (beta) {
      list.push(buildItem(beta))
    }
  }

  const fullAdjacentSize = adjacent * 2 + 2

  addPrevNext('prev')

  if (pageCount <= fullAdjacentSize + 2) {
    addRange(1, pageCount)
  } else if (page - adjacent <= 2) {
    const start = 1
    const finish = 1 + fullAdjacentSize

    addRange(start, finish)
    addLast(finish)
  } else if (page < pageCount - (adjacent + 2)) {
    const start = page - adjacent
    const finish = page + adjacent

    addFirst(start)
    addRange(start, finish)
    addLast(finish)
  } else {
    const start = pageCount - fullAdjacentSize
    const finish = pageCount

    addFirst(start)
    addRange(start, finish)
  }

  addPrevNext('next')

  return {
    hide,
    list,
  }
})
</script>

<template>
  <ul v-if="!state.hide" class="m-0 inline-flex list-none gap-1.5 p-0">
    <li
      v-for="(item, index) in state.list"
      :key="`${index}-${item.value}`"
      :title="item.title"
    >
      <button
        type="button"
        :disabled="item.disabled"
        class="inline-flex h-[34px] min-w-9 items-center justify-center rounded-lg border px-2.5 text-[13px] font-semibold"
        :class="{
          'border-[#171b24] bg-[#171b24] text-white': item.active,
          'border-[#d3d8e2] bg-white text-[#232838] hover:border-[#171b24] hover:bg-[#171b24] hover:text-white': !item.active && !item.disabled,
          'cursor-not-allowed border-[#e1e5ec] bg-[#f5f6f9] text-[#a3a9b8]': item.disabled,
        }"
        @click="item.action()"
      >
        {{ item.value }}
      </button>
    </li>
  </ul>
</template>
