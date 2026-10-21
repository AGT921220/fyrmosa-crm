<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Features\Clients\Application\SaveClient;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Client::class);

        return view('clients.index', [
            'clients' => Client::query()->withCount('contacts')->orderBy('name')->paginate(15),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Client::class);

        return view('clients.create');
    }

    public function store(StoreClientRequest $request, SaveClient $save): RedirectResponse
    {
        $save($request->validated());

        return redirect()->route('clients.index')->with('success', 'Cliente guardado.');
    }

    public function edit(Client $client): View
    {
        $this->authorize('update', $client);
        $client->load('contacts');

        return view('clients.edit', ['client' => $client]);
    }

    public function update(UpdateClientRequest $request, Client $client, SaveClient $save): RedirectResponse
    {
        $save($request->validated(), $client);

        return redirect()->route('clients.index')->with('success', 'Cliente actualizado.');
    }

    public function destroy(Client $client): RedirectResponse
    {
        $this->authorize('delete', $client);
        $client->delete();

        return redirect()->route('clients.index')->with('success', 'Cliente eliminado.');
    }
}
