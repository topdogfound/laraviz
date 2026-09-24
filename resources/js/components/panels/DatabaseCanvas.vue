<template>
    <CanvasShell
        :nodes="nodes"
        :edges="edges"
        :loading="dbStore.loading"
        :error="dbStore.error"
        :is-empty="!dbStore.loading && !dbStore.error && nodes.length === 0"
        loading-text="Introspecting database schema..."
        empty-text="No tables found. Check your database connection."
        @node-click="onNodeClick"
        @pane-click="appStore.closeDetailPanel()"
        @retry="dbStore.refresh()"
        :flow-props="flowProps"
    >
        <!-- Custom node types -->
        <template #node-tableNode="{ data, selected }">
            <TableNode :data="data" :selected="selected" />
        </template>

        <!-- Toolbar slot -->
        <template #actions>
            <!-- Connection selector -->
            <select
                v-if="dbStore.connections.length > 1"
                v-model="activeConnection"
                class="bg-canvas-panel border border-canvas-border text-slate-300 text-xs rounded-lg px-2 py-1 focus:outline-none focus:border-accent-cyan/50"
            >
                <option v-for="c in dbStore.connections" :key="c.connection" :value="c.connection">
                    {{ c.connection }} ({{ c.driver }})
                </option>
            </select>

            <!-- Stats pill -->
            <div class="lv-badge bg-canvas-panel/90 border border-canvas-border text-[10px] font-mono text-slate-500 backdrop-blur-sm gap-1.5">
                <span class="text-node-db">{{ tableCount }}</span> tables
                <span class="text-slate-600">·</span>
                <span class="text-slate-400">{{ fkCount }}</span> FK
                <span class="text-slate-600">·</span>
                <span class="text-slate-500">{{ totalRows }}</span> rows
            </div>
        </template>
    </CanvasShell>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { useDatabaseStore } from '@/stores/database'
import { useAppStore }      from '@/stores/app'
import { useAutoLayout }    from '@/composables/useCanvas'
import CanvasShell          from '@/components/ui/CanvasShell.vue'
import TableNode            from '@/components/nodes/TableNode.vue'

const dbStore  = useDatabaseStore()
const appStore = useAppStore()
const { gridLayout } = useAutoLayout()

const activeConnection = ref(null)

const flowProps = {
    minZoom: 0.1,
    maxZoom: 2,
    fitViewOnInit: true,
    snapToGrid: false,
    deleteKeyCode: null,
}

// ── Data derivation ───────────────────────────────────────────────────────
const currentConnection = computed(() => {
    if (!dbStore.connections.length) return null
    return dbStore.connections.find(c => c.connection === activeConnection.value)
        ?? dbStore.connections[0]
})

const tableCount = computed(() => Object.keys(currentConnection.value?.tables ?? {}).length)
const fkCount    = computed(() => currentConnection.value?.foreign_keys?.length ?? 0)
const totalRows  = computed(() => {
    const tables = Object.values(currentConnection.value?.tables ?? {})
    const total  = tables.reduce((sum, t) => sum + (t.row_count ?? 0), 0)
    if (total >= 1_000_000) return (total / 1_000_000).toFixed(1) + 'M'
    if (total >= 1_000)     return (total / 1_000).toFixed(1) + 'K'
    return total.toString()
})

// ── Build nodes ───────────────────────────────────────────────────────────
const rawNodes = computed(() => {
    if (!currentConnection.value?.tables) return []

    return Object.entries(currentConnection.value.tables).map(([name, table]) => ({
        id:   `table-${name}`,
        type: 'tableNode',
        data: { ...table },
        position: { x: 0, y: 0 }, // will be overwritten by layout
    }))
})

const nodes = computed(() => {
    const q = appStore.searchQuery.toLowerCase().trim()
    const filtered = q
        ? rawNodes.value.filter(n => n.data.name.toLowerCase().includes(q))
        : rawNodes.value

    // Sort: most columns first (richer tables more prominent)
    const sorted = [...filtered].sort((a, b) =>
        (b.data.columns?.length ?? 0) - (a.data.columns?.length ?? 0)
    )

    return gridLayout(sorted, { cols: 4, colWidth: 320, rowHeight: 260, padding: 60 })
})

// ── Build edges (foreign keys) ────────────────────────────────────────────
const edges = computed(() => {
    const fks = currentConnection.value?.foreign_keys ?? []
    const tableIds = new Set(nodes.value.map(n => n.id))

    return fks
        .filter(fk => {
            const srcId = `table-${fk.from_table}`
            const tgtId = `table-${fk.to_table}`
            return tableIds.has(srcId) && tableIds.has(tgtId)
        })
        .map(fk => ({
            id:        `fk-${fk.from_table}-${fk.from_column}-${fk.to_table}`,
            source:    `table-${fk.from_table}`,
            target:    `table-${fk.to_table}`,
            type:      'smoothstep',
            animated:  false,
            label:     `${fk.from_column} → ${fk.to_column}`,
            labelStyle: { fill: '#475569', fontSize: 10, fontFamily: 'JetBrains Mono' },
            labelBgStyle: { fill: '#0d1526', fillOpacity: 0.9 },
            style: { stroke: '#0ea5e9', strokeWidth: 1.5, opacity: 0.6 },
            markerEnd: {
                type: 'arrowclosed',
                color: '#0ea5e9',
                width: 12,
                height: 12,
            },
            data: { fk },
        }))
})

// ── Events ────────────────────────────────────────────────────────────────
function onNodeClick(node) {
    appStore.setDetailPanel({
        title: node.data.name,
        type:  'table',
        data:  node.data,
    })
}

// ── Init ──────────────────────────────────────────────────────────────────
onMounted(async () => {
    await dbStore.fetch()
    if (dbStore.connections.length) {
        activeConnection.value = dbStore.connections[0].connection
    }
})

watch(() => dbStore.connections, conns => {
    if (conns.length && !activeConnection.value) {
        activeConnection.value = conns[0].connection
    }
})
</script>
