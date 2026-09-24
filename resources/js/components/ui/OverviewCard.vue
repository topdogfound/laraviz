<template>
    <div
        class="lv-panel p-4 cursor-pointer hover:border-canvas-active transition-all duration-200 group relative overflow-hidden"
        @click="$emit('click')"
    >
        <!-- Subtle background glow on hover -->
        <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"
             :style="{ background: `radial-gradient(circle at 0% 0%, ${glowColor}08 0%, transparent 70%)` }" />

        <div class="relative">
            <div class="flex items-start justify-between mb-3">
                <span class="text-2xl leading-none">{{ icon }}</span>
                <svg class="w-3.5 h-3.5 text-slate-600 group-hover:text-slate-400 transition-colors"
                     viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="3" y1="13" x2="13" y2="3"/>
                    <polyline points="7 3 13 3 13 9"/>
                </svg>
            </div>

            <!-- Animated value -->
            <div v-if="loading" class="h-7 w-16 bg-canvas-hover rounded animate-pulse mb-1" />
            <div v-else :class="['text-2xl font-bold font-mono transition-all', `text-${color}`]">
                {{ displayValue }}
            </div>

            <div class="text-xs font-semibold text-slate-300 mt-1">{{ label }}</div>
            <div v-if="sublabel" class="text-[10px] text-slate-600 mt-0.5 truncate">{{ sublabel }}</div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { useAnimatedCounter } from '@/composables/useAnimatedCounter'

const props = defineProps({
    icon:     String,
    label:    String,
    value:    [String, Number],
    sublabel: String,
    color:    { type: String, default: 'slate-300' },
    loading:  { type: Boolean, default: false },
})
defineEmits(['click'])

const numericValue = computed(() => typeof props.value === 'number' ? props.value : parseInt(props.value) || 0)
const animatedNum  = useAnimatedCounter(numericValue)

// Show animated number only if value is numeric, otherwise show raw
const displayValue = computed(() => {
    if (typeof props.value === 'number') return animatedNum.value
    if (props.value === '—' || props.value == null) return '—'
    if (!isNaN(parseInt(props.value))) return animatedNum.value
    return props.value
})

const glowMap = {
    'node-db': '#0ea5e9', 'node-model': '#10b981', 'node-route': '#f59e0b',
    'node-service': '#a855f7', 'node-event': '#ec4899', 'node-job': '#f97316',
    'slate-300': '#94a3b8',
}
const glowColor = computed(() => glowMap[props.color] ?? '#94a3b8')
</script>
