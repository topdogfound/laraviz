/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './resources/js/**/*.{js,vue}',
        './resources/views/**/*.blade.php',
    ],
    theme: {
        extend: {
            colors: {
                // LaraViz dark canvas palette
                canvas: {
                    bg:      '#050a14',
                    surface: '#0d1526',
                    panel:   '#0a1628',
                    border:  '#1e3a5f',
                    hover:   '#162a4a',
                    active:  '#1e3a5f',
                },
                accent: {
                    cyan:    '#00d4ff',
                    purple:  '#7c3aed',
                    violet:  '#a855f7',
                    green:   '#10b981',
                    orange:  '#f59e0b',
                    red:     '#ef4444',
                    pink:    '#ec4899',
                },
                // Node type colors
                node: {
                    db:       '#0ea5e9',
                    model:    '#10b981',
                    route:    '#f59e0b',
                    service:  '#a855f7',
                    event:    '#ec4899',
                    job:      '#f97316',
                    middleware: '#06b6d4',
                    config:   '#64748b',
                },
            },
            fontFamily: {
                sans: ['Inter', 'system-ui', 'sans-serif'],
                mono: ['JetBrains Mono', 'ui-monospace', 'monospace'],
            },
            boxShadow: {
                'node':     '0 0 0 1px rgba(0,212,255,0.2), 0 4px 24px rgba(0,0,0,0.4)',
                'node-active': '0 0 0 2px rgba(0,212,255,0.6), 0 0 40px rgba(0,212,255,0.15)',
                'panel':    '0 0 0 1px rgba(30,58,95,0.8), 0 8px 32px rgba(0,0,0,0.5)',
                'glow-cyan': '0 0 20px rgba(0,212,255,0.3)',
                'glow-purple': '0 0 20px rgba(124,58,237,0.3)',
            },
            backgroundImage: {
                'grid-pattern': `
                    linear-gradient(rgba(30,58,95,0.3) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(30,58,95,0.3) 1px, transparent 1px)
                `,
                'gradient-radial': 'radial-gradient(var(--tw-gradient-stops))',
            },
            animation: {
                'pulse-slow':  'pulse 3s cubic-bezier(0.4,0,0.6,1) infinite',
                'glow':        'glow 2s ease-in-out infinite alternate',
                'slide-in-right': 'slideInRight 0.3s ease-out',
                'slide-in-left':  'slideInLeft 0.3s ease-out',
                'fade-in':     'fadeIn 0.2s ease-out',
            },
            keyframes: {
                glow: {
                    '0%':   { boxShadow: '0 0 5px rgba(0,212,255,0.3)' },
                    '100%': { boxShadow: '0 0 20px rgba(0,212,255,0.8), 0 0 40px rgba(0,212,255,0.3)' },
                },
                slideInRight: {
                    '0%':   { transform: 'translateX(100%)', opacity: '0' },
                    '100%': { transform: 'translateX(0)',    opacity: '1' },
                },
                slideInLeft: {
                    '0%':   { transform: 'translateX(-100%)', opacity: '0' },
                    '100%': { transform: 'translateX(0)',      opacity: '1' },
                },
                fadeIn: {
                    '0%':   { opacity: '0', transform: 'translateY(4px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                },
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
    ],
}
