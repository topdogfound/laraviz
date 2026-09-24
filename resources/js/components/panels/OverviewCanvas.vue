<template>
    <div class="h-full overflow-y-auto p-6 space-y-6">

        <!-- Hero header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-100">
                    <span class="bg-gradient-to-r from-accent-cyan to-accent-purple bg-clip-text text-transparent">{{ appStore.appName }}</span>
                </h1>
                <p class="text-sm text-slate-500 mt-1 font-mono">
                    Laravel {{ appStore.laravelVersion }} · PHP {{ appStore.phpVersion }} ·
                    <span :class="envClass">{{ appStore.environment }}</span>
                </p>
            </div>
            <div class="text-xs font-mono text-slate-600">
                Analyzed {{ generatedAt }}
            </div>
        </div>

        <!-- Stat grid -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <OverviewCard
                v-for="card in statCards"
                :key="card.label"
                :icon="card.icon"
                :label="card.label"
                :value="card.value"
                :sublabel="card.sublabel"
                :color="card.color"
                :loading="card.loading"
                @click="appStore.setActiveView(card.view)"
            />
        </div>

        <!-- Two columns: charts + quick nav -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

            <!-- Route method distribution -->
            <div class="lv-panel p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-slate-300">Route Methods</h3>
                    <button class="lv-btn-ghost text-xs" @click="appStore.setActiveView('routes')">View all →</button>
                </div>
                <div class="space-y-2">
                    <div
                        v-for="(count, method) in routesMethods"
                        :key="method"
                        class="flex items-center gap-3 text-xs"
                    >
                        <span :class="methodClass(method)" class="w-16 font-mono font-semibold">{{ method }}</span>
                        <div class="flex-1 h-2 bg-canvas-bg rounded-full overflow-hidden border border-canvas-border/50">
                            <div
                                :class="barClass(method)"
                                :style="{ width: `${Math.round((count / totalRoutes) * 100)}%` }"
                                class="h-full rounded-full transition-all duration-700"
                            />
                        </div>
                        <span class="w-8 text-right font-mono text-slate-500">{{ count }}</span>
                    </div>
                </div>
            </div>

            <!-- DB connections -->
            <div class="lv-panel p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-slate-300">Database</h3>
                    <button class="lv-btn-ghost text-xs" @click="appStore.setActiveView('database')">View schema →</button>
                </div>
                <div v-if="dbLoading" class="text-xs text-slate-600">Loading...</div>
                <div v-else class="space-y-2">
                    <div
                        v-for="conn in dbConns"
                        :key="conn.connection"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-canvas-bg border border-canvas-border"
                    >
                        <div class="w-2 h-2 rounded-full bg-node-db flex-shrink-0" />
                        <div class="flex-1">
                            <div class="text-xs font-mono text-slate-300">{{ conn.connection }}</div>
                            <div class="text-[10px] text-slate-600">{{ conn.driver }} · {{ conn.database }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-bold font-mono text-node-db">{{ Object.keys(conn.tables ?? {}).length }}</div>
                            <div class="text-[10px] text-slate-600">tables</div>
                        </div>
                    </div>
                    <div v-if="!dbConns.length" class="text-xs text-slate-600 text-center py-2">No connections</div>
                </div>
            </div>

            <!-- Config snapshot -->
            <div class="lv-panel p-4 space-y-3">
                <h3 class="text-sm font-semibold text-slate-300">Configuration</h3>
                <div v-if="configLoading" class="text-xs text-slate-600">Loading...</div>
                <div v-else class="grid grid-cols-2 gap-2 text-xs">
                    <ConfigRow label="Cache"     :value="configData?.cache_driver" />
                    <ConfigRow label="Queue"      :value="configData?.queue_driver" />
                    <ConfigRow label="Session"    :value="configData?.session_driver" />
                    <ConfigRow label="Mail"       :value="configData?.mail_driver" />
                    <ConfigRow label="Filesystem" :value="configData?.filesystem_driver" />
                    <ConfigRow label="Debug"      :value="configData?.debug ? 'ON' : 'OFF'" :warn="configData?.debug" />
                </div>
            </div>

            <!-- Quick nav -->
            <div class="lv-panel p-4 space-y-3">
                <h3 class="text-sm font-semibold text-slate-300">Quick Navigate</h3>
                <div class="grid grid-cols-2 gap-2">
                    <button
                        v-for="nav in quickNav"
                        :key="nav.view"
                        class="flex items-center gap-2.5 px-3 py-3 rounded-xl bg-canvas-bg border border-canvas-border hover:border-canvas-active transition-all group"
                        @click="appStore.setActiveView(nav.view); $router.push(`/canvas/${nav.view}`)"
                    >
                        <span class="text-xl leading-none">{{ nav.icon }}</span>
                        <div class="text-left">
                            <div class="text-xs font-semibold text-slate-300 group-hover:text-slate-100 transition-colors">{{ nav.label }}</div>
                            <div class="text-[10px] text-slate-600">{{ nav.desc }}</div>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { api }           from '@/composables/useApi'
import { useAppStore }   from '@/stores/app'
import { useDatabaseStore } from '@/stores/database'
import OverviewCard from '@/components/ui/OverviewCard.vue'

const appStore = useAppStore()
const dbStore  = useDatabaseStore()
const router   = useRouter()

const routesData  = ref(null)
const modelsData  = ref(null)
const servicesData = ref(null)
const configData  = ref(null)
const configLoading = ref(false)
const generatedAt = ref('just now')

// Load all summary data in parallel
onMounted(async () => {
    configLoading.value = true
    await dbStore.fetch()

    const [routes, models, services, config] = await Promise.allSettled([
        api.get('/routes'),
        api.get('/models'),
        api.get('/services'),
        api.get('/config'),
    ])

    if (routes.status === 'fulfilled')   routesData.value   = routes.value.data.data
    if (models.status === 'fulfilled')   modelsData.value   = models.value.data.data
    if (services.status === 'fulfilled') servicesData.value = services.value.data.data
    if (config.status === 'fulfilled')   configData.value   = config.value.data.data

    configLoading.value = false
    generatedAt.value = new Date().toLocaleTimeString()
})

const dbLoading   = computed(() => dbStore.loading)
const dbConns     = computed(() => dbStore.connections)
const totalTables = computed(() => dbStore.allTables.length)

const routesMethods = computed(() => routesData.value?.summary?.methods ?? {})
const totalRoutes   = computed(() => routesData.value?.total ?? 1)

const statCards = computed(() => [
    {
        icon: '🗄️', label: 'Tables',    value: totalTables.value,
        sublabel: `${dbStore.foreignKeys.length} FK constraints`,
        color: 'node-db', view: 'database', loading: dbLoading.value,
    },
    {
        icon: '🗺️', label: 'Routes',    value: routesData.value?.total ?? '—',
        sublabel: `${routesData.value?.summary?.api_routes ?? 0} API · ${routesData.value?.summary?.web_routes ?? 0} web`,
        color: 'node-route', view: 'routes', loading: !routesData.value,
    },
    {
        icon: '📐', label: 'Models',    value: modelsData.value?.total ?? '—',
        sublabel: 'Eloquent models',
        color: 'node-model', view: 'models', loading: !modelsData.value,
    },
    {
        icon: '⚙️', label: 'Providers', value: servicesData.value?.providers?.length ?? '—',
        sublabel: `${servicesData.value?.providers?.filter(p => !p.is_core).length ?? 0} app-defined`,
        color: 'node-service', view: 'services', loading: !servicesData.value,
    },
])

const envClass = computed(() => ({
    local:      'text-emerald-400',
    production: 'text-red-400',
    staging:    'text-amber-400',
    testing:    'text-blue-400',
}[appStore.environment] ?? 'text-slate-400'))

const quickNav = [
    { view: 'database', icon: '🗄️', label: 'Schema',    desc: 'Tables & relations' },
    { view: 'routes',   icon: '🗺️', label: 'Routes',    desc: 'API & web routes' },
    { view: 'models',   icon: '📐', label: 'Models',    desc: 'Eloquent graph' },
    { view: 'services', icon: '⚙️', label: 'Services',  desc: 'Container bindings' },
    { view: 'events',   icon: '⚡', label: 'Events',    desc: 'Event listeners' },
    { view: 'flow',     icon: '🔭', label: 'App Flow',  desc: 'Request lifecycle' },
]

function methodClass(m) {
    const map = { GET: 'text-emerald-400', POST: 'text-blue-400', PUT: 'text-amber-400', PATCH: 'text-orange-400', DELETE: 'text-red-400' }
    return map[m] ?? 'text-slate-400'
}
function barClass(m) {
    const map = { GET: 'bg-emerald-500', POST: 'bg-blue-500', PUT: 'bg-amber-500', PATCH: 'bg-orange-500', DELETE: 'bg-red-500' }
    return map[m] ?? 'bg-slate-500'
}
</script>
