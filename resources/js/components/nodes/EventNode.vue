<template>
    <div
        :class="[
            'event-node w-52 rounded-xl border transition-all duration-200 cursor-pointer select-none overflow-hidden',
            selected
                ? 'border-node-event shadow-[0_0_0_2px_rgba(236,72,153,0.5),0_0_30px_rgba(236,72,153,0.1)]'
                : 'border-canvas-border shadow-node hover:border-node-event/50'
        ]"
        style="background: #0a1628"
    >
        <!-- Header -->
        <div class="flex items-center gap-2 px-3 py-2.5 border-b border-canvas-border/70"
             :style="{ background: 'linear-gradient(135deg, rgba(236,72,153,0.1) 0%, rgba(236,72,153,0.03) 100%)' }">
            <div class="w-5 h-5 rounded flex-shrink-0 flex items-center justify-center"
                 style="background: rgba(236,72,153,0.15); border: 1px solid rgba(236,72,153,0.3)">
                <svg class="w-3 h-3 text-node-event" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M9 1L5 9h4l-2 6 7-9H9l2-5z"/>
                </svg>
            </div>
            <span class="text-sm font-semibold text-slate-100 truncate flex-1">{{ data.short_name }}</span>
            <span v-if="data.details?.broadcasts" class="text-[9px] px-1 py-0.5 rounded bg-pink-400/10 text-pink-400 border border-pink-400/20">📡</span>
        </div>

        <!-- Listeners -->
        <div class="px-3 py-2 space-y-1">
            <div
                v-for="l in (data.listeners ?? []).slice(0, 4)"
                :key="l.class ?? 'closure'"
                class="flex items-center gap-1.5 text-[11px]"
            >
                <span :class="l.is_queued ? 'text-node-job' : 'text-slate-600'" class="flex-shrink-0">
                    {{ l.is_queued ? '⚡' : '•' }}
                </span>
                <span class="text-slate-400 font-mono truncate">{{ l.short_name ?? 'Closure' }}</span>
            </div>
            <div v-if="!data.listeners?.length" class="text-[10px] text-slate-600">No listeners</div>
            <div v-if="data.listeners?.length > 4" class="text-[10px] text-slate-600">
                +{{ data.listeners.length - 4 }} more
            </div>
        </div>
    </div>

    <Handle type="target" :position="Position.Left"  class="!w-2 !h-2 !bg-node-event !border-canvas-bg" />
    <Handle type="source" :position="Position.Right" class="!w-2 !h-2 !bg-node-event !border-canvas-bg" />
</template>

<script setup>
import { Handle, Position } from '@vue-flow/core'
defineProps({ data: Object, selected: Boolean })
</script>
