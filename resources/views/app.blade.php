<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>LaraViz — {{ config('app.name', 'Laravel') }}</title>

    <meta name="description" content="Interactive Laravel application visualizer" />
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🔭</text></svg>" />

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet" />

    {{-- Compiled assets --}}
    @if(file_exists(public_path('vendor/laraviz/manifest.json')))
        @php
            $manifest = json_decode(file_get_contents(public_path('vendor/laraviz/manifest.json')), true);
        @endphp
        @foreach($manifest as $src => $entry)
            @if(isset($entry['isEntry']) && $entry['isEntry'])
                @if(isset($entry['css']))
                    @foreach($entry['css'] as $css)
                        <link rel="stylesheet" href="{{ asset('vendor/laraviz/' . $css) }}" />
                    @endforeach
                @endif
            @endif
        @endforeach
    @else
        {{-- Dev mode: Vite dev server --}}
        <script type="module" src="http://localhost:5173/@vite/client"></script>
        <script type="module" src="http://localhost:5173/resources/js/main.js"></script>
    @endif

    <style>
        :root {
            --laraviz-bg: #050a14;
            --laraviz-surface: #0d1526;
            --laraviz-border: #1e3a5f;
            --laraviz-accent: #00d4ff;
            --laraviz-accent2: #7c3aed;
            --laraviz-text: #e2e8f0;
            --laraviz-text-muted: #64748b;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        html, body, #app {
            width: 100%;
            height: 100%;
            overflow: hidden;
            background: var(--laraviz-bg);
            color: var(--laraviz-text);
            font-family: 'Inter', system-ui, sans-serif;
        }

        /* Loading screen */
        #laraviz-loader {
            position: fixed;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: var(--laraviz-bg);
            z-index: 9999;
            gap: 1.5rem;
            transition: opacity 0.4s ease;
        }

        #laraviz-loader.hidden {
            opacity: 0;
            pointer-events: none;
        }

        .loader-logo {
            font-size: 2.5rem;
            font-weight: 700;
            letter-spacing: -0.03em;
            background: linear-gradient(135deg, #00d4ff, #7c3aed);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .loader-logo span {
            font-weight: 300;
        }

        .loader-bar-wrap {
            width: 200px;
            height: 2px;
            background: #1e3a5f;
            border-radius: 99px;
            overflow: hidden;
        }

        .loader-bar {
            height: 100%;
            width: 40%;
            background: linear-gradient(90deg, #00d4ff, #7c3aed);
            border-radius: 99px;
            animation: loaderSlide 1.2s ease-in-out infinite;
        }

        @keyframes loaderSlide {
            0%   { transform: translateX(-100%); }
            100% { transform: translateX(350%); }
        }

        .loader-hint {
            font-size: 0.75rem;
            color: #475569;
            font-family: 'JetBrains Mono', monospace;
            letter-spacing: 0.05em;
        }
    </style>
</head>
<body>

{{-- Loading screen --}}
<div id="laraviz-loader">
    <div class="loader-logo">Lara<span>Viz</span></div>
    <div class="loader-bar-wrap">
        <div class="loader-bar"></div>
    </div>
    <div class="loader-hint">Analyzing your application...</div>
</div>

{{-- Vue SPA mount point --}}
<div id="app"></div>

{{-- Bootstrap data injected from PHP --}}
<script>
    window.__LARAVIZ__ = {
        apiBase: "{{ url(config('laraviz.route_prefix', 'laraviz') . '/api') }}",
        routePrefix: "{{ config('laraviz.route_prefix', 'laraviz') }}",
        appName: "{{ config('app.name', 'Laravel') }}",
        environment: "{{ app()->environment() }}",
        theme: "{{ config('laraviz.theme', 'dark') }}",
        csrfToken: "{{ csrf_token() }}",
        laravelVersion: "{{ app()->version() }}",
        phpVersion: "{{ PHP_VERSION }}",
    };
</script>

@if(file_exists(public_path('vendor/laraviz/manifest.json')))
    @php
        $manifest = json_decode(file_get_contents(public_path('vendor/laraviz/manifest.json')), true);
    @endphp
    @foreach($manifest as $src => $entry)
        @if(isset($entry['isEntry']) && $entry['isEntry'])
            <script type="module" src="{{ asset('vendor/laraviz/' . $entry['file']) }}"></script>
        @endif
    @endforeach
@endif

</body>
</html>
