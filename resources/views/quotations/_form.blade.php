@php
    $document = $quotation ?? null;
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

<form method="POST" action="{{ $document ? route('quotations.update', $document) : route('quotations.store') }}">
    @csrf
    @if ($document)
        @method('PUT')
    @endif
    <div class="card mb-3">
        <div class="card-header"><h3 class="card-title">Información general</h3></div>
        <div class="card-body">
            <div class="row g-3">
                @include('partials.party-picker')
                <div class="col-md-3">
                    <label class="form-label" for="issued_on">Fecha</label>
                    <input class="form-control" id="issued_on" type="date" name="issued_on" value="{{ old('issued_on', isset($document) ? $document->issued_on->toDateString() : now()->toDateString()) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="expires_on">Vencimiento</label>
                    <input class="form-control" id="expires_on" type="date" name="expires_on" value="{{ old('expires_on', isset($document) ? $document->expires_on->toDateString() : now()->addDays(15)->toDateString()) }}" required>
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
                    <label class="form-label" for="payment_terms">Forma y condiciones de pago</label>
                    <textarea class="form-control" id="payment_terms" name="payment_terms" rows="3">{{ old('payment_terms', $document->payment_terms ?? '') }}</textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="delivery_time">Tiempo de entrega</label>
                    <input class="form-control" id="delivery_time" name="delivery_time" value="{{ old('delivery_time', $document->delivery_time ?? '') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="internal_notes">Observaciones internas</label>
                    <textarea class="form-control" id="internal_notes" name="internal_notes" rows="3">{{ old('internal_notes', $document->internal_notes ?? '') }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="client_notes">Notas visibles para el cliente</label>
                    <textarea class="form-control" id="client_notes" name="client_notes" rows="3">{{ old('client_notes', $document->client_notes ?? '') }}</textarea>
                </div>
            </div>
        </div>
    </div>
    @include('partials.line-items', ['items' => $items ?? []])
    <div class="mt-3">
        <button class="btn btn-primary" type="submit">Guardar cotización</button>
        <a class="btn btn-link" href="{{ $document ? route('quotations.show', $document) : route('quotations.index') }}">Cancelar</a>
    </div>
</form>
