import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { api } from '@/composables/useApi'

export const useAppStore = defineStore('app', () => {
    // ── State ──────────────────────────────────────────────────────────
    const meta        = ref(null)
    const loading     = ref(false)
    const error       = ref(null)
    const activeView  = ref('database') // database | routes | models | services | events | jobs | flow
    const sidebarOpen = ref(true)
    const detailPanel = ref(null)       // currently selected node payload
    const searchQuery = ref('')
    const notifications = ref([])

    // ── Getters ────────────────────────────────────────────────────────
    const appName = computed(() => meta.value?.app_name ?? window.__LARAVIZ__?.appName ?? 'Laravel')
    const environment = computed(() => meta.value?.environment ?? window.__LARAVIZ__?.environment ?? 'local')
    const laravelVersion = computed(() => meta.value?.laravel_version ?? window.__LARAVIZ__?.laravelVersion ?? '')
    const phpVersion = computed(() => meta.value?.php_version ?? window.__LARAVIZ__?.phpVersion ?? '')

    // ── Actions ────────────────────────────────────────────────────────
    async function fetchMeta() {
        try {
            const res = await api.get('/meta')
            meta.value = res.data.data
        } catch (e) {
            // Non-fatal — bootstrap data from window is enough
        }
    }

    function setActiveView(view) {
        activeView.value = view
        detailPanel.value = null
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

    function notify(message, type = 'info', duration = 3000) {
        const id = Date.now()
        notifications.value.push({ id, message, type })
        setTimeout(() => {
            notifications.value = notifications.value.filter(n => n.id !== id)
        }, duration)
    }

    return {
        meta, loading, error, activeView, sidebarOpen,
        detailPanel, searchQuery, notifications,
        appName, environment, laravelVersion, phpVersion,
        fetchMeta, setActiveView, setDetailPanel,
        closeDetailPanel, toggleSidebar, notify,
    }
})
