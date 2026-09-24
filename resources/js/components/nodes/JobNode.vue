<template>
    <div
        :class="[
            'job-node w-48 rounded-xl border transition-all duration-200 cursor-pointer select-none overflow-hidden',
            selected
                ? 'border-node-job shadow-[0_0_0_2px_rgba(249,115,22,0.5),0_0_30px_rgba(249,115,22,0.1)]'
                : 'border-canvas-border shadow-node hover:border-node-job/50'
        ]"
        style="background: #0a1628"
    >
        <div class="flex items-center gap-2 px-3 py-2.5"
             :style="{ background: 'linear-gradient(135deg, rgba(249,115,22,0.1) 0%, rgba(249,115,22,0.03) 100%)' }">
            <div class="w-5 h-5 rounded flex-shrink-0 flex items-center justify-center"
                 style="background: rgba(249,115,22,0.15); border: 1px solid rgba(249,115,22,0.3)">
                <svg class="w-3 h-3 text-node-job" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                    <rect x="2" y="4" width="12" height="9" rx="1.5"/>
                    <path d="M5 4V3a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v1"/>
                    <line x1="8" y1="7" x2="8" y2="10"/>
                    <line x1="6.5" y1="8.5" x2="9.5" y2="8.5"/>
                </svg>
            </div>
            <span class="text-sm font-semibold text-slate-100 truncate flex-1">{{ data.short_name }}</span>
        </div>

        <div class="px-3 py-2 flex flex-wrap gap-1.5">
            <span v-if="data.is_queued"    class="text-[9px] px-1.5 py-0.5 rounded bg-orange-500/10 text-orange-400 border border-orange-500/20">Queued</span>
            <span v-if="!data.is_queued"   class="text-[9px] px-1.5 py-0.5 rounded bg-slate-500/10  text-slate-400  border border-canvas-border">Sync</span>
            <span v-if="data.is_unique"    class="text-[9px] px-1.5 py-0.5 rounded bg-purple-500/10 text-purple-400 border border-purple-500/20">Unique</span>
            <span v-if="data.queue" class="text-[9px] px-1.5 py-0.5 rounded font-mono bg-canvas-bg text-slate-500 border border-canvas-border">{{ data.queue }}</span>
        </div>
    </div>

    <Handle type="target" :position="Position.Left"  class="!w-2 !h-2 !bg-node-job !border-canvas-bg" />
    <Handle type="source" :position="Position.Right" class="!w-2 !h-2 !bg-node-job !border-canvas-bg" />
</template>

<script setup>
import { Handle, Position } from '@vue-flow/core'
defineProps({ data: Object, selected: Boolean })
</script>
