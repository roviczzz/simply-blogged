<x-layout.app
    :title="$post->title"
    :description="$post->summary"
    :image="$post->cover_image_url"
    type="article"
    :highlight="true"
>
    <article>
        <header>
            <h1 class="text-4xl font-semibold tracking-tight">{{ $post->title }}</h1>

            <p class="mt-4 flex flex-wrap items-center gap-x-2 text-sm text-zinc-500 dark:text-zinc-400">
                @if ($post->author?->exists)
                    <span>By {{ $post->author->name }}</span>
                    <span aria-hidden="true">&middot;</span>
                @endif
                <time datetime="{{ $post->published_at->toIso8601String() }}">{{ $post->published_at->format('F j, Y') }}</time>
                <span aria-hidden="true">&middot;</span>
                <span>{{ $post->reading_time }} min read</span>
            </p>

            <x-tag-list :tags="$post->tags" class="mt-4" />
        </header>

        @if ($post->cover_image_url)
            <img src="{{ $post->cover_image_url }}" alt="{{ $post->title }}" class="mt-8 w-full rounded-lg">
        @endif

        <div class="post-content mt-10">
            {!! $post->body_html !!}
        </div>
    </article>
</x-layout.app>
