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
    <h1>Pedido {{ $order->folio }}</h1>
    <p class="muted">{{ $order->status->label() }} · {{ $order->currency->label() }}
        @if ($order->quotation) · Cotización {{ $order->quotation->folio }} v{{ $order->quotation_version }} @endif
    </p>
    <p>
        <strong>{{ $order->client_name }}</strong><br>
        {{ $order->legal_name }}<br>
        {{ $order->contactName() }} · {{ $order->email }} · {{ $order->phone }}
    </p>
    <p>Emisión: {{ $order->issued_on->format('d/m/Y') }}
        @if ($order->estimated_delivery_on) · Entrega estimada: {{ $order->estimated_delivery_on->format('d/m/Y') }} @endif
        <br>Responsable: {{ $order->user->name }}
        @if ($order->client_folio) <br>Folio del cliente: {{ $order->client_folio }} @endif
    </p>
    <table>
        <thead>
            <tr><th>SKU</th><th>Descripción</th><th>Cant.</th><th>Unidad</th><th>Precio</th><th>Desc.</th><th>Impuesto</th><th>Total</th></tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
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
    <p class="right">Descuentos: {{ $order->discount_total }}<br>Subtotal: {{ $order->subtotal }}<br>Impuestos: {{ $order->tax_total }}<br><strong>Total: {{ $order->total }} {{ $order->currency->label() }}</strong></p>
    @if ($order->commercial_terms)<p><strong>Condiciones comerciales</strong><br>{{ $order->commercial_terms }}</p>@endif
    @if ($order->payment_terms)<p><strong>Pago</strong><br>{{ $order->payment_terms }}</p>@endif
    @if ($order->notes)<p>{{ $order->notes }}</p>@endif
</body>
</html>
