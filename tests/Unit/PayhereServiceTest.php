<?php

namespace Tests\Unit;

use App\Services\PayhereService;
use Tests\TestCase;

class PayhereServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'payhere.merchant_id' => '1210001',
            'payhere.merchant_secret' => 'test-secret',
            'payhere.currency' => 'LKR',
            'payhere.sandbox' => true,
        ]);
    }

    public function testCheckoutHashUsesThePayhereFormula(): void
    {
        $service = new PayhereService();
        $amount = '1000.00';
        $expected = strtoupper(md5(
            '1210001'.'BK1'.$amount.'LKR'.strtoupper(md5('test-secret'))
        ));

        $this->assertSame($expected, $service->checkoutHash('BK1', $amount));
    }

    public function testNotificationSignatureMustMatch(): void
    {
        $service = new PayhereService();
        $payload = [
            'merchant_id' => '1210001',
            'order_id' => 'BK1',
            'payhere_amount' => '1000.00',
            'payhere_currency' => 'LKR',
            'status_code' => '2',
        ];
        $payload['md5sig'] = strtoupper(md5(
            $payload['merchant_id']
            . $payload['order_id']
            . $payload['payhere_amount']
            . $payload['payhere_currency']
            . $payload['status_code']
            . strtoupper(md5('test-secret'))
        ));

        $this->assertTrue($service->verifyNotification($payload));

        $payload['md5sig'] = str_repeat('A', 32);

        $this->assertFalse($service->verifyNotification($payload));
    }
}
