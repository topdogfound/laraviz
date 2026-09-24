<template>
    <div class="flex flex-col gap-0.5 px-3 py-2.5 rounded-lg bg-canvas-bg border border-canvas-border">
        <span :class="['text-lg font-bold font-mono leading-none', color]">{{ value }}</span>
        <span class="text-[10px] text-slate-500 uppercase tracking-wider">{{ label }}</span>
    </div>
</template>

<script setup>
defineProps({
    label: String,
    value: [String, Number],
    color: { type: String, default: 'text-slate-200' },
})
</script>
