<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\Role;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\CreatesCrmUsers;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use CreatesCrmUsers;
    use RefreshDatabase;

    public function test_registers_partial_and_complete_deliveries_without_exceeding_quantity(): void
    {
        $operations = $this->userWithRole(Role::Facturacion);
        $this->actingAs($operations)->post(route('orders.store'), $this->orderPayload($operations));
        $order = Order::query()->first();
        $item = $order->items()->first();

        $this->actingAs($operations)->post(route('orders.status', $order), [
            'status' => OrderStatus::PendingConfirmation->value,
        ])->assertRedirect();
        $this->actingAs($operations)->post(route('orders.status', $order), [
            'status' => OrderStatus::Confirmed->value,
        ])->assertRedirect();

        $this->actingAs($operations)->post(route('orders.deliveries.store', $order), [
            'delivered_on' => '2026-10-08',
            'items' => [[
                'order_item_id' => $item->id,
                'quantity' => '4',
            ]],
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertSame(OrderStatus::PartiallyDelivered, $order->fresh()->status);
        $this->assertSame('4.0000', $item->fresh()->quantity_delivered);

        $this->actingAs($operations)
            ->from(route('orders.show', $order))
            ->post(route('orders.deliveries.store', $order), [
                'delivered_on' => '2026-10-08',
                'items' => [[
                    'order_item_id' => $item->id,
                    'quantity' => '7',
                ]],
            ])
            ->assertSessionHas('error');
        $this->assertSame('4.0000', $item->fresh()->quantity_delivered);

        $this->actingAs($operations)->post(route('orders.deliveries.store', $order), [
            'delivered_on' => '2026-10-09',
            'items' => [[
                'order_item_id' => $item->id,
                'quantity' => '6',
            ]],
        ])->assertSessionHas('success');

        $this->assertSame(OrderStatus::Completed, $order->fresh()->status);
        $this->assertSame('10.0000', OrderItem::query()->find($item->id)->quantity_delivered);
    }

    public function test_cancelled_orders_do_not_accept_deliveries(): void
    {
        $operations = $this->userWithRole(Role::Facturacion);
        $this->actingAs($operations)->post(route('orders.store'), $this->orderPayload($operations));
        $order = Order::query()->first();

        $this->actingAs($operations)->post(route('orders.status', $order), [
            'status' => OrderStatus::Cancelled->value,
            'comment' => 'El cliente canceló',
        ])->assertSessionHas('success');

        $this->actingAs($operations)
            ->from(route('orders.show', $order))
            ->post(route('orders.deliveries.store', $order), [
                'delivered_on' => '2026-10-08',
                'items' => [[
                    'order_item_id' => $order->items()->first()->id,
                    'quantity' => '1',
                ]],
            ])
            ->assertSessionHas('error');

        $this->assertSame('0.0000', $order->items()->first()->quantity_delivered);
    }

    public function test_stores_client_and_sae_folios_and_shows_them_in_index(): void
    {
        $operations = $this->userWithRole(Role::Facturacion);

        $this->actingAs($operations)
            ->post(route('orders.store'), $this->orderPayload($operations, [
                'client_folio' => 'PED-CLI-99',
                'sae_folio' => 'SAE-7788',
            ]))
            ->assertRedirect();

        $order = Order::query()->first();
        $this->assertSame('PED-CLI-99', $order->client_folio);
        $this->assertSame('SAE-7788', $order->sae_folio);

        $this->actingAs($operations)
            ->get(route('orders.index'))
            ->assertOk()
            ->assertSee('Pedido de cliente')
            ->assertSee('Folio SAE')
            ->assertSee('PED-CLI-99')
            ->assertSee('SAE-7788');
    }

    public function test_create_form_does_not_list_the_owner(): void
    {
        $this->seed(DatabaseSeeder::class);
        $operations = $this->userWithRole(Role::Facturacion);
        $owner = User::query()->where('nickname', 'superadmin')->firstOrFail();

        $users = $this->actingAs($operations)
            ->get(route('orders.create'))
            ->assertOk()
            ->viewData('users');

        $this->assertFalse($users->contains(fn (User $user): bool => $user->id === $owner->id));
    }
}
