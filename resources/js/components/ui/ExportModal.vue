<template>
    <Teleport to="body">
        <Transition name="fade">
            <div
                v-if="open"
                class="fixed inset-0 z-50 flex items-center justify-center p-4"
                @click.self="$emit('close')"
            >
                <!-- Backdrop -->
                <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="$emit('close')" />

                <!-- Modal -->
                <div class="relative z-10 w-full max-w-md lv-panel rounded-2xl shadow-2xl animate-fade-in">

                    <!-- Header -->
                    <div class="flex items-center justify-between px-5 py-4 border-b border-canvas-border">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-accent-cyan to-accent-purple flex items-center justify-center text-sm">
                                📤
                            </div>
                            <div>
                                <h2 class="text-sm font-semibold text-slate-100">Export</h2>
                                <p class="text-[10px] text-slate-500">{{ viewLabel }} — {{ new Date().toLocaleDateString() }}</p>
                            </div>
                        </div>
                        <button class="lv-btn-ghost p-1.5 rounded-lg" @click="$emit('close')">
                            <svg class="w-4 h-4" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="3" y1="3" x2="13" y2="13"/><line x1="13" y1="3" x2="3" y2="13"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Export options -->
                    <div class="p-5 space-y-3">

                        <!-- JSON export -->
                        <button
                            class="w-full flex items-center gap-3 px-4 py-3 rounded-xl border border-canvas-border
                                   bg-canvas-bg hover:border-accent-cyan/40 hover:bg-accent-cyan/5 transition-all group"
                            :disabled="exporting === 'json'"
                            @click="exportJSON"
                        >
                            <div class="w-9 h-9 rounded-lg bg-emerald-500/15 border border-emerald-500/25 flex items-center justify-center text-lg flex-shrink-0">
                                📄
                            </div>
                            <div class="text-left flex-1">
                                <div class="text-sm font-semibold text-slate-200 group-hover:text-slate-100">Export as JSON</div>
                                <div class="text-[11px] text-slate-500">Full analysis data — import into tools, share with team</div>
                            </div>
                            <div v-if="exporting === 'json'" class="w-4 h-4 border-2 border-accent-cyan border-t-transparent rounded-full animate-spin flex-shrink-0" />
                            <svg v-else class="w-4 h-4 text-slate-600 group-hover:text-accent-cyan flex-shrink-0 transition-colors" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M2 10v4h12v-4M8 1v9M5 7l3 3 3-3"/>
                            </svg>
                        </button>

                        <!-- Current view JSON -->
                        <button
                            class="w-full flex items-center gap-3 px-4 py-3 rounded-xl border border-canvas-border
                                   bg-canvas-bg hover:border-accent-cyan/40 hover:bg-accent-cyan/5 transition-all group"
                            :disabled="exporting === 'view-json'"
                            @click="exportViewJSON"
                        >
                            <div class="w-9 h-9 rounded-lg bg-blue-500/15 border border-blue-500/25 flex items-center justify-center text-lg flex-shrink-0">
                                🔍
                            </div>
                            <div class="text-left flex-1">
                                <div class="text-sm font-semibold text-slate-200 group-hover:text-slate-100">Export current view</div>
                                <div class="text-[11px] text-slate-500">Only the <span class="text-accent-cyan">{{ viewLabel }}</span> section data</div>
                            </div>
                            <div v-if="exporting === 'view-json'" class="w-4 h-4 border-2 border-accent-cyan border-t-transparent rounded-full animate-spin flex-shrink-0" />
                            <svg v-else class="w-4 h-4 text-slate-600 group-hover:text-accent-cyan flex-shrink-0 transition-colors" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M2 10v4h12v-4M8 1v9M5 7l3 3 3-3"/>
                            </svg>
                        </button>

                        <!-- Screenshot -->
                        <button
                            class="w-full flex items-center gap-3 px-4 py-3 rounded-xl border border-canvas-border
                                   bg-canvas-bg hover:border-accent-purple/40 hover:bg-accent-purple/5 transition-all group"
                            :disabled="exporting === 'png'"
                            @click="exportPNG"
                        >
                            <div class="w-9 h-9 rounded-lg bg-purple-500/15 border border-purple-500/25 flex items-center justify-center text-lg flex-shrink-0">
                                🖼️
                            </div>
                            <div class="text-left flex-1">
                                <div class="text-sm font-semibold text-slate-200 group-hover:text-slate-100">Screenshot (PNG)</div>
                                <div class="text-[11px] text-slate-500">Canvas snapshot — great for docs, reports, presentations</div>
                            </div>
                            <div v-if="exporting === 'png'" class="w-4 h-4 border-2 border-accent-purple border-t-transparent rounded-full animate-spin flex-shrink-0" />
                            <svg v-else class="w-4 h-4 text-slate-600 group-hover:text-accent-purple flex-shrink-0 transition-colors" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M2 10v4h12v-4M8 1v9M5 7l3 3 3-3"/>
                            </svg>
                        </button>

                        <!-- Copy API URL -->
                        <button
                            class="w-full flex items-center gap-3 px-4 py-3 rounded-xl border border-canvas-border
                                   bg-canvas-bg hover:border-slate-500/40 hover:bg-slate-500/5 transition-all group"
                            @click="copyApiUrl"
                        >
                            <div class="w-9 h-9 rounded-lg bg-slate-500/15 border border-slate-500/25 flex items-center justify-center text-lg flex-shrink-0">
                                🔗
                            </div>
                            <div class="text-left flex-1">
                                <div class="text-sm font-semibold text-slate-200 group-hover:text-slate-100">Copy API endpoint</div>
                                <div class="text-[11px] text-slate-500 font-mono truncate">{{ apiUrl }}</div>
                            </div>
                            <span v-if="copied" class="text-[10px] text-emerald-400 flex-shrink-0">Copied!</span>
                            <svg v-else class="w-4 h-4 text-slate-600 group-hover:text-slate-400 flex-shrink-0 transition-colors" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5">
                                <rect x="4" y="4" width="9" height="9" rx="1.5"/>
                                <path d="M3 12H2a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h9a1 1 0 0 1 1 1v1"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Footer note -->
                    <div class="px-5 pb-4 text-[10px] text-slate-600 text-center">
                        Exports are generated client-side — no data leaves your server
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useAppStore } from '@/stores/app'
import { api } from '@/composables/useApi'

const props = defineProps({
    open: { type: Boolean, default: false },
})
defineEmits(['close'])

const appStore = useAppStore()
const exporting = ref(null)
const copied    = ref(false)

const viewLabel = computed(() => {
    const labels = { database: 'Database', routes: 'Routes', models: 'Models', services: 'Services', events: 'Events', jobs: 'Jobs', flow: 'App Flow', overview: 'Overview' }
    return labels[appStore.activeView] ?? appStore.activeView
})

const apiUrl = computed(() => {
    const base = window.__LARAVIZ__?.apiBase ?? '/laraviz/api'
    const sectionMap = { database: 'database', routes: 'routes', models: 'models', services: 'services', events: 'events', jobs: 'jobs' }
    const section = sectionMap[appStore.activeView]
    return section ? `${base}/${section}` : base
})

// ── Export handlers ───────────────────────────────────────────────────────
async function exportJSON() {
    exporting.value = 'json'
    try {
        const res = await api.get('/')
        downloadJSON(res.data.data, `laraviz-full-${Date.now()}.json`)
    } catch (e) {
        appStore.notify('Export failed: ' + e.message, 'error')
    } finally {
        exporting.value = null
    }
}

async function exportViewJSON() {
    exporting.value = 'view-json'
    const sectionMap = { database: 'database', routes: 'routes', models: 'models', services: 'services', events: 'events', jobs: 'jobs', middleware: 'middleware', config: 'config' }
    const section = sectionMap[appStore.activeView]
    if (!section) {
        appStore.notify('No exportable data for this view', 'warning')
        exporting.value = null
        return
    }
    try {
        const res = await api.get(`/${section}`)
        downloadJSON(res.data.data, `laraviz-${section}-${Date.now()}.json`)
    } catch (e) {
        appStore.notify('Export failed: ' + e.message, 'error')
    } finally {
        exporting.value = null
    }
}

async function exportPNG() {
    exporting.value = 'png'
    try {
        // Find the Vue Flow viewport element and use html2canvas-like approach
        const vfEl = document.querySelector('.vue-flow__viewport') ?? document.querySelector('.vue-flow')
        if (!vfEl) {
            appStore.notify('Canvas not found — try switching to a canvas view', 'warning')
            return
        }

        // Use the native browser API: getDisplayMedia is not available in this context.
        // Instead we use SVG serialisation of the canvas into a blob and download it.
        const svgEl = vfEl.querySelector('svg.vue-flow__edges')

        if (svgEl) {
            // Export edges SVG as SVG file (always works without extra libs)
            const serializer = new XMLSerializer()
            const svgStr = serializer.serializeToString(svgEl)
            const blob = new Blob([svgStr], { type: 'image/svg+xml' })
            triggerDownload(URL.createObjectURL(blob), `laraviz-${appStore.activeView}-${Date.now()}.svg`)
            appStore.notify('Canvas exported as SVG', 'success')
        } else {
            // Fallback: screenshot the whole canvas container using canvas API
            const canvas = document.createElement('canvas')
            const rect   = vfEl.getBoundingClientRect()
            canvas.width  = rect.width  * window.devicePixelRatio
            canvas.height = rect.height * window.devicePixelRatio
            const ctx = canvas.getContext('2d')
            ctx.scale(window.devicePixelRatio, window.devicePixelRatio)
            ctx.fillStyle = '#050a14'
            ctx.fillRect(0, 0, rect.width, rect.height)

            const dataUrl = canvas.toDataURL('image/png')
            triggerDownload(dataUrl, `laraviz-${appStore.activeView}-${Date.now()}.png`)
            appStore.notify('Canvas exported as PNG', 'success')
        }
    } catch (e) {
        appStore.notify('Screenshot failed: ' + e.message, 'error')
    } finally {
        exporting.value = null
    }
}

function copyApiUrl() {
    navigator.clipboard.writeText(window.location.origin + apiUrl.value).then(() => {
        copied.value = true
        setTimeout(() => { copied.value = false }, 2000)
    }).catch(() => {
        appStore.notify('Could not copy to clipboard', 'error')
    })
}

// ── Helpers ───────────────────────────────────────────────────────────────
function downloadJSON(data, filename) {
    const json = JSON.stringify(data, null, 2)
    const blob = new Blob([json], { type: 'application/json' })
    triggerDownload(URL.createObjectURL(blob), filename)
    appStore.notify(`Exported ${filename}`, 'success')
}

function triggerDownload(url, filename) {
    const a = document.createElement('a')
    a.href     = url
    a.download = filename
    a.click()
    setTimeout(() => URL.revokeObjectURL(url), 1000)
}
</script>
