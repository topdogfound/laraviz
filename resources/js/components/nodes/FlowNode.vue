<template>
    <div
        :class="[
            'flow-node rounded-xl border-2 transition-all duration-200 cursor-default select-none overflow-hidden',
            selected ? `border-[${data.color}] shadow-[0_0_0_3px_${data.color}30,0_0_40px_${data.color}20]` : 'border-canvas-border shadow-node',
        ]"
        :style="{
            background: `linear-gradient(135deg, ${data.color}12 0%, ${data.color}04 100%)`,
            borderColor: selected ? data.color : undefined,
            minWidth: '160px',
        }"
    >
        <!-- Icon + label -->
        <div class="flex items-center gap-2.5 px-4 py-3">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center text-lg flex-shrink-0"
                 :style="{ background: `${data.color}20`, border: `1px solid ${data.color}40` }">
                {{ data.icon }}
            </div>
            <div>
                <div class="text-sm font-semibold text-slate-100 leading-tight">{{ data.label }}</div>
                <div v-if="data.sublabel" class="text-[10px] font-mono text-slate-500 mt-0.5">{{ data.sublabel }}</div>
            </div>
        </div>

        <!-- Detail list (optional) -->
        <div v-if="data.items?.length" class="border-t border-canvas-border/40 px-3 py-2 space-y-1">
            <div
                v-for="item in data.items.slice(0, 4)"
                :key="item"
                class="text-[10px] font-mono text-slate-500 truncate flex items-center gap-1.5"
            >
                <span :style="{ color: data.color }" class="opacity-60">▸</span>
                {{ item }}
            </div>
            <div v-if="data.items.length > 4" class="text-[10px] text-slate-600">+{{ data.items.length - 4 }} more</div>
        </div>
    </div>

    <Handle type="target" :position="Position.Left"  class="!w-3 !h-3 !border-2 !border-canvas-bg" :style="{ background: data.color }" />
    <Handle type="source" :position="Position.Right" class="!w-3 !h-3 !border-2 !border-canvas-bg" :style="{ background: data.color }" />
</template>

<script setup>
import { Handle, Position } from '@vue-flow/core'
defineProps({ data: Object, selected: Boolean })
</script>
