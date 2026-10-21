<div class="card mb-3">
    <div class="card-header d-flex justify-content-between">
        <h3 class="card-title mb-0">Conceptos</h3>
        <button class="btn btn-outline-primary" id="add-item" type="button">Agregar concepto</button>
    </div>
    <div class="table-responsive">
        <table class="table card-table" id="items-table">
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Descripción</th>
                    <th>Cantidad</th>
                    <th>Unidad</th>
                    <th>Precio</th>
                    <th>Descuento</th>
                    <th>Tipo</th>
                    <th>Impuesto %</th>
                    <th>Total</th>
                    <th></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
    <div class="card-footer">
        <div class="row g-2 text-end">
            <div class="col-6 col-md-3 ms-md-auto">Descuentos <strong id="sum-discount">0.00</strong></div>
            <div class="col-6 col-md-3">Subtotal <strong id="sum-subtotal">0.00</strong></div>
            <div class="col-6 col-md-3">Impuestos <strong id="sum-tax">0.00</strong></div>
            <div class="col-6 col-md-3">Total <strong id="sum-total">0.00</strong></div>
        </div>
    </div>
</div>
<script type="application/json" id="line-items-data">@json($items ?? [])</script>
@push('scripts')
    <script src="{{ asset('js/line-items.js') }}"></script>
@endpush
