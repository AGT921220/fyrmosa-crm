@php
    $selectedPermissions = collect($selected ?? []);
    $grouped = [];
    foreach (App\Enums\Permission::cases() as $permission) {
        $grouped[$permission->group()][] = $permission;
    }
@endphp
<div class="row g-3">
    @foreach ($grouped as $group => $permissions)
        <div class="col-12">
            <div class="fw-bold mb-2">{{ $group }}</div>
            <div class="row">
                @foreach ($permissions as $permission)
                    <div class="col-md-4">
                        <label class="form-check">
                            <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->value }}" @checked($selectedPermissions->contains($permission->value))>
                            <span class="form-check-label">{{ $permission->label() }}</span>
                        </label>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
