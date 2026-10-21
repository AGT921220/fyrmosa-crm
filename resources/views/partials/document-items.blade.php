<div class="table-responsive">
    <table class="table card-table">
        <thead>
            <tr>
                <th>SKU</th>
                <th>Descripción</th>
                <th>Cantidad</th>
                <th>Unidad</th>
                <th>Precio</th>
                <th>Descuento</th>
                <th>Impuesto</th>
                <th>Subtotal</th>
                <th>Total</th>
                @isset($showDelivered)
                    <th>Entregado</th>
                @endisset
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $item)
                <tr>
                    <td>{{ $item->sku }}</td>
                    <td>{{ $item->description }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ $item->unit }}</td>
                    <td>{{ $item->unit_price }}</td>
                    <td>{{ $item->discount_amount }} @if($item->discount_type->value === 'percent')({{ $item->discount_value }}%)@endif</td>
                    <td>{{ $item->tax_amount }} ({{ $item->tax_rate }}%)</td>
                    <td>{{ $item->subtotal }}</td>
                    <td>{{ $item->total }}</td>
                    @isset($showDelivered)
                        <td>{{ $item->quantity_delivered }}</td>
                    @endisset
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="card-footer text-end">
    <div>Descuentos: {{ \App\Support\Money::display($document->discount_total, $document->currency) }}</div>
    <div>Subtotal: {{ \App\Support\Money::display($document->subtotal, $document->currency) }}</div>
    <div>Impuestos: {{ \App\Support\Money::display($document->tax_total, $document->currency) }}</div>
    <div class="fw-bold">Total: {{ \App\Support\Money::display($document->total, $document->currency) }}</div>
</div>
