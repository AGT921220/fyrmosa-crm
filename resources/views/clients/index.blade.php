@extends('layouts.app')
@section('title', 'Clientes')
@section('actions')
    @can('create', App\Models\Client::class)
        <a class="btn btn-primary" href="{{ route('clients.create') }}">Nuevo cliente</a>
    @endcan
@endsection
@section('content')
    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead><tr><th>Nombre</th><th>Razón social</th><th>Contactos</th><th></th></tr></thead>
                <tbody>
                    @forelse ($clients as $client)
                        <tr>
                            <td>{{ $client->name }}</td>
                            <td>{{ $client->legal_name }}</td>
                            <td>{{ $client->contacts_count }}</td>
                            <td class="text-end">
                                @can('update', $client)
                                    <a class="btn btn-sm" href="{{ route('clients.edit', $client) }}">Editar</a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-secondary">No hay clientes.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($clients->hasPages())
            <div class="card-footer">{{ $clients->links() }}</div>
        @endif
    </div>
@endsection
