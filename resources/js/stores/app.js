import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { api } from '@/composables/useApi'

export const useAppStore = defineStore('app', () => {
    // ── State ──────────────────────────────────────────────────────────
    const meta          = ref(null)
    const loading       = ref(false)
    const error         = ref(null)
    const activeView    = ref('database')
    const sidebarOpen   = ref(window.innerWidth >= 768) // closed on mobile by default
    const detailPanel   = ref(null)
    const searchQuery   = ref('')
    const notifications = ref([])
    const globalRefreshing = ref(false)
    const exportModalOpen  = ref(false)

    // ── Getters ────────────────────────────────────────────────────────
    const appName        = computed(() => meta.value?.app_name       ?? window.__LARAVIZ__?.appName        ?? 'Laravel')
    const environment    = computed(() => meta.value?.environment     ?? window.__LARAVIZ__?.environment    ?? 'local')
    const laravelVersion = computed(() => meta.value?.laravel_version ?? window.__LARAVIZ__?.laravelVersion ?? '')
    const phpVersion     = computed(() => meta.value?.php_version     ?? window.__LARAVIZ__?.phpVersion     ?? '')

    // ── Actions ────────────────────────────────────────────────────────
    async function fetchMeta() {
        try {
            const res = await api.get('/meta')
            meta.value = res.data.data
        } catch {
            // Non-fatal — bootstrap data from window.__LARAVIZ__ is enough
        }
    }

    /**
     * Refresh all Pinia stores that have a refresh() method.
     * Does NOT reload the page — surgically resets fetched flags and re-fetches.
     */
    async function refreshAll() {
        if (globalRefreshing.value) return
        globalRefreshing.value = true

        try {
            // 1. Flush server-side cache first
            try {
                await api.delete('/cache')
            } catch {
                // Cache flush is best-effort — continue even if it fails
            }

            // 2. Dynamically import all stores to avoid circular deps at module load
            const [
                { useDatabaseStore },
                { useRoutesStore },
                { useModelsStore },
                { useServicesStore },
            ] = await Promise.all([
                import('@/stores/database'),
                import('@/stores/routes'),
                import('@/stores/models'),
                import('@/stores/services'),
            ])

            // 3. Reset every store's fetched flag
            const stores = [
                useDatabaseStore(),
                useRoutesStore(),
                useModelsStore(),
                useServicesStore(),
            ]
            stores.forEach(s => { if ('fetched' in s) s.fetched = false })

            // 4. Re-fetch only the active view's store immediately; others lazy-fetch on visit
            const activeStoreMap = {
                database: useDatabaseStore,
                routes:   useRoutesStore,
                models:   useModelsStore,
                services: useServicesStore,
            }
            const ActiveStore = activeStoreMap[activeView.value]
            if (ActiveStore) {
                await ActiveStore().fetch()
            }

            notify('Data refreshed successfully', 'success')
        } catch (e) {
            notify('Refresh failed: ' + e.message, 'error')
        } finally {
            globalRefreshing.value = false
        }
    }

    function setActiveView(view) {
        activeView.value = view
        detailPanel.value = null
        searchQuery.value = ''
    }

    function setDetailPanel(payload) {
        detailPanel.value = payload
    }

    function closeDetailPanel() {
        detailPanel.value = null
    }

    function toggleSidebar() {
        sidebarOpen.value = !sidebarOpen.value
    }

    function openExportModal() {
        exportModalOpen.value = true
    }

    function closeExportModal() {
        exportModalOpen.value = false
    }

    function notify(message, type = 'info', duration = 3500) {
        const id = Date.now() + Math.random()
        notifications.value.push({ id, message, type })
        setTimeout(() => {
            notifications.value = notifications.value.filter(n => n.id !== id)
        }, duration)
    }

    return {
        meta, loading, error, activeView, sidebarOpen,
        detailPanel, searchQuery, notifications,
        globalRefreshing, exportModalOpen,
        appName, environment, laravelVersion, phpVersion,
        fetchMeta, refreshAll, setActiveView,
        setDetailPanel, closeDetailPanel, toggleSidebar,
        openExportModal, closeExportModal, notify,
    }
})
