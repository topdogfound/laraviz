<template>
    <div class="space-y-2">
        <div class="text-[10px] font-semibold uppercase tracking-widest text-slate-500">{{ title }}</div>
        <slot />
    </div>
</template>

<script setup>
defineProps({ title: String })
</script>
