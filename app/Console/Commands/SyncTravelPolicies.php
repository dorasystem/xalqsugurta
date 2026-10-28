<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\Travel\TravelPolicyService;
use Illuminate\Console\Command;

/** Marks travel orders paid at the insurer (Payme / Click links) as paid and stores their policy */
final class SyncTravelPolicies extends Command
{
    protected $signature = 'travel:sync-policies {--days=30 : How old an unpaid order may be}';

    protected $description = 'Checks unpaid travel orders against the insurer (get) and stores issued policies';

    public function handle(TravelPolicyService $policies): int
    {
        $found = 0;

        Order::where('product_key', 'travel')
            ->whereIn('status', [Order::STATUS_NEW, Order::STATUS_PENDING])
            ->where('created_at', '>=', now()->subDays((int) $this->option('days')))
            ->orderBy('id')
            ->each(function (Order $order) use ($policies, &$found): void {
                $found += (int) $policies->refresh($order);
            });

        $this->info("Policies found: {$found}");

        return self::SUCCESS;
    }
}
