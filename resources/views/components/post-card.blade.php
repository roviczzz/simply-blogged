@props(['post'])

<article {{ $attributes }}>
    <h2 class="text-2xl font-semibold tracking-tight">
        <a href="{{ route('posts.show', $post->slug) }}" class="hover:underline">{{ $post->title }}</a>
    </h2>

    <p class="mt-2 flex flex-wrap items-center gap-x-2 text-sm text-zinc-500 dark:text-zinc-400">
        <time datetime="{{ $post->published_at->toIso8601String() }}">{{ $post->published_at->format('M j, Y') }}</time>
        <span aria-hidden="true">&middot;</span>
        <span>{{ $post->reading_time }} min read</span>
    </p>

    <p class="mt-3 leading-7 text-zinc-700 dark:text-zinc-300">{{ $post->summary }}</p>

    <x-tag-list :tags="$post->tags" class="mt-4" />
</article>
