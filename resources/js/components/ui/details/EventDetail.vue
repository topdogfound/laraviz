<template>
    <div class="space-y-4 text-sm">
        <div class="grid grid-cols-2 gap-2">
            <StatCard label="Listeners" :value="data.listeners?.length ?? 0" color="text-node-event" />
            <StatCard label="Queued"    :value="(data.listeners ?? []).filter(l => l.is_queued).length" color="text-node-job" />
        </div>

        <div class="flex flex-wrap gap-1.5" v-if="data.details">
            <span v-if="data.details.broadcasts" class="lv-badge bg-pink-500/10 text-pink-400 border border-pink-500/20">Broadcasts</span>
        </div>

        <Section title="Listeners" v-if="data.listeners?.length">
            <div class="space-y-1">
                <div
                    v-for="l in data.listeners"
                    :key="l.class ?? 'closure'"
                    class="flex items-center gap-2 text-xs px-2 py-1.5 rounded-lg bg-canvas-bg border border-canvas-border/50"
                >
                    <span v-if="l.is_queued" class="text-node-job" title="Queued">⚡</span>
                    <span class="text-slate-300 font-mono truncate">{{ l.short_name ?? 'Closure' }}</span>
                    <span v-if="l.method" class="text-slate-600 text-[10px] ml-auto">@{{ l.method }}</span>
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
