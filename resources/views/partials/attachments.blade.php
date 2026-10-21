<ul class="list-unstyled">
    @forelse ($attachments as $attachment)
        <li class="d-flex justify-content-between align-items-center mb-2">
            <a href="{{ route('attachments.download', $attachment) }}">{{ $attachment->original_name }}</a>
            @if ($canUpload)
                <form method="POST" action="{{ route('attachments.destroy', $attachment) }}">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger" type="submit">Quitar</button>
                </form>
            @endif
        </li>
    @empty
        <li class="text-secondary">Sin documentos.</li>
    @endforelse
</ul>
@if ($canUpload)
    <form class="mt-3" method="POST" action="{{ $action }}" enctype="multipart/form-data">
        @csrf
        <div class="row g-2">
            <div class="col-md-8"><input class="form-control" type="file" name="file" required></div>
            <div class="col-md-4"><button class="btn btn-primary w-100" type="submit">Adjuntar</button></div>
        </div>
    </form>
@endif
