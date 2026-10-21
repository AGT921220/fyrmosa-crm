@extends('layouts.app')
@section('title', 'Roles')
@section('actions')
    @can('create', Spatie\Permission\Models\Role::class)
        <a class="btn btn-primary" href="{{ route('roles.create') }}">Nuevo rol</a>
    @endcan
@endsection
@section('content')
    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead><tr><th>Rol</th><th>Permisos</th><th></th></tr></thead>
                <tbody>
                    @forelse ($roles as $role)
                        <tr>
                            <td>{{ \App\Enums\Role::tryFrom($role->name)?->label() ?? $role->name }}</td>
                            <td>{{ $role->permissions_count }}</td>
                            <td class="text-end">
                                @can('update', $role)
                                    <a class="btn btn-sm" href="{{ route('roles.edit', $role) }}">Editar</a>
                                @endcan
                                @can('delete', $role)
                                    @unless (\App\Enums\Role::isBuiltIn($role->name))
                                        <form class="d-inline" method="POST" action="{{ route('roles.destroy', $role) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" type="submit">Eliminar</button>
                                        </form>
                                    @endunless
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-secondary">No hay roles.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($roles->hasPages())
            <div class="card-footer">{{ $roles->links() }}</div>
        @endif
    </div>
@endsection
