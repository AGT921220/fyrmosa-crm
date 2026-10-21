<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ActivityAction;
use App\Enums\QuotationStatus;
use App\Enums\Role;
use App\Models\ActivityLog;
use App\Models\Quotation;
use App\Models\User;
use App\Models\QuotationVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\CreatesCrmUsers;
use Tests\TestCase;

class QuotationTest extends TestCase
{
    use CreatesCrmUsers;
    use RefreshDatabase;

    public function test_creates_quotation_with_totals_and_unique_folio(): void
    {
        $sales = $this->userWithRole(Role::Vendedor);

        $this->actingAs($sales)
            ->post(route('quotations.store'), $this->quotationPayload($sales))
            ->assertRedirect();

        $this->actingAs($sales)
            ->post(route('quotations.store'), $this->quotationPayload($sales))
            ->assertRedirect();

        $first = Quotation::query()->where('folio', 'COT-000001')->first();
        $second = Quotation::query()->where('folio', 'COT-000002')->first();

        $this->assertNotNull($first);
        $this->assertNotNull($second);
        $this->assertSame('225.00', $first->subtotal);
        $this->assertSame('25.00', $first->discount_total);
        $this->assertSame('28.80', $first->tax_total);
        $this->assertSame('253.80', $first->total);
        $this->assertSame(QuotationStatus::Draft, $first->status);
        $this->assertDatabaseHas('activity_logs', [
            'subject_id' => $first->id,
            'action' => ActivityAction::Created->value,
            'user_id' => $sales->id,
        ]);
    }

    public function test_rejects_discount_above_the_line_amount(): void
    {
        $sales = $this->userWithRole(Role::Vendedor);
        $payload = $this->quotationPayload($sales, [
            'items' => [[
                'description' => 'Servicio',
                'quantity' => '1',
                'unit' => 'PZA',
                'unit_price' => '10',
                'discount_type' => 'amount',
                'discount_value' => '25',
                'tax_rate' => '0',
            ]],
        ]);

        $this->actingAs($sales)
            ->from(route('quotations.create'))
            ->post(route('quotations.store'), $payload)
            ->assertRedirect(route('quotations.create'))
            ->assertSessionHas('error');

        $this->assertSame(0, Quotation::query()->count());
    }

    public function test_status_transitions_follow_ups_versions_and_history(): void
    {
        $sales = $this->userWithRole(Role::Vendedor);
        $this->actingAs($sales)->post(route('quotations.store'), $this->quotationPayload($sales));
        $quotation = Quotation::query()->first();

        $this->actingAs($sales)
            ->post(route('quotations.status', $quotation), ['status' => 'aprobada', 'comment' => 'Directo'])
            ->assertRedirect()
            ->assertSessionHas('error');
        $this->assertSame(QuotationStatus::Draft, $quotation->fresh()->status);

        $this->actingAs($sales)
            ->post(route('quotations.status', $quotation), ['status' => 'enviada', 'comment' => 'Enviada por correo'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->actingAs($sales)
            ->post(route('quotations.follow-ups.store', $quotation), [
                'type' => 'call',
                'occurred_at' => '2026-10-02 10:00:00',
                'result' => 'Pidió ajustes',
                'notes' => 'Llamada de seguimiento',
                'next_follow_up_at' => '2026-10-09 09:00:00',
                'user_id' => $sales->id,
            ])
            ->assertRedirect();

        $this->assertNotNull($quotation->fresh()->next_follow_up_at);
        $this->assertDatabaseHas('follow_ups', ['result' => 'Pidió ajustes']);

        $this->actingAs($sales)
            ->put(route('quotations.update', $quotation), $this->quotationPayload($sales, [
                'commercial_terms' => 'Precio revisado',
                'items' => [[
                    'description' => 'Servicio ajustado',
                    'quantity' => '1',
                    'unit' => 'PZA',
                    'unit_price' => '80',
                    'discount_type' => 'amount',
                    'discount_value' => '0',
                    'tax_rate' => '0',
                ]],
            ]))
            ->assertRedirect();

        $this->assertSame('80.00', $quotation->fresh()->total);
        $this->assertTrue(ActivityLog::query()->where('action', ActivityAction::PricesChanged->value)->exists());
        $this->assertTrue(ActivityLog::query()->where('action', ActivityAction::TermsChanged->value)->exists());

        $this->actingAs($sales)->post(route('quotations.versions.store', $quotation))->assertRedirect();

        $version = Quotation::query()->where('version_number', 2)->first();
        $this->assertSame('COT-000001-V2', $version->folio);
        $this->assertSame(QuotationStatus::Draft, $version->status);
        $this->assertSame(QuotationStatus::Sent, $quotation->fresh()->status);
        $this->assertSame('80.00', $quotation->fresh()->total);
        $this->assertTrue(QuotationVersion::query()->where('quotation_id', $quotation->id)->exists());

        $this->actingAs($sales)
            ->post(route('quotations.status', $quotation), ['status' => 'aprobada'])
            ->assertRedirect();

        $this->actingAs($sales)
            ->from(route('quotations.edit', $quotation))
            ->put(route('quotations.update', $quotation), $this->quotationPayload($sales))
            ->assertSessionHas('error');
        $this->assertSame('80.00', $quotation->fresh()->total);
    }

    public function test_converts_an_approved_quotation_once(): void
    {
        $sales = $this->userWithRole(Role::Vendedor);
        $quotation = $this->approvedQuotation($sales);

        $this->actingAs($sales)
            ->post(route('quotations.convert', $quotation))
            ->assertRedirect();

        $order = $quotation->fresh()->order;
        $this->assertNotNull($order);
        $this->assertSame('PED-000001', $order->folio);
        $this->assertSame((string) $quotation->fresh()->total, (string) $order->total);
        $this->assertSame($quotation->id, $order->quotation_id);
        $this->assertSame($sales->id, $order->converted_by);

        $this->actingAs($sales)
            ->from(route('quotations.show', $quotation))
            ->post(route('quotations.convert', $quotation))
            ->assertSessionHas('error');
        $this->assertSame(1, $quotation->order()->count());
    }

    public function test_expires_sent_quotations_past_their_date(): void
    {
        $sales = $this->userWithRole(Role::Vendedor);
        $this->actingAs($sales)->post(route('quotations.store'), $this->quotationPayload($sales, [
            'issued_on' => '2026-09-01',
            'expires_on' => '2026-09-15',
        ]));
        $quotation = Quotation::query()->first();
        $this->actingAs($sales)->post(route('quotations.status', $quotation), ['status' => 'enviada']);

        $this->artisan('crm:expire-quotations')->assertSuccessful();

        $this->assertSame(QuotationStatus::Expired, $quotation->fresh()->status);
        $this->assertDatabaseHas('activity_logs', [
            'subject_id' => $quotation->id,
            'action' => ActivityAction::StatusChanged->value,
            'comment' => 'Vencida por fecha de vencimiento.',
        ]);
    }

    public function test_sales_can_download_the_pdf(): void
    {
        $sales = $this->userWithRole(Role::Vendedor);
        $this->actingAs($sales)->post(route('quotations.store'), $this->quotationPayload($sales));
        $quotation = Quotation::query()->first();

        $this->actingAs($sales)
            ->get(route('quotations.pdf', ['quotation' => $quotation, 'download' => 1]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    private function approvedQuotation(User $sales): Quotation
    {
        $this->actingAs($sales)->post(route('quotations.store'), $this->quotationPayload($sales));
        $quotation = Quotation::query()->first();
        $this->actingAs($sales)->post(route('quotations.status', $quotation), ['status' => 'enviada']);
        $this->actingAs($sales)->post(route('quotations.status', $quotation), ['status' => 'aprobada']);

        return $quotation->fresh();
    }
}
