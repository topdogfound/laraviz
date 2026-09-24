import { ref, watch } from 'vue'

/**
 * Animates a number from its previous value to a new target.
 * Returns a reactive ref that counts up smoothly.
 */
export function useAnimatedCounter(source, duration = 800) {
    const display = ref(0)
    let raf = null

    watch(source, (newVal) => {
        const target = typeof newVal === 'number' ? newVal : parseInt(newVal) || 0
        const start  = display.value
        const startTime = performance.now()

        function tick(now) {
            const elapsed  = now - startTime
            const progress = Math.min(elapsed / duration, 1)
            // Ease out cubic
            const ease = 1 - Math.pow(1 - progress, 3)
            display.value = Math.round(start + (target - start) * ease)
            if (progress < 1) raf = requestAnimationFrame(tick)
        }

        cancelAnimationFrame(raf)
        raf = requestAnimationFrame(tick)
    }, { immediate: true })

    return display
}
