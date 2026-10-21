<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\Currency;
use App\Enums\QuotationStatus;
use App\Features\Quotations\Application\AddFollowUp;
use App\Features\Quotations\Application\ChangeQuotationStatus;
use App\Features\Quotations\Application\ConvertQuotationToOrder;
use App\Features\Quotations\Application\CreateQuotation;
use App\Features\Quotations\Application\CreateQuotationVersion;
use App\Features\Quotations\Application\DuplicateQuotation;
use App\Features\Quotations\Application\UpdateQuotation;
use App\Features\Shared\Application\StoreAttachment;
use App\Http\Requests\ChangeQuotationStatusRequest;
use App\Http\Requests\StoreAttachmentRequest;
use App\Http\Requests\StoreFollowUpRequest;
use App\Http\Requests\StoreQuotationRequest;
use App\Http\Requests\UpdateQuotationRequest;
use App\Models\Client;
use App\Models\Quotation;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class QuotationController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Quotation::class);

        $filters = $request->only(['q', 'status', 'currency', 'client_id', 'user_id', 'date_from', 'date_to', 'follow_up', 'sort', 'direction']);

        return view('quotations.index', [
            'quotations' => Quotation::query()->with('user')->filtered($filters)->paginate(15)->withQueryString(),
            'filters' => $filters,
            'clients' => Client::query()->orderBy('name')->get(['id', 'name']),
            'users' => User::query()->orderBy('name')->get(['id', 'name']),
            'statuses' => QuotationStatus::cases(),
            'currencies' => Currency::cases(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Quotation::class);

        return view('quotations.create', $this->formData());
    }

    public function store(StoreQuotationRequest $request, CreateQuotation $create): RedirectResponse
    {
        $quotation = $create($request->user(), $request->validated());

        return redirect()->route('quotations.show', $quotation)->with('success', 'Cotización '.$quotation->folio.' creada.');
    }

    public function show(Quotation $quotation): View
    {
        $this->authorize('view', $quotation);
        $quotation->load(['items', 'user', 'client', 'followUps.user', 'activities.user', 'attachments.user', 'order']);
        $chain = $quotation->chain()->with(['order', 'versions.user'])->orderBy('version_number')->get();

        return view('quotations.show', [
            'quotation' => $quotation,
            'chain' => $chain,
            'hasOrder' => $chain->contains(fn (Quotation $item) => $item->order !== null),
            'users' => User::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function edit(Quotation $quotation): View
    {
        $this->authorize('update', $quotation);
        $quotation->load('items');

        return view('quotations.edit', ['quotation' => $quotation] + $this->formData());
    }

    public function update(UpdateQuotationRequest $request, Quotation $quotation, UpdateQuotation $update): RedirectResponse
    {
        $update($quotation, $request->user(), $request->validated());

        return redirect()->route('quotations.show', $quotation)->with('success', 'Cotización actualizada.');
    }

    public function duplicate(Quotation $quotation, DuplicateQuotation $duplicate): RedirectResponse
    {
        $this->authorize('duplicate', $quotation);
        $copy = $duplicate($quotation, request()->user());

        return redirect()->route('quotations.show', $copy)->with('success', 'Se creó el duplicado '.$copy->folio.'.');
    }

    public function status(ChangeQuotationStatusRequest $request, Quotation $quotation, ChangeQuotationStatus $change): RedirectResponse
    {
        $change(
            $quotation,
            QuotationStatus::from($request->validated('status')),
            $request->user(),
            $request->validated('comment'),
        );

        return back()->with('success', 'Estado actualizado.');
    }

    public function version(Quotation $quotation, CreateQuotationVersion $createVersion): RedirectResponse
    {
        $this->authorize('version', $quotation);
        $copy = $createVersion($quotation, request()->user());

        return redirect()->route('quotations.show', $copy)->with('success', 'Versión '.$copy->folio.' creada.');
    }

    public function convert(Quotation $quotation, ConvertQuotationToOrder $convert): RedirectResponse
    {
        $this->authorize('convert', $quotation);
        $order = $convert($quotation, request()->user());

        return redirect()->route('orders.show', $order)->with('success', 'Pedido '.$order->folio.' generado.');
    }

    public function followUp(StoreFollowUpRequest $request, Quotation $quotation, AddFollowUp $addFollowUp): RedirectResponse
    {
        $this->authorize('followUp', $quotation);
        $addFollowUp($quotation, $request->user(), $request->validated());

        return back()->with('success', 'Seguimiento registrado.');
    }

    public function attachment(StoreAttachmentRequest $request, Quotation $quotation, StoreAttachment $store): RedirectResponse
    {
        $this->authorize('attach', $quotation);
        $store($quotation, $request->file('file'), $request->user());

        return back()->with('success', 'Documento adjunto guardado.');
    }

    public function pdf(Request $request, Quotation $quotation): Response
    {
        $this->authorize('pdf', $quotation);
        $quotation->load(['items', 'user']);
        $pdf = Pdf::loadView('quotations.pdf', ['quotation' => $quotation])->setPaper('letter');

        if ($request->boolean('download')) {
            return $pdf->download($quotation->folio.'.pdf');
        }

        return $pdf->stream($quotation->folio.'.pdf');
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
