<template>
    <div class="w-full h-full bg-canvas-bg text-slate-200 font-sans overflow-hidden flex flex-col">
        <router-view />
    </div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useAppStore } from '@/stores/app'

const appStore = useAppStore()

onMounted(async () => {
    await appStore.fetchMeta()
})
</script>
