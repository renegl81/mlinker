<?php

namespace App\Console\Commands;

use App\Models\Plan;
use Illuminate\Console\Command;
use Stripe\Price;
use Stripe\Product;
use Stripe\Stripe;

class SyncPlansToStripe extends Command
{
    protected $signature = 'stripe:sync-plans
                            {--force : Overwrite existing Stripe price IDs}';

    protected $description = 'Create Stripe products & prices for each local plan and store the price IDs';

    public function handle(): int
    {
        Stripe::setApiKey(config('cashier.secret'));

        $plans = Plan::where('slug', '!=', 'free')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        if ($plans->isEmpty()) {
            $this->warn('No paid plans found. Run the PlanSeeder first.');

            return self::FAILURE;
        }

        foreach ($plans as $plan) {
            if ($plan->stripe_price_id && ! $this->option('force')) {
                $this->line("  ⏭  <comment>{$plan->name}</comment> already has price ID {$plan->stripe_price_id} — skipping (use --force to overwrite)");

                continue;
            }

            $product = Product::create([
                'name' => "MenuLinker {$plan->name}",
                'description' => $plan->description,
                'metadata' => [
                    'plan_slug' => $plan->slug,
                    'plan_id' => $plan->id,
                ],
            ]);

            $interval = match ($plan->period) {
                'year' => 'year',
                default => 'month',
            };

            $price = Price::create([
                'product' => $product->id,
                'unit_amount' => (int) round($plan->price * 100),
                'currency' => config('cashier.currency', 'eur'),
                'recurring' => ['interval' => $interval],
                'metadata' => [
                    'plan_slug' => $plan->slug,
                    'plan_id' => $plan->id,
                ],
            ]);

            $plan->update(['stripe_price_id' => $price->id]);

            $this->info("  ✓  {$plan->name}: product {$product->id} → price {$price->id}");
        }

        $this->newLine();
        $this->info('Done! All plans synced to Stripe.');

        return self::SUCCESS;
    }
}
