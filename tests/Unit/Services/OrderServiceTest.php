<?php

namespace Tests\Unit\Services;

use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    private OrderService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new OrderService();
    }

    // ─── createOrder ──────────────────────────────────────────────────────────

    public function test_create_order_persists_all_fields(): void
    {
        $data = [
            'product_name'             => 'Gaz balon sug\'urtasi',
            'amount'                   => 150000,
            'insurance_id'             => 'gas_abc123',
            'phone'                    => '998901234567',
            'insurances_data'          => ['_product_key' => 'gas', 'applicant' => ['name' => 'Test']],
            'insurances_response_data' => ['contract_id' => 999, 'result' => 0],
            'contractStartDate'        => '2026-03-10',
            'contractEndDate'          => '2027-03-09',
            'insuranceProductName'     => 'Gaz balon sug\'urtasi',
        ];

        $order = $this->service->createOrder($data);

        $this->assertInstanceOf(Order::class, $order);
        $this->assertNotNull($order->id);
        $this->assertEquals('Gaz balon sug\'urtasi', $order->product_name);
        $this->assertEquals(150000, $order->amount);
        $this->assertEquals('gas_abc123', $order->insurance_id);
        $this->assertEquals('998901234567', $order->phone);
        $this->assertEquals('gas', $order->insurances_data['_product_key']);
        $this->assertEquals(999, $order->insurances_response_data['contract_id']);
        $this->assertEquals(Order::STATUS_NEW, $order->status);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'new']);
    }

    public function test_create_order_applies_defaults_for_missing_fields(): void
    {
        $order = $this->service->createOrder(['insurance_id' => 'test_001']);

        $this->assertEquals('MOL-MULK Sug\'urta', $order->product_name);
        $this->assertEquals(0, $order->amount);
        $this->assertEquals(Order::STATUS_NEW, $order->status);
        $this->assertNull($order->phone);
        $this->assertNull($order->insurances_data);
        $this->assertNull($order->insurances_response_data);
    }

    public function test_create_order_status_is_always_new_regardless_of_input(): void
    {
        $order = $this->service->createOrder([
            'insurance_id' => 'x',
            'status'       => Order::STATUS_PAID,
        ]);

        $this->assertEquals(Order::STATUS_NEW, $order->status);
    }

    public function test_create_order_stores_insurances_data_as_json(): void
    {
        $insurancesData = [
            '_product_key' => 'property',
            'xalq_contract_number' => 'PROP-abc',
            'applicant' => ['pinfl' => '12345678901234'],
        ];

        $order = $this->service->createOrder([
            'insurance_id'    => 'prop_1',
            'insurances_data' => $insurancesData,
        ]);

        $this->assertEquals('property', $order->insurances_data['_product_key']);
        $this->assertEquals('PROP-abc', $order->insurances_data['xalq_contract_number']);
        $this->assertEquals('12345678901234', $order->insurances_data['applicant']['pinfl']);
    }

    // ─── getOrderById ─────────────────────────────────────────────────────────

    public function test_get_order_by_id_returns_order_when_found(): void
    {
        $created = $this->service->createOrder(['insurance_id' => 'find_me']);

        $found = $this->service->getOrderById($created->id);

        $this->assertNotNull($found);
        $this->assertEquals($created->id, $found->id);
    }

    public function test_get_order_by_id_returns_null_when_not_found(): void
    {
        $result = $this->service->getOrderById(99999);

        $this->assertNull($result);
    }

    // ─── updateOrderStatus ────────────────────────────────────────────────────

    public function test_update_order_status_changes_status(): void
    {
        $order = $this->service->createOrder(['insurance_id' => 'upd_status']);
        $this->assertEquals(Order::STATUS_NEW, $order->status);

        $result = $this->service->updateOrderStatus($order->id, Order::STATUS_PAID);

        $this->assertTrue($result);
        $this->assertEquals(Order::STATUS_PAID, $order->fresh()->status);
    }

    public function test_update_order_status_returns_false_for_non_existent_order(): void
    {
        $result = $this->service->updateOrderStatus(99999, Order::STATUS_PAID);

        $this->assertFalse($result);
    }

    public function test_update_order_status_supports_all_statuses(): void
    {
        $order = $this->service->createOrder(['insurance_id' => 'status_test']);

        foreach ([Order::STATUS_PENDING, Order::STATUS_PAID, Order::STATUS_CANCELLED, Order::STATUS_FAILED] as $status) {
            $this->service->updateOrderStatus($order->id, $status);
            $this->assertEquals($status, $order->fresh()->status);
        }
    }

    // ─── updateOrderPaymentType ───────────────────────────────────────────────

    public function test_update_order_payment_type_sets_payment_type(): void
    {
        $order = $this->service->createOrder(['insurance_id' => 'pay_type']);
        $this->assertNull($order->payment_type);

        $result = $this->service->updateOrderPaymentType($order->id, Order::PAYMENT_PAYME);

        $this->assertTrue($result);
        $this->assertEquals(Order::PAYMENT_PAYME, $order->fresh()->payment_type);
    }

    public function test_update_order_payment_type_returns_false_for_non_existent_order(): void
    {
        $result = $this->service->updateOrderPaymentType(99999, Order::PAYMENT_CLICK);

        $this->assertFalse($result);
    }
}
