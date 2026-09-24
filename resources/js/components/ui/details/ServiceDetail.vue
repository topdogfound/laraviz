<template>
    <div class="space-y-4 text-sm">
        <div class="font-mono text-xs text-slate-500 break-all">{{ data.class }}</div>

        <div class="flex flex-wrap gap-1.5">
            <span v-if="data.is_deferred" class="lv-badge bg-amber-500/10 text-amber-400 border border-amber-500/20">Deferred</span>
            <span v-if="!data.is_core"    class="lv-badge bg-node-service/10 text-node-service border border-node-service/20">App Provider</span>
            <span v-else                  class="lv-badge bg-slate-500/10 text-slate-400 border border-canvas-border">Core</span>
        </div>

        <Section title="File" v-if="data.file">
            <span class="font-mono text-xs text-slate-500 break-all">{{ data.file }}</span>
        </Section>
    </div>
</template>

<script setup>
import Section from '../Section.vue'
defineProps({ data: Object })
</script>
