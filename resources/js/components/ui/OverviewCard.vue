<template>
    <div
        class="lv-panel p-4 cursor-pointer hover:border-canvas-active transition-all duration-200 group"
        @click="$emit('click')"
    >
        <div class="flex items-start justify-between mb-3">
            <span class="text-2xl leading-none">{{ icon }}</span>
            <svg class="w-3.5 h-3.5 text-slate-600 group-hover:text-slate-400 transition-colors"
                 viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="3" y1="13" x2="13" y2="3"/>
                <polyline points="7 3 13 3 13 9"/>
            </svg>
        </div>
        <div v-if="loading" class="h-7 w-16 bg-canvas-hover rounded animate-pulse mb-1" />
        <div v-else :class="['text-2xl font-bold font-mono', `text-${color}`]">{{ value }}</div>
        <div class="text-xs font-semibold text-slate-300 mt-1">{{ label }}</div>
        <div v-if="sublabel" class="text-[10px] text-slate-600 mt-0.5 truncate">{{ sublabel }}</div>
    </div>
</template>

<script setup>
defineProps({
    icon:     String,
    label:    String,
    value:    [String, Number],
    sublabel: String,
    color:    { type: String, default: 'slate-300' },
    loading:  { type: Boolean, default: false },
})
defineEmits(['click'])
</script>
