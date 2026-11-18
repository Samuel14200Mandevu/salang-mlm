<?php

namespace Tests\Unit;

use App\Models\Consultation;
use App\Services\ConsultationSaleService;
use Tests\TestCase;

class ConsultationServiceSaleTest extends TestCase
{
    public function test_can_pay_partial_sessions_then_remaining(): void
    {
        $consultation = new Consultation([
            'seances_ceragem' => 5,
            'prix_ceragem' => 20,
            'seances_detox' => 0,
            'prix_detox' => 0,
            'total_services' => 100,
            'services_payment' => null,
            'status' => 'completed',
        ]);

        $stats = ConsultationSaleService::serviceStats($consultation);
        $this->assertSame(1, $stats['pending']);
        $this->assertSame(100.0, $stats['recommended_amount']);
        $this->assertSame(100.0, $stats['remaining_amount']);

        $partial = ConsultationSaleService::resolvePendingServices($consultation, ['ceragem' => 2]);
        $this->assertSame(2, (int) $partial[0]['pay_seances']);
        $this->assertSame(40.0, (float) $partial[0]['pay_amount']);

        $cart = ConsultationSaleService::buildServiceCartItems($consultation, $partial);
        $this->assertSame('Séances Ceragem (x2)', $cart[0]['name']);
        $this->assertSame(40.0, (float) $cart[0]['price']);
        $this->assertSame(2, (int) $cart[0]['consultation_service_seances']);

        // Simule un 1er paiement de 2 séances
        $consultation->services_payment = [
            'ceragem' => [
                'status' => ConsultationSaleService::STATUS_PARTIAL,
                'seances_paid' => 2,
                'amount_paid' => 40,
                'order_id' => 10,
                'payments' => [
                    ['order_id' => 10, 'seances' => 2, 'amount' => 40],
                ],
            ],
        ];

        $after = ConsultationSaleService::serviceLines($consultation)['ceragem'];
        $this->assertSame('partial', $after['status']);
        $this->assertSame(3, (int) $after['seances_remaining']);
        $this->assertSame(60.0, (float) $after['amount_remaining']);

        $rest = ConsultationSaleService::resolvePendingServices($consultation, ['ceragem' => 3]);
        $this->assertSame(60.0, (float) $rest[0]['pay_amount']);

        $this->expectException(\InvalidArgumentException::class);
        ConsultationSaleService::resolvePendingServices($consultation, ['ceragem' => 4]);
    }
}
