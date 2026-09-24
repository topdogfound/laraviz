import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { api } from '@/composables/useApi'

export const useModelsStore = defineStore('models', () => {
    const raw     = ref(null)
    const loading = ref(false)
    const error   = ref(null)
    const fetched = ref(false)

    const models = computed(() => raw.value?.models ?? [])

    // Build a relationship graph between models
    const relationGraph = computed(() => {
        const edges = []
        for (const model of models.value) {
            for (const rel of (model.relations ?? [])) {
                if (rel.related) {
                    edges.push({
                        from:   model.short_name,
                        to:     rel.related,
                        type:   rel.type,
                        method: rel.method,
                    })
                }
            }
        }
        return edges
    })

    async function fetch() {
        if (fetched.value) return
        loading.value = true
        error.value   = null
        try {
            const res = await api.get('/models')
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
        models, relationGraph,
        fetch, refresh,
    }
})
