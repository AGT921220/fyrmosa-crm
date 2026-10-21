<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2933; }
        h1 { font-size: 20px; margin-bottom: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #d9e2ec; padding: 6px; text-align: left; }
        th { background: #f0f4f8; }
        .right { text-align: right; }
        .muted { color: #627d98; }
    </style>
</head>
<body>
    <h1>Cotización {{ $quotation->folio }}</h1>
    <p class="muted">Versión {{ $quotation->version_number }} · {{ $quotation->status->label() }} · {{ $quotation->currency->label() }}</p>
    <p>
        <strong>{{ $quotation->client_name }}</strong><br>
        {{ $quotation->legal_name }}<br>
        {{ $quotation->contactName() }} · {{ $quotation->email }} · {{ $quotation->phone }}
    </p>
    <p>Fecha: {{ $quotation->issued_on->format('d/m/Y') }} · Vencimiento: {{ $quotation->expires_on->format('d/m/Y') }}<br>
        Responsable: {{ $quotation->user->name }}<br>
        Entrega: {{ $quotation->delivery_time }}</p>
    <table>
        <thead>
            <tr>
                <th>SKU</th><th>Descripción</th><th>Cant.</th><th>Unidad</th><th>Precio</th><th>Desc.</th><th>Impuesto</th><th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($quotation->items as $item)
                <tr>
                    <td>{{ $item->sku }}</td>
                    <td>{{ $item->description }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ $item->unit }}</td>
                    <td class="right">{{ $item->unit_price }}</td>
                    <td class="right">{{ $item->discount_amount }}</td>
                    <td class="right">{{ $item->tax_amount }}</td>
                    <td class="right">{{ $item->total }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <p class="right">Descuentos: {{ $quotation->discount_total }}<br>Subtotal: {{ $quotation->subtotal }}<br>Impuestos: {{ $quotation->tax_total }}<br><strong>Total: {{ $quotation->total }} {{ $quotation->currency->label() }}</strong></p>
    @if ($quotation->commercial_terms)<p><strong>Condiciones comerciales</strong><br>{{ $quotation->commercial_terms }}</p>@endif
    @if ($quotation->payment_terms)<p><strong>Pago</strong><br>{{ $quotation->payment_terms }}</p>@endif
    @if ($quotation->client_notes)<p>{{ $quotation->client_notes }}</p>@endif
</body>
</html>
