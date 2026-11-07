{!! '<'.'?xml version="1.0" encoding="UTF-8"?'.'>' !!}
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>{{ config('app.name') }}</title>
        <link>{{ route('home') }}</link>
        <description>Latest posts from {{ config('app.name') }}</description>
        <language>{{ str_replace('_', '-', app()->getLocale()) }}</language>
        <atom:link href="{{ route('feed') }}" rel="self" type="application/rss+xml" />
        @if ($posts->isNotEmpty())
            <lastBuildDate>{{ $posts->first()->published_at->toRssString() }}</lastBuildDate>
        @endif
        @foreach ($posts as $post)
            <item>
                <title>{{ $post->title }}</title>
                <link>{{ route('posts.show', $post->slug) }}</link>
                <guid isPermaLink="true">{{ route('posts.show', $post->slug) }}</guid>
                <pubDate>{{ $post->published_at->toRssString() }}</pubDate>
                <description>{{ $post->summary }}</description>
            </item>
        @endforeach
    </channel>
</rss>
