@php
    $selectedClientId = old('client_id', $document->client_id ?? '');
    $selectedContactId = old('contact_id', $document->contact_id ?? '');
    $selectedClient = $clients->first(fn ($client) => (string) $client->id === (string) $selectedClientId);
    $selectedContact = $selectedClient?->contacts->first(fn ($contact) => (string) $contact->id === (string) $selectedContactId);
    $catalog = $clients->map(fn ($client) => [
        'id' => $client->id,
        'name' => $client->name,
        'legal_name' => $client->legal_name,
        'contacts' => $client->contacts->map(fn ($contact) => [
            'id' => $contact->id,
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'phone' => $contact->phone,
            'email' => $contact->email,
        ])->values(),
    ])->values();
@endphp
<div class="col-md-4">
    <label class="form-label" for="client_id">Cliente</label>
    <select class="form-select" id="client_id" name="client_id" required>
        <option value="">Selecciona un cliente</option>
        @foreach ($clients as $client)
            <option value="{{ $client->id }}" @selected((string) $selectedClientId === (string) $client->id)>{{ $client->name }}</option>
        @endforeach
    </select>
</div>
<div class="col-md-4">
    <label class="form-label" for="contact_id">Contacto</label>
    <select class="form-select" id="contact_id" name="contact_id" data-selected="{{ $selectedContactId }}" required>
        <option value="">Selecciona un contacto</option>
    </select>
</div>
<div class="col-md-4">
    <label class="form-label" for="party-legal-name">Razón social</label>
    <input class="form-control" id="party-legal-name" value="{{ $selectedClient->legal_name ?? '' }}" readonly>
</div>
<div class="col-md-3">
    <label class="form-label" for="party-first-name">Nombre</label>
    <input class="form-control" id="party-first-name" value="{{ $selectedContact->first_name ?? '' }}" readonly>
</div>
<div class="col-md-3">
    <label class="form-label" for="party-last-name">Apellido</label>
    <input class="form-control" id="party-last-name" value="{{ $selectedContact->last_name ?? '' }}" readonly>
</div>
<div class="col-md-3">
    <label class="form-label" for="party-phone">Teléfono</label>
    <input class="form-control" id="party-phone" value="{{ $selectedContact->phone ?? '' }}" readonly>
</div>
<div class="col-md-3">
    <label class="form-label" for="party-email">Correo</label>
    <input class="form-control" id="party-email" value="{{ $selectedContact->email ?? '' }}" readonly>
</div>
<script type="application/json" id="party-data">@json($catalog)</script>
@push('scripts')
    <script src="{{ asset('js/party.js') }}"></script>
@endpush
