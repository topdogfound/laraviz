<template>
    <header class="flex items-center gap-2 h-11 px-3 border-b border-canvas-border bg-canvas-panel/90 backdrop-blur-sm shrink-0 z-10">

        <!-- Sidebar toggle -->
        <button
            class="lv-btn-ghost p-1.5 rounded-md shrink-0"
            @click="appStore.toggleSidebar()"
            title="Toggle sidebar (B)"
        >
            <svg class="w-4 h-4" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                <rect x="1" y="1" width="14" height="14" rx="2"/>
                <line x1="5" y1="1" x2="5" y2="15"/>
            </svg>
        </button>

        <!-- Breadcrumb (hidden on very small screens) -->
        <div class="hidden sm:flex items-center gap-1.5 text-sm shrink-0">
            <span class="text-slate-500">LaraViz</span>
            <span class="text-slate-700">/</span>
            <span class="text-slate-200 font-medium capitalize">{{ appStore.activeView }}</span>
        </div>

        <!-- Spacer -->
        <div class="flex-1 min-w-0" />

        <!-- Search (collapses on mobile) -->
        <div class="relative hidden sm:block">
            <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3 h-3 text-slate-500 pointer-events-none"
                 viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="6.5" cy="6.5" r="5"/><line x1="10.5" y1="10.5" x2="14" y2="14"/>
            </svg>
            <input
                v-model="appStore.searchQuery"
                type="text"
                placeholder="Search..."
                class="w-36 lg:w-48 bg-canvas-bg border border-canvas-border rounded-lg pl-7 pr-7 py-1 text-xs text-slate-300
                       placeholder-slate-600 focus:outline-none focus:border-accent-cyan/50 focus:ring-1 focus:ring-accent-cyan/20
                       transition-all focus:w-56"
            />
            <button
                v-if="appStore.searchQuery"
                class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-500 hover:text-slate-200 transition-colors"
                @click="appStore.searchQuery = ''"
            >
                <svg class="w-3 h-3" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="3" y1="3" x2="13" y2="13"/><line x1="13" y1="3" x2="3" y2="13"/>
                </svg>
            </button>
            <kbd v-else class="absolute right-2 top-1/2 -translate-y-1/2 text-[9px] font-mono text-slate-600 bg-canvas-surface border border-canvas-border rounded px-1 pointer-events-none">
                ⌘K
            </kbd>
        </div>

        <!-- Mobile search icon -->
        <button
            class="sm:hidden lv-btn-ghost p-1.5 rounded-md"
            @click="mobileSearchOpen = !mobileSearchOpen"
        >
            <svg class="w-4 h-4" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="6.5" cy="6.5" r="5"/><line x1="10.5" y1="10.5" x2="14" y2="14"/>
            </svg>
        </button>

        <!-- View-specific slot -->
        <slot />

        <!-- Refresh button with spinner -->
        <button
            class="lv-btn-ghost p-1.5 rounded-md shrink-0 hidden sm:flex"
            :class="{ 'text-accent-cyan': appStore.globalRefreshing }"
            :title="appStore.globalRefreshing ? 'Refreshing...' : 'Refresh data (⌘R)'"
            @click="appStore.refreshAll()"
            :disabled="appStore.globalRefreshing"
        >
            <svg
                :class="['w-4 h-4', appStore.globalRefreshing ? 'animate-spin' : '']"
                viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
            >
                <path d="M13.5 2.5A6.5 6.5 0 1 1 7 1.5"/>
                <polyline points="10 1 13.5 1 13.5 4.5"/>
            </svg>
        </button>

        <!-- Fit view button -->
        <button
            class="lv-btn-ghost p-1.5 rounded-md shrink-0 hidden sm:flex"
            title="Fit view (F)"
            @click="$emit('fit-view')"
        >
            <svg class="w-4 h-4" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M1 5V2h3M15 5V2h-3M1 11v3h3M15 11v3h-3"/>
            </svg>
        </button>

        <!-- Export button -->
        <button
            class="lv-btn-primary text-xs py-1 px-2.5 shrink-0 gap-1.5"
            title="Export (⌘E)"
            @click="$emit('export')"
        >
            <svg class="w-3.5 h-3.5" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M2 10v4h12v-4M8 1v9M5 7l3 3 3-3"/>
            </svg>
            <span class="hidden sm:inline">Export</span>
        </button>

    </header>

    <!-- Mobile search bar (expandable) -->
    <Transition name="slide-up">
        <div v-if="mobileSearchOpen" class="sm:hidden px-3 py-2 border-b border-canvas-border bg-canvas-panel/90">
            <div class="relative">
                <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-500 pointer-events-none"
                     viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="6.5" cy="6.5" r="5"/><line x1="10.5" y1="10.5" x2="14" y2="14"/>
                </svg>
                <input
                    v-model="appStore.searchQuery"
                    type="text"
                    placeholder="Search..."
                    class="w-full bg-canvas-bg border border-canvas-border rounded-lg pl-8 pr-3 py-1.5 text-sm text-slate-300
                           placeholder-slate-600 focus:outline-none focus:border-accent-cyan/50"
                    autofocus
                />
            </div>
        </div>
    </Transition>
</template>

<script setup>
import { ref } from 'vue'
import { useAppStore } from '@/stores/app'

const appStore = useAppStore()
const mobileSearchOpen = ref(false)

defineEmits(['fit-view', 'export'])
</script>
