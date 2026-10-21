<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ActivityAction;
use App\Features\Shared\Application\RecordActivity;
use App\Models\Attachment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    public function download(Attachment $attachment): StreamedResponse
    {
        $this->authorize('view', $attachment->attachable);

        return Storage::disk($attachment->disk)->download($attachment->path, $attachment->original_name);
    }

    public function destroy(Attachment $attachment, RecordActivity $activity): RedirectResponse
    {
        $parent = $attachment->attachable;
        $this->authorize('attach', $parent);
        $name = $attachment->original_name;
        Storage::disk($attachment->disk)->delete($attachment->path);
        $attachment->delete();
        $activity($parent, ActivityAction::AttachmentRemoved, request()->user(), 'Se eliminó '.$name.'.');

        return back()->with('success', 'Documento eliminado.');
    }
}
