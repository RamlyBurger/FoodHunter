<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CartDataProtectionService;

/**
 * Purge Old Cart Data Command
 * 
 * [Security Requirement #132] Automated purging of old cart data
 * that is no longer required.
 * 
 * This command should be scheduled to run daily to:
 * - Remove abandoned cart items older than threshold
 * - Clear temporary menu search cache
 * - Purge expired voucher cache
 * - Maintain database hygiene
 * 
 * Usage:
 *   php artisan cart:purge-old-data [--days=90] [--dry-run]
 * 
 * Schedule in app/Console/Kernel.php:
 *   $schedule->command('cart:purge-old-data')->daily();
 */
class PurgeOldCartData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cart:purge-old-data 
                            {--days=90 : Number of days after which cart data should be purged}
                            {--dry-run : Show what would be purged without actually purging}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '[Security #132] Purge old cart data and temporary cache to protect sensitive information';

    private CartDataProtectionService $dataProtection;

    /**
     * Create a new command instance.
     */
    public function __construct(CartDataProtectionService $dataProtection)
    {
        parent::__construct();
        $this->dataProtection = $dataProtection;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $dryRun = $this->option('dry-run');

        $this->info("Cart Data Purge Operation");
        $this->info("=========================");
        $this->line("");

        if ($dryRun) {
            $this->warn("DRY RUN MODE - No data will be deleted");
            $this->line("");
        }

        // Purge old cart items
        $this->info("Purging cart items older than {$days} days...");
        
        if (!$dryRun) {
            $result = $this->dataProtection->purgeOldCartData($days);
            
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Items Deleted', $result['items_deleted']],
                    ['Threshold Days', $result['threshold_days']],
                    ['Cutoff Date', $result['cutoff_date']],
                ]
            );

            if ($result['items_deleted'] > 0) {
                $this->info("✓ Successfully purged {$result['items_deleted']} old cart items");
            } else {
                $this->comment("✓ No old cart items found to purge");
            }
        } else {
            $this->comment("Would purge cart items updated before " . now()->subDays($days)->toDateString());
        }

        $this->line("");

        // Purge temporary search cache
        $this->info("Purging temporary search cache...");
        
        if (!$dryRun) {
            $this->dataProtection->purgeSearchCache();
            $this->info("✓ Search cache purged successfully");
        } else {
            $this->comment("Would purge all menu search cache entries");
        }

        $this->line("");

        // Summary
        $this->info("=========================");
        $this->info("Purge operation completed!");
        
        if ($dryRun) {
            $this->line("");
            $this->warn("Run without --dry-run to actually purge data");
        }

        return Command::SUCCESS;
    }
}
