<template>
    <div class="flex h-full overflow-hidden">

        <!-- Mobile filter toggle button (visible only on small screens) -->
        <button
            class="md:hidden absolute top-2 left-2 z-20 lv-btn-ghost p-2 rounded-lg bg-canvas-panel border border-canvas-border shadow-panel"
            @click="filterSidebarOpen = !filterSidebarOpen"
        >
            <svg class="w-4 h-4" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                <line x1="2" y1="4" x2="14" y2="4"/>
                <line x1="4" y1="8" x2="12" y2="8"/>
                <line x1="6" y1="12" x2="10" y2="12"/>
            </svg>
        </button>

        <!-- Mobile backdrop -->
        <div
            v-if="filterSidebarOpen"
            class="md:hidden fixed inset-0 bg-black/50 z-10"
            @click="filterSidebarOpen = false"
        />

        <!-- Left: filter sidebar -->
        <Transition name="slide-right">
            <div
                v-show="filterSidebarOpen || isDesktop"
                class="w-64 shrink-0 border-r border-canvas-border bg-canvas-panel flex flex-col overflow-hidden
                       max-md:fixed max-md:left-0 max-md:top-0 max-md:bottom-0 max-md:z-20 max-md:shadow-2xl"
            >
                <!-- Mobile header -->
                <div class="flex items-center justify-between px-4 py-3 border-b border-canvas-border md:hidden">
                    <span class="text-sm font-semibold text-slate-200">Filters</span>
                    <button class="lv-btn-ghost p-1" @click="filterSidebarOpen = false">✕</button>
                </div>

                <!-- Search -->
                <div class="px-4 pt-3 pb-2">
                    <div class="relative">
                        <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3 h-3 text-slate-500 pointer-events-none"
                             viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="6.5" cy="6.5" r="5"/>
                            <line x1="10.5" y1="10.5" x2="14" y2="14"/>
                        </svg>
                        <input
                            v-model="routesStore.filterSearch"
                            type="text"
                            placeholder="Search routes..."
                            class="w-full bg-canvas-bg border border-canvas-border rounded-lg pl-7 pr-3 py-1.5 text-xs
                                   text-slate-300 placeholder-slate-600 focus:outline-none focus:border-accent-cyan/50
                                   focus:ring-1 focus:ring-accent-cyan/20 transition-all"
                        />
                        <button
                            v-if="routesStore.filterSearch"
                            class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-300"
                            @click="routesStore.filterSearch = ''"
                        >✕</button>
                    </div>
                </div>

                <!-- Filters -->
                <div class="px-4 py-3 border-b border-canvas-border space-y-4">

                    <!-- Method filter -->
                    <div>
                        <div class="text-[10px] uppercase tracking-widest text-slate-600 font-semibold mb-2">Method</div>
                        <div class="flex flex-wrap gap-1">
                            <button
                                v-for="m in ['ALL', 'GET', 'POST', 'PUT', 'PATCH', 'DELETE']"
                                :key="m"
                                :class="[
                                    'text-[10px] px-2 py-1 rounded font-mono font-medium transition-all border',
                                    routesStore.filterMethod === m
                                        ? methodActiveClass(m)
                                        : 'bg-canvas-bg text-slate-500 border-canvas-border hover:text-slate-300 hover:border-slate-500'
                                ]"
                                @click="setMethodFilter(m)"
                            >{{ m }}</button>
                        </div>
                    </div>

                    <!-- Type filter -->
                    <div>
                        <div class="text-[10px] uppercase tracking-widest text-slate-600 font-semibold mb-2">Type</div>
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
                                @click="setTypeFilter(t.v)"
                            >{{ t.l }}</button>
                        </div>
                    </div>

                    <!-- Active filter chips -->
                    <div v-if="hasActiveFilters" class="flex flex-wrap gap-1 pt-1">
                        <span v-if="routesStore.filterMethod !== 'ALL'"
                              class="inline-flex items-center gap-1 text-[10px] px-2 py-0.5 rounded-full bg-accent-cyan/10 text-accent-cyan border border-accent-cyan/20">
                            {{ routesStore.filterMethod }}
                            <button @click="routesStore.filterMethod = 'ALL'" class="hover:text-white">✕</button>
                        </span>
                        <span v-if="routesStore.filterType !== 'ALL'"
                              class="inline-flex items-center gap-1 text-[10px] px-2 py-0.5 rounded-full bg-purple-500/10 text-purple-400 border border-purple-500/20">
                            {{ routesStore.filterType }}
                            <button @click="routesStore.filterType = 'ALL'" class="hover:text-white">✕</button>
                        </span>
                        <button @click="clearFilters" class="text-[10px] text-slate-500 hover:text-red-400 transition-colors ml-auto">
                            Clear all
                        </button>
                    </div>
                </div>

                <!-- Summary stats -->
                <div class="px-4 py-3 border-b border-canvas-border space-y-2">
                    <div class="text-[10px] uppercase tracking-widest text-slate-600 font-semibold">
                        {{ hasActiveFilters ? `${routesStore.filteredRoutes.length} matching` : 'Summary' }}
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div class="bg-canvas-bg rounded-lg px-2.5 py-2 border border-canvas-border">
                            <div class="text-base font-bold font-mono text-node-route">
                                {{ hasActiveFilters ? filteredApiCount : (routesStore.summary.api_routes ?? 0) }}
                            </div>
                            <div class="text-[10px] text-slate-600">API routes</div>
                        </div>
                        <div class="bg-canvas-bg rounded-lg px-2.5 py-2 border border-canvas-border">
                            <div class="text-base font-bold font-mono text-slate-300">
                                {{ hasActiveFilters ? filteredWebCount : (routesStore.summary.web_routes ?? 0) }}
                            </div>
                            <div class="text-[10px] text-slate-600">Web routes</div>
                        </div>
                    </div>

                    <!-- Method breakdown bar -->
                    <div v-if="routesStore.summary.methods" class="space-y-1 pt-1">
                        <div
                            v-for="(count, method) in routesStore.summary.methods"
                            :key="method"
                            class="flex items-center gap-2 text-[10px] cursor-pointer"
                            @click="setMethodFilter(method)"
                        >
                            <span :class="[methodTextClass(method), 'w-12 font-mono font-medium hover:opacity-80']">{{ method }}</span>
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
                    <TransitionGroup name="fade">
                        <div
                            v-for="route in routesStore.filteredRoutes"
                            :key="`${route.methods?.join(',')}-${route.uri}`"
                            class="flex items-start gap-2 px-2.5 py-2 rounded-lg cursor-pointer transition-all hover:bg-canvas-hover group"
                            :class="selectedRouteId === routeId(route) ? 'bg-canvas-active border border-canvas-border' : 'border border-transparent'"
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
                    </TransitionGroup>

                    <div v-if="!routesStore.filteredRoutes.length && !routesStore.loading"
                         class="text-center py-8 space-y-2">
                        <div class="text-2xl">🔍</div>
                        <div class="text-xs text-slate-600">No routes match</div>
                        <button class="text-[11px] text-accent-cyan hover:text-accent-cyan/80" @click="clearFilters">
                            Clear filters
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Right: canvas -->
        <div class="flex-1 relative overflow-hidden">
            <CanvasShell
                :nodes="nodes"
                :edges="[]"
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
                    <!-- View mode toggle -->
                    <div class="flex items-center bg-canvas-panel border border-canvas-border rounded-lg overflow-hidden">
                        <button
                            :class="['text-[11px] px-3 py-1.5 font-medium transition-all', viewMode === 'grouped' ? 'bg-accent-cyan/15 text-accent-cyan' : 'text-slate-500 hover:text-slate-300']"
                            @click="viewMode = 'grouped'"
                        >Groups</button>
                        <div class="w-px h-4 bg-canvas-border" />
                        <button
                            :class="['text-[11px] px-3 py-1.5 font-medium transition-all', viewMode === 'flat' ? 'bg-accent-cyan/15 text-accent-cyan' : 'text-slate-500 hover:text-slate-300']"
                            @click="viewMode = 'flat'"
                        >Flat</button>
                    </div>

                    <!-- Results count -->
                    <span class="lv-badge bg-canvas-panel/90 border border-canvas-border text-[10px] font-mono text-slate-500 backdrop-blur-sm">
                        {{ nodes.length }} {{ viewMode === 'grouped' ? 'groups' : 'routes' }}
                        <span v-if="hasActiveFilters" class="text-accent-cyan">· filtered</span>
                    </span>
                </template>
            </CanvasShell>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRoutesStore }  from '@/stores/routes'
import { useAppStore }     from '@/stores/app'
import { useAutoLayout }   from '@/composables/useCanvas'
import CanvasShell         from '@/components/ui/CanvasShell.vue'
import RouteGroupNode      from '@/components/nodes/RouteGroupNode.vue'
import RouteNode           from '@/components/nodes/RouteNode.vue'

const routesStore = useRoutesStore()
const appStore    = useAppStore()
const { gridLayout } = useAutoLayout()

const viewMode          = ref('grouped')
const selectedRouteId   = ref(null)
const filterSidebarOpen = ref(false)
const isDesktop         = ref(window.innerWidth >= 768)

// Track window resize for responsive sidebar
function onResize() { isDesktop.value = window.innerWidth >= 768 }
onMounted(() => window.addEventListener('resize', onResize))
onUnmounted(() => window.removeEventListener('resize', onResize))

const flowProps = {
    minZoom: 0.05, maxZoom: 2,
    fitViewOnInit: true,
    deleteKeyCode: null,
}

// ── Filter helpers ────────────────────────────────────────────────────────
const hasActiveFilters = computed(() =>
    routesStore.filterMethod !== 'ALL' ||
    routesStore.filterType !== 'ALL' ||
    routesStore.filterSearch.trim() !== ''
)

const filteredApiCount = computed(() => routesStore.filteredRoutes.filter(r => r.is_api).length)
const filteredWebCount = computed(() => routesStore.filteredRoutes.filter(r => !r.is_api).length)

function setMethodFilter(m) {
    routesStore.filterMethod = routesStore.filterMethod === m && m !== 'ALL' ? 'ALL' : m
}

function setTypeFilter(t) {
    routesStore.filterType = t
}

function clearFilters() {
    routesStore.filterMethod = 'ALL'
    routesStore.filterType   = 'ALL'
    routesStore.filterSearch = ''
}

// ── Nodes ─────────────────────────────────────────────────────────────────
const nodes = computed(() =>
    viewMode.value === 'grouped' ? groupedNodes.value : flatNodes.value
)

/**
 * FIX: rebuild groups from filteredRoutes instead of using raw routesStore.groups.
 * This means method/type/search filters affect the grouped canvas nodes.
 */
const groupedNodes = computed(() => {
    const q = appStore.searchQuery.toLowerCase().trim()

    // Build groups from filtered routes
    const groupMap = {}
    for (const route of routesStore.filteredRoutes) {
        const segment = (route.uri.split('/').find(s => s && !s.startsWith('{')) ?? 'root')
        if (!groupMap[segment]) {
            groupMap[segment] = {
                prefix:      segment,
                routes:      [],
                is_api:      false,
                route_count: 0,
            }
        }
        groupMap[segment].routes.push(route)
        groupMap[segment].route_count++
        if (route.is_api) groupMap[segment].is_api = true
    }

    let groups = Object.values(groupMap)

    // Also apply top-bar search on group prefix
    if (q) {
        groups = groups.filter(g =>
            g.prefix.toLowerCase().includes(q) ||
            g.routes.some(r => r.uri.toLowerCase().includes(q))
        )
    }

    const raw = groups.map(g => ({
        id:   `group-${g.prefix}`,
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

// ── Events ────────────────────────────────────────────────────────────────
function routeId(r) {
    return `route-${r.methods?.join(',')}-${r.uri}`
}

function selectRoute(route) {
    selectedRouteId.value = routeId(route)
    appStore.setDetailPanel({ title: `/${route.uri}`, type: 'route', data: route })
    if (!isDesktop.value) filterSidebarOpen.value = false
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

// ── Style helpers ─────────────────────────────────────────────────────────
function methodActiveClass(m) {
    const map = {
        ALL:    'bg-slate-500/20 text-slate-300 border-slate-500/30',
        GET:    'bg-emerald-500/20 text-emerald-400 border-emerald-500/30',
        POST:   'bg-blue-500/20   text-blue-400   border-blue-500/30',
        PUT:    'bg-amber-500/20  text-amber-400  border-amber-500/30',
        PATCH:  'bg-orange-500/20 text-orange-400 border-orange-500/30',
        DELETE: 'bg-red-500/20    text-red-400    border-red-500/30',
    }
    return map[m] ?? map.ALL
}
function methodTextClass(m) {
    return { GET: 'text-emerald-400', POST: 'text-blue-400', PUT: 'text-amber-400', PATCH: 'text-orange-400', DELETE: 'text-red-400' }[m] ?? 'text-slate-400'
}
function methodBarClass(m) {
    return { GET: 'bg-emerald-500', POST: 'bg-blue-500', PUT: 'bg-amber-500', PATCH: 'bg-orange-500', DELETE: 'bg-red-500' }[m] ?? 'bg-slate-500'
}
function methodBadgeClass(m) {
    return {
        GET: 'bg-emerald-500/15 text-emerald-400', POST: 'bg-blue-500/15 text-blue-400',
        PUT: 'bg-amber-500/15  text-amber-400',    PATCH: 'bg-orange-500/15 text-orange-400',
        DELETE: 'bg-red-500/15 text-red-400',
    }[m] ?? 'bg-slate-500/15 text-slate-400'
}

onMounted(async () => { await routesStore.fetch() })
</script>
