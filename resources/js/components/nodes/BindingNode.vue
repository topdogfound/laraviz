<template>
    <div
        :class="[
            'binding-node w-64 rounded-xl border transition-all duration-200 cursor-pointer select-none overflow-hidden',
            selected
                ? 'border-accent-cyan/60 shadow-[0_0_0_2px_rgba(0,212,255,0.3)]'
                : 'border-canvas-border shadow-node hover:border-accent-cyan/30'
        ]"
        style="background: #0a1628"
    >
        <div class="flex items-start gap-2 px-3 py-2.5">
            <span class="text-[9px] px-1.5 py-1 rounded font-mono mt-0.5 flex-shrink-0"
                  :class="data.shared ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-canvas-bg text-slate-500 border border-canvas-border'">
                {{ data.shared ? 'singleton' : 'bind' }}
            </span>
            <div class="flex-1 min-w-0">
                <div class="text-[11px] font-mono text-slate-300 truncate">{{ shortName(data.abstract) }}</div>
                <div v-if="data.concrete && data.concrete !== 'Closure'" class="text-[10px] font-mono text-slate-600 truncate mt-0.5">
                    → {{ data.concrete }}
                </div>
                <div v-else-if="data.concrete === 'Closure'" class="text-[10px] font-mono text-slate-600 mt-0.5">→ Closure</div>
            </div>
            <span v-if="data.is_interface" class="text-[9px] px-1 py-0.5 rounded bg-accent-cyan/5 text-accent-cyan/60 border border-accent-cyan/15 flex-shrink-0">IF</span>
        </div>
    </div>
    <Handle type="target" :position="Position.Left"  class="!w-2 !h-2 !bg-accent-cyan !border-canvas-bg" />
    <Handle type="source" :position="Position.Right" class="!w-2 !h-2 !bg-accent-cyan !border-canvas-bg" />
</template>
<script setup>
import { Handle, Position } from '@vue-flow/core'
const props = defineProps({ data: Object, selected: Boolean })
function shortName(fqn) {
    if (!fqn) return ''
    const parts = fqn.split('\\')
    return parts.length > 2 ? '…\\' + parts.slice(-2).join('\\') : fqn
}
</script>
