<template>
  <div class="table-wrap">
    <table class="table">
      <caption v-if="caption" class="sr-only">{{ caption }}</caption>
      <thead>
        <tr>
          <th v-if="selectable" style="width:36px">
            <input type="checkbox" class="check" :checked="allSelected" aria-label="Select all rows" @change="toggleAll">
          </th>
          <th v-for="c in columns" :key="c.key" :style="{ width: c.width, textAlign: c.align }" :aria-sort="sortKey === c.key ? (sortDir === 'asc' ? 'ascending' : 'descending') : undefined">
            <button v-if="c.sortable !== false" type="button" @click="sortBy(c.key)">
              {{ c.label }}
              <i v-if="sortKey === c.key" :class="['bi', sortDir === 'asc' ? 'bi-arrow-up' : 'bi-arrow-down']" aria-hidden="true" />
            </button>
            <template v-else>{{ c.label }}</template>
          </th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="row in sorted"
          :key="row[rowKey]"
          :class="{ clickable: clickable, selected: activeKey === row[rowKey] }"
          :tabindex="clickable ? 0 : undefined"
          @click="clickable && emit('row-click', row)"
          @keydown.enter="clickable && emit('row-click', row)"
        >
          <td v-if="selectable" @click.stop>
            <input type="checkbox" class="check" :checked="selected.includes(row[rowKey])" :aria-label="`Select ${row[rowKey]}`" @change="toggle(row[rowKey])">
          </td>
          <td v-for="c in columns" :key="c.key" :style="{ textAlign: c.align }">
            <slot :name="`cell-${c.key}`" :row="row" :value="row[c.key]">{{ row[c.key] ?? '-' }}</slot>
          </td>
        </tr>
      </tbody>
    </table>
    <div v-if="!rows.length" class="empty">
      <i class="bi bi-inbox" aria-hidden="true" />
      <span>{{ empty ?? 'Nothing to show' }}</span>
    </div>
  </div>
</template>

<script setup lang="ts" generic="T extends Record<string, any>">
export interface Column { key: string, label: string, width?: string, align?: 'left' | 'right' | 'center', sortable?: boolean, sortValue?: (row: any) => string | number }

const props = withDefaults(defineProps<{
  columns: Column[]
  rows: T[]
  rowKey?: string
  clickable?: boolean
  selectable?: boolean
  activeKey?: unknown
  empty?: string
  caption?: string
}>(), { rowKey: 'id' })
const selected = defineModel<unknown[]>('selected', { default: () => [] })
const emit = defineEmits<{ 'row-click': [T] }>()

const sortKey = ref<string | null>(null)
const sortDir = ref<'asc' | 'desc'>('asc')
const sortBy = (key: string) => {
  if (sortKey.value === key) sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc'
  else { sortKey.value = key; sortDir.value = 'asc' }
}
const sorted = computed(() => {
  if (!sortKey.value) return props.rows
  const col = props.columns.find(c => c.key === sortKey.value)
  const val = (r: T) => col?.sortValue ? col.sortValue(r) : r[sortKey.value!]
  return [...props.rows].sort((a, b) => {
    const x = val(a), y = val(b)
    const cmp = x == null ? 1 : y == null ? -1 : x < y ? -1 : x > y ? 1 : 0
    return sortDir.value === 'asc' ? cmp : -cmp
  })
})

const allSelected = computed(() => props.rows.length > 0 && props.rows.every(r => selected.value.includes(r[props.rowKey])))
const toggle = (k: unknown) => { selected.value = selected.value.includes(k) ? selected.value.filter(x => x !== k) : [...selected.value, k] }
const toggleAll = () => { selected.value = allSelected.value ? [] : props.rows.map(r => r[props.rowKey]) }
</script>
