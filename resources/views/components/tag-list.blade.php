@props(['tags'])

@if ($tags->isNotEmpty())
    <ul {{ $attributes->merge(['class' => 'flex flex-wrap gap-2']) }}>
        @foreach ($tags as $tag)
            <li>
                <a
                    href="{{ route('tags.show', $tag->slug) }}"
                    class="inline-block rounded-full bg-zinc-100 px-3 py-1 text-xs font-medium text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                >{{ $tag->name }}</a>
            </li>
        @endforeach
    </ul>
@endif
