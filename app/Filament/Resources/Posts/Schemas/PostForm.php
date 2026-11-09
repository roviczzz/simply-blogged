<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Actions\ConvertImageToWebp;
use App\Models\Post;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Content')
                    ->columnSpan(2)
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state, string $operation): void {
                                if ($operation === 'create' && filled($state)) {
                                    $set('slug', Str::slug($state));
                                }
                            }),
                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(Post::class, 'slug', ignoreRecord: true)
                            ->alphaDash(),
                        Textarea::make('excerpt')
                            ->rows(3)
                            ->columnSpanFull(),
                        MarkdownEditor::make('body')
                            ->required()
                            ->columnSpanFull()
                            ->fileAttachmentsDisk(fn (): string => config('blog.media_disk') ?: config('blog.cover_disk') ?: 'public')
                            ->fileAttachmentsDirectory('attachments')
                            ->fileAttachmentsAcceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->fileAttachmentsMaxSize(10240)
                            ->saveUploadedFileAttachmentUsing(static function (ConvertImageToWebp $converter, TemporaryUploadedFile|UploadedFile $file): string {
                                $disk = config('blog.media_disk') ?: config('blog.cover_disk') ?: 'public';

                                return $converter->execute($file, disk: $disk, directory: 'attachments');
                            })
                            ->getFileAttachmentUrlUsing(static function (?string $file): ?string {
                                if (blank($file)) {
                                    return null;
                                }

                                $disk = config('blog.media_disk') ?: config('blog.cover_disk') ?: 'public';

                                return Storage::disk($disk)->url($file);
                            }),
                    ]),
                Section::make('Publishing')
                    ->columnSpan(1)
                    ->schema([
                        FileUpload::make('cover_image')
                            ->image()
                            ->disk(config('blog.media_disk') ?: config('blog.cover_disk') ?: 'public')
                            ->directory('covers')
                            ->visibility('public')
                            ->maxSize(10240)
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->saveUploadedFileUsing(static function (ConvertImageToWebp $converter, TemporaryUploadedFile|UploadedFile $file): string {
                                return $converter->execute($file, config('blog.media_disk') ?: config('blog.cover_disk') ?: 'public');
                            }),
                        Select::make('tags')
                            ->relationship('tags', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                            ]),
                        Toggle::make('is_published')
                            ->label('Published')
                            ->live()
                            ->afterStateUpdated(function (Get $get, Set $set, bool $state): void {
                                if ($state && blank($get('published_at'))) {
                                    $set('published_at', now());
                                }
                            }),
                        DateTimePicker::make('published_at')
                            ->label('Publish at')
                            ->helperText('A future date keeps the post hidden until that time.')
                            ->required(fn (Get $get): bool => (bool) $get('is_published')),
                    ]),
            ])
            ->columns(3);
    }
}
