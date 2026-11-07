<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class BlogContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $author = User::where('email', config('blog.admin.email'))->firstOrFail();

        $tags = collect(['Laravel', 'PHP', 'Writing'])
            ->map(fn (string $name): Tag => Tag::firstOrCreate(
                ['slug' => str($name)->slug()->toString()],
                ['name' => $name],
            ));

        $codeSample = <<<'MD'
## Hello, world

A short post with a code block:

```php
echo 'Hello, world!';
```

| Feature | Supported |
| ------- | --------- |
| Tables  | Yes       |
MD;

        $posts = [
            [
                'title' => 'Welcome to Simply Blogged',
                'excerpt' => 'A first published post showing Markdown and code highlighting.',
                'body' => $codeSample,
                'is_published' => true,
                'published_at' => now()->subDays(2),
            ],
            [
                'title' => 'Coming Soon',
                'excerpt' => 'A post scheduled for future publication.',
                'body' => 'This post stays hidden until its scheduled time arrives.',
                'is_published' => true,
                'published_at' => now()->addWeek(),
            ],
            [
                'title' => 'Work in Progress',
                'excerpt' => 'A draft that is not yet public.',
                'body' => 'This draft is only visible in the admin panel.',
                'is_published' => false,
                'published_at' => null,
            ],
        ];

        foreach ($posts as $attributes) {
            $post = Post::firstOrCreate(
                ['slug' => str($attributes['title'])->slug()->toString()],
                $attributes + ['user_id' => $author->id],
            );

            $post->tags()->syncWithoutDetaching($tags->pluck('id'));
        }
    }
}
