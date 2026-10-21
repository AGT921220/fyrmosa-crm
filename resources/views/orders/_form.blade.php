@php
    $document = $order ?? null;
    $items = old('items');
    if ($items === null && $document) {
        $items = $document->items->map(fn ($item) => [
            'sku' => $item->sku,
            'description' => $item->description,
            'quantity' => $item->quantity,
            'unit' => $item->unit,
            'unit_price' => $item->unit_price,
            'discount_type' => $item->discount_type->value,
            'discount_value' => $item->discount_value,
            'tax_rate' => $item->tax_rate,
        ])->all();
    }
@endphp
<form method="POST" action="{{ $document ? route('orders.update', $document) : route('orders.store') }}">
    @csrf
    @if ($document) @method('PUT') @endif
    <div class="card mb-3">
        <div class="card-header"><h3 class="card-title">Información general</h3></div>
        <div class="card-body">
            <div class="row g-3">
                @include('partials.party-picker')
                <div class="col-md-4">
                    <label class="form-label" for="client_folio">Folio del cliente</label>
                    <input class="form-control" id="client_folio" name="client_folio" value="{{ old('client_folio', $document->client_folio ?? '') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="issued_on">Emisión</label>
                    <input class="form-control" id="issued_on" type="date" name="issued_on" value="{{ old('issued_on', isset($document) ? $document->issued_on->toDateString() : now()->toDateString()) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="estimated_delivery_on">Entrega estimada</label>
                    <input class="form-control" id="estimated_delivery_on" type="date" name="estimated_delivery_on" value="{{ old('estimated_delivery_on', isset($document) && $document->estimated_delivery_on ? $document->estimated_delivery_on->toDateString() : '') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="user_id">Responsable</label>
                    <select class="form-select" id="user_id" name="user_id" required>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @selected(old('user_id', $document->user_id ?? auth()->id()) == $user->id)>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="currency">Moneda</label>
                    <select class="form-select" id="currency" name="currency" required>
                        @foreach ($currencies as $currency)
                            <option value="{{ $currency->value }}" @selected(old('currency', $document->currency->value ?? 'mxn') === $currency->value)>{{ $currency->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="commercial_terms">Condiciones comerciales</label>
                    <textarea class="form-control" id="commercial_terms" name="commercial_terms" rows="3">{{ old('commercial_terms', $document->commercial_terms ?? '') }}</textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="payment_terms">Condiciones de pago</label>
                    <textarea class="form-control" id="payment_terms" name="payment_terms" rows="3">{{ old('payment_terms', $document->payment_terms ?? '') }}</textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="notes">Observaciones</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes', $document->notes ?? '') }}</textarea>
                </div>
            </div>
        </div>
    </div>
    @include('partials.line-items', ['items' => $items ?? []])
    <div class="mt-3">
        <button class="btn btn-primary" type="submit">Guardar pedido</button>
        <a class="btn btn-link" href="{{ $document ? route('orders.show', $document) : route('orders.index') }}">Cancelar</a>
    </div>
</form>
