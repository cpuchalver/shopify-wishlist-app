<?php

namespace App\Console\Commands;

use App\Models\Shop;
use App\Services\ShopifyTokenRefresher;
use Illuminate\Console\Command;

class RefreshShopifyTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shopify:refresh-tokens';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh expiring offline access tokens that are missing or about to expire';

    /**
     * Execute the console command.
     */
    public function handle(ShopifyTokenRefresher $refresher): int
    {
        $shops = Shop::query()
            ->whereNotNull('refresh_token')
            ->where(function ($query): void {
                $query->whereNull('token_expires_at')
                    ->orWhere('token_expires_at', '<=', now()->addMinutes(10));
            })
            ->get();

        $refreshed = 0;
        $failed = 0;

        foreach ($shops as $shop) {
            try {
                $refresher->refreshIfExpiring($shop, 600);
                $refreshed++;
            } catch (\Throwable $e) {
                $this->error("[{$shop->domain}] {$e->getMessage()}");
                $failed++;
            }
        }

        $this->info("Refreshed {$refreshed} token(s), {$failed} failure(s).");

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
