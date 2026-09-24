import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { api } from '@/composables/useApi'

export const useRoutesStore = defineStore('routes', () => {
    const raw     = ref(null)
    const loading = ref(false)
    const error   = ref(null)
    const fetched = ref(false)

    const filterMethod    = ref('ALL')
    const filterType      = ref('ALL')   // ALL | api | web
    const filterSearch    = ref('')

    const routes = computed(() => raw.value?.routes ?? [])
    const groups = computed(() => raw.value?.groups ?? [])
    const summary = computed(() => raw.value?.summary ?? {})

    const filteredRoutes = computed(() => {
        let list = routes.value

        if (filterMethod.value !== 'ALL') {
            list = list.filter(r => r.methods.includes(filterMethod.value))
        }
        if (filterType.value !== 'ALL') {
            list = list.filter(r => filterType.value === 'api' ? r.is_api : !r.is_api)
        }
        if (filterSearch.value.trim()) {
            const q = filterSearch.value.toLowerCase()
            list = list.filter(r =>
                r.uri.toLowerCase().includes(q) ||
                (r.name ?? '').toLowerCase().includes(q) ||
                (r.controller ?? '').toLowerCase().includes(q)
            )
        }

        return list
    })

    async function fetch() {
        if (fetched.value) return
        loading.value = true
        error.value   = null
        try {
            const res = await api.get('/routes')
            raw.value   = res.data.data
            fetched.value = true
        } catch (e) {
            error.value = e.message
        } finally {
            loading.value = false
        }
    }

    async function refresh() {
        fetched.value = false
        await fetch()
    }

    return {
        raw, loading, error, fetched,
        filterMethod, filterType, filterSearch,
        routes, groups, summary, filteredRoutes,
        fetch, refresh,
    }
})
