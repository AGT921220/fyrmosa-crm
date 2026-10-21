@extends('layouts.app')
@section('title', 'Usuarios')
@section('actions')
    @can('create', App\Models\User::class)
        <a class="btn btn-primary" href="{{ route('users.create') }}">Nuevo usuario</a>
    @endcan
@endsection
@section('content')
    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead><tr><th>Nombre</th><th>Nickname</th><th>Rol</th><th></th></tr></thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->nickname }}</td>
                            <td>{{ $user->roleLabel() }}</td>
                            <td class="text-end">
                                @can('update', $user)
                                    <a class="btn btn-sm" href="{{ route('users.edit', $user) }}">Editar</a>
                                @endcan
                                @can('delete', $user)
                                    @if ($user->isNot(auth()->user()))
                                        <form class="d-inline" method="POST" action="{{ route('users.destroy', $user) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" type="submit">Eliminar</button>
                                        </form>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-secondary">No hay usuarios.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($users->hasPages())
            <div class="card-footer">{{ $users->links() }}</div>
        @endif
    </div>
@endsection
