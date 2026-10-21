@php
    $role = $role ?? null;
    $builtIn = $builtIn ?? false;
    $selected = old('_permissions') ? old('permissions', []) : ($selected ?? []);
    $label = $role ? (\App\Enums\Role::tryFrom($role->name)?->label() ?? $role->name) : '';
@endphp
<form method="POST" action="{{ $role ? route('roles.update', $role) : route('roles.store') }}">
    @csrf
    @if ($role) @method('PUT') @endif
    <input type="hidden" name="_permissions" value="1">
    <div class="card mb-3">
        <div class="card-body">
            <label class="form-label">Nombre</label>
            @if ($builtIn)
                <input class="form-control" value="{{ $label }}" disabled>
                <div class="form-hint">Rol del sistema. El nombre no cambia. El superadmin conserva todos los permisos.</div>
            @else
                <input class="form-control" name="name" value="{{ old('name', $role?->name ?? '') }}" required placeholder="almacen">
                <div class="form-hint">Letras minúsculas. Los espacios se convierten en guion bajo.</div>
            @endif
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-header"><h3 class="card-title">Permisos</h3></div>
        <div class="card-body">
            @include('partials.permission-checks', ['selected' => $selected])
        </div>
    </div>
    <button class="btn btn-primary" type="submit">Guardar rol</button>
</form>
