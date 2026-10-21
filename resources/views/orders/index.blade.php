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
            <div class="row g-2">
                <div class="col-md-3"><input class="form-control" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Folio, cliente o cotización"></div>
                <div class="col-md-2">
                    <select class="form-select" name="status">
                        <option value="">Estado</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="currency">
                        <option value="">Moneda</option>
                        @foreach ($currencies as $currency)
                            <option value="{{ $currency->value }}" @selected(($filters['currency'] ?? '') === $currency->value)>{{ $currency->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="client_id">
                        <option value="">Cliente</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}" @selected(($filters['client_id'] ?? '') == $client->id)>{{ $client->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="user_id">
                        <option value="">Responsable</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @selected(($filters['user_id'] ?? '') == $user->id)>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2"><input class="form-control" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}"></div>
                <div class="col-md-2"><input class="form-control" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}"></div>
                <div class="col-md-2"><button class="btn btn-primary w-100" type="submit">Filtrar</button></div>
            </div>
        </div>
    </form>
    <div class="card">
        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th><a href="{{ $sortLink('folio') }}">Folio</a></th>
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
                                <a class="btn btn-sm" href="{{ route('orders.pdf', $order) }}">PDF</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-secondary">No hay pedidos con estos filtros.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($orders->hasPages())
            <div class="card-footer">{{ $orders->links() }}</div>
        @endif
    </div>
@endsection
