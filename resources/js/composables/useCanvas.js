import { ref, computed } from 'vue'

/**
 * Shared canvas state: zoom, pan, selected nodes, layout helpers.
 */
export function useCanvas() {
    const zoom       = ref(1)
    const selectedIds = ref([])

    function fitView(instance) {
        instance?.fitView({ padding: 0.15, duration: 600 })
    }

    function zoomIn(instance) {
        instance?.zoomIn({ duration: 300 })
    }

    function zoomOut(instance) {
        instance?.zoomOut({ duration: 300 })
    }

    function resetView(instance) {
        instance?.setTransform({ x: 0, y: 0, zoom: 1 }, { duration: 400 })
    }

    return {
        zoom,
        selectedIds,
        fitView,
        zoomIn,
        zoomOut,
        resetView,
    }
}

/**
 * Auto-layout helper: arrange nodes in a grid or tree.
 */
export function useAutoLayout() {
    function gridLayout(nodes, options = {}) {
        const { cols = 4, colWidth = 300, rowHeight = 240, padding = 60 } = options
        return nodes.map((node, i) => ({
            ...node,
            position: {
                x: (i % cols) * colWidth + padding,
                y: Math.floor(i / cols) * rowHeight + padding,
            },
        }))
    }

    function verticalLayout(nodes, options = {}) {
        const { x = 60, startY = 60, gap = 200 } = options
        return nodes.map((node, i) => ({
            ...node,
            position: { x, y: startY + i * gap },
        }))
    }

    function horizontalLayout(nodes, options = {}) {
        const { y = 60, startX = 60, gap = 280 } = options
        return nodes.map((node, i) => ({
            ...node,
            position: { x: startX + i * gap, y },
        }))
    }

    /**
     * Simple layered/hierarchical layout — groups nodes by a "layer" key.
     * Each layer is a column, nodes within a layer are stacked vertically.
     */
    function layeredLayout(nodes, getLayer, options = {}) {
        const { layerWidth = 320, rowHeight = 180, padX = 80, padY = 60 } = options

        const layers = {}
        for (const node of nodes) {
            const layer = getLayer(node)
            if (! layers[layer]) layers[layer] = []
            layers[layer].push(node)
        }

        const result = []
        const layerKeys = Object.keys(layers).sort()

        for (let li = 0; li < layerKeys.length; li++) {
            const layerNodes = layers[layerKeys[li]]
            for (let ni = 0; ni < layerNodes.length; ni++) {
                result.push({
                    ...layerNodes[ni],
                    position: {
                        x: padX + li * layerWidth,
                        y: padY + ni * rowHeight,
                    },
                })
            }
        }

        return result
    }

    return { gridLayout, verticalLayout, horizontalLayout, layeredLayout }
}

/**
 * Build edge styles based on edge type.
 */
export function edgeStyle(type = 'default') {
    const styles = {
        'foreign-key':  { stroke: '#0ea5e9', strokeWidth: 1.5 },
        'relation':     { stroke: '#10b981', strokeWidth: 1.5 },
        'dependency':   { stroke: '#a855f7', strokeWidth: 1.5 },
        'event':        { stroke: '#ec4899', strokeWidth: 1.5 },
        'flow':         { stroke: '#00d4ff', strokeWidth: 2, strokeDasharray: '6 3' },
        'default':      { stroke: '#1e3a5f', strokeWidth: 1 },
    }
    return styles[type] ?? styles.default
}
