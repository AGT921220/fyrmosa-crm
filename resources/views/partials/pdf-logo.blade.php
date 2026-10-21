@php
    $logoPath = public_path('images/logoFyrmosa2.png');
@endphp
@if (is_file($logoPath))
    <img class="logo" src="{{ $logoPath }}" alt="Fyrmosa">
@endif
