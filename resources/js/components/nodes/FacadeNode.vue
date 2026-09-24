<template>
    <div
        :class="[
            'facade-node w-44 rounded-xl border transition-all duration-200 cursor-pointer select-none overflow-hidden',
            selected
                ? 'border-accent-violet shadow-[0_0_0_2px_rgba(168,85,247,0.4)]'
                : 'border-canvas-border shadow-node hover:border-accent-violet/50'
        ]"
        style="background: #0a1628"
    >
        <div class="flex items-center gap-2 px-3 py-2.5"
             style="background: linear-gradient(135deg, rgba(168,85,247,0.1) 0%, rgba(168,85,247,0.03) 100%)">
            <span class="text-base leading-none flex-shrink-0">🎭</span>
            <span class="text-sm font-semibold text-slate-100 truncate flex-1">{{ data.facade }}</span>
            <span v-if="data.is_core" class="text-[9px] px-1 py-0.5 rounded bg-slate-500/10 text-slate-500 border border-canvas-border">core</span>
        </div>
        <div class="px-3 py-1.5 border-t border-canvas-border/40 text-[10px] font-mono text-slate-500 truncate">
            {{ data.accessor }}
        </div>
    </div>
    <Handle type="target" :position="Position.Left"  class="!w-2 !h-2 !bg-accent-violet !border-canvas-bg" />
    <Handle type="source" :position="Position.Right" class="!w-2 !h-2 !bg-accent-violet !border-canvas-bg" />
</template>
<script setup>
import { Handle, Position } from '@vue-flow/core'
defineProps({ data: Object, selected: Boolean })
</script>
