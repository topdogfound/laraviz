import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { api } from '@/composables/useApi'

export const useDatabaseStore = defineStore('database', () => {
    const raw       = ref(null)
    const loading   = ref(false)
    const error     = ref(null)
    const fetched   = ref(false)

    // Which tables are expanded in the node
    const expandedTables = ref(new Set())
    const selectedTable  = ref(null)

    const connections = computed(() => {
        if (! raw.value) return []
        return Object.entries(raw.value).map(([name, conn]) => ({ name, ...conn }))
    })

    const allTables = computed(() => {
        const tables = []
        for (const conn of connections.value) {
            if (conn.tables) {
                Object.values(conn.tables).forEach(t => {
                    tables.push({ ...t, connection: conn.connection, driver: conn.driver })
                })
            }
        }
        return tables
    })

    const foreignKeys = computed(() => {
        const keys = []
        for (const conn of connections.value) {
            if (conn.foreign_keys) keys.push(...conn.foreign_keys)
        }
        return keys
    })

    async function fetch() {
        if (fetched.value) return
        loading.value = true
        error.value   = null
        try {
            const res = await api.get('/database')
            raw.value = res.data.data
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

    function toggleTable(tableName) {
        if (expandedTables.value.has(tableName)) {
            expandedTables.value.delete(tableName)
        } else {
            expandedTables.value.add(tableName)
        }
    }

    function selectTable(tableName) {
        selectedTable.value = tableName
    }

    return {
        raw, loading, error, fetched,
        expandedTables, selectedTable,
        connections, allTables, foreignKeys,
        fetch, refresh, toggleTable, selectTable,
    }
})
