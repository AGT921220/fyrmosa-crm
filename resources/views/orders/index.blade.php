@extends('layouts.app')
@section('title', 'Pedidos')
@section('actions')
    @can('create', App\Models\Order::class)
        <a class="btn btn-primary" href="{{ route('orders.create') }}"><i class="ti ti-plus"></i> Nuevo pedido</a>
    @endcan
@endsection
@section('content')
    @php
        $sortLink = function (string $column) use ($filters) {
            $direction = ($filters['sort'] ?? '') === $column && ($filters['direction'] ?? 'desc') === 'asc' ? 'desc' : 'asc';
            return request()->fullUrlWithQuery(['sort' => $column, 'direction' => $direction]);
        };
    @endphp
    <form class="card mb-3" method="GET">
        <div class="card-body">
            <div class="row g-2 align-items-start">
                <div class="col-md-3">
                    <label class="form-label mb-1">Buscar</label>
                    <input class="form-control" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Folio, SAE, pedido cliente o cotización">
                </div>
                @include('partials.multi-filter', [
                    'name' => 'status',
                    'label' => 'Estado',
                    'col' => 2,
                    'selected' => $filters['status'] ?? [],
                    'options' => array_map(fn ($status) => ['value' => $status->value, 'label' => $status->label()], $statuses),
                ])
                <div class="col-md-2">
                    <label class="form-label mb-1">Moneda</label>
                    <select class="form-select" name="currency">
                        <option value="">Todas</option>
                        @foreach ($currencies as $currency)
                            <option value="{{ $currency->value }}" @selected(($filters['currency'] ?? '') === $currency->value)>{{ $currency->label() }}</option>
                        @endforeach
                    </select>
                </div>
                @include('partials.multi-filter', [
                    'name' => 'client_id',
                    'label' => 'Cliente',
                    'col' => 2,
                    'selected' => $filters['client_id'] ?? [],
                    'options' => $clients->map(fn ($client) => ['value' => (string) $client->id, 'label' => $client->name])->all(),
                ])
                @include('partials.multi-filter', [
                    'name' => 'user_id',
                    'label' => 'Responsable',
                    'col' => 3,
                    'selected' => $filters['user_id'] ?? [],
                    'options' => $users->map(fn ($user) => ['value' => (string) $user->id, 'label' => $user->name])->all(),
                ])
                <div class="col-md-2">
                    <label class="form-label mb-1">Desde</label>
                    <input class="form-control" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Hasta</label>
                    <input class="form-control" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}">
                </div>
                <div class="col-md-2 d-grid">
                    <label class="form-label mb-1">&nbsp;</label>
                    <button class="btn btn-primary w-100" type="submit">Filtrar</button>
                </div>
            </div>
        </div>
    </form>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th><a href="{{ $sortLink('folio') }}">Folio</a></th>
                        <th>Pedido de cliente</th>
                        <th>Folio SAE</th>
                        <th>Cotización</th>
                        <th><a href="{{ $sortLink('client_name') }}">Cliente</a></th>
                        <th><a href="{{ $sortLink('issued_on') }}">Emisión</a></th>
                        <th><a href="{{ $sortLink('estimated_delivery_on') }}">Entrega</a></th>
                        <th><a href="{{ $sortLink('total') }}">Total</a></th>
                        <th><a href="{{ $sortLink('status') }}">Estado</a></th>
                        <th>Responsable</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td><a href="{{ route('orders.show', $order) }}">{{ $order->folio }}</a></td>
                            <td>{{ $order->client_folio }}</td>
                            <td>{{ $order->sae_folio }}</td>
                            <td>
                                @if ($order->quotation)
                                    <a href="{{ route('quotations.show', $order->quotation) }}">{{ $order->quotation->folio }}</a>
                                @endif
                            </td>
                            <td>{{ $order->client_name }}</td>
                            <td>{{ $order->issued_on->format('d/m/Y') }}</td>
                            <td>{{ $order->estimated_delivery_on?->format('d/m/Y') }}</td>
                            <td>{{ \App\Support\Money::display($order->total, $order->currency) }}</td>
                            <td>@include('partials.status-badge', ['status' => $order->status])</td>
                            <td>{{ $order->user->name }}</td>
                            <td class="text-end">
                                <a class="btn btn-sm" href="{{ route('orders.show', $order) }}">Ver</a>
                                <a class="btn btn-sm" href="{{ route('orders.pdf', $order) }}" target="_blank" rel="noopener">PDF</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="11" class="text-secondary">No hay pedidos con estos filtros.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($orders->hasPages())
            <div class="card-footer">{{ $orders->links() }}</div>
        @endif
    </div>
@endsection
