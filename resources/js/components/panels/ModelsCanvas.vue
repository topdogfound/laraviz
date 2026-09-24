<template>
    <CanvasShell
        :nodes="nodes"
        :edges="edges"
        :loading="modelsStore.loading"
        :error="modelsStore.error"
        :is-empty="!modelsStore.loading && nodes.length === 0"
        loading-text="Discovering Eloquent models..."
        empty-text="No Eloquent models found in your app."
        @node-click="onNodeClick"
        @pane-click="appStore.closeDetailPanel()"
        @retry="modelsStore.refresh()"
        :flow-props="flowProps"
    >
        <template #node-modelNode="{ data, selected }">
            <ModelNode :data="data" :selected="selected" />
        </template>

        <template #actions>
            <div class="lv-badge bg-canvas-panel/90 border border-canvas-border text-[10px] font-mono text-slate-500 backdrop-blur-sm gap-1.5">
                <span class="text-node-model">{{ modelsStore.models.length }}</span> models
                <span class="text-slate-600">·</span>
                <span class="text-slate-400">{{ totalRelations }}</span> relations
            </div>
        </template>
    </CanvasShell>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useModelsStore }  from '@/stores/models'
import { useAppStore }     from '@/stores/app'
import { useAutoLayout }   from '@/composables/useCanvas'
import CanvasShell         from '@/components/ui/CanvasShell.vue'
import ModelNode           from '@/components/nodes/ModelNode.vue'

const modelsStore = useModelsStore()
const appStore    = useAppStore()
const { gridLayout } = useAutoLayout()

const flowProps = {
    minZoom: 0.1, maxZoom: 2,
    fitViewOnInit: true,
    deleteKeyCode: null,
}

const totalRelations = computed(() =>
    modelsStore.models.reduce((s, m) => s + (m.relations?.length ?? 0), 0)
)

const nodes = computed(() => {
    const q = appStore.searchQuery.toLowerCase().trim()
    let list = modelsStore.models
    if (q) list = list.filter(m => m.short_name.toLowerCase().includes(q) || m.table.toLowerCase().includes(q))

    const raw = list.map(m => ({
        id:   `model-${m.short_name}`,
        type: 'modelNode',
        data: { ...m },
        position: { x: 0, y: 0 },
    }))

    return gridLayout(raw, { cols: 4, colWidth: 260, rowHeight: 240, padding: 60 })
})

const edges = computed(() => {
    const nodeIds = new Set(nodes.value.map(n => n.id))
    const edgeSet = new Set()
    const result  = []

    for (const rel of modelsStore.relationGraph) {
        const srcId  = `model-${rel.from}`
        const tgtId  = `model-${rel.to}`
        const edgeId = `rel-${rel.from}-${rel.method}`

        if (!nodeIds.has(srcId) || !nodeIds.has(tgtId)) continue
        if (edgeSet.has(edgeId)) continue
        edgeSet.add(edgeId)

        const isManyToMany = rel.type.includes('Many')

        result.push({
            id:     edgeId,
            source: srcId,
            target: tgtId,
            type:   'smoothstep',
            animated: isManyToMany,
            label:  `${rel.method}()`,
            labelStyle: { fill: '#475569', fontSize: 10, fontFamily: 'JetBrains Mono' },
            labelBgStyle: { fill: '#0d1526', fillOpacity: 0.85 },
            style: {
                stroke: isManyToMany ? '#a855f7' : '#10b981',
                strokeWidth: isManyToMany ? 1.5 : 1,
                strokeDasharray: isManyToMany ? '5 3' : null,
            },
            markerEnd: {
                type: 'arrowclosed',
                color: isManyToMany ? '#a855f7' : '#10b981',
                width: 10,
                height: 10,
            },
        })
    }

    return result
})

function onNodeClick(node) {
    appStore.setDetailPanel({
        title: node.data.short_name,
        type:  'model',
        data:  node.data,
    })
}

onMounted(async () => {
    await modelsStore.fetch()
})
</script>
