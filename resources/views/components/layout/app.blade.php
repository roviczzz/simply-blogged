@props(['title' => null, 'description' => null, 'image' => null, 'type' => 'website', 'highlight' => false])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

        <x-layout.seo :title="$title" :description="$description" :image="$image" :type="$type" />

        <link rel="alternate" type="application/rss+xml" title="{{ config('app.name') }}" href="{{ route('feed') }}">

        @fonts

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif

        @if ($highlight)
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/themes/prism-tomorrow.min.css">
        @endif
    </head>
    <body class="flex min-h-screen flex-col bg-white font-sans text-zinc-900 antialiased dark:bg-zinc-950 dark:text-zinc-100">
        <header class="border-b border-zinc-200 dark:border-zinc-800">
            <div class="mx-auto flex w-full max-w-3xl items-center justify-between gap-4 px-4 py-5 sm:px-6">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                    <img src="{{ asset('simply-blogged.webp') }}" alt="" class="h-8 w-8 rounded-lg object-contain">
                    <span class="text-xl font-semibold tracking-tight">{{ config('app.name') }}</span>
                </a>

                <nav class="flex items-center gap-5 text-sm font-medium text-zinc-600 dark:text-zinc-400">
                    <a href="{{ route('home') }}" class="hover:text-zinc-900 dark:hover:text-white">Posts</a>
                    <a href="{{ route('feed') }}" class="hover:text-zinc-900 dark:hover:text-white">RSS</a>

                    @guest
                        <a href="{{ route('filament.admin.auth.login') }}" class="hover:text-zinc-900 dark:hover:text-white">Sign in</a>
                    @endguest

                    @auth
                        <a href="{{ route('filament.admin.pages.dashboard') }}" class="inline-flex items-center gap-2 hover:text-zinc-900 dark:hover:text-white">
                            @if (auth()->user()?->avatar_url)
                                <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="h-5 w-5 rounded-full object-cover">
                            @endif
                            <span>Admin</span>
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="cursor-pointer hover:text-zinc-900 dark:hover:text-white">Sign out</button>
                        </form>
                    @endauth
                </nav>
            </div>
        </header>

        <main class="mx-auto w-full max-w-3xl grow px-4 py-10 sm:px-6 sm:py-14">
            {{ $slot }}
        </main>

        <footer class="border-t border-zinc-200 dark:border-zinc-800">
            <div class="mx-auto w-full max-w-3xl px-4 py-6 text-sm text-zinc-500 sm:px-6 dark:text-zinc-400">
                &copy; {{ now()->year }} {{ config('app.name') }}
            </div>
        </footer>

        @if ($highlight)
            <script src="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/components/prism-core.min.js" defer></script>
            <script src="https://cdn.jsdelivr.net/npm/prismjs@1.29.0/plugins/autoloader/prism-autoloader.min.js" defer></script>
        @endif
    </body>
</html>
