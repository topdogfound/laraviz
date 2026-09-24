<template>
    <div class="space-y-4 text-sm">
        <!-- Stats row -->
        <div class="grid grid-cols-2 gap-2">
            <StatCard label="Columns" :value="data.columns?.length ?? 0" color="text-node-db" />
            <StatCard label="Rows" :value="(data.row_count ?? 0).toLocaleString()" color="text-slate-300" />
        </div>

        <!-- Columns -->
        <Section title="Columns">
            <div class="space-y-1">
                <div
                    v-for="col in data.columns"
                    :key="col.name"
                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg bg-canvas-bg border border-canvas-border/50"
                >
                    <!-- Key indicator -->
                    <span v-if="col.key === 'PRI'" class="text-amber-400 text-xs" title="Primary key">🔑</span>
                    <span v-else-if="col.key === 'MUL'" class="text-blue-400 text-xs" title="Foreign key">🔗</span>
                    <span v-else-if="col.key === 'UNI'" class="text-purple-400 text-xs" title="Unique">◆</span>
                    <span v-else class="w-3.5" />

                    <span class="text-slate-200 font-mono text-xs flex-1 truncate">{{ col.name }}</span>
                    <span class="text-slate-500 font-mono text-[10px]">{{ col.type }}</span>
                    <span v-if="col.nullable" class="text-slate-600 text-[10px]">null</span>
                </div>
            </div>
        </Section>

        <!-- Indexes -->
        <Section title="Indexes" v-if="data.indexes?.length">
            <div class="space-y-1">
                <div
                    v-for="idx in data.indexes"
                    :key="idx.name"
                    class="flex items-center gap-2 text-xs px-2 py-1.5 rounded-lg bg-canvas-bg border border-canvas-border/50"
                >
                    <span :class="idx.primary ? 'text-amber-400' : idx.unique ? 'text-purple-400' : 'text-slate-500'">
                        {{ idx.primary ? 'PRIMARY' : idx.unique ? 'UNIQUE' : 'INDEX' }}
                    </span>
                    <span class="text-slate-400 font-mono truncate">{{ idx.columns?.join(', ') || idx.name }}</span>
                </div>
            </div>
        </Section>
    </div>
</template>

<script setup>
import StatCard from '../StatCard.vue'
import Section  from '../Section.vue'
defineProps({ data: Object })
</script>
