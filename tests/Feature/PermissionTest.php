<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Quotation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\CreatesCrmUsers;
use Tests\TestCase;

class PermissionTest extends TestCase
{
    use CreatesCrmUsers;
    use RefreshDatabase;

    public function test_login_uses_nickname_and_the_shared_password(): void
    {
        $admin = $this->userWithRole(Role::Admin);
        $admin->update([
            'nickname' => 'admin',
            'password' => 'admin',
        ]);

        $this->post(route('login'), [
            'nickname' => 'admin',
            'password' => 'admin',
        ])->assertRedirect(route('dashboard'));

        $this->post(route('logout'));

        $this->post(route('login'), [
            'email' => $admin->email,
            'password' => 'admin',
        ])->assertSessionHasErrors('nickname');
    }

    public function test_billing_cannot_approve_quotations_and_sales_cannot_confirm_orders(): void
    {
        $sales = $this->userWithRole(Role::Vendedor);
        $operations = $this->userWithRole(Role::Facturacion);

        $this->actingAs($sales)->post(route('quotations.store'), $this->quotationPayload($sales));
        $quotation = Quotation::query()->first();
        $this->actingAs($sales)->post(route('quotations.status', $quotation), ['status' => 'enviada']);

        $this->actingAs($operations)
            ->post(route('quotations.status', $quotation), ['status' => 'aprobada'])
            ->assertForbidden();

        $this->actingAs($operations)
            ->post(route('quotations.store'), $this->quotationPayload($operations))
            ->assertForbidden();

        $this->actingAs($operations)->post(route('orders.store'), $this->orderPayload($operations))->assertRedirect();

        $this->actingAs($sales)
            ->post(route('orders.status', \App\Models\Order::query()->first()), [
                'status' => 'confirmada',
            ])
            ->assertForbidden();

        $this->actingAs($sales)->get(route('quotations.show', $quotation))->assertOk();
        $this->actingAs($operations)->get(route('dashboard'))->assertOk();
    }
}
