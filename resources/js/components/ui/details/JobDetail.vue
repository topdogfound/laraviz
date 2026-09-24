<template>
    <div class="space-y-4 text-sm">
        <div class="font-mono text-xs text-slate-500 break-all">{{ data.class }}</div>

        <div class="flex flex-wrap gap-1.5">
            <span v-if="data.is_queued"   class="lv-badge bg-node-job/10 text-node-job border border-node-job/20">Queued</span>
            <span v-else                  class="lv-badge bg-slate-500/10 text-slate-400 border border-canvas-border">Synchronous</span>
            <span v-if="data.is_unique"   class="lv-badge bg-purple-500/10 text-purple-400 border border-purple-500/20">Unique</span>
            <span v-if="data.is_encrypted" class="lv-badge bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Encrypted</span>
        </div>

        <Section title="Queue Config" v-if="data.queue || data.connection || data.tries || data.timeout">
            <div class="space-y-1 font-mono text-xs text-slate-400">
                <div v-if="data.queue">      Queue: <span class="text-accent-cyan">{{ data.queue }}</span></div>
                <div v-if="data.connection"> Connection: <span class="text-accent-cyan">{{ data.connection }}</span></div>
                <div v-if="data.tries">      Tries: <span class="text-slate-200">{{ data.tries }}</span></div>
                <div v-if="data.timeout">    Timeout: <span class="text-slate-200">{{ data.timeout }}s</span></div>
            </div>
        </Section>

        <Section title="File">
            <span class="font-mono text-xs text-slate-500 break-all">{{ data.file }}</span>
        </Section>
    </div>
</template>

<script setup>
import Section from '../Section.vue'
defineProps({ data: Object })
</script>
