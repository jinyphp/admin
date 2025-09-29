<?php

namespace Jiny\Admin\Console\Commands;

use Illuminate\Console\Command;
use jiny\admin\App\Services\IpTrackingService;

class IpStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:ip-stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display IP tracking statistics';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $ipService = app(IpTrackingService::class);
        $stats = $ipService->getStatistics();
        
        $this->info('=== IP Tracking Statistics ===');
        $this->newLine();
        
        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Blocked IPs', $stats['total_blocked_ips']],
                ['Total Whitelisted IPs', $stats['total_whitelisted_ips']],
                ['Recent Attempts (24h)', $stats['recent_attempts']],
                ['Currently Blocked', $stats['blocked_today']],
            ]
        );
        
        if (!empty($stats['top_attempted_ips'])) {
            $this->newLine();
            $this->info('Top Attempted IPs (Last 7 days):');
            
            $topIps = [];
            foreach ($stats['top_attempted_ips'] as $ip) {
                $topIps[] = [
                    $ip->ip_address,
                    $ip->attempts,
                    $ip->is_blocked ? 'Yes' : 'No',
                    $ip->last_attempt_at
                ];
            }
            
            $this->table(
                ['IP Address', 'Attempts', 'Blocked', 'Last Attempt'],
                $topIps
            );
        }
        
        return Command::SUCCESS;
    }
}