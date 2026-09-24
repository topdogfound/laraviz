<template>
    <div
        :class="[
            'route-node min-w-[200px] rounded-xl border transition-all duration-200 cursor-pointer select-none overflow-hidden',
            selected
                ? 'border-node-route shadow-[0_0_0_2px_rgba(245,158,11,0.5),0_0_30px_rgba(245,158,11,0.1)]'
                : 'border-canvas-border shadow-node hover:border-node-route/50'
        ]"
        style="background: #0a1628"
    >
        <!-- Method badges + URI -->
        <div class="flex items-start gap-2 px-3 py-2.5">
            <div class="flex flex-col gap-1 flex-shrink-0 mt-0.5">
                <span
                    v-for="m in httpMethods"
                    :key="m"
                    :class="methodClass(m)"
                    class="lv-badge text-[9px] py-0.5 px-1.5"
                >{{ m }}</span>
            </div>
            <div class="flex-1 min-w-0">
                <div class="text-xs font-mono text-slate-200 break-all leading-snug">
                    /{{ data.uri }}
                </div>
                <div v-if="data.name" class="text-[10px] font-mono text-slate-600 mt-0.5 truncate">
                    {{ data.name }}
                </div>
            </div>
        </div>

        <!-- Controller -->
        <div v-if="data.controller" class="px-3 pb-2.5 border-t border-canvas-border/40 pt-2">
            <div class="text-[10px] text-slate-500 truncate font-mono">
                <span class="text-node-route">{{ data.controller }}</span>
                <span v-if="data.method" class="text-slate-600">@{{ data.method }}</span>
            </div>
        </div>

        <!-- Middleware pills -->
        <div v-if="appMiddleware.length" class="px-3 pb-2 flex flex-wrap gap-1">
            <span
                v-for="mw in appMiddleware"
                :key="mw"
                class="text-[9px] px-1.5 py-0.5 rounded font-mono text-node-middleware bg-node-middleware/5 border border-node-middleware/15"
            >{{ mw }}</span>
        </div>

        <!-- API tag -->
        <div v-if="data.is_api" class="absolute top-2 right-2">
            <span class="text-[9px] font-mono px-1 py-0.5 rounded bg-purple-500/15 text-purple-400 border border-purple-500/20">API</span>
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

const httpMethods = computed(() =>
    (props.data.methods ?? []).filter(m => m !== 'HEAD')
)

const CORE_MIDDLEWARE = ['web', 'api', 'auth', 'guest', 'verified', 'throttle']
const appMiddleware = computed(() =>
    (props.data.middleware ?? [])
        .filter(m => !CORE_MIDDLEWARE.includes(m))
        .slice(0, 3)
)

function methodClass(method) {
    const map = {
        GET:    'bg-emerald-500/15 text-emerald-400 border border-emerald-500/25',
        POST:   'bg-blue-500/15    text-blue-400    border border-blue-500/25',
        PUT:    'bg-amber-500/15   text-amber-400   border border-amber-500/25',
        PATCH:  'bg-orange-500/15  text-orange-400  border border-orange-500/25',
        DELETE: 'bg-red-500/15     text-red-400     border border-red-500/25',
        ANY:    'bg-slate-500/15   text-slate-400   border border-canvas-border',
    }
    return map[method] ?? map.ANY
}
</script>
