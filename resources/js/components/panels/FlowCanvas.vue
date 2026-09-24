<template>
    <CanvasShell
        :nodes="nodes"
        :edges="edges"
        :loading="loading"
        :error="null"
        :is-empty="false"
        :flow-props="flowProps"
        @node-click="onNodeClick"
        @pane-click="appStore.closeDetailPanel()"
    >
        <template #node-flowNode="{ data, selected }">
            <FlowNode :data="data" :selected="selected" />
        </template>

        <template #actions>
            <div class="flex items-center gap-1.5">
                <button
                    v-for="view in flowViews" :key="view.id"
                    :class="[
                        'text-[10px] px-2.5 py-1 rounded-lg border font-medium transition-all',
                        activeFlow === view.id
                            ? 'bg-accent-cyan/10 text-accent-cyan border-accent-cyan/30'
                            : 'bg-canvas-panel/80 text-slate-500 border-canvas-border hover:text-slate-300'
                    ]"
                    @click="activeFlow = view.id"
                >{{ view.label }}</button>
            </div>
        </template>
    </CanvasShell>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { api }           from '@/composables/useApi'
import { useAppStore }   from '@/stores/app'
import CanvasShell       from '@/components/ui/CanvasShell.vue'
import FlowNode          from '@/components/nodes/FlowNode.vue'

const appStore  = useAppStore()
const activeFlow = ref('request')
const loading   = ref(false)
const liveData  = ref(null)

const flowViews = [
    { id: 'request',  label: 'Request Flow' },
    { id: 'queue',    label: 'Queue Flow' },
    { id: 'event',    label: 'Event Flow' },
]

const flowProps = {
    minZoom: 0.15, maxZoom: 1.5,
    fitViewOnInit: true,
    deleteKeyCode: null,
    panOnScroll: true,
}

// ── Request lifecycle flow ────────────────────────────────────────────────
const requestFlowNodes = computed(() => {
    const middleware = liveData.value?.middleware?.groups?.web?.middleware?.slice(0, 5).map(m => m.short_name) ?? []
    const routes     = liveData.value?.routes?.summary?.controllers_used?.slice(0, 4).map(c => c.split('\\').pop()) ?? []
    const models     = liveData.value?.models?.models?.slice(0, 4).map(m => m.short_name) ?? []

    const layers = [
        { id: 'internet',   icon: '🌐', label: 'Internet',       sublabel: 'HTTP Request',        color: '#64748b', items: [],           x: 60  },
        { id: 'webserver',  icon: '🖥️',  label: 'Web Server',     sublabel: 'Nginx / Apache',      color: '#64748b', items: [],           x: 320 },
        { id: 'bootstrap',  icon: '⚡',  label: 'Bootstrap',      sublabel: 'app()->boot()',        color: '#00d4ff', items: ['Kernel','ServiceProviders'], x: 580 },
        { id: 'middleware', icon: '🛡️',  label: 'Middleware',     sublabel: 'Pipeline',            color: '#06b6d4', items: middleware,   x: 840 },
        { id: 'router',     icon: '🗺️',  label: 'Router',         sublabel: 'Route::match()',      color: '#f59e0b', items: [],           x: 1100 },
        { id: 'controller', icon: '🎮', label: 'Controller',     sublabel: 'Action / Invokable',  color: '#a855f7', items: routes,       x: 1360 },
        { id: 'service',    icon: '⚙️',  label: 'Service Layer',  sublabel: 'Business logic',      color: '#8b5cf6', items: [],           x: 1620 },
        { id: 'model',      icon: '🗃️',  label: 'Eloquent ORM',   sublabel: 'Query Builder',       color: '#10b981', items: models,       x: 1880 },
        { id: 'database',   icon: '🗄️',  label: 'Database',       sublabel: 'MySQL / SQLite',      color: '#0ea5e9', items: [],           x: 2140 },
        { id: 'response',   icon: '📦', label: 'Response',       sublabel: 'JSON / View',         color: '#00d4ff', items: [],           x: 2400 },
    ]

    return layers.map((l, i) => ({
        id:   `flow-req-${l.id}`,
        type: 'flowNode',
        data: { icon: l.icon, label: l.label, sublabel: l.sublabel, color: l.color, items: l.items },
        position: { x: l.x, y: 200 },
    }))
})

const requestFlowEdges = computed(() => {
    const ids = requestFlowNodes.value.map(n => n.id)
    return ids.slice(0, -1).map((id, i) => ({
        id:     `req-edge-${i}`,
        source: id,
        target: ids[i + 1],
        type:   'smoothstep',
        animated: true,
        style:  { stroke: '#00d4ff', strokeWidth: 2, opacity: 0.5 },
        markerEnd: { type: 'arrowclosed', color: '#00d4ff', width: 12, height: 12 },
    }))
})

// ── Queue flow ────────────────────────────────────────────────────────────
const queueFlowNodes = computed(() => {
    const jobs = liveData.value?.jobs?.jobs?.slice(0, 3).map(j => j.short_name) ?? []
    const layers = [
        { id: 'dispatch',  icon: '📤', label: 'Dispatch',      sublabel: 'Job::dispatch()',   color: '#f97316', items: [], x: 60  },
        { id: 'serialize', icon: '📦', label: 'Serialize',     sublabel: 'Payload builder',   color: '#f59e0b', items: [], x: 320 },
        { id: 'queue',     icon: '📋', label: 'Queue Driver',  sublabel: 'Redis / SQS / DB',  color: '#0ea5e9', items: [], x: 580 },
        { id: 'worker',    icon: '👷', label: 'Queue Worker',  sublabel: 'php artisan queue:work', color: '#10b981', items: [], x: 840 },
        { id: 'job',       icon: '⚡', label: 'Job Handler',   sublabel: 'handle() method',   color: '#f97316', items: jobs, x: 1100 },
        { id: 'complete',  icon: '✅', label: 'Complete',      sublabel: 'ack / delete',       color: '#10b981', items: [], x: 1360 },
    ]
    return layers.map(l => ({
        id: `flow-q-${l.id}`, type: 'flowNode',
        data: { icon: l.icon, label: l.label, sublabel: l.sublabel, color: l.color, items: l.items },
        position: { x: l.x, y: 200 },
    }))
})

const queueFlowEdges = computed(() => {
    const ids = queueFlowNodes.value.map(n => n.id)
    return ids.slice(0, -1).map((id, i) => ({
        id: `q-edge-${i}`, source: id, target: ids[i + 1], type: 'smoothstep', animated: true,
        style: { stroke: '#f97316', strokeWidth: 2, opacity: 0.5 },
        markerEnd: { type: 'arrowclosed', color: '#f97316', width: 12, height: 12 },
    }))
})

// ── Event flow ────────────────────────────────────────────────────────────
const eventFlowNodes = computed(() => {
    const events = liveData.value?.events?.events?.slice(0, 3) ?? []
    const result = []
    let x = 60

    // Trigger node
    result.push({
        id: 'flow-ev-trigger', type: 'flowNode',
        data: { icon: '💥', label: 'Event Trigger', sublabel: 'event(new Foo)', color: '#ec4899', items: [] },
        position: { x, y: 200 },
    })
    x += 260

    // Event dispatcher
    result.push({
        id: 'flow-ev-dispatcher', type: 'flowNode',
        data: { icon: '📡', label: 'Dispatcher', sublabel: 'EventServiceProvider', color: '#a855f7', items: [] },
        position: { x, y: 200 },
    })
    x += 260

    // Each event + listeners
    events.forEach((ev, ei) => {
        result.push({
            id: `flow-ev-${ei}`, type: 'flowNode',
            data: { icon: '⚡', label: ev.short_name, sublabel: `${ev.listeners?.length ?? 0} listeners`, color: '#ec4899', items: [] },
            position: { x, y: 100 + ei * 220 },
        })
        ;(ev.listeners ?? []).slice(0, 2).forEach((l, li) => {
            result.push({
                id: `flow-evl-${ei}-${li}`, type: 'flowNode',
                data: { icon: l.is_queued ? '⚡' : '👂', label: l.short_name ?? 'Closure', sublabel: l.is_queued ? 'Queued' : 'Sync', color: l.is_queued ? '#f97316' : '#ec4899', items: [] },
                position: { x: x + 260, y: 60 + ei * 220 + li * 130 },
            })
        })
    })

    return result
})

const eventFlowEdges = computed(() => {
    const result = []
    result.push({
        id: 'ev-e0', source: 'flow-ev-trigger', target: 'flow-ev-dispatcher',
        type: 'smoothstep', animated: true,
        style: { stroke: '#ec4899', strokeWidth: 2, opacity: 0.5 },
        markerEnd: { type: 'arrowclosed', color: '#ec4899', width: 12, height: 12 },
    })
    const events = liveData.value?.events?.events?.slice(0, 3) ?? []
    events.forEach((ev, ei) => {
        result.push({
            id: `ev-e${ei + 1}`, source: 'flow-ev-dispatcher', target: `flow-ev-${ei}`,
            type: 'smoothstep', animated: false,
            style: { stroke: '#a855f7', strokeWidth: 1.5, opacity: 0.5 },
            markerEnd: { type: 'arrowclosed', color: '#a855f7', width: 10, height: 10 },
        })
        ;(ev.listeners ?? []).slice(0, 2).forEach((l, li) => {
            result.push({
                id: `ev-el-${ei}-${li}`, source: `flow-ev-${ei}`, target: `flow-evl-${ei}-${li}`,
                type: 'smoothstep', animated: l.is_queued,
                style: { stroke: l.is_queued ? '#f97316' : '#ec4899', strokeWidth: 1.5, opacity: 0.5 },
                markerEnd: { type: 'arrowclosed', color: l.is_queued ? '#f97316' : '#ec4899', width: 10, height: 10 },
            })
        })
    })
    return result
})

// ── Active selection ──────────────────────────────────────────────────────
const nodes = computed(() => {
    if (activeFlow.value === 'queue') return queueFlowNodes.value
    if (activeFlow.value === 'event') return eventFlowNodes.value
    return requestFlowNodes.value
})

const edges = computed(() => {
    if (activeFlow.value === 'queue') return queueFlowEdges.value
    if (activeFlow.value === 'event') return eventFlowEdges.value
    return requestFlowEdges.value
})

function onNodeClick() {}

onMounted(async () => {
    loading.value = true
    try {
        const res = await api.get('/')
        liveData.value = res.data.data
    } catch {
        // Use static flow if API fails
    } finally {
        loading.value = false
    }
})
</script>
