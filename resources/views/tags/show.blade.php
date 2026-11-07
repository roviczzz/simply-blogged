<x-layout.app :title="$tag->name" :description="'Posts tagged '.$tag->name">
    <header class="mb-10">
        <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Tagged</p>
        <h1 class="mt-1 text-3xl font-semibold tracking-tight">{{ $tag->name }}</h1>
    </header>

    @if ($posts->isEmpty())
        <p class="text-zinc-500 dark:text-zinc-400">No published posts use this tag yet.</p>
    @else
        <div class="divide-y divide-zinc-200 dark:divide-zinc-800">
            @foreach ($posts as $post)
                <x-post-card :post="$post" class="py-10 first:pt-0 last:pb-0" />
            @endforeach
        </div>
    @endif

    <div class="mt-12">
        {{ $posts->links() }}
    </div>
</x-layout.app>
