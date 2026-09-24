<template>
    <div class="flex items-center justify-between px-2.5 py-1.5 rounded-lg bg-canvas-bg border border-canvas-border">
        <span class="text-slate-500 text-[11px]">{{ label }}</span>
        <span :class="['font-mono text-[11px] font-medium', warn ? 'text-red-400' : 'text-slate-300']">
            {{ value ?? '—' }}
        </span>
    </div>
</template>
<script setup>
defineProps({ label: String, value: [String, Boolean], warn: Boolean })
</script>
