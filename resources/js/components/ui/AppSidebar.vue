<template>
    <aside class="flex flex-col w-56 shrink-0 bg-canvas-panel border-r border-canvas-border overflow-y-auto h-full">

        <!-- Logo -->
        <div class="flex items-center gap-2.5 px-4 py-4 border-b border-canvas-border">
            <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-accent-cyan to-accent-purple flex items-center justify-center text-xs font-bold text-white select-none shrink-0">
                LV
            </div>
            <div class="min-w-0">
                <div class="text-sm font-semibold text-slate-100 leading-none">LaraViz</div>
                <div class="text-[10px] text-slate-500 font-mono mt-0.5 truncate">{{ appStore.appName }}</div>
            </div>
            <!-- Close on mobile -->
            <button
                class="ml-auto md:hidden lv-btn-ghost p-1 rounded shrink-0"
                @click="appStore.toggleSidebar()"
            >
                <svg class="w-3.5 h-3.5" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="3" y1="3" x2="13" y2="13"/><line x1="13" y1="3" x2="3" y2="13"/>
                </svg>
            </button>
        </div>

        <!-- Environment badge -->
        <div class="px-4 py-2.5 border-b border-canvas-border">
            <div class="flex items-center justify-between gap-2">
                <span class="text-[10px] font-mono text-slate-500 uppercase tracking-wider">Env</span>
                <span :class="envClass">{{ appStore.environment }}</span>
            </div>
            <div class="mt-1.5 flex items-center gap-2 text-[10px] font-mono text-slate-500">
                <span>PHP {{ appStore.phpVersion }}</span>
                <span class="text-slate-600">·</span>
                <span class="truncate">Laravel {{ appStore.laravelVersion }}</span>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 px-2 py-3 space-y-0.5">
            <div class="px-2 mb-2 text-[10px] font-semibold uppercase tracking-widest text-slate-600">
                Visualize
            </div>

            <NavItem
                v-for="item in navItems"
                :key="item.view"
                :item="item"
                :active="appStore.activeView === item.view"
                @click="navigate(item.view)"
            />

            <div class="px-2 mt-4 mb-2 text-[10px] font-semibold uppercase tracking-widest text-slate-600">
                Analyze
            </div>

            <NavItem
                v-for="item in analyzeItems"
                :key="item.view"
                :item="item"
                :active="appStore.activeView === item.view"
                @click="navigate(item.view)"
            />
        </nav>

        <!-- Keyboard shortcuts hint -->
        <div class="hidden md:block px-4 py-2 border-t border-canvas-border/50">
            <div class="text-[9px] text-slate-700 space-y-0.5">
                <div class="flex justify-between"><span>Focus search</span><kbd class="font-mono">⌘K</kbd></div>
                <div class="flex justify-between"><span>Fit view</span><kbd class="font-mono">F</kbd></div>
                <div class="flex justify-between"><span>Toggle sidebar</span><kbd class="font-mono">B</kbd></div>
                <div class="flex justify-between"><span>Export</span><kbd class="font-mono">⌘E</kbd></div>
                <div class="flex justify-between"><span>Refresh</span><kbd class="font-mono">⌘R</kbd></div>
            </div>
        </div>

        <!-- Bottom actions -->
        <div class="px-3 py-3 border-t border-canvas-border space-y-1">
            <button
                class="lv-btn-ghost w-full justify-start text-xs gap-2"
                :class="{ 'opacity-60 pointer-events-none': appStore.globalRefreshing }"
                @click="handleRefresh"
            >
                <svg
                    :class="['w-3.5 h-3.5 shrink-0 transition-transform', appStore.globalRefreshing ? 'animate-spin' : '']"
                    viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                >
                    <path d="M13.5 2.5A6.5 6.5 0 1 1 7 1.5"/>
                    <polyline points="10 1 13.5 1 13.5 4.5"/>
                </svg>
                <span>{{ appStore.globalRefreshing ? 'Refreshing...' : 'Refresh data' }}</span>
            </button>
        </div>

    </aside>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAppStore } from '@/stores/app'
import NavItem from './NavItem.vue'

const appStore = useAppStore()
const router   = useRouter()

const navItems = [
    { view: 'overview', label: 'Overview', icon: 'grid',     color: 'text-slate-400' },
    { view: 'database', label: 'Database', icon: 'database', color: 'text-node-db' },
    { view: 'routes',   label: 'Routes',   icon: 'route',    color: 'text-node-route' },
    { view: 'models',   label: 'Models',   icon: 'model',    color: 'text-node-model' },
]

const analyzeItems = [
    { view: 'services', label: 'Services', icon: 'service', color: 'text-node-service' },
    { view: 'events',   label: 'Events',   icon: 'event',   color: 'text-node-event' },
    { view: 'jobs',     label: 'Jobs',     icon: 'job',     color: 'text-node-job' },
    { view: 'flow',     label: 'App Flow', icon: 'flow',    color: 'text-accent-cyan' },
]

const envClass = computed(() => {
    const map = {
        local:      'lv-badge bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
        production: 'lv-badge bg-red-500/10     text-red-400     border border-red-500/20',
        staging:    'lv-badge bg-amber-500/10   text-amber-400   border border-amber-500/20',
        testing:    'lv-badge bg-blue-500/10    text-blue-400    border border-blue-500/20',
    }
    return map[appStore.environment] ?? 'lv-badge bg-slate-500/10 text-slate-400 border border-slate-500/20'
})

function navigate(view) {
    appStore.setActiveView(view)
    router.push(`/canvas/${view}`)
    // Auto-close on mobile after navigating
    if (window.innerWidth < 768) appStore.toggleSidebar()
}

async function handleRefresh() {
    await appStore.refreshAll()
}
</script>
