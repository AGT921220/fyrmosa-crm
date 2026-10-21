@extends('layouts.app')

@section('title', $quotation->folio)
@section('subtitle')
    Versión {{ $quotation->version_number }} · @include('partials.status-badge', ['status' => $quotation->status])
@endsection

@section('actions')
    @can('pdf', $quotation)
        <a class="btn" href="{{ route('quotations.pdf', $quotation) }}">Ver PDF</a>
        <a class="btn" href="{{ route('quotations.pdf', [$quotation, 'download' => 1]) }}">Descargar PDF</a>
    @endcan
    @can('duplicate', $quotation)
        <form method="POST" action="{{ route('quotations.duplicate', $quotation) }}">@csrf<button class="btn" type="submit">Duplicar</button></form>
    @endcan
    @can('update', $quotation)
        @if ($quotation->status->isEditable())
            <a class="btn btn-primary" href="{{ route('quotations.edit', $quotation) }}">Editar</a>
        @endif
    @endcan
    @can('version', $quotation)
        @if ($quotation->status->canVersion())
            <form method="POST" action="{{ route('quotations.versions.store', $quotation) }}">@csrf<button class="btn" type="submit">Nueva versión</button></form>
        @endif
    @endcan
    @can('convert', $quotation)
        @if ($quotation->status === App\Enums\QuotationStatus::Approved && ! $hasOrder)
            <form method="POST" action="{{ route('quotations.convert', $quotation) }}">@csrf<button class="btn btn-success" type="submit">Convertir a pedido</button></form>
        @endif
    @endcan
@endsection

@section('content')
    <ul class="nav nav-tabs" data-bs-toggle="tabs">
        <li class="nav-item"><a class="nav-link active" href="#general" data-bs-toggle="tab">Información general</a></li>
        <li class="nav-item"><a class="nav-link" href="#items" data-bs-toggle="tab">Conceptos</a></li>
        <li class="nav-item"><a class="nav-link" href="#follow" data-bs-toggle="tab">Seguimientos</a></li>
        @can('viewHistory', $quotation)
            <li class="nav-item"><a class="nav-link" href="#history" data-bs-toggle="tab">Histórico</a></li>
        @endcan
        <li class="nav-item"><a class="nav-link" href="#files" data-bs-toggle="tab">Documentos</a></li>
        <li class="nav-item"><a class="nav-link" href="#versions" data-bs-toggle="tab">Versiones</a></li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane active show card" id="general">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4"><div class="text-secondary">Cliente</div><div>{{ $quotation->client_name }}</div></div>
                    <div class="col-md-4"><div class="text-secondary">Razón social</div><div>{{ $quotation->legal_name }}</div></div>
                    <div class="col-md-4"><div class="text-secondary">Contacto</div><div>{{ $quotation->contactName() }}</div></div>
                    <div class="col-md-4"><div class="text-secondary">Correo</div><div>{{ $quotation->email }}</div></div>
                    <div class="col-md-4"><div class="text-secondary">Teléfono</div><div>{{ $quotation->phone }}</div></div>
                    <div class="col-md-4"><div class="text-secondary">Responsable</div><div>{{ $quotation->user->name }}</div></div>
                    <div class="col-md-3"><div class="text-secondary">Fecha</div><div>{{ $quotation->issued_on->format('d/m/Y') }}</div></div>
                    <div class="col-md-3"><div class="text-secondary">Vencimiento</div><div>{{ $quotation->expires_on->format('d/m/Y') }}</div></div>
                    <div class="col-md-3"><div class="text-secondary">Moneda</div><div>{{ $quotation->currency->label() }}</div></div>
                    <div class="col-md-3"><div class="text-secondary">Total</div><div>{{ \App\Support\Money::display($quotation->total, $quotation->currency) }}</div></div>
                    <div class="col-md-4"><div class="text-secondary">Condiciones comerciales</div><div>{{ $quotation->commercial_terms }}</div></div>
                    <div class="col-md-4"><div class="text-secondary">Pago</div><div>{{ $quotation->payment_terms }}</div></div>
                    <div class="col-md-4"><div class="text-secondary">Entrega</div><div>{{ $quotation->delivery_time }}</div></div>
                    <div class="col-md-6"><div class="text-secondary">Notas para el cliente</div><div>{{ $quotation->client_notes }}</div></div>
                    <div class="col-md-6"><div class="text-secondary">Observaciones internas</div><div>{{ $quotation->internal_notes }}</div></div>
                </div>
                @if ($quotation->order)
                    <div class="mt-3">Pedido relacionado: <a href="{{ route('orders.show', $quotation->order) }}">{{ $quotation->order->folio }}</a></div>
                @endif
                @if ($quotation->status->transitions() !== [])
                    @can('changeStatus', $quotation)
                    <form class="row g-2 mt-3" method="POST" action="{{ route('quotations.status', $quotation) }}">
                        @csrf
                        <div class="col-md-4">
                            <select class="form-select" name="status" required>
                                @foreach ($quotation->status->transitions() as $status)
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
            @include('partials.document-items', ['items' => $quotation->items, 'document' => $quotation])
        </div>
        <div class="tab-pane card" id="follow">
            <div class="card-body">
                @include('partials.timeline', ['followUps' => $quotation->followUps])
                @can('followUp', $quotation)
                    @if ($quotation->status->acceptsFollowUps())
                        @include('partials.follow-up-form', ['action' => route('quotations.follow-ups.store', $quotation), 'users' => $users])
                    @endif
                @endcan
            </div>
        </div>
        @can('viewHistory', $quotation)
            <div class="tab-pane card" id="history">
                <div class="card-body">@include('partials.activity-log', ['activities' => $quotation->activities])</div>
            </div>
        @endcan
        <div class="tab-pane card" id="files">
            <div class="card-body">
                @include('partials.attachments', ['attachments' => $quotation->attachments, 'action' => route('quotations.attachments.store', $quotation), 'canUpload' => auth()->user()->can('attach', $quotation)])
            </div>
        </div>
        <div class="tab-pane card" id="versions">
            <div class="table-responsive">
                <table class="table card-table">
                    <thead><tr><th>Folio</th><th>Versión</th><th>Estado</th><th>Total</th><th>Fecha</th></tr></thead>
                    <tbody>
                        @foreach ($chain as $version)
                            <tr>
                                <td><a href="{{ route('quotations.show', $version) }}">{{ $version->folio }}</a></td>
                                <td>{{ $version->version_number }}</td>
                                <td>@include('partials.status-badge', ['status' => $version->status])</td>
                                <td>{{ \App\Support\Money::display($version->total, $version->currency) }}</td>
                                <td>{{ $version->issued_on->format('d/m/Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-body">
                <h3 class="card-title">Instantáneas</h3>
                @forelse ($chain->flatMap(fn ($version) => $version->versions) as $snapshot)
                    <div class="mb-2">Versión {{ $snapshot->version_number }} guardada el {{ $snapshot->created_at->format('d/m/Y H:i') }} por {{ $snapshot->user->name ?? 'Sistema' }}. Total {{ $snapshot->snapshot['total'] ?? '' }} {{ strtoupper($snapshot->snapshot['currency'] ?? '') }}.</div>
                @empty
                    <div class="text-secondary">Todavía no hay instantáneas. Se generan al aprobar o al crear una versión nueva.</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
