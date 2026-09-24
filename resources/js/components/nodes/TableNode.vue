<template>
    <div
        :class="[
            'table-node min-w-[220px] max-w-[280px] rounded-xl border transition-all duration-200 cursor-pointer select-none overflow-hidden',
            selected
                ? 'border-node-db shadow-[0_0_0_2px_rgba(14,165,233,0.5),0_0_40px_rgba(14,165,233,0.15)]'
                : 'border-canvas-border shadow-node hover:border-node-db/50'
        ]"
        style="background: #0a1628"
    >
        <!-- Header -->
        <div class="flex items-center gap-2 px-3 py-2.5 border-b border-canvas-border/70"
             :style="{ background: 'linear-gradient(135deg, rgba(14,165,233,0.12) 0%, rgba(14,165,233,0.04) 100%)' }">

            <div class="w-5 h-5 rounded flex-shrink-0 flex items-center justify-center"
                 style="background: rgba(14,165,233,0.15); border: 1px solid rgba(14,165,233,0.3)">
                <svg class="w-3 h-3 text-node-db" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                    <ellipse cx="8" cy="4" rx="6" ry="2"/>
                    <path d="M2 4v4c0 1.1 2.7 2 6 2s6-.9 6-2V4"/>
                    <path d="M2 8v4c0 1.1 2.7 2 6 2s6-.9 6-2V8"/>
                </svg>
            </div>

            <span class="text-sm font-semibold text-slate-100 font-mono truncate flex-1">{{ data.name }}</span>

            <!-- Row count pill -->
            <span class="text-[10px] font-mono text-slate-500 bg-canvas-bg px-1.5 py-0.5 rounded border border-canvas-border/50 flex-shrink-0">
                {{ formatCount(data.row_count) }}
            </span>

            <!-- Expand toggle -->
            <button
                class="flex-shrink-0 text-slate-500 hover:text-slate-200 transition-colors"
                @click.stop="toggleExpanded"
            >
                <svg
                    :class="['w-3.5 h-3.5 transition-transform duration-200', expanded ? 'rotate-180' : '']"
                    viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"
                >
                    <polyline points="4 6 8 10 12 6"/>
                </svg>
            </button>
        </div>

        <!-- Column summary (collapsed) -->
        <div v-if="!expanded" class="px-3 py-2 flex items-center gap-2">
            <span class="text-[10px] text-slate-500">{{ data.columns?.length ?? 0 }} columns</span>
            <span class="text-slate-600">·</span>
            <div class="flex gap-1 flex-wrap">
                <span
                    v-for="col in primaryCols"
                    :key="col.name"
                    class="text-[10px] font-mono text-amber-400 bg-amber-400/5 px-1 rounded"
                >{{ col.name }}</span>
            </div>
        </div>

        <!-- Column list (expanded) -->
        <div v-if="expanded" class="divide-y divide-canvas-border/40">
            <div
                v-for="col in data.columns"
                :key="col.name"
                :class="[
                    'flex items-center gap-2 px-3 py-1.5 text-xs group',
                    col.key === 'PRI' ? 'bg-amber-400/5' : ''
                ]"
            >
                <!-- Key icon -->
                <span class="w-3.5 flex-shrink-0 text-center">
                    <span v-if="col.key === 'PRI'" class="text-amber-400 text-[10px]">🔑</span>
                    <span v-else-if="col.key === 'MUL'" class="text-node-db text-[10px]">🔗</span>
                    <span v-else-if="col.key === 'UNI'" class="text-purple-400 text-[10px]">◆</span>
                </span>

                <!-- Column name -->
                <span :class="['font-mono flex-1 truncate', col.key === 'PRI' ? 'text-amber-300' : 'text-slate-300']">
                    {{ col.name }}
                </span>

                <!-- Type -->
                <span class="font-mono text-slate-600 text-[10px] flex-shrink-0 group-hover:text-slate-400 transition-colors">
                    {{ shortType(col.type) }}
                </span>

                <!-- Nullable dot -->
                <span v-if="col.nullable" class="w-1.5 h-1.5 rounded-full bg-slate-700 flex-shrink-0" title="nullable" />
                <span v-else class="w-1.5 h-1.5 rounded-full bg-slate-600 flex-shrink-0" title="NOT NULL" />
            </div>
        </div>

        <!-- Footer: index count -->
        <div v-if="data.indexes?.length && expanded" class="px-3 py-1.5 border-t border-canvas-border/40 flex gap-2 flex-wrap">
            <span
                v-for="idx in data.indexes.slice(0, 5)"
                :key="idx.name"
                :class="[
                    'text-[10px] px-1.5 py-0.5 rounded font-mono',
                    idx.primary ? 'text-amber-400 bg-amber-400/10' :
                    idx.unique  ? 'text-purple-400 bg-purple-400/10' :
                                  'text-slate-500 bg-canvas-bg'
                ]"
            >
                {{ idx.primary ? 'PK' : idx.unique ? 'UQ' : 'IDX' }}
            </span>
            <span v-if="data.indexes.length > 5" class="text-[10px] text-slate-600">+{{ data.indexes.length - 5 }}</span>
        </div>

        <!-- Vue Flow handles -->
        <Handle type="target" :position="Position.Left"  class="!w-2 !h-2 !bg-node-db !border-canvas-bg" />
        <Handle type="source" :position="Position.Right" class="!w-2 !h-2 !bg-node-db !border-canvas-bg" />
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Handle, Position } from '@vue-flow/core'

const props = defineProps({
    data:     { type: Object, required: true },
    selected: { type: Boolean, default: false },
})

const expanded = ref(false)

const primaryCols = computed(() =>
    (props.data.columns ?? []).filter(c => c.key === 'PRI').slice(0, 3)
)

function toggleExpanded() {
    expanded.value = !expanded.value
}

function formatCount(n) {
    if (n == null) return '—'
    if (n >= 1_000_000) return (n / 1_000_000).toFixed(1) + 'M'
    if (n >= 1_000) return (n / 1_000).toFixed(1) + 'K'
    return n.toString()
}

function shortType(type) {
    if (! type) return ''
    // Strip length suffixes: varchar(255) → varchar
    return type.replace(/\(\d+\)/g, '').replace(/\s+unsigned$/, '').trim().toLowerCase()
}
</script>

<style scoped>
.table-node { font-family: 'JetBrains Mono', monospace; }
</style>
