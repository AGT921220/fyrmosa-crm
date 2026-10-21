<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\Currency;
use App\Enums\OrderStatus;
use App\Features\Orders\Application\ChangeOrderStatus;
use App\Features\Orders\Application\CreateOrder;
use App\Features\Orders\Application\RegisterDelivery;
use App\Features\Orders\Application\UpdateOrder;
use App\Features\Quotations\Application\AddFollowUp;
use App\Features\Shared\Application\StoreAttachment;
use App\Http\Requests\ChangeOrderStatusRequest;
use App\Http\Requests\StoreAttachmentRequest;
use App\Http\Requests\StoreDeliveryRequest;
use App\Http\Requests\StoreFollowUpRequest;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Client;
use App\Models\Order;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Order::class);
        $filters = $request->only(['q', 'status', 'currency', 'client_id', 'user_id', 'date_from', 'date_to', 'follow_up', 'sort', 'direction']);

        return view('orders.index', [
            'orders' => Order::query()->with(['user', 'quotation'])->filtered($filters)->paginate(15)->withQueryString(),
            'filters' => $filters,
            'clients' => Client::query()->orderBy('name')->get(['id', 'name']),
            'users' => User::query()->orderBy('name')->get(['id', 'name']),
            'statuses' => OrderStatus::cases(),
            'currencies' => Currency::cases(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Order::class);

        return view('orders.create', $this->formData());
    }

    public function store(StoreOrderRequest $request, CreateOrder $create): RedirectResponse
    {
        $order = $create($request->user(), $request->validated());

        return redirect()->route('orders.show', $order)->with('success', 'Pedido '.$order->folio.' creado.');
    }

    public function show(Order $order): View
    {
        $this->authorize('view', $order);
        $order->load([
            'items',
            'user',
            'client',
            'quotation',
            'converter',
            'deliveries.user',
            'deliveries.items',
            'followUps.user',
            'activities.user',
            'attachments.user',
        ]);

        return view('orders.show', [
            'order' => $order,
            'users' => User::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function edit(Order $order): View
    {
        $this->authorize('update', $order);
        $order->load('items');

        return view('orders.edit', ['order' => $order] + $this->formData());
    }

    public function update(UpdateOrderRequest $request, Order $order, UpdateOrder $update): RedirectResponse
    {
        $update($order, $request->user(), $request->validated());

        return redirect()->route('orders.show', $order)->with('success', 'Pedido actualizado.');
    }

    public function status(ChangeOrderStatusRequest $request, Order $order, ChangeOrderStatus $change): RedirectResponse
    {
        $change(
            $order,
            OrderStatus::from($request->validated('status')),
            $request->user(),
            $request->validated('comment'),
        );

        return back()->with('success', 'Estado actualizado.');
    }

    public function delivery(StoreDeliveryRequest $request, Order $order, RegisterDelivery $register): RedirectResponse
    {
        $this->authorize('deliver', $order);
        $register($order, $request->user(), $request->validated());

        return back()->with('success', 'Entrega registrada.');
    }

    public function followUp(StoreFollowUpRequest $request, Order $order, AddFollowUp $addFollowUp): RedirectResponse
    {
        $this->authorize('followUp', $order);
        $addFollowUp($order, $request->user(), $request->validated());

        return back()->with('success', 'Seguimiento registrado.');
    }

    public function attachment(StoreAttachmentRequest $request, Order $order, StoreAttachment $store): RedirectResponse
    {
        $this->authorize('attach', $order);
        $store($order, $request->file('file'), $request->user());

        return back()->with('success', 'Documento adjunto guardado.');
    }

    public function pdf(Request $request, Order $order): Response
    {
        $this->authorize('pdf', $order);
        $order->load(['items', 'user', 'quotation']);
        $pdf = Pdf::loadView('orders.pdf', ['order' => $order])->setPaper('letter');

        if ($request->boolean('download')) {
            return $pdf->download($order->folio.'.pdf');
        }

        return $pdf->stream($order->folio.'.pdf');
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(): array
    {
        return [
            'clients' => Client::query()->with('contacts')->orderBy('name')->get(),
            'users' => User::query()->orderBy('name')->get(['id', 'name']),
            'currencies' => Currency::cases(),
        ];
    }
}
