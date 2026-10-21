@extends('layouts.app')
@section('title', $order->folio)
@section('subtitle')
    @include('partials.status-badge', ['status' => $order->status])
    @if ($order->quotation)
        · Cotización <a href="{{ route('quotations.show', $order->quotation) }}">{{ $order->quotation->folio }}</a> v{{ $order->quotation_version }}
    @endif
@endsection
@section('actions')
    @can('pdf', $order)
        <a class="btn" href="{{ route('orders.pdf', $order) }}">Ver PDF</a>
        <a class="btn" href="{{ route('orders.pdf', [$order, 'download' => 1]) }}">Descargar PDF</a>
    @endcan
    @can('update', $order)
        @if ($order->status->isEditable())
            <a class="btn btn-primary" href="{{ route('orders.edit', $order) }}">Editar</a>
        @endif
    @endcan
@endsection
@section('content')
    <ul class="nav nav-tabs" data-bs-toggle="tabs">
        <li class="nav-item"><a class="nav-link active" href="#general" data-bs-toggle="tab">Información general</a></li>
        <li class="nav-item"><a class="nav-link" href="#items" data-bs-toggle="tab">Conceptos</a></li>
        <li class="nav-item"><a class="nav-link" href="#deliveries" data-bs-toggle="tab">Entregas</a></li>
        <li class="nav-item"><a class="nav-link" href="#follow" data-bs-toggle="tab">Seguimiento</a></li>
        <li class="nav-item"><a class="nav-link" href="#history" data-bs-toggle="tab">Histórico</a></li>
        <li class="nav-item"><a class="nav-link" href="#files" data-bs-toggle="tab">Documentos</a></li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane active show card" id="general">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4"><div class="text-secondary">Cliente</div><div>{{ $order->client_name }}</div></div>
                    <div class="col-md-4"><div class="text-secondary">Razón social</div><div>{{ $order->legal_name }}</div></div>
                    <div class="col-md-4"><div class="text-secondary">Contacto</div><div>{{ $order->contactName() }}</div></div>
                    <div class="col-md-4"><div class="text-secondary">Correo</div><div>{{ $order->email }}</div></div>
                    <div class="col-md-4"><div class="text-secondary">Teléfono</div><div>{{ $order->phone }}</div></div>
                    <div class="col-md-4"><div class="text-secondary">Folio del cliente</div><div>{{ $order->client_folio }}</div></div>
                    <div class="col-md-3"><div class="text-secondary">Emisión</div><div>{{ $order->issued_on->format('d/m/Y') }}</div></div>
                    <div class="col-md-3"><div class="text-secondary">Entrega estimada</div><div>{{ $order->estimated_delivery_on?->format('d/m/Y') }}</div></div>
                    <div class="col-md-3"><div class="text-secondary">Responsable</div><div>{{ $order->user->name }}</div></div>
                    <div class="col-md-3"><div class="text-secondary">Total</div><div>{{ \App\Support\Money::display($order->total, $order->currency) }}</div></div>
                    <div class="col-md-6"><div class="text-secondary">Condiciones comerciales</div><div>{{ $order->commercial_terms }}</div></div>
                    <div class="col-md-6"><div class="text-secondary">Pago</div><div>{{ $order->payment_terms }}</div></div>
                    <div class="col-12"><div class="text-secondary">Observaciones</div><div>{{ $order->notes }}</div></div>
                    @if ($order->converted_at)
                        <div class="col-12 text-secondary">Convertida el {{ $order->converted_at->format('d/m/Y H:i') }} por {{ $order->converter->name ?? 'Sistema' }}.</div>
                    @endif
                </div>
                @if ($order->status->transitions() !== [])
                    @can('changeStatus', $order)
                    <form class="row g-2 mt-3" method="POST" action="{{ route('orders.status', $order) }}">
                        @csrf
                        <div class="col-md-4">
                            <select class="form-select" name="status" required>
                                @foreach ($order->status->transitions() as $status)
                                    <option value="{{ $status->value }}">{{ $status->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5"><input class="form-control" name="comment" placeholder="Comentario o motivo"></div>
                        <div class="col-md-3"><button class="btn btn-primary w-100" type="submit">Cambiar estado</button></div>
                    </form>
                    @endcan
                @endif
            </div>
        </div>
        <div class="tab-pane card" id="items">
            @include('partials.document-items', ['items' => $order->items, 'document' => $order, 'showDelivered' => true])
        </div>
        <div class="tab-pane card" id="deliveries">
            <div class="card-body">
                @forelse ($order->deliveries as $delivery)
                    <div class="mb-3">
                        <div class="fw-bold">{{ $delivery->delivered_on->format('d/m/Y') }} · {{ $delivery->user->name ?? 'Sistema' }}</div>
                        <div>{{ $delivery->notes }}</div>
                        <ul>
                            @foreach ($delivery->items as $line)
                                <li>{{ $line->quantity }} de {{ $order->items->firstWhere('id', $line->order_item_id)?->description }}</li>
                            @endforeach
                        </ul>
                    </div>
                @empty
                    <div class="text-secondary mb-3">Sin entregas.</div>
                @endforelse
                @can('deliver', $order)
                    @if ($order->status->acceptsDeliveries())
                        <form method="POST" action="{{ route('orders.deliveries.store', $order) }}">
                            @csrf
                            <div class="row g-2 mb-2">
                                <div class="col-md-4"><input class="form-control" type="date" name="delivered_on" value="{{ now()->toDateString() }}" required></div>
                                <div class="col-md-8"><input class="form-control" name="notes" placeholder="Notas de la entrega"></div>
                            </div>
                            @foreach ($order->items as $index => $item)
                                <div class="row g-2 mb-2 align-items-center">
                                    <div class="col-md-6">{{ $item->description }} · pendiente {{ \App\Support\Money::sub((string) $item->quantity, (string) $item->quantity_delivered, 4) }}</div>
                                    <div class="col-md-3">
                                        <input type="hidden" name="items[{{ $index }}][order_item_id]" value="{{ $item->id }}">
                                        <input class="form-control" name="items[{{ $index }}][quantity]" value="0" inputmode="decimal">
                                    </div>
                                </div>
                            @endforeach
                            <button class="btn btn-primary" type="submit">Registrar entrega</button>
                        </form>
                    @endif
                @endcan
            </div>
        </div>
        <div class="tab-pane card" id="follow">
            <div class="card-body">
                @include('partials.timeline', ['followUps' => $order->followUps])
                @can('followUp', $order)
                    @if ($order->status->acceptsFollowUps())
                        @include('partials.follow-up-form', ['action' => route('orders.follow-ups.store', $order), 'users' => $users])
                    @endif
                @endcan
            </div>
        </div>
        <div class="tab-pane card" id="history">
            <div class="card-body">@include('partials.activity-log', ['activities' => $order->activities])</div>
        </div>
        <div class="tab-pane card" id="files">
            <div class="card-body">
                @include('partials.attachments', ['attachments' => $order->attachments, 'action' => route('orders.attachments.store', $order), 'canUpload' => auth()->user()->can('attach', $order)])
            </div>
        </div>
    </div>
@endsection
