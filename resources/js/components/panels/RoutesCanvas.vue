<template>
    <div class="flex h-full overflow-hidden">

        <!-- Left: filter sidebar -->
        <div class="w-64 shrink-0 border-r border-canvas-border bg-canvas-panel flex flex-col overflow-hidden">

            <!-- Header -->
            <div class="px-4 py-3 border-b border-canvas-border">
                <div class="text-xs font-semibold text-slate-300 mb-3">Filters</div>

                <!-- Method filter -->
                <div class="space-y-1 mb-3">
                    <div class="text-[10px] uppercase tracking-widest text-slate-600 font-semibold mb-1.5">Method</div>
                    <div class="flex flex-wrap gap-1">
                        <button
                            v-for="m in ['ALL', 'GET', 'POST', 'PUT', 'PATCH', 'DELETE']"
                            :key="m"
                            :class="[
                                'text-[10px] px-2 py-1 rounded font-mono font-medium transition-all',
                                routesStore.filterMethod === m
                                    ? methodActiveClass(m)
                                    : 'bg-canvas-bg text-slate-500 border border-canvas-border hover:text-slate-300'
                            ]"
                            @click="routesStore.filterMethod = m"
                        >{{ m }}</button>
                    </div>
                </div>

                <!-- Type filter -->
                <div class="space-y-1">
                    <div class="text-[10px] uppercase tracking-widest text-slate-600 font-semibold mb-1.5">Type</div>
                    <div class="flex gap-1">
                        <button
                            v-for="t in [{ v: 'ALL', l: 'All' }, { v: 'api', l: 'API' }, { v: 'web', l: 'Web' }]"
                            :key="t.v"
                            :class="[
                                'flex-1 text-[10px] px-2 py-1.5 rounded font-medium transition-all border',
                                routesStore.filterType === t.v
                                    ? 'bg-accent-cyan/10 text-accent-cyan border-accent-cyan/30'
                                    : 'bg-canvas-bg text-slate-500 border-canvas-border hover:text-slate-300'
                            ]"
                            @click="routesStore.filterType = t.v"
                        >{{ t.l }}</button>
                    </div>
                </div>
            </div>

            <!-- Summary stats -->
            <div class="px-4 py-3 border-b border-canvas-border space-y-2">
                <div class="text-[10px] uppercase tracking-widest text-slate-600 font-semibold">Summary</div>
                <div class="grid grid-cols-2 gap-2">
                    <div class="bg-canvas-bg rounded-lg px-2.5 py-2 border border-canvas-border">
                        <div class="text-base font-bold font-mono text-node-route">{{ routesStore.summary.api_routes ?? 0 }}</div>
                        <div class="text-[10px] text-slate-600">API routes</div>
                    </div>
                    <div class="bg-canvas-bg rounded-lg px-2.5 py-2 border border-canvas-border">
                        <div class="text-base font-bold font-mono text-slate-300">{{ routesStore.summary.web_routes ?? 0 }}</div>
                        <div class="text-[10px] text-slate-600">Web routes</div>
                    </div>
                </div>

                <!-- Method breakdown bar -->
                <div v-if="routesStore.summary.methods" class="space-y-1 pt-1">
                    <div
                        v-for="(count, method) in routesStore.summary.methods"
                        :key="method"
                        class="flex items-center gap-2 text-[10px]"
                    >
                        <span :class="methodTextClass(method)" class="w-12 font-mono font-medium">{{ method }}</span>
                        <div class="flex-1 h-1.5 bg-canvas-bg rounded-full overflow-hidden border border-canvas-border/50">
                            <div
                                :class="methodBarClass(method)"
                                :style="{ width: `${(count / routesStore.summary.total) * 100}%` }"
                                class="h-full rounded-full transition-all duration-500"
                            />
                        </div>
                        <span class="text-slate-600 w-6 text-right">{{ count }}</span>
                    </div>
                </div>
            </div>

            <!-- Route list -->
            <div class="flex-1 overflow-y-auto px-2 py-2 space-y-0.5">
                <div
                    v-for="route in routesStore.filteredRoutes"
                    :key="`${route.methods?.join(',')}-${route.uri}`"
                    class="flex items-start gap-2 px-2.5 py-2 rounded-lg cursor-pointer transition-all hover:bg-canvas-hover group"
                    :class="selectedRouteId === routeId(route) ? 'bg-canvas-active border border-canvas-border' : ''"
                    @click="selectRoute(route)"
                >
                    <div class="flex flex-col gap-0.5 flex-shrink-0 mt-0.5">
                        <span
                            v-for="m in (route.methods ?? []).filter(m => m !== 'HEAD').slice(0, 2)"
                            :key="m"
                            :class="methodBadgeClass(m)"
                            class="text-[8px] px-1 py-0.5 rounded font-mono font-bold"
                        >{{ m }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-[11px] font-mono text-slate-300 truncate">/{{ route.uri }}</div>
                        <div v-if="route.controller" class="text-[10px] text-slate-600 truncate">{{ route.controller }}</div>
                    </div>
                </div>

                <div v-if="!routesStore.filteredRoutes.length" class="text-center py-8 text-slate-600 text-xs">
                    No routes match
                </div>
            </div>
        </div>

        <!-- Right: canvas -->
        <div class="flex-1 relative overflow-hidden">
            <CanvasShell
                :nodes="nodes"
                :edges="edges"
                :loading="routesStore.loading"
                :error="routesStore.error"
                :is-empty="!routesStore.loading && nodes.length === 0"
                loading-text="Mapping application routes..."
                @node-click="onNodeClick"
                @pane-click="onPaneClick"
                @retry="routesStore.refresh()"
                :flow-props="flowProps"
            >
                <template #node-routeGroupNode="{ data, selected }">
                    <RouteGroupNode :data="data" :selected="selected" />
                </template>
                <template #node-routeNode="{ data, selected }">
                    <RouteNode :data="data" :selected="selected" />
                </template>

                <template #actions>
                    <div class="flex items-center gap-1.5">
                        <button
                            :class="['lv-btn-ghost text-xs py-1 px-2.5', viewMode === 'grouped' ? 'text-accent-cyan' : '']"
                            @click="viewMode = 'grouped'"
                        >Groups</button>
                        <button
                            :class="['lv-btn-ghost text-xs py-1 px-2.5', viewMode === 'flat' ? 'text-accent-cyan' : '']"
                            @click="viewMode = 'flat'"
                        >Flat</button>
                    </div>
                </template>
            </CanvasShell>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoutesStore }  from '@/stores/routes'
import { useAppStore }     from '@/stores/app'
import { useAutoLayout }   from '@/composables/useCanvas'
import CanvasShell         from '@/components/ui/CanvasShell.vue'
import RouteGroupNode      from '@/components/nodes/RouteGroupNode.vue'
import RouteNode           from '@/components/nodes/RouteNode.vue'

const routesStore = useRoutesStore()
const appStore    = useAppStore()
const { gridLayout, layeredLayout } = useAutoLayout()

const viewMode        = ref('grouped')   // 'grouped' | 'flat'
const selectedRouteId = ref(null)

const flowProps = {
    minZoom: 0.05, maxZoom: 2,
    fitViewOnInit: true,
    deleteKeyCode: null,
}

// ── Nodes ─────────────────────────────────────────────────────────────────
const nodes = computed(() => {
    if (viewMode.value === 'grouped') return groupedNodes.value
    return flatNodes.value
})

const groupedNodes = computed(() => {
    const q = appStore.searchQuery.toLowerCase().trim()
    let groups = routesStore.groups

    if (q) {
        groups = groups.filter(g =>
            g.prefix.toLowerCase().includes(q) ||
            g.routes.some(r => r.uri.toLowerCase().includes(q))
        )
    }

    const raw = groups.map(g => ({
        id:   `group-${g.prefix || 'root'}`,
        type: 'routeGroupNode',
        data: { ...g },
        position: { x: 0, y: 0 },
    }))

    return gridLayout(raw, { cols: 4, colWidth: 260, rowHeight: 160, padding: 60 })
})

const flatNodes = computed(() => {
    const q = appStore.searchQuery.toLowerCase().trim()
    let routes = routesStore.filteredRoutes

    if (q) routes = routes.filter(r => r.uri.toLowerCase().includes(q))

    const raw = routes.map(r => ({
        id:   routeId(r),
        type: 'routeNode',
        data: { ...r },
        position: { x: 0, y: 0 },
    }))

    return gridLayout(raw, { cols: 5, colWidth: 260, rowHeight: 160, padding: 60 })
})

// ── Edges (group → route in flat mode; nothing in grouped) ────────────────
const edges = computed(() => [])  // Routes don't have meaningful edges between them

// ── Events ────────────────────────────────────────────────────────────────
function routeId(r) {
    return `route-${r.methods?.join(',')}-${r.uri}`
}

function selectRoute(route) {
    const id = routeId(route)
    selectedRouteId.value = id
    appStore.setDetailPanel({ title: `/${route.uri}`, type: 'route', data: route })
}

function onNodeClick(node) {
    if (node.type === 'routeGroupNode') {
        appStore.setDetailPanel({ title: `/${node.data.prefix}`, type: 'routegroup', data: node.data })
    } else {
        appStore.setDetailPanel({ title: `/${node.data.uri}`, type: 'route', data: node.data })
    }
}

function onPaneClick() {
    selectedRouteId.value = null
    appStore.closeDetailPanel()
}

// ── Styling helpers ───────────────────────────────────────────────────────
function methodActiveClass(m) {
    const map = {
        ALL:    'bg-slate-500/20 text-slate-300 border border-slate-500/30',
        GET:    'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30',
        POST:   'bg-blue-500/20 text-blue-400 border border-blue-500/30',
        PUT:    'bg-amber-500/20 text-amber-400 border border-amber-500/30',
        PATCH:  'bg-orange-500/20 text-orange-400 border border-orange-500/30',
        DELETE: 'bg-red-500/20 text-red-400 border border-red-500/30',
    }
    return map[m] ?? map.ALL
}
function methodTextClass(m) {
    const map = { GET: 'text-emerald-400', POST: 'text-blue-400', PUT: 'text-amber-400', PATCH: 'text-orange-400', DELETE: 'text-red-400' }
    return map[m] ?? 'text-slate-400'
}
function methodBarClass(m) {
    const map = { GET: 'bg-emerald-500', POST: 'bg-blue-500', PUT: 'bg-amber-500', PATCH: 'bg-orange-500', DELETE: 'bg-red-500' }
    return map[m] ?? 'bg-slate-500'
}
function methodBadgeClass(m) {
    const map = {
        GET: 'bg-emerald-500/15 text-emerald-400', POST: 'bg-blue-500/15 text-blue-400',
        PUT: 'bg-amber-500/15 text-amber-400', PATCH: 'bg-orange-500/15 text-orange-400',
        DELETE: 'bg-red-500/15 text-red-400',
    }
    return map[m] ?? 'bg-slate-500/15 text-slate-400'
}

onMounted(async () => {
    await routesStore.fetch()
})
</script>
