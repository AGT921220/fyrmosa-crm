<?php

declare(strict_types=1);

namespace App\Features\Shared\Application;

use App\Enums\ActivityAction;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class StoreAttachment
{
    public function __construct(private readonly RecordActivity $activity) {}

    public function __invoke(Model $subject, UploadedFile $file, User $user): void
    {
        DB::transaction(function () use ($subject, $file, $user) {
            $folder = 'attachments/'.$subject->getMorphClass().'/'.$subject->getKey();
            $name = Str::uuid()->toString().'.'.$file->getClientOriginalExtension();
            $path = $file->storeAs($folder, $name, 'local');

            $subject->attachments()->create([
                'disk' => 'local',
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize() ?: 0,
                'user_id' => $user->id,
            ]);

            ($this->activity)($subject, ActivityAction::AttachmentAdded, $user, 'Se adjuntó '.$file->getClientOriginalName().'.');
        });
    }
}
