<template>
    <div class="absolute right-0 top-0 bottom-0 w-80 z-30 flex flex-col lv-panel border-l border-canvas-border
                shadow-panel animate-slide-in-right overflow-hidden">

        <!-- Header -->
        <div class="flex items-center justify-between px-4 py-3 border-b border-canvas-border shrink-0">
            <div class="flex items-center gap-2">
                <div :class="['w-2 h-2 rounded-full', colorDot]" />
                <span class="text-sm font-semibold text-slate-100">{{ payload.title }}</span>
            </div>
            <button class="lv-btn-ghost p-1 rounded" @click="$emit('close')">
                <svg class="w-4 h-4" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="3" y1="3" x2="13" y2="13"/>
                    <line x1="13" y1="3" x2="3" y2="13"/>
                </svg>
            </button>
        </div>

        <!-- Type badge -->
        <div class="px-4 py-2 border-b border-canvas-border shrink-0">
            <span class="lv-badge bg-canvas-hover text-slate-400 border border-canvas-border font-mono text-[10px] uppercase tracking-wider">
                {{ payload.type }}
            </span>
        </div>

        <!-- Content -->
        <div class="flex-1 overflow-y-auto p-4 space-y-4">
            <component
                :is="detailComponent"
                v-if="detailComponent"
                :data="payload.data"
            />
            <template v-else>
                <pre class="text-xs font-mono text-slate-400 whitespace-pre-wrap break-all bg-canvas-bg rounded-lg p-3 border border-canvas-border">{{ JSON.stringify(payload.data, null, 2) }}</pre>
            </template>
        </div>

    </div>
</template>

<script setup>
import { computed, defineAsyncComponent } from 'vue'

const props = defineProps({
    payload: { type: Object, required: true },
})
defineEmits(['close'])

const colorMap = {
    table:      'bg-node-db',
    model:      'bg-node-model',
    route:      'bg-node-route',
    routegroup: 'bg-node-route',
    service:    'bg-node-service',
    event:      'bg-node-event',
    job:        'bg-node-job',
    middleware: 'bg-node-middleware',
}

const colorDot = computed(() => colorMap[props.payload.type] ?? 'bg-slate-500')

const componentMap = {
    table:    () => import('./details/TableDetail.vue'),
    model:    () => import('./details/ModelDetail.vue'),
    route:    () => import('./details/RouteDetail.vue'),
    job:      () => import('./details/JobDetail.vue'),
    event:    () => import('./details/EventDetail.vue'),
    service:  () => import('./details/ServiceDetail.vue'),
}

const detailComponent = computed(() => {
    const loader = componentMap[props.payload.type]
    return loader ? defineAsyncComponent(loader) : null
})
</script>
