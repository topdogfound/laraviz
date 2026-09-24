<template>
    <div class="flex h-full w-full overflow-hidden bg-canvas-bg">

        <!-- ── Sidebar ───────────────────────────────────────────────── -->
        <Transition name="slide-right">
            <AppSidebar v-if="appStore.sidebarOpen" />
        </Transition>

        <!-- ── Main area ─────────────────────────────────────────────── -->
        <div class="flex flex-col flex-1 min-w-0 overflow-hidden">

            <!-- Top bar -->
            <AppTopBar />

            <!-- Canvas area -->
            <div class="relative flex-1 overflow-hidden">

                <!-- Dynamic canvas panel based on active view -->
                <Transition name="fade" mode="out-in">
                    <component :is="activeComponent" :key="appStore.activeView" />
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

        <!-- ── Notifications ─────────────────────────────────────────── -->
        <NotificationStack :notifications="appStore.notifications" />

    </div>
</template>

<script setup>
import { computed, watch, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAppStore } from '@/stores/app'

import AppSidebar    from '@/components/ui/AppSidebar.vue'
import AppTopBar     from '@/components/ui/AppTopBar.vue'
import DetailPanel   from '@/components/ui/DetailPanel.vue'
import NotificationStack from '@/components/ui/NotificationStack.vue'

import DatabaseCanvas   from '@/components/panels/DatabaseCanvas.vue'
import RoutesCanvas     from '@/components/panels/RoutesCanvas.vue'
import ModelsCanvas     from '@/components/panels/ModelsCanvas.vue'
import ServicesCanvas   from '@/components/panels/ServicesCanvas.vue'
import EventsCanvas     from '@/components/panels/EventsCanvas.vue'
import JobsCanvas       from '@/components/panels/JobsCanvas.vue'
import FlowCanvas       from '@/components/panels/FlowCanvas.vue'
import OverviewCanvas   from '@/components/panels/OverviewCanvas.vue'

const props = defineProps({
    view: { type: String, default: 'database' },
})

const appStore = useAppStore()
const route    = useRoute()

// Sync URL param → store
onMounted(() => {
    if (props.view) appStore.setActiveView(props.view)
})

watch(() => route.params.view, v => {
    if (v) appStore.setActiveView(v)
})

const viewMap = {
    database:  DatabaseCanvas,
    routes:    RoutesCanvas,
    models:    ModelsCanvas,
    services:  ServicesCanvas,
    events:    EventsCanvas,
    jobs:      JobsCanvas,
    flow:      FlowCanvas,
    overview:  OverviewCanvas,
}

const activeComponent = computed(() => viewMap[appStore.activeView] ?? DatabaseCanvas)
</script>
