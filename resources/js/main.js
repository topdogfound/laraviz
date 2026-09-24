import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { createRouter, createWebHistory } from 'vue-router'
import App from './App.vue'

import './style.css'

// ── Routes ────────────────────────────────────────────────────────────────
const routes = [
    {
        path: '/',
        redirect: '/canvas/database',
    },
    {
        path: '/canvas/:view?',
        name: 'canvas',
        component: () => import('./views/CanvasView.vue'),
        props: true,
    },
]

const prefix = window.__LARAVIZ__?.routePrefix ?? 'laraviz'

const router = createRouter({
    history: createWebHistory(`/${prefix}/`),
    routes,
})

// ── App ───────────────────────────────────────────────────────────────────
const app = createApp(App)
app.use(createPinia())
app.use(router)
app.mount('#app')

// Hide loader once Vue is mounted
document.getElementById('laraviz-loader')?.classList.add('hidden')
setTimeout(() => {
    const el = document.getElementById('laraviz-loader')
    if (el) el.remove()
}, 500)
