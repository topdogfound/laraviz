<template>
    <div class="flex h-full overflow-hidden">

        <!-- Tab bar -->
        <div class="w-60 shrink-0 border-r border-canvas-border bg-canvas-panel flex flex-col overflow-hidden">
            <div class="px-4 py-3 border-b border-canvas-border">
                <div class="text-xs font-semibold text-slate-300 mb-2">View</div>
                <div class="space-y-0.5">
                    <button
                        v-for="tab in tabs" :key="tab.id"
                        :class="[
                            'w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg text-sm transition-all',
                            activeTab === tab.id
                                ? 'bg-node-service/10 text-node-service border border-node-service/20'
                                : 'text-slate-400 hover:text-slate-200 hover:bg-canvas-hover border border-transparent'
                        ]"
                        @click="activeTab = tab.id"
                    >
                        <span class="text-base leading-none">{{ tab.icon }}</span>
                        <span class="font-medium flex-1 text-left">{{ tab.label }}</span>
                        <span class="text-[10px] font-mono text-slate-600">{{ tab.count }}</span>
                    </button>
                </div>
            </div>

            <!-- Core toggle -->
            <div class="px-4 py-3 border-b border-canvas-border">
                <label class="flex items-center gap-2.5 cursor-pointer">
                    <div class="relative">
                        <input type="checkbox" class="sr-only peer"
                               v-model="showCore"
                               @change="onCoreToggle" />
                        <div class="w-8 h-4 bg-canvas-bg border border-canvas-border rounded-full peer-checked:bg-accent-cyan/20 peer-checked:border-accent-cyan/40 transition-all" />
                        <div class="absolute top-0.5 left-0.5 w-3 h-3 bg-slate-600 rounded-full transition-all peer-checked:translate-x-4 peer-checked:bg-accent-cyan" />
                    </div>
                    <span class="text-xs text-slate-400">Show core framework</span>
                </label>
            </div>

            <!-- Stats -->
            <div class="px-4 py-3 space-y-2">
                <div class="text-[10px] uppercase tracking-widest text-slate-600 font-semibold">Stats</div>
                <div class="space-y-1.5 text-xs">
                    <div class="flex justify-between text-slate-500">
                        <span>App providers</span>
                        <span class="font-mono text-node-service">{{ appProviderCount }}</span>
                    </div>
                    <div class="flex justify-between text-slate-500">
                        <span>Deferred</span>
                        <span class="font-mono text-amber-400">{{ deferredCount }}</span>
                    </div>
                    <div class="flex justify-between text-slate-500">
                        <span>Facades</span>
                        <span class="font-mono text-purple-400">{{ servicesStore.facades.length }}</span>
                    </div>
                    <div class="flex justify-between text-slate-500">
                        <span>Bindings</span>
                        <span class="font-mono text-slate-300">{{ servicesStore.bindings.length }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Canvas -->
        <div class="flex-1 relative overflow-hidden">
            <CanvasShell
                :nodes="nodes"
                :edges="edges"
                :loading="servicesStore.loading"
                :error="servicesStore.error"
                :is-empty="!servicesStore.loading && nodes.length === 0"
                loading-text="Resolving service container..."
                empty-text="No services found."
                @node-click="onNodeClick"
                @pane-click="appStore.closeDetailPanel()"
                @retry="servicesStore.refresh()"
                :flow-props="flowProps"
            >
                <template #node-serviceNode="{ data, selected }">
                    <ServiceNode :data="data" :selected="selected" />
                </template>
                <template #node-facadeNode="{ data, selected }">
                    <FacadeNode :data="data" :selected="selected" />
                </template>
                <template #node-bindingNode="{ data, selected }">
                    <BindingNode :data="data" :selected="selected" />
                </template>
            </CanvasShell>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useServicesStore } from '@/stores/services'
import { useAppStore }      from '@/stores/app'
import { useAutoLayout }    from '@/composables/useCanvas'
import CanvasShell          from '@/components/ui/CanvasShell.vue'
import ServiceNode          from '@/components/nodes/ServiceNode.vue'
import FacadeNode           from '@/components/nodes/FacadeNode.vue'
import BindingNode          from '@/components/nodes/BindingNode.vue'

const servicesStore = useServicesStore()
const appStore      = useAppStore()
const { gridLayout } = useAutoLayout()

const activeTab = ref('providers')
const showCore  = ref(false)

const flowProps = {
    minZoom: 0.05, maxZoom: 2, fitViewOnInit: true, deleteKeyCode: null,
}

const appProviderCount = computed(() => servicesStore.providers.filter(p => !p.is_core).length)
const deferredCount    = computed(() => servicesStore.providers.filter(p => p.is_deferred).length)

const tabs = computed(() => [
    { id: 'providers', icon: '⚙️', label: 'Providers',  count: servicesStore.providers.length },
    { id: 'facades',   icon: '🎭', label: 'Facades',    count: servicesStore.facades.filter(f => !f.is_core || showCore.value).length },
    { id: 'bindings',  icon: '🔗', label: 'Bindings',   count: servicesStore.bindings.length },
])

function onCoreToggle() {
    servicesStore.showCoreProviders = showCore.value
    servicesStore.showCoreBindings  = showCore.value
}

const nodes = computed(() => {
    const q = appStore.searchQuery.toLowerCase().trim()

    if (activeTab.value === 'providers') {
        let list = servicesStore.providers
        if (q) list = list.filter(p => p.short_name.toLowerCase().includes(q))
        const raw = list.map(p => ({
            id: `prov-${p.class}`, type: 'serviceNode', data: { ...p }, position: { x: 0, y: 0 },
        }))
        return gridLayout(raw, { cols: 4, colWidth: 260, rowHeight: 130, padding: 60 })
    }

    if (activeTab.value === 'facades') {
        let list = servicesStore.facades.filter(f => showCore.value || !f.is_core)
        if (q) list = list.filter(f => f.facade.toLowerCase().includes(q))
        const raw = list.map(f => ({
            id: `facade-${f.facade}`, type: 'facadeNode', data: { ...f }, position: { x: 0, y: 0 },
        }))
        return gridLayout(raw, { cols: 5, colWidth: 220, rowHeight: 110, padding: 60 })
    }

    if (activeTab.value === 'bindings') {
        let list = servicesStore.bindings
        if (q) list = list.filter(b => b.abstract.toLowerCase().includes(q))
        const raw = list.map(b => ({
            id: `bind-${b.abstract}`, type: 'bindingNode', data: { ...b }, position: { x: 0, y: 0 },
        }))
        return gridLayout(raw, { cols: 4, colWidth: 280, rowHeight: 110, padding: 60 })
    }

    return []
})

const edges = computed(() => [])

function onNodeClick(node) {
    const typeMap = { serviceNode: 'service', facadeNode: 'service', bindingNode: 'service' }
    appStore.setDetailPanel({
        title: node.data.short_name ?? node.data.facade ?? node.data.abstract,
        type:  typeMap[node.type] ?? 'service',
        data:  node.data,
    })
}

onMounted(async () => { await servicesStore.fetch() })
</script>
