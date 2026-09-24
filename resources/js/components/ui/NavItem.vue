<template>
    <button
        :class="[
            'w-full flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg text-sm transition-all duration-150 cursor-pointer select-none',
            active
                ? 'bg-accent-cyan/10 text-accent-cyan border border-accent-cyan/20'
                : 'text-slate-400 hover:text-slate-100 hover:bg-canvas-hover border border-transparent'
        ]"
        @click="$emit('click')"
    >
        <!-- Icon -->
        <span :class="['w-4 h-4 flex-shrink-0', active ? 'text-accent-cyan' : item.color]">
            <component :is="iconComponent" class="w-4 h-4" />
        </span>

        <span class="font-medium">{{ item.label }}</span>

        <!-- Active indicator dot -->
        <span v-if="active" class="ml-auto w-1.5 h-1.5 rounded-full bg-accent-cyan shadow-glow-cyan" />
    </button>
</template>

<script setup>
import { computed, defineAsyncComponent } from 'vue'

const props = defineProps({
    item:   { type: Object, required: true },
    active: { type: Boolean, default: false },
})

defineEmits(['click'])

const iconMap = {
    grid:     () => import('./icons/IconGrid.vue'),
    database: () => import('./icons/IconDatabase.vue'),
    route:    () => import('./icons/IconRoute.vue'),
    model:    () => import('./icons/IconModel.vue'),
    service:  () => import('./icons/IconService.vue'),
    event:    () => import('./icons/IconEvent.vue'),
    job:      () => import('./icons/IconJob.vue'),
    flow:     () => import('./icons/IconFlow.vue'),
}

const iconComponent = computed(() =>
    defineAsyncComponent(iconMap[props.item.icon] ?? iconMap.grid)
)
</script>
