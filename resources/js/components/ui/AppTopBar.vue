<template>
    <header class="flex items-center gap-3 h-11 px-4 border-b border-canvas-border bg-canvas-panel/80 backdrop-blur-sm shrink-0 z-10">

        <!-- Sidebar toggle -->
        <button class="lv-btn-ghost p-1.5 rounded-md" @click="appStore.toggleSidebar()" title="Toggle sidebar">
            <svg class="w-4 h-4" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                <rect x="1" y="1" width="14" height="14" rx="2"/>
                <line x1="5" y1="1" x2="5" y2="15"/>
            </svg>
        </button>

        <!-- Breadcrumb -->
        <div class="flex items-center gap-1.5 text-sm">
            <span class="text-slate-500">LaraViz</span>
            <span class="text-slate-600">/</span>
            <span class="text-slate-200 font-medium capitalize">{{ appStore.activeView }}</span>
        </div>

        <!-- Spacer -->
        <div class="flex-1" />

        <!-- Search -->
        <div class="relative">
            <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-500 pointer-events-none"
                 viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="6.5" cy="6.5" r="5"/>
                <line x1="10.5" y1="10.5" x2="14" y2="14"/>
            </svg>
            <input
                v-model="appStore.searchQuery"
                type="text"
                placeholder="Search..."
                class="w-44 bg-canvas-bg border border-canvas-border rounded-lg pl-8 pr-3 py-1 text-xs text-slate-300
                       placeholder-slate-600 focus:outline-none focus:border-accent-cyan/50 focus:ring-1 focus:ring-accent-cyan/20
                       transition-all"
            />
            <kbd class="absolute right-2 top-1/2 -translate-y-1/2 text-[9px] font-mono text-slate-600 bg-canvas-surface border border-canvas-border rounded px-1">
                ⌘K
            </kbd>
        </div>

        <!-- View-specific actions (slot) -->
        <slot />

        <!-- Fit view button -->
        <button
            class="lv-btn-ghost p-1.5 rounded-md"
            title="Fit view"
            @click="$emit('fit-view')"
        >
            <svg class="w-4 h-4" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M1 5V2h3M15 5V2h-3M1 11v3h3M15 11v3h-3"/>
            </svg>
        </button>

        <!-- Export button -->
        <button class="lv-btn-primary text-xs py-1 px-2.5" @click="$emit('export')">
            <svg class="w-3.5 h-3.5" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M2 10v4h12v-4M8 1v9M5 7l3 3 3-3"/>
            </svg>
            Export
        </button>

    </header>
</template>

<script setup>
import { useAppStore } from '@/stores/app'
const appStore = useAppStore()
defineEmits(['fit-view', 'export'])
</script>
