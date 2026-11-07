<x-layout.app :description="'Latest posts from '.config('app.name')">
    <h1 class="sr-only">Latest posts</h1>

    @if ($posts->isEmpty())
        <p class="text-zinc-500 dark:text-zinc-400">No posts have been published yet.</p>
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
