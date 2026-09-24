<template>
    <CanvasShell
        :nodes="nodes"
        :edges="edges"
        :loading="loading"
        :error="error"
        :is-empty="!loading && nodes.length === 0"
        loading-text="Scanning events & listeners..."
        empty-text="No events registered in this application."
        @node-click="onNodeClick"
        @pane-click="appStore.closeDetailPanel()"
        @retry="load"
        :flow-props="flowProps"
    >
        <template #node-eventNode="{ data, selected }">
            <EventNode :data="data" :selected="selected" />
        </template>
        <template #node-listenerNode="{ data, selected }">
            <ListenerNode :data="data" :selected="selected" />
        </template>

        <template #actions>
            <div class="lv-badge bg-canvas-panel/90 border border-canvas-border text-[10px] font-mono text-slate-500 backdrop-blur-sm gap-1.5">
                <span class="text-node-event">{{ raw?.total ?? 0 }}</span> events
                <span class="text-slate-600">·</span>
                <span class="text-slate-400">{{ raw?.summary?.total_listeners ?? 0 }}</span> listeners
                <span v-if="queuedListeners" class="text-slate-600">·</span>
                <span v-if="queuedListeners" class="text-node-job">{{ queuedListeners }}</span>
                <span v-if="queuedListeners" class="text-slate-500">queued</span>
            </div>
        </template>
    </CanvasShell>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { api }           from '@/composables/useApi'
import { useAppStore }   from '@/stores/app'
import { useAutoLayout } from '@/composables/useCanvas'
import CanvasShell       from '@/components/ui/CanvasShell.vue'
import EventNode         from '@/components/nodes/EventNode.vue'
import ListenerNode      from '@/components/nodes/ListenerNode.vue'

const appStore = useAppStore()
const { gridLayout } = useAutoLayout()

const raw     = ref(null)
const loading = ref(false)
const error   = ref(null)

const flowProps = {
    minZoom: 0.05, maxZoom: 2, fitViewOnInit: true, deleteKeyCode: null,
}

const queuedListeners = computed(() => {
    if (!raw.value?.events) return 0
    return raw.value.events.reduce((s, e) =>
        s + (e.listeners ?? []).filter(l => l.is_queued).length, 0
    )
})

// Build nodes: each event + its listeners arranged in fan-out layout
const nodes = computed(() => {
    if (!raw.value?.events) return []
    const q = appStore.searchQuery.toLowerCase().trim()
    let events = raw.value.events
    if (q) events = events.filter(e => e.short_name.toLowerCase().includes(q))

    const result = []
    let xOffset = 60

    for (const event of events) {
        // Event node
        result.push({
            id:   `event-${event.event}`,
            type: 'eventNode',
            data: { ...event },
            position: { x: xOffset, y: 60 },
        })

        // Listener nodes stacked to the right
        const listeners = event.listeners ?? []
        const spacing   = 130
        const totalH    = listeners.length * spacing
        const startY    = 60 - (totalH - spacing) / 2

        listeners.forEach((l, i) => {
            result.push({
                id:   `listener-${event.event}-${i}`,
                type: 'listenerNode',
                data: { ...l, eventName: event.short_name },
                position: { x: xOffset + 280, y: startY + i * spacing },
            })
        })

        xOffset += 600
    }

    return result
})

// Edges: event → each listener
const edges = computed(() => {
    if (!raw.value?.events) return []
    const result = []

    for (const event of raw.value.events) {
        ;(event.listeners ?? []).forEach((l, i) => {
            result.push({
                id:     `edge-${event.event}-${i}`,
                source: `event-${event.event}`,
                target: `listener-${event.event}-${i}`,
                type:   'smoothstep',
                animated: l.is_queued,
                style: {
                    stroke: l.is_queued ? '#f97316' : '#ec4899',
                    strokeWidth: 1.5,
                    opacity: 0.6,
                },
                markerEnd: { type: 'arrowclosed', color: l.is_queued ? '#f97316' : '#ec4899', width: 10, height: 10 },
            })
        })
    }

    return result
})

function onNodeClick(node) {
    if (node.type === 'eventNode') {
        appStore.setDetailPanel({ title: node.data.short_name, type: 'event', data: node.data })
    }
}

async function load() {
    loading.value = true
    error.value   = null
    try {
        const res = await api.get('/events')
        raw.value = res.data.data
    } catch (e) {
        error.value = e.message
    } finally {
        loading.value = false
    }
}

onMounted(load)
</script>
