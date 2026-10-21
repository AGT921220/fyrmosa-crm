<ul class="list-unstyled">
    @forelse ($activities as $activity)
        <li class="mb-3">
            <div class="fw-bold">{{ $activity->created_at->format('d/m/Y H:i') }} · {{ $activity->action->label() }}</div>
            <div>{{ $activity->user->name ?? 'Sistema' }}</div>
            @if ($activity->comment)<div>{{ $activity->comment }}</div>@endif
            @if (!empty($activity->properties['from']))
                <div class="text-secondary">{{ $activity->properties['from'] }} → {{ $activity->properties['to'] }}</div>
            @endif
        </li>
    @empty
        <li class="text-secondary">Sin movimientos.</li>
    @endforelse
</ul>
