<template>
    <div class="flex h-full w-full overflow-hidden bg-canvas-bg">

        <!-- ── Sidebar overlay backdrop (mobile) ────────────────────── -->
        <Transition name="fade">
            <div
                v-if="appStore.sidebarOpen && !isDesktop"
                class="fixed inset-0 bg-black/50 z-20 md:hidden"
                @click="appStore.toggleSidebar()"
            />
        </Transition>

        <!-- ── Sidebar ───────────────────────────────────────────────── -->
        <Transition name="slide-right">
            <AppSidebar
                v-if="appStore.sidebarOpen"
                :class="[
                    'z-30',
                    !isDesktop ? 'fixed left-0 top-0 bottom-0 shadow-2xl' : 'relative'
                ]"
            />
        </Transition>

        <!-- ── Main area ─────────────────────────────────────────────── -->
        <div class="flex flex-col flex-1 min-w-0 overflow-hidden">

            <!-- Top bar — wired up for export + fit-view -->
            <AppTopBar
                @export="appStore.openExportModal()"
                @fit-view="triggerFitView"
            />

            <!-- Canvas area -->
            <div class="relative flex-1 overflow-hidden">

                <!-- Dynamic canvas panel -->
                <Transition name="fade" mode="out-in">
                    <component
                        :is="activeComponent"
                        :key="appStore.activeView"
                        :ref="el => { canvasRef = el }"
                    />
                </Transition>

                <!-- Detail slide-over panel -->
                <Transition name="slide-right">
                    <DetailPanel
                        v-if="appStore.detailPanel"
                        :payload="appStore.detailPanel"
                        @close="appStore.closeDetailPanel()"
                    />
                </Transition>

            </div>
        </div>

        <!-- ── Export Modal ───────────────────────────────────────────── -->
        <ExportModal
            :open="appStore.exportModalOpen"
            @close="appStore.closeExportModal()"
        />

        <!-- ── Notifications ─────────────────────────────────────────── -->
        <NotificationStack :notifications="appStore.notifications" />

        <!-- ── Keyboard shortcuts ────────────────────────────────────── -->
        <!-- Handled in onKeyDown below -->

    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAppStore } from '@/stores/app'

import AppSidebar        from '@/components/ui/AppSidebar.vue'
import AppTopBar         from '@/components/ui/AppTopBar.vue'
import DetailPanel       from '@/components/ui/DetailPanel.vue'
import NotificationStack from '@/components/ui/NotificationStack.vue'
import ExportModal       from '@/components/ui/ExportModal.vue'

import DatabaseCanvas  from '@/components/panels/DatabaseCanvas.vue'
import RoutesCanvas    from '@/components/panels/RoutesCanvas.vue'
import ModelsCanvas    from '@/components/panels/ModelsCanvas.vue'
import ServicesCanvas  from '@/components/panels/ServicesCanvas.vue'
import EventsCanvas    from '@/components/panels/EventsCanvas.vue'
import JobsCanvas      from '@/components/panels/JobsCanvas.vue'
import FlowCanvas      from '@/components/panels/FlowCanvas.vue'
import OverviewCanvas  from '@/components/panels/OverviewCanvas.vue'

const props = defineProps({ view: { type: String, default: 'database' } })

const appStore  = useAppStore()
const route     = useRoute()
const router    = useRouter()
const canvasRef = ref(null)
const isDesktop = ref(window.innerWidth >= 768)

function onResize() { isDesktop.value = window.innerWidth >= 768 }

// ── View routing ──────────────────────────────────────────────────────────
onMounted(() => {
    if (props.view) appStore.setActiveView(props.view)
    window.addEventListener('resize', onResize)
    window.addEventListener('keydown', onKeyDown)
})

onUnmounted(() => {
    window.removeEventListener('resize', onResize)
    window.removeEventListener('keydown', onKeyDown)
})

watch(() => route.params.view, v => { if (v) appStore.setActiveView(v) })

// ── Canvas map ────────────────────────────────────────────────────────────
const viewMap = {
    database: DatabaseCanvas,
    routes:   RoutesCanvas,
    models:   ModelsCanvas,
    services: ServicesCanvas,
    events:   EventsCanvas,
    jobs:     JobsCanvas,
    flow:     FlowCanvas,
    overview: OverviewCanvas,
}

const activeComponent = computed(() => viewMap[appStore.activeView] ?? DatabaseCanvas)

// ── Fit view: delegate to CanvasShell's exposed fitView ───────────────────
function triggerFitView() {
    if (canvasRef.value?.fitView) {
        canvasRef.value.fitView()
    }
}

// ── Keyboard shortcuts ────────────────────────────────────────────────────
function onKeyDown(e) {
    // Ignore when typing in inputs
    if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return

    // Cmd/Ctrl+K → focus search
    if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
        e.preventDefault()
        document.querySelector('input[placeholder="Search..."]')?.focus()
    }

    // Cmd/Ctrl+E → open export modal
    if ((e.metaKey || e.ctrlKey) && e.key === 'e') {
        e.preventDefault()
        appStore.openExportModal()
    }

    // Cmd/Ctrl+R → refresh data (prevent browser refresh)
    if ((e.metaKey || e.ctrlKey) && e.key === 'r') {
        e.preventDefault()
        appStore.refreshAll()
    }

    // Escape → close detail panel / export modal / mobile sidebar
    if (e.key === 'Escape') {
        if (appStore.exportModalOpen) { appStore.closeExportModal(); return }
        if (appStore.detailPanel)     { appStore.closeDetailPanel(); return }
        if (appStore.sidebarOpen && !isDesktop.value) { appStore.toggleSidebar() }
    }

    // F → fit view
    if (e.key === 'f' || e.key === 'F') triggerFitView()

    // B → toggle sidebar
    if (e.key === 'b' || e.key === 'B') appStore.toggleSidebar()

    // Number keys 1-8 → quick view switch
    const viewKeys = { '1': 'overview', '2': 'database', '3': 'routes', '4': 'models', '5': 'services', '6': 'events', '7': 'jobs', '8': 'flow' }
    if (viewKeys[e.key] && !e.metaKey && !e.ctrlKey) {
        const v = viewKeys[e.key]
        appStore.setActiveView(v)
        router.push(`/canvas/${v}`)
    }
}
</script>
