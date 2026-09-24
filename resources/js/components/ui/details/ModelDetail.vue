<template>
    <div class="space-y-4 text-sm">
        <div class="font-mono text-xs text-slate-500 break-all">{{ data.class }}</div>

        <div class="grid grid-cols-2 gap-2">
            <StatCard label="Relations" :value="data.relations?.length ?? 0" color="text-node-model" />
            <StatCard label="Fillable"  :value="data.fillable?.length ?? 0" color="text-slate-300" />
        </div>

        <!-- Badges -->
        <div class="flex flex-wrap gap-1.5">
            <span v-if="data.soft_deletes" class="lv-badge bg-red-500/10 text-red-400 border border-red-500/20">SoftDeletes</span>
            <span v-if="!data.timestamps" class="lv-badge bg-slate-500/10 text-slate-400 border border-canvas-border">No timestamps</span>
            <span
                v-for="trait in (data.traits ?? []).filter(t => !['HasFactory','SoftDeletes'].includes(t))"
                :key="trait"
                class="lv-badge bg-canvas-bg text-slate-400 border border-canvas-border font-mono text-[10px]"
            >{{ trait }}</span>
        </div>

        <!-- Table -->
        <Section title="Table">
            <span class="font-mono text-xs text-accent-cyan">{{ data.table }}</span>
        </Section>

        <!-- Relations -->
        <Section title="Relations" v-if="data.relations?.length">
            <div class="space-y-1">
                <div
                    v-for="rel in data.relations"
                    :key="rel.method"
                    class="flex items-center gap-2 text-xs px-2 py-1.5 rounded-lg bg-canvas-bg border border-canvas-border/50"
                >
                    <span class="text-node-model font-mono">{{ rel.method }}()</span>
                    <span class="text-slate-600">→</span>
                    <span class="text-slate-400">{{ rel.type }}</span>
                    <span v-if="rel.related" class="ml-auto text-slate-500 text-[10px]">{{ rel.related }}</span>
                </div>
            </div>
        </Section>

        <!-- Scopes -->
        <Section title="Scopes" v-if="data.scopes?.length">
            <div class="flex flex-wrap gap-1.5">
                <span
                    v-for="s in data.scopes"
                    :key="s"
                    class="lv-badge bg-canvas-bg text-emerald-400 border border-emerald-500/20 font-mono text-[10px]"
                >scope:{{ s }}</span>
            </div>
        </Section>
    </div>
</template>

<script setup>
import StatCard from '../StatCard.vue'
import Section  from '../Section.vue'
defineProps({ data: Object })
</script>
