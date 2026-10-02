<?php

namespace Tests\Feature\Admin;

use App\Filament\Admin\Pages\Dashboard;
use App\Filament\Admin\Widgets\OrdersStatsOverview;
use App\Filament\Admin\Widgets\ProductSalesChart;
use App\Filament\Admin\Widgets\RevenueChart;
use App\Models\Order;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-09-29 15:00:00');
        $this->actingAs(User::factory()->create());
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    private function order(string $status, int $amount, string $createdAt, string $product = 'Gaz ballon', string $key = 'gas'): Order
    {
        $order = Order::create([
            'product_name'         => $product,
            'insuranceProductName' => $product,
            'amount'               => $amount,
            'status'               => $status,
            'insurance_id'         => 'X',
            'insurances_data'      => ['_product_key' => $key],
        ]);
        $order->forceFill(['created_at' => $createdAt])->save();

        return $order;
    }

    public function test_widgets_add_up_the_orders(): void
    {
        $this->order(Order::STATUS_PAID, 100000, '2026-09-29 10:00:00');
        $this->order(Order::STATUS_NEW, 50000, '2026-09-29 11:00:00');
        $this->order(Order::STATUS_PAID, 200000, '2026-09-10 09:00:00', 'OSGOR', 'osgor');
        $this->order(Order::STATUS_PAID, 999000, '2026-08-01 09:00:00');   // outside every window

        $this->get(Dashboard::getUrl())->assertOk();

        Livewire::test(OrdersStatsOverview::class)
            ->assertSee('Bugungi buyurtmalar')
            ->assertSee('1 tasi to\'langan')
            ->assertSee('300 000')                     // this month: 100 000 + 200 000
            ->assertSee('67%')                         // 30 days: 2 paid of 3
            ->assertSee('30 kun: 2 / 3 buyurtma');

        $revenue = Livewire::test(RevenueChart::class)->instance();
        $data    = (fn () => $this->getData())->call($revenue);
        $this->assertSame(100000.0, end($data['datasets'][0]['data']));
        $this->assertSame(300000.0, array_sum($data['datasets'][0]['data']));

        $sales = (fn () => $this->getData())->call(Livewire::test(ProductSalesChart::class)->instance());
        $this->assertEqualsCanonicalizing(['Gaz ballon', 'OSGOR'], $sales['labels']);
        $this->assertSame([1, 1], $sales['datasets'][0]['data']);
    }

    public function test_product_key_column_follows_insurances_data(): void
    {
        $order = $this->order(Order::STATUS_PAID, 1000, '2026-09-29 10:00:00', 'OSGOP', 'osgop');

        $this->assertSame('osgop', $order->getAttributes()['product_key']);
        $this->assertSame(1, Order::where('product_key', 'osgop')->count());
    }
}
