import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { api } from '@/composables/useApi'

export const useServicesStore = defineStore('services', () => {
    const raw     = ref(null)
    const loading = ref(false)
    const error   = ref(null)
    const fetched = ref(false)

    const showCoreProviders = ref(false)
    const showCoreBindings  = ref(false)

    const providers = computed(() => {
        const all = raw.value?.providers ?? []
        return showCoreProviders.value ? all : all.filter(p => !p.is_core)
    })

    const bindings = computed(() => {
        const all = raw.value?.bindings ?? []
        return showCoreBindings.value ? all : all.filter(b => !b.is_core)
    })

    const facades  = computed(() => raw.value?.facades ?? [])
    const singletons = computed(() => raw.value?.singletons ?? [])

    async function fetch() {
        if (fetched.value) return
        loading.value = true
        error.value   = null
        try {
            const res = await api.get('/services')
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
        showCoreProviders, showCoreBindings,
        providers, bindings, facades, singletons,
        fetch, refresh,
    }
})
