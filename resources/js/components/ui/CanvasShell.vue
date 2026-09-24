<template>
    <div class="relative w-full h-full">

        <!-- Loading overlay -->
        <Transition name="fade">
            <div v-if="loading" class="absolute inset-0 z-20 flex items-center justify-center bg-canvas-bg/80 backdrop-blur-sm">
                <div class="flex flex-col items-center gap-4">
                    <div class="w-8 h-8 border-2 border-canvas-border border-t-accent-cyan rounded-full animate-spin" />
                    <span class="text-sm text-slate-500 font-mono">{{ loadingText }}</span>
                </div>
            </div>
        </Transition>

        <!-- Error overlay -->
        <Transition name="fade">
            <div v-if="error" class="absolute inset-0 z-20 flex items-center justify-center">
                <div class="lv-panel p-6 max-w-sm text-center space-y-3">
                    <div class="text-2xl">⚠️</div>
                    <p class="text-sm text-red-400">{{ error }}</p>
                    <button class="lv-btn-primary text-xs" @click="$emit('retry')">Retry</button>
                </div>
            </div>
        </Transition>

        <!-- Empty state -->
        <Transition name="fade">
            <div v-if="!loading && !error && isEmpty" class="absolute inset-0 z-20 flex items-center justify-center">
                <div class="lv-panel p-8 max-w-sm text-center space-y-3">
                    <div class="text-3xl">🔭</div>
                    <p class="text-sm text-slate-400">{{ emptyText }}</p>
                </div>
            </div>
        </Transition>

        <!-- Vue Flow canvas -->
        <VueFlow
            v-bind="flowProps"
            :nodes="nodes"
            :edges="edges"
            :default-edge-options="defaultEdgeOptions"
            fit-view-on-init
            class="w-full h-full"
            @node-click="onNodeClick"
            @pane-click="onPaneClick"
        >
            <!-- Dot grid background -->
            <Background
                :variant="BackgroundVariant.Dots"
                :gap="24"
                :size="1"
                pattern-color="#1e3a5f"
            />

            <!-- Minimap -->
            <MiniMap
                v-if="showMinimap"
                :node-color="minimapNodeColor"
                :mask-color="'rgba(5,10,20,0.7)'"
                position="bottom-right"
                :width="140"
                :height="90"
            />

            <!-- Controls -->
            <Controls position="bottom-left" :show-interactive="false" />

            <!-- Pass through all named slots (custom node types) -->
            <template v-for="(_, name) in $slots" #[name]="slotProps">
                <slot :name="name" v-bind="slotProps ?? {}" />
            </template>

        </VueFlow>

        <!-- Floating action bar -->
        <div class="absolute top-3 right-3 z-10 flex items-center gap-1.5">
            <slot name="actions" />

            <button class="lv-btn-ghost text-xs py-1 px-2.5" @click="handleFitView" title="Fit view">
                <svg class="w-3.5 h-3.5" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M1 5V2h3M15 5V2h-3M1 11v3h3M15 11v3h-3"/>
                </svg>
                Fit
            </button>

            <button
                class="lv-btn-ghost text-xs py-1 px-2.5"
                :class="{ 'text-accent-cyan border-accent-cyan/30 bg-accent-cyan/5': showMinimap }"
                @click="showMinimap = !showMinimap"
                title="Toggle minimap"
            >
                <svg class="w-3.5 h-3.5" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                    <rect x="1" y="1" width="14" height="14" rx="2"/>
                    <rect x="3" y="3" width="4" height="4" rx="1"/>
                </svg>
                Map
            </button>
        </div>

        <!-- Node / edge count badge -->
        <div class="absolute bottom-3 left-36 z-10 lv-badge bg-canvas-panel/80 text-slate-500 border border-canvas-border text-[10px] font-mono backdrop-blur-sm">
            {{ nodes.length }} nodes · {{ edges.length }} edges
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { VueFlow, useVueFlow, BackgroundVariant } from '@vue-flow/core'
import { Background, MiniMap, Controls } from '@vue-flow/additional-components'
import '@vue-flow/core/dist/style.css'
import '@vue-flow/additional-components/dist/style.css'

const props = defineProps({
    nodes:       { type: Array, default: () => [] },
    edges:       { type: Array, default: () => [] },
    loading:     { type: Boolean, default: false },
    error:       { type: String, default: null },
    isEmpty:     { type: Boolean, default: false },
    loadingText: { type: String, default: 'Loading...' },
    emptyText:   { type: String, default: 'Nothing to display yet.' },
    flowProps:   { type: Object, default: () => ({}) },
})

const emit = defineEmits(['node-click', 'pane-click', 'init', 'retry'])

const showMinimap = ref(true)
const { fitView } = useVueFlow()

const defaultEdgeOptions = {
    type: 'smoothstep',
    animated: false,
    style: { stroke: '#1e3a5f', strokeWidth: 1.5 },
}

function handleFitView() {
    fitView({ padding: 0.15, duration: 600 })
}

function onNodeClick({ node }) {
    emit('node-click', node)
}

function onPaneClick() {
    emit('pane-click')
}

function minimapNodeColor(node) {
    const map = {
        tableNode:      '#0ea5e9',
        modelNode:      '#10b981',
        routeNode:      '#f59e0b',
        routeGroupNode: '#f59e0b',
        serviceNode:    '#a855f7',
        eventNode:      '#ec4899',
        jobNode:        '#f97316',
        middlewareNode: '#06b6d4',
        flowNode:       '#00d4ff',
    }
    return map[node.type] ?? '#1e3a5f'
}

defineExpose({ fitView: handleFitView })
</script>
