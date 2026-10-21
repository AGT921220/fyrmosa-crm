<form class="row g-2 mt-3" method="POST" action="{{ $action }}">
    @csrf
    <div class="col-md-3">
        <label class="form-label">Tipo</label>
        <select class="form-select" name="type" required>
            @foreach (App\Enums\FollowUpType::cases() as $type)
                <option value="{{ $type->value }}">{{ $type->label() }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Fecha del contacto</label>
        <input class="form-control" type="datetime-local" name="occurred_at" value="{{ now()->format('Y-m-d\TH:i') }}" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Próximo seguimiento</label>
        <input class="form-control" type="datetime-local" name="next_follow_up_at">
    </div>
    <div class="col-md-3">
        <label class="form-label">Responsable</label>
        <select class="form-select" name="user_id" required>
            @foreach ($users as $user)
                <option value="{{ $user->id }}" @selected($user->id === auth()->id())>{{ $user->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Resultado</label>
        <input class="form-control" name="result">
    </div>
    <div class="col-md-6">
        <label class="form-label">Notas</label>
        <input class="form-control" name="notes">
    </div>
    <div class="col-12"><button class="btn btn-primary" type="submit">Registrar seguimiento</button></div>
</form>
