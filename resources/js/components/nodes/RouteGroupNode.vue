<template>
    <div
        :class="[
            'route-group-node w-52 rounded-xl border-2 transition-all duration-200 cursor-pointer select-none',
            selected
                ? 'border-node-route/70 shadow-[0_0_0_2px_rgba(245,158,11,0.4),0_0_40px_rgba(245,158,11,0.12)]'
                : 'border-canvas-border hover:border-node-route/40',
            data.is_api ? 'border-purple-500/30' : ''
        ]"
        style="background: rgba(10,22,40,0.95)"
    >
        <!-- Header -->
        <div class="flex items-center gap-2 px-3 py-2.5 border-b border-canvas-border/70"
             :style="{ background: data.is_api
                ? 'linear-gradient(135deg, rgba(168,85,247,0.1) 0%, rgba(168,85,247,0.03) 100%)'
                : 'linear-gradient(135deg, rgba(245,158,11,0.1) 0%, rgba(245,158,11,0.03) 100%)' }">

            <svg class="w-4 h-4 flex-shrink-0" :class="data.is_api ? 'text-purple-400' : 'text-node-route'"
                 viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                <circle cx="3" cy="3" r="1.5"/>
                <circle cx="13" cy="13" r="1.5"/>
                <path d="M4.5 3h3a3 3 0 0 1 3 3v4a3 3 0 0 0 3 3"/>
            </svg>

            <span class="text-sm font-semibold font-mono truncate flex-1"
                  :class="data.is_api ? 'text-purple-300' : 'text-amber-300'">
                /{{ data.prefix || 'root' }}
            </span>

            <span class="lv-badge text-[9px]"
                  :class="data.is_api
                    ? 'bg-purple-500/15 text-purple-400 border border-purple-500/25'
                    : 'bg-amber-500/15  text-amber-400  border border-amber-500/25'">
                {{ data.is_api ? 'API' : 'WEB' }}
            </span>
        </div>

        <!-- Stats -->
        <div class="px-3 py-2 flex items-center justify-between">
            <div class="flex items-center gap-1 text-[11px] text-slate-400">
                <svg class="w-3 h-3 text-slate-600" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                    <line x1="2" y1="8" x2="14" y2="8"/>
                    <polyline points="9 3 14 8 9 13"/>
                </svg>
                <span class="font-mono">{{ data.route_count }}</span>
                <span class="text-slate-600">routes</span>
            </div>

            <!-- Method breakdown -->
            <div class="flex gap-1">
                <span v-for="(count, method) in methodBreakdown" :key="method"
                      :class="methodDotClass(method)"
                      class="w-1.5 h-1.5 rounded-full"
                      :title="`${count} ${method}`"
                />
            </div>
        </div>

        <Handle type="target" :position="Position.Left"  class="!w-2 !h-2 !bg-node-route !border-canvas-bg" />
        <Handle type="source" :position="Position.Right" class="!w-2 !h-2 !bg-node-route !border-canvas-bg" />
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { Handle, Position } from '@vue-flow/core'

const props = defineProps({
    data:     { type: Object, required: true },
    selected: { type: Boolean, default: false },
})

const methodBreakdown = computed(() => {
    const counts = {}
    for (const route of props.data.routes ?? []) {
        for (const m of route.methods ?? []) {
            if (m !== 'HEAD') counts[m] = (counts[m] ?? 0) + 1
        }
    }
    return counts
})

function methodDotClass(method) {
    const map = {
        GET: 'bg-emerald-400', POST: 'bg-blue-400',
        PUT: 'bg-amber-400', PATCH: 'bg-orange-400',
        DELETE: 'bg-red-400',
    }
    return map[method] ?? 'bg-slate-500'
}
</script>
