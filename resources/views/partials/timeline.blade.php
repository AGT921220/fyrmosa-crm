<ul class="timeline">
    @forelse ($followUps as $followUp)
        <li class="mb-3">
            <div class="fw-bold">{{ $followUp->occurred_at->format('d/m/Y H:i') }} · {{ $followUp->type->label() }}</div>
            <div>{{ $followUp->user->name }}</div>
            @if ($followUp->result)<div>Resultado: {{ $followUp->result }}</div>@endif
            @if ($followUp->notes)<div class="text-secondary">{{ $followUp->notes }}</div>@endif
            @if ($followUp->next_follow_up_at)
                <div>Próximo: {{ $followUp->next_follow_up_at->format('d/m/Y H:i') }}</div>
            @endif
        </li>
    @empty
        <li class="text-secondary">Sin seguimientos.</li>
    @endforelse
</ul>
