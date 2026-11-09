<?php

namespace App\Filament\Pages\Auth;

use App\Actions\ConvertImageToWebp;
use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use SensitiveParameter;

class EditProfile extends BaseEditProfile
{
    protected function beforeValidate(): void
    {
        $avatar = data_get($this, 'data.avatar_path');
        if (is_array($avatar) && count($avatar) > 1) {
            data_set($this, 'data.avatar_path', [Arr::last($avatar)]);
        }
    }

    protected function getAvatarFormComponent(): Component
    {
        return FileUpload::make('avatar_path')
            ->label('Avatar')
            ->avatar()
            ->disk(config('blog.media_disk') ?: 'public')
            ->directory('avatars')
            ->visibility('public')
            ->maxSize(5120)
            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->saveUploadedFileUsing(static function (ConvertImageToWebp $converter, TemporaryUploadedFile|UploadedFile $file): string {
                return $converter->execute($file, config('blog.media_disk') ?: 'public', 'avatars');
            });
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getAvatarFormComponent(),
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
                $this->getCurrentPasswordFormComponent(),
            ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, #[SensitiveParameter] array $data): Model
    {
        $oldAvatar = $record->avatar_path;

        $updatedRecord = parent::handleRecordUpdate($record, $data);

        $newAvatar = $updatedRecord->avatar_path;

        if (filled($oldAvatar) && $oldAvatar !== $newAvatar) {
            $disk = config('blog.media_disk') ?: 'public';
            if (Storage::disk($disk)->exists($oldAvatar)) {
                Storage::disk($disk)->delete($oldAvatar);
            }
        }

        return $updatedRecord;
    }
}
