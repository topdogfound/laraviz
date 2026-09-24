<template>
    <div class="fixed bottom-4 right-4 z-50 flex flex-col gap-2 pointer-events-none">
        <TransitionGroup name="slide-up">
            <div
                v-for="n in notifications"
                :key="n.id"
                :class="['flex items-center gap-2.5 px-4 py-2.5 rounded-xl border text-sm font-medium shadow-panel pointer-events-auto', typeClass(n.type)]"
            >
                <span :class="dotClass(n.type)" class="w-2 h-2 rounded-full shrink-0" />
                {{ n.message }}
            </div>
        </TransitionGroup>
    </div>
</template>

<script setup>
defineProps({
    notifications: { type: Array, default: () => [] },
})

function typeClass(type) {
    const map = {
        success: 'bg-emerald-950/90 border-emerald-700/50 text-emerald-300',
        error:   'bg-red-950/90 border-red-700/50 text-red-300',
        warning: 'bg-amber-950/90 border-amber-700/50 text-amber-300',
        info:    'bg-canvas-panel border-canvas-border text-slate-300',
    }
    return map[type] ?? map.info
}

function dotClass(type) {
    const map = {
        success: 'bg-emerald-400',
        error:   'bg-red-400',
        warning: 'bg-amber-400',
        info:    'bg-accent-cyan',
    }
    return map[type] ?? map.info
}
</script>
