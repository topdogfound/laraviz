<template>
    <div
        :class="[
            'listener-node w-48 rounded-xl border transition-all duration-200 cursor-pointer select-none overflow-hidden',
            data.is_queued
                ? 'border-node-job/40 hover:border-node-job/70'
                : 'border-canvas-border hover:border-node-event/50',
            selected ? (data.is_queued ? 'border-node-job shadow-[0_0_0_2px_rgba(249,115,22,0.4)]' : 'border-node-event shadow-[0_0_0_2px_rgba(236,72,153,0.4)]') : ''
        ]"
        style="background: #0a1628"
    >
        <div class="flex items-center gap-2 px-3 py-2.5"
             :style="{ background: data.is_queued
                ? 'linear-gradient(135deg,rgba(249,115,22,0.08) 0%,rgba(249,115,22,0.02) 100%)'
                : 'linear-gradient(135deg,rgba(236,72,153,0.08) 0%,rgba(236,72,153,0.02) 100%)' }">
            <span :class="data.is_queued ? 'text-node-job' : 'text-node-event'" class="text-xs flex-shrink-0">
                {{ data.is_queued ? '⚡' : '👂' }}
            </span>
            <span class="text-xs font-semibold text-slate-200 truncate flex-1">
                {{ data.short_name ?? 'Closure' }}
            </span>
        </div>
        <div v-if="data.method" class="px-3 py-1.5 border-t border-canvas-border/40 text-[10px] font-mono text-slate-600">
            @{{ data.method }}
        </div>
    </div>
    <Handle type="target" :position="Position.Left" class="!w-2 !h-2 !bg-node-event !border-canvas-bg" />
</template>
<script setup>
import { Handle, Position } from '@vue-flow/core'
defineProps({ data: Object, selected: Boolean })
</script>
