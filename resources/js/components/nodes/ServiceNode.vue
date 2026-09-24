<template>
    <div
        :class="[
            'service-node w-52 rounded-xl border transition-all duration-200 cursor-pointer select-none overflow-hidden',
            selected
                ? 'border-node-service shadow-[0_0_0_2px_rgba(168,85,247,0.5),0_0_30px_rgba(168,85,247,0.1)]'
                : 'border-canvas-border shadow-node hover:border-node-service/50'
        ]"
        style="background: #0a1628"
    >
        <div class="flex items-center gap-2 px-3 py-2.5"
             :style="{ background: 'linear-gradient(135deg, rgba(168,85,247,0.1) 0%, rgba(168,85,247,0.03) 100%)' }">
            <div class="w-5 h-5 rounded flex-shrink-0 flex items-center justify-center"
                 style="background: rgba(168,85,247,0.15); border: 1px solid rgba(168,85,247,0.3)">
                <svg class="w-3 h-3 text-node-service" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                    <circle cx="8" cy="8" r="2.5"/>
                    <path d="M8 1v2M8 13v2M1 8h2M13 8h2"/>
                </svg>
            </div>
            <span class="text-sm font-semibold text-slate-100 truncate flex-1">{{ data.short_name }}</span>
            <span v-if="data.is_deferred" class="text-[9px] px-1 py-0.5 rounded bg-amber-400/10 text-amber-400 border border-amber-400/20">Deferred</span>
        </div>

        <div class="px-3 py-1.5 text-[10px] font-mono text-slate-500 truncate border-t border-canvas-border/40">
            {{ shortClass(data.class) }}
        </div>
    </div>

    <Handle type="target" :position="Position.Left"  class="!w-2 !h-2 !bg-node-service !border-canvas-bg" />
    <Handle type="source" :position="Position.Right" class="!w-2 !h-2 !bg-node-service !border-canvas-bg" />
</template>

<script setup>
import { Handle, Position } from '@vue-flow/core'
const props = defineProps({ data: Object, selected: Boolean })

function shortClass(cls) {
    if (!cls) return ''
    const parts = cls.split('\\')
    return parts.slice(-2).join('\\')
}
</script>
