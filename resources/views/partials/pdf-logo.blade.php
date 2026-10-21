@php
    $logoPath = public_path('images/logoFyrmosa.png');
@endphp
@if (is_file($logoPath))
    <img class="logo" src="{{ $logoPath }}" alt="Fyrmosa">
@endif
