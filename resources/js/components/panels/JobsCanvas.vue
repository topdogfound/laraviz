<template>
    <CanvasShell
        :nodes="nodes"
        :edges="[]"
        :loading="loading"
        :error="error"
        :is-empty="!loading && nodes.length === 0"
        loading-text="Discovering queue jobs..."
        empty-text="No queued jobs found in this application."
        @node-click="onNodeClick"
        @pane-click="appStore.closeDetailPanel()"
        @retry="load"
        :flow-props="flowProps"
    >
        <template #node-jobNode="{ data, selected }">
            <JobNode :data="data" :selected="selected" />
        </template>

        <template #actions>
            <div v-if="raw" class="flex items-center gap-1.5">
                <!-- Queue filter pills -->
                <button
                    v-for="q in queueNames" :key="q"
                    :class="[
                        'text-[10px] px-2 py-1 rounded font-mono border transition-all',
                        filterQueue === q
                            ? 'bg-node-job/15 text-node-job border-node-job/30'
                            : 'bg-canvas-panel/80 text-slate-500 border-canvas-border hover:text-slate-300'
                    ]"
                    @click="filterQueue = filterQueue === q ? null : q"
                >{{ q ?? 'default' }}</button>

                <div class="lv-badge bg-canvas-panel/90 border border-canvas-border text-[10px] font-mono text-slate-500 backdrop-blur-sm gap-1.5 ml-1">
                    <span class="text-node-job">{{ raw.summary?.queued ?? 0 }}</span> queued
                    <span class="text-slate-600">·</span>
                    <span class="text-slate-400">{{ raw.summary?.synchronous ?? 0 }}</span> sync
                </div>
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
import JobNode           from '@/components/nodes/JobNode.vue'

const appStore = useAppStore()
const { gridLayout } = useAutoLayout()

const raw         = ref(null)
const loading     = ref(false)
const error       = ref(null)
const filterQueue = ref(null)

const flowProps = {
    minZoom: 0.1, maxZoom: 2, fitViewOnInit: true, deleteKeyCode: null,
}

const queueNames = computed(() => raw.value?.summary?.queues ?? [])

const nodes = computed(() => {
    if (!raw.value?.jobs) return []
    const q = appStore.searchQuery.toLowerCase().trim()
    let jobs = raw.value.jobs

    if (q) jobs = jobs.filter(j => j.short_name.toLowerCase().includes(q))
    if (filterQueue.value !== null) jobs = jobs.filter(j => (j.queue ?? null) === filterQueue.value)

    // Sort: queued first, then by name
    jobs = [...jobs].sort((a, b) => (b.is_queued ? 1 : 0) - (a.is_queued ? 1 : 0) || a.short_name.localeCompare(b.short_name))

    const raw2 = jobs.map(j => ({
        id: `job-${j.class}`, type: 'jobNode', data: { ...j }, position: { x: 0, y: 0 },
    }))

    return gridLayout(raw2, { cols: 5, colWidth: 230, rowHeight: 140, padding: 60 })
})

function onNodeClick(node) {
    appStore.setDetailPanel({ title: node.data.short_name, type: 'job', data: node.data })
}

async function load() {
    loading.value = true; error.value = null
    try {
        const res = await api.get('/jobs')
        raw.value = res.data.data
    } catch (e) { error.value = e.message }
    finally { loading.value = false }
}

onMounted(load)
</script>
