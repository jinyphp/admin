<?php

namespace Jiny\Admin\Console\Commands;

use Illuminate\Console\Command;
use jiny\admin\App\Services\IpTrackingService;

class IpCleanup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:ip-cleanup {--days=30 : Days to keep records}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old IP tracking records';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');
        
        $this->info("Cleaning up IP records older than {$days} days...");
        
        $ipService = app(IpTrackingService::class);
        $deleted = $ipService->cleanup($days);
        
        $this->info("✅ Deleted {$deleted} old IP records.");
        
        return Command::SUCCESS;
    }
}