@php
    $client = $client ?? null;
    $rows = old('contacts');
    if ($rows === null) {
        $rows = $client
            ? $client->contacts->map(fn ($contact) => [
                'id' => $contact->id,
                'first_name' => $contact->first_name,
                'last_name' => $contact->last_name,
                'puesto' => $contact->puesto,
                'email' => $contact->email,
                'comentario' => $contact->comentario,
                'phones' => $contact->phones->pluck('phone')->all() ?: [''],
            ])->all()
            : [[
                'id' => '',
                'first_name' => '',
                'last_name' => '',
                'puesto' => '',
                'email' => '',
                'comentario' => '',
                'phones' => [''],
            ]];
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
                <div class="border rounded p-3 contact-row mb-3">
                    <input type="hidden" data-name="id" value="{{ $row['id'] ?? '' }}">
                    <div class="row g-3">
                        <div class="col-md-3"><label class="form-label">Nombre</label><input class="form-control" data-name="first_name" value="{{ $row['first_name'] ?? '' }}" required></div>
                        <div class="col-md-3"><label class="form-label">Apellido</label><input class="form-control" data-name="last_name" value="{{ $row['last_name'] ?? '' }}" required></div>
                        <div class="col-md-3"><label class="form-label">Puesto</label><input class="form-control" data-name="puesto" value="{{ $row['puesto'] ?? '' }}"></div>
                        <div class="col-md-3"><label class="form-label">Correo</label><input class="form-control" type="email" data-name="email" value="{{ $row['email'] ?? '' }}" required></div>
                        <div class="col-md-9"><label class="form-label">Comentario</label><textarea class="form-control" data-name="comentario" rows="2">{{ $row['comentario'] ?? '' }}</textarea></div>
                        <div class="col-md-3 d-flex align-items-end"><button class="btn btn-outline-danger w-100" type="button" data-remove-contact>Quitar contacto</button></div>
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label mb-0">Teléfonos</label>
                                <button class="btn btn-sm btn-outline-primary" type="button" data-add-phone>Agregar teléfono</button>
                            </div>
                            <div data-phones>
                                @foreach (($row['phones'] ?? ['']) as $phone)
                                    <div class="input-group mb-2 phone-row">
                                        <input class="form-control" data-phone value="{{ $phone }}" required>
                                        <button class="btn btn-outline-danger" type="button" data-remove-phone>Quitar</button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="card-footer"><button class="btn btn-primary" type="submit">Guardar</button></div>
    </div>
</form>
<template id="contact-template">
    <div class="border rounded p-3 contact-row mb-3">
        <input type="hidden" data-name="id" value="">
        <div class="row g-3">
            <div class="col-md-3"><label class="form-label">Nombre</label><input class="form-control" data-name="first_name" required></div>
            <div class="col-md-3"><label class="form-label">Apellido</label><input class="form-control" data-name="last_name" required></div>
            <div class="col-md-3"><label class="form-label">Puesto</label><input class="form-control" data-name="puesto"></div>
            <div class="col-md-3"><label class="form-label">Correo</label><input class="form-control" type="email" data-name="email" required></div>
            <div class="col-md-9"><label class="form-label">Comentario</label><textarea class="form-control" data-name="comentario" rows="2"></textarea></div>
            <div class="col-md-3 d-flex align-items-end"><button class="btn btn-outline-danger w-100" type="button" data-remove-contact>Quitar contacto</button></div>
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="form-label mb-0">Teléfonos</label>
                    <button class="btn btn-sm btn-outline-primary" type="button" data-add-phone>Agregar teléfono</button>
                </div>
                <div data-phones>
                    <div class="input-group mb-2 phone-row">
                        <input class="form-control" data-phone required>
                        <button class="btn btn-outline-danger" type="button" data-remove-phone>Quitar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
@push('scripts')
<script>
    const contacts = document.getElementById('contacts');
    const template = document.getElementById('contact-template');

    const reindex = () => {
        [...contacts.querySelectorAll('.contact-row')].forEach((row, index) => {
            row.querySelectorAll('[data-name]').forEach((field) => {
                field.name = `contacts[${index}][${field.dataset.name}]`;
            });
            [...row.querySelectorAll('[data-phones] .phone-row')].forEach((phoneRow, phoneIndex) => {
                const input = phoneRow.querySelector('[data-phone]');
                if (input) {
                    input.name = `contacts[${index}][phones][${phoneIndex}]`;
                }
            });
        });
    };

    document.getElementById('add-contact').addEventListener('click', () => {
        contacts.appendChild(template.content.cloneNode(true));
        reindex();
    });

    contacts.addEventListener('click', (event) => {
        const removeContact = event.target.closest('[data-remove-contact]');
        if (removeContact) {
            if (contacts.querySelectorAll('.contact-row').length === 1) {
                return;
            }
            removeContact.closest('.contact-row').remove();
            reindex();
            return;
        }

        const addPhone = event.target.closest('[data-add-phone]');
        if (addPhone) {
            const phones = addPhone.closest('.contact-row').querySelector('[data-phones]');
            const phoneRow = document.createElement('div');
            phoneRow.className = 'input-group mb-2 phone-row';
            phoneRow.innerHTML = '<input class="form-control" data-phone required><button class="btn btn-outline-danger" type="button" data-remove-phone>Quitar</button>';
            phones.appendChild(phoneRow);
            reindex();
            return;
        }

        const removePhone = event.target.closest('[data-remove-phone]');
        if (!removePhone) {
            return;
        }
        const phones = removePhone.closest('[data-phones]');
        if (phones.querySelectorAll('.phone-row').length === 1) {
            return;
        }
        removePhone.closest('.phone-row').remove();
        reindex();
    });

    reindex();
</script>
@endpush
