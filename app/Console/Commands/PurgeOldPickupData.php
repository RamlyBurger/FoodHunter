<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PickupDataProtectionService;

/**
 * Purge Old Pickup Data Command
 * 
 * [132] Purges old pickup data that is no longer required
 * Should be scheduled to run daily
 */
class PurgeOldPickupData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pickup:purge-old-data 
                            {--days=30 : Number of days to keep data}
                            {--dry-run : Show what would be purged without actually purging}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Purge old pickup data and cache to protect sensitive information';

    private PickupDataProtectionService $dataProtection;

    /**
     * Create a new command instance.
     */
    public function __construct(PickupDataProtectionService $dataProtection)
    {
        parent::__construct();
        $this->dataProtection = $dataProtection;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting pickup data purge...');
        
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->warn('DRY RUN MODE - No data will be actually purged');
        }
        
        // Purge old completed pickup data
        $this->info('Purging old completed pickup data...');
        
        if (!$dryRun) {
            $purgedCount = $this->dataProtection->purgeOldPickupData();
            $this->info("Purged {$purgedCount} old pickup records");
        } else {
            $this->info('Would purge old pickup records (dry run)');
        }
        
        // Purge temporary queue data
        $this->info('Purging temporary queue data...');
        
        if (!$dryRun) {
            $this->dataProtection->purgeTemporaryQueueData();
            $this->info('Temporary queue data purged');
        } else {
            $this->info('Would purge temporary queue data (dry run)');
        }
        
        // Show cache statistics
        $stats = $this->dataProtection->getCacheStatistics();
        $this->table(
            ['Setting', 'Value'],
            [
                ['Active Pickup TTL', $stats['active_pickup_ttl'] . ' seconds'],
                ['Completed Pickup TTL', $stats['completed_pickup_ttl'] . ' seconds'],
                ['Purge Threshold', $stats['purge_threshold_days'] . ' days'],
                ['Cache Driver', $stats['cache_driver']],
            ]
        );
        
        $this->info('✅ Pickup data purge completed successfully');
        
        return 0;
    }
}
