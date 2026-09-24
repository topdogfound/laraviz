<template>
    <div
        :class="[
            'model-node w-56 rounded-xl border transition-all duration-200 cursor-pointer select-none overflow-hidden',
            selected
                ? 'border-node-model shadow-[0_0_0_2px_rgba(16,185,129,0.5),0_0_40px_rgba(16,185,129,0.12)]'
                : 'border-canvas-border shadow-node hover:border-node-model/50'
        ]"
        style="background: #0a1628"
    >
        <!-- Header -->
        <div class="flex items-center gap-2 px-3 py-2.5 border-b border-canvas-border/70"
             :style="{ background: 'linear-gradient(135deg, rgba(16,185,129,0.12) 0%, rgba(16,185,129,0.04) 100%)' }">
            <div class="w-5 h-5 rounded flex-shrink-0 flex items-center justify-center"
                 style="background: rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.3)">
                <svg class="w-3 h-3 text-node-model" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                    <rect x="1" y="1" width="6" height="6" rx="1.5"/>
                    <rect x="9" y="1" width="6" height="6" rx="1.5"/>
                    <rect x="1" y="9" width="6" height="6" rx="1.5"/>
                    <rect x="9" y="9" width="6" height="6" rx="1.5"/>
                </svg>
            </div>
            <span class="text-sm font-semibold text-slate-100 truncate flex-1">{{ data.short_name }}</span>

            <!-- Badges -->
            <span v-if="data.soft_deletes" class="text-[9px] text-red-400 bg-red-400/10 px-1 rounded border border-red-400/20">SD</span>
        </div>

        <!-- Table name -->
        <div class="px-3 py-1.5 border-b border-canvas-border/40 text-[10px] font-mono text-slate-500 flex items-center gap-1.5">
            <svg class="w-2.5 h-2.5 text-slate-600" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                <ellipse cx="8" cy="4" rx="6" ry="2"/><path d="M2 4v8c0 1.1 2.7 2 6 2s6-.9 6-2V4"/>
            </svg>
            {{ data.table }}
        </div>

        <!-- Relations -->
        <div v-if="data.relations?.length" class="px-3 py-2 space-y-1">
            <div
                v-for="rel in data.relations.slice(0, 5)"
                :key="rel.method"
                class="flex items-center gap-1.5 text-[11px]"
            >
                <span :class="['w-1.5 h-1.5 rounded-full flex-shrink-0', relColor(rel.type)]" />
                <span class="text-slate-400 font-mono truncate">{{ rel.method }}()</span>
                <span class="ml-auto text-slate-600 text-[10px] flex-shrink-0">{{ shortRelType(rel.type) }}</span>
            </div>
            <div v-if="data.relations.length > 5" class="text-[10px] text-slate-600 pt-0.5">
                +{{ data.relations.length - 5 }} more
            </div>
        </div>
        <div v-else class="px-3 py-2 text-[10px] text-slate-600">No relations</div>

        <!-- Traits pills -->
        <div v-if="appTraits.length" class="px-3 pb-2 flex flex-wrap gap-1">
            <span
                v-for="t in appTraits"
                :key="t"
                class="text-[9px] px-1.5 py-0.5 rounded font-mono text-emerald-400 bg-emerald-400/5 border border-emerald-400/15"
            >{{ t }}</span>
        </div>

        <Handle type="target" :position="Position.Left"  class="!w-2 !h-2 !bg-node-model !border-canvas-bg" />
        <Handle type="source" :position="Position.Right" class="!w-2 !h-2 !bg-node-model !border-canvas-bg" />
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { Handle, Position } from '@vue-flow/core'

const props = defineProps({
    data:     { type: Object, required: true },
    selected: { type: Boolean, default: false },
})

const SYSTEM_TRAITS = ['HasFactory', 'SoftDeletes', 'Notifiable', 'HasUuids']
const appTraits = computed(() =>
    (props.data.traits ?? []).filter(t => !SYSTEM_TRAITS.includes(t)).slice(0, 4)
)

function relColor(type) {
    if (!type) return 'bg-slate-600'
    if (type.includes('HasMany') || type.includes('MorphMany')) return 'bg-node-model'
    if (type.includes('HasOne')  || type.includes('MorphOne'))  return 'bg-emerald-300'
    if (type.includes('BelongsToMany')) return 'bg-purple-400'
    if (type.includes('BelongsTo'))     return 'bg-blue-400'
    return 'bg-slate-500'
}

function shortRelType(type) {
    if (!type) return ''
    const map = {
        HasMany: '1→N', HasOne: '1→1',
        BelongsTo: 'N→1', BelongsToMany: 'N→N',
        MorphMany: '1→N*', MorphOne: '1→1*',
        MorphTo: '*→1', HasOneThrough: '1→1→1',
        HasManyThrough: '1→N→N',
    }
    for (const [k, v] of Object.entries(map)) {
        if (type.includes(k)) return v
    }
    return type
}
</script>
