@php
    $client = $client ?? null;
    $rows = old('contacts');
    if ($rows === null) {
        $rows = $client
            ? $client->contacts->map(fn ($contact) => [
                'id' => $contact->id,
                'first_name' => $contact->first_name,
                'last_name' => $contact->last_name,
                'phone' => $contact->phone,
                'email' => $contact->email,
            ])->all()
            : [['id' => '', 'first_name' => '', 'last_name' => '', 'phone' => '', 'email' => '']];
    }
@endphp
<form method="POST" action="{{ $client ? route('clients.update', $client) : route('clients.store') }}">
    @csrf
    @if ($client) @method('PUT') @endif
    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Nombre</label><input class="form-control" name="name" value="{{ old('name', $client->name ?? '') }}" required></div>
                <div class="col-md-6"><label class="form-label">Razón social</label><input class="form-control" name="legal_name" value="{{ old('legal_name', $client->legal_name ?? '') }}"></div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header d-flex justify-content-between">
            <h3 class="card-title mb-0">Contactos</h3>
            <button class="btn btn-outline-primary" id="add-contact" type="button">Agregar contacto</button>
        </div>
        <div class="card-body" id="contacts">
            @foreach ($rows as $index => $row)
                <div class="row g-3 contact-row mb-3">
                    <input type="hidden" name="contacts[{{ $index }}][id]" value="{{ $row['id'] ?? '' }}">
                    <div class="col-md-3"><label class="form-label">Nombre</label><input class="form-control" name="contacts[{{ $index }}][first_name]" value="{{ $row['first_name'] ?? '' }}" required></div>
                    <div class="col-md-3"><label class="form-label">Apellido</label><input class="form-control" name="contacts[{{ $index }}][last_name]" value="{{ $row['last_name'] ?? '' }}" required></div>
                    <div class="col-md-2"><label class="form-label">Teléfono</label><input class="form-control" name="contacts[{{ $index }}][phone]" value="{{ $row['phone'] ?? '' }}" required></div>
                    <div class="col-md-3"><label class="form-label">Correo</label><input class="form-control" type="email" name="contacts[{{ $index }}][email]" value="{{ $row['email'] ?? '' }}" required></div>
                    <div class="col-md-1 d-flex align-items-end"><button class="btn btn-outline-danger w-100" type="button" data-remove-contact>Quitar</button></div>
                </div>
            @endforeach
        </div>
        <div class="card-footer"><button class="btn btn-primary" type="submit">Guardar</button></div>
    </div>
</form>
<template id="contact-template">
    <div class="row g-3 contact-row mb-3">
        <input type="hidden" data-name="id" value="">
        <div class="col-md-3"><label class="form-label">Nombre</label><input class="form-control" data-name="first_name" required></div>
        <div class="col-md-3"><label class="form-label">Apellido</label><input class="form-control" data-name="last_name" required></div>
        <div class="col-md-2"><label class="form-label">Teléfono</label><input class="form-control" data-name="phone" required></div>
        <div class="col-md-3"><label class="form-label">Correo</label><input class="form-control" type="email" data-name="email" required></div>
        <div class="col-md-1 d-flex align-items-end"><button class="btn btn-outline-danger w-100" type="button" data-remove-contact>Quitar</button></div>
    </div>
</template>
@push('scripts')
<script>
    const contacts = document.getElementById('contacts');
    const template = document.getElementById('contact-template');

    const reindex = () => {
        [...contacts.querySelectorAll('.contact-row')].forEach((row, index) => {
            row.querySelectorAll('[name], [data-name]').forEach((field) => {
                const key = field.dataset.name || field.name.replace(/contacts\[\d+\]\[(\w+)\]/, '$1');
                field.name = `contacts[${index}][${key}]`;
            });
        });
    };

    document.getElementById('add-contact').addEventListener('click', () => {
        contacts.appendChild(template.content.cloneNode(true));
        reindex();
    });

    contacts.addEventListener('click', (event) => {
        const button = event.target.closest('[data-remove-contact]');
        if (!button || contacts.querySelectorAll('.contact-row').length === 1) {
            return;
        }
        button.closest('.contact-row').remove();
        reindex();
    });
</script>
@endpush
