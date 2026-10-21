@extends('layouts.app')
@section('title', 'Dashboard')
@section('subtitle', 'Los montos se calculan por moneda. MXN y USD no se suman entre sí.')
@section('content')
    <form class="card mb-3" method="GET">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-md-2"><input class="form-control" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}"></div>
                <div class="col-md-2"><input class="form-control" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}"></div>
                <div class="col-md-2">
                    <select class="form-select" name="client_id">
                        <option value="">Cliente</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}" @selected(($filters['client_id'] ?? '') == $client->id)>{{ $client->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="user_id">
                        <option value="">Responsable</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @selected(($filters['user_id'] ?? '') == $user->id)>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="quotation_status">
                        <option value="">Estado de cotización</option>
                        @foreach ($quotationStatuses as $status)
                            <option value="{{ $status->value }}" @selected(($filters['quotation_status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" name="order_status">
                        <option value="">Estado del pedido</option>
                        @foreach ($orderStatuses as $status)
                            <option value="{{ $status->value }}" @selected(($filters['order_status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2"><button class="btn btn-primary w-100" type="submit">Aplicar</button></div>
            </div>
        </div>
    </form>

    <div class="row row-cards mb-3">
        <div class="col-md-6">
            <a class="card card-link" href="{{ route('quotations.index', ['follow_up' => 'pending']) }}">
                <div class="card-body"><div class="text-secondary">Seguimientos pendientes</div><div class="h1">{{ $metrics['follow_ups']['pending'] }}</div></div>
            </a>
        </div>
        <div class="col-md-6">
            <a class="card card-link" href="{{ route('quotations.index', ['follow_up' => 'overdue']) }}">
                <div class="card-body"><div class="text-secondary">Seguimientos vencidos</div><div class="h1 text-danger">{{ $metrics['follow_ups']['overdue'] }}</div></div>
            </a>
        </div>
    </div>

    @foreach ($metrics['currencies'] as $code => $bucket)
        <h3 class="mb-2">{{ $bucket['label'] }}</h3>
        <div class="row row-cards mb-3">
            @foreach ([
                'Cotizaciones creadas' => $bucket['quotations_created'],
                'Pendientes' => $bucket['quotations_pending'],
                'Aprobadas' => $bucket['quotations_approved'],
                'Rechazadas' => $bucket['quotations_rejected'],
                'Vencidas' => $bucket['quotations_expired'],
                'Monto cotizado' => \App\Support\Money::display($bucket['quoted_amount'], $code),
                'Monto aprobado' => \App\Support\Money::display($bucket['approved_amount'], $code),
                'Pedidos activos' => $bucket['orders_active'],
                'Pedidos completados' => $bucket['orders_completed'],
                'Monto de pedidos' => \App\Support\Money::display($bucket['orders_amount'], $code),
                'Conversión' => $bucket['conversion']['rate'].'%',
            ] as $label => $value)
                <div class="col-6 col-md-3">
                    <div class="card"><div class="card-body"><div class="text-secondary">{{ $label }}</div><div class="h2 mb-0">{{ $value }}</div></div></div>
                </div>
            @endforeach
        </div>
        <div class="row mb-4">
            <div class="col-md-6"><div class="card"><div class="card-body"><h4>Cotizaciones por mes</h4><canvas id="month-{{ $code }}"></canvas></div></div></div>
            <div class="col-md-6"><div class="card"><div class="card-body"><h4>Montos cotizados y aprobados</h4><canvas id="amounts-{{ $code }}"></canvas></div></div></div>
        </div>
    @endforeach

    <script type="application/json" id="chart-data">@json($metrics)</script>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js"></script>
<script>
    const metrics = JSON.parse(document.getElementById('chart-data').textContent);
    Object.entries(metrics.currencies).forEach(([code, bucket]) => {
        const labels = Object.keys(bucket.status_counts);
        const values = Object.values(bucket.status_counts);
        const series = metrics.series[code];
        new Chart(document.getElementById('month-' + code), {
            type: 'bar',
            data: { labels: metrics.months, datasets: [{ label: 'Cotizaciones', data: series.quotations }] },
        });
        new Chart(document.getElementById('amounts-' + code), {
            type: 'bar',
            data: {
                labels: metrics.months,
                datasets: [
                    { label: 'Cotizado', data: series.quoted },
                    { label: 'Aprobado', data: series.approved },
                ],
            },
        });
        const holder = document.getElementById('month-' + code).parentElement;
        if (labels.length) {
            const canvas = document.createElement('canvas');
            holder.appendChild(document.createElement('h4')).textContent = 'Cotizaciones por estado';
            holder.appendChild(canvas);
            new Chart(canvas, { type: 'doughnut', data: { labels, datasets: [{ data: values }] } });
        }
        const conversion = document.createElement('canvas');
        holder.appendChild(document.createElement('h4')).textContent = 'Conversión a pedido';
        holder.appendChild(conversion);
        new Chart(conversion, {
            type: 'doughnut',
            data: {
                labels: ['Convertidas', 'Aprobadas sin pedido'],
                datasets: [{ data: [bucket.conversion.converted, Math.max(bucket.conversion.approved - bucket.conversion.converted, 0)] }],
            },
        });
    });
</script>
@endpush
