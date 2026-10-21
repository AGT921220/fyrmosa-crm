@php
    $user = $user ?? null;
    $selected = old('_permissions') ? old('permissions', []) : ($directPermissions ?? []);
@endphp
<form method="POST" action="{{ $user ? route('users.update', $user) : route('users.store') }}">
    @csrf
    @if ($user) @method('PUT') @endif
    <input type="hidden" name="_permissions" value="1">
    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nombre</label>
                    <input class="form-control" name="name" value="{{ old('name', $user?->name ?? '') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nickname</label>
                    <input class="form-control" name="nickname" value="{{ old('nickname', $user?->nickname ?? '') }}" required autocomplete="off">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Correo</label>
                    <input class="form-control" type="email" name="email" value="{{ old('email', $user?->email ?? '') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Rol</label>
                    <select class="form-select" name="role" required>
                        @foreach ($roles as $role)
                            <option value="{{ $role->name }}" @selected(old('role', $user?->role ?? '') === $role->name)>
                                {{ \App\Enums\Role::tryFrom($role->name)?->label() ?? $role->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Contraseña</label>
                    <input class="form-control" type="password" name="password" @unless($user) required @endunless autocomplete="new-password">
                    @if ($user)
                        <div class="form-hint">En blanco conserva la actual.</div>
                    @endif
                </div>
                <div class="col-md-6">
                    <label class="form-label">Confirmar contraseña</label>
                    <input class="form-control" type="password" name="password_confirmation" @unless($user) required @endunless autocomplete="new-password">
                </div>
            </div>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">Permisos adicionales</h3>
            <div class="card-subtitle">Se suman a los del rol. No hace falta repetir los que el rol ya trae.</div>
        </div>
        <div class="card-body">
            @include('partials.permission-checks', ['selected' => $selected])
        </div>
    </div>
    <button class="btn btn-primary" type="submit">Guardar usuario</button>
</form>
