<?php

namespace Jiny\Admin\Console\Commands;

use Illuminate\Console\Command;
use jiny\admin\App\Services\IpTrackingService;

class IpUnblock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:ip-unblock {ip : IP address to unblock}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Unblock a specific IP address';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $ip = $this->argument('ip');
        
        // IP 주소 유효성 검증
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            $this->error("Invalid IP address: {$ip}");
            return Command::FAILURE;
        }
        
        $this->info("Unblocking IP: {$ip}...");
        
        $ipService = app(IpTrackingService::class);
        $ipService->unblockIp($ip);
        
        $this->info("✅ IP {$ip} has been unblocked successfully.");
        
        return Command::SUCCESS;
    }
}