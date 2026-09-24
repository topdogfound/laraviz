<template>
    <div class="space-y-4 text-sm">
        <!-- URI -->
        <div class="px-3 py-2.5 rounded-lg bg-canvas-bg border border-canvas-border font-mono text-xs text-accent-cyan break-all">
            {{ data.uri }}
        </div>

        <!-- Method badges -->
        <div class="flex flex-wrap gap-1.5">
            <span
                v-for="method in data.methods?.filter(m => m !== 'HEAD')"
                :key="method"
                :class="`lv-tag-method-${method.toLowerCase()}`"
            >
                {{ method }}
            </span>
            <span v-if="data.is_api" class="lv-badge bg-purple-500/10 text-purple-400 border border-purple-500/20">API</span>
            <span v-else class="lv-badge bg-slate-500/10 text-slate-400 border border-slate-500/20">Web</span>
        </div>

        <!-- Controller -->
        <Section title="Handler" v-if="data.controller">
            <p class="font-mono text-xs text-slate-300 break-all">{{ data.controller_fqn ?? data.controller }}</p>
            <p v-if="data.method" class="font-mono text-xs text-accent-cyan mt-1">→ {{ data.method }}()</p>
        </Section>

        <!-- Middleware -->
        <Section title="Middleware" v-if="data.middleware?.length">
            <div class="flex flex-wrap gap-1.5">
                <span
                    v-for="mw in data.middleware"
                    :key="mw"
                    class="lv-badge bg-canvas-bg text-slate-400 border border-canvas-border font-mono text-[10px]"
                >{{ mw }}</span>
            </div>
        </Section>

        <!-- Parameters -->
        <Section title="Parameters" v-if="data.parameters?.length">
            <div class="flex flex-wrap gap-1.5">
                <span
                    v-for="p in data.parameters"
                    :key="p"
                    class="lv-badge bg-amber-500/10 text-amber-400 border border-amber-500/20 font-mono text-[10px]"
                >:{{ p }}</span>
            </div>
        </Section>
    </div>
</template>

<script setup>
import Section from '../Section.vue'
defineProps({ data: Object })
</script>
