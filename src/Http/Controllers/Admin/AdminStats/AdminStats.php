<?php

namespace Jiny\Admin\Http\Controllers\Admin\AdminStats;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Jiny\Admin\Models\AdminUserLog;
use Jiny\Admin\Models\AdminUserSession;
use Jiny\Admin\Services\JsonConfigService;
use Jiny\Admin\Models\User;

/**
 * AdminStats Main Controller
 *
 * 브라우저 및 기기 사용 통계를 표시하는 컨트롤러
 */
class AdminStats extends Controller
{
    private $jsonData;

    public function __construct()
    {
        // 서비스를 사용하여 JSON 파일 로드
        $jsonConfigService = new JsonConfigService;
        $this->jsonData = $jsonConfigService->loadFromControllerPath(__DIR__);
    }

    // private function loadJsonFromCurrentPath()
    // {
    //     try {
    //         $jsonFilePath = __DIR__ . DIRECTORY_SEPARATOR . 'AdminStats.json';

    //         if (!file_exists($jsonFilePath)) {
    //             return $this->getDefaultJsonData();
    //         }

    //         $jsonContent = file_get_contents($jsonFilePath);
    //         $jsonData = json_decode($jsonContent, true);

    //         if (json_last_error() !== JSON_ERROR_NONE) {
    //             return $this->getDefaultJsonData();
    //         }

    //         return $jsonData;

    //     } catch (\Exception $e) {
    //         return $this->getDefaultJsonData();
    //     }
    // }

    // private function getDefaultJsonData()
    // {
    //     return [
    //         'title' => 'User Statistics',
    //         'subtitle' => 'Browser and device usage analytics',
    //         'route' => [
    //             'name' => 'admin.user.stats'
    //         ],
    //         'template' => [
    //             'layout' => 'jiny-admin::layouts.admin',
    //             'index' => 'jiny-admin::admin.admin_stats.index'
    //         ]
    //     ];
    // }

    /**
     * Display statistics dashboard
     */
    public function __invoke(Request $request)
    {
        if (! $this->jsonData) {
            return response('Error: JSON 데이터를 로드할 수 없습니다.', 500);
        }

        // JSON 파일 경로 추가
        $jsonPath = __DIR__.DIRECTORY_SEPARATOR.'AdminStats.json';
        $settingsPath = $jsonPath;

        // currentRoute 설정
        $this->jsonData['currentRoute'] = 'admin.user.stats';

        // 통계 데이터 수집
        $statistics = $this->collectStatistics($request);

        return view($this->jsonData['template']['index'], [
            'jsonData' => $this->jsonData,
            'jsonPath' => $jsonPath,
            'settingsPath' => $settingsPath,
            'title' => $this->jsonData['title'] ?? '사용자 통계',
            'subtitle' => $this->jsonData['subtitle'] ?? '브라우저 및 기기 사용 분석',
            'statistics' => $statistics,
        ]);
    }

    /**
     * Collect all statistics data
     */
    private function collectStatistics(Request $request)
    {
        $dateRange = $this->getDateRange($request);

        return [
            'browser_usage' => $this->getBrowserUsage($dateRange),
            'browser_versions' => $this->getBrowserVersions($dateRange),
            'operating_systems' => $this->getOperatingSystems($dateRange),
            'device_types' => $this->getDeviceTypes($dateRange),
            'login_methods' => $this->getLoginMethods($dateRange),
            'session_status' => $this->getSessionStatus(),
            'peak_usage' => $this->getPeakUsageTimes($dateRange),
            'geographic' => $this->getGeographicDistribution($dateRange),
            'summary' => $this->getSummaryStats($dateRange),
            'trends' => $this->getTrends($dateRange),
        ];
    }

    /**
     * Get date range for filtering
     */
    private function getDateRange(Request $request)
    {
        $period = $request->get('period', '7days');

        switch ($period) {
            case '24hours':
                $start = Carbon::now()->subDay();
                break;
            case '7days':
                $start = Carbon::now()->subDays(7);
                break;
            case '30days':
                $start = Carbon::now()->subDays(30);
                break;
            case '90days':
                $start = Carbon::now()->subDays(90);
                break;
            default:
                $start = Carbon::now()->subDays(7);
        }

        return [
            'start' => $start,
            'end' => Carbon::now(),
        ];
    }

    /**
     * Get browser usage statistics
     */
    private function getBrowserUsage($dateRange)
    {
        $browsers = AdminUserSession::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->whereNotNull('browser')
            ->selectRaw('browser, COUNT(*) as count')
            ->groupBy('browser')
            ->orderByDesc('count')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->browser ?: 'Unknown',
                    'count' => $item->count,
                    'percentage' => 0, // Will be calculated
                ];
            });

        $total = $browsers->sum('count');

        return $browsers->map(function ($browser) use ($total) {
            $browser['percentage'] = $total > 0 ? round(($browser['count'] / $total) * 100, 1) : 0;

            return $browser;
        });
    }

    /**
     * Get browser version statistics
     */
    private function getBrowserVersions($dateRange)
    {
        return AdminUserSession::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->whereNotNull('browser')
            ->whereNotNull('browser_version')
            ->selectRaw('browser, browser_version, COUNT(*) as count')
            ->groupBy('browser', 'browser_version')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->map(function ($item) {
                return [
                    'browser' => $item->browser,
                    'version' => $item->browser_version,
                    'full_name' => $item->browser.' '.$item->browser_version,
                    'count' => $item->count,
                ];
            });
    }

    /**
     * Get operating system statistics
     */
    private function getOperatingSystems($dateRange)
    {
        $systems = AdminUserSession::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->whereNotNull('platform')
            ->selectRaw('platform, COUNT(*) as count')
            ->groupBy('platform')
            ->orderByDesc('count')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $this->formatPlatformName($item->platform),
                    'count' => $item->count,
                    'percentage' => 0,
                ];
            });

        $total = $systems->sum('count');

        return $systems->map(function ($system) use ($total) {
            $system['percentage'] = $total > 0 ? round(($system['count'] / $total) * 100, 1) : 0;

            return $system;
        });
    }

    /**
     * Get device type statistics
     */
    private function getDeviceTypes($dateRange)
    {
        $devices = AdminUserSession::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->whereNotNull('device')
            ->selectRaw('device, COUNT(*) as count')
            ->groupBy('device')
            ->orderByDesc('count')
            ->get()
            ->map(function ($item) {
                return [
                    'type' => ucfirst($item->device),
                    'count' => $item->count,
                    'percentage' => 0,
                    'icon' => $this->getDeviceIcon($item->device),
                ];
            });

        $total = $devices->sum('count');

        return $devices->map(function ($device) use ($total) {
            $device['percentage'] = $total > 0 ? round(($device['count'] / $total) * 100, 1) : 0;

            return $device;
        });
    }

    /**
     * Get login method statistics
     */
    private function getLoginMethods($dateRange)
    {
        $methods = AdminUserLog::whereBetween('logged_at', [$dateRange['start'], $dateRange['end']])
            ->where('action', 'login')
            ->selectRaw('two_factor_used, COUNT(*) as count')
            ->groupBy('two_factor_used')
            ->get();

        $normalLogin = $methods->where('two_factor_used', false)->first();
        $twoFactorLogin = $methods->where('two_factor_used', true)->first();

        $total = $methods->sum('count');

        return [
            [
                'method' => '일반 로그인',
                'count' => $normalLogin ? $normalLogin->count : 0,
                'percentage' => $total > 0 && $normalLogin ? round(($normalLogin->count / $total) * 100, 1) : 0,
                'color' => 'blue',
            ],
            [
                'method' => '2단계 인증 로그인',
                'count' => $twoFactorLogin ? $twoFactorLogin->count : 0,
                'percentage' => $total > 0 && $twoFactorLogin ? round(($twoFactorLogin->count / $total) * 100, 1) : 0,
                'color' => 'green',
            ],
        ];
    }

    /**
     * Get active vs inactive session statistics
     */
    private function getSessionStatus()
    {
        $active = AdminUserSession::where('is_active', true)->count();
        $inactive = AdminUserSession::where('is_active', false)->count();
        $total = $active + $inactive;

        return [
            'active' => [
                'count' => $active,
                'percentage' => $total > 0 ? round(($active / $total) * 100, 1) : 0,
            ],
            'inactive' => [
                'count' => $inactive,
                'percentage' => $total > 0 ? round(($inactive / $total) * 100, 1) : 0,
            ],
            'total' => $total,
        ];
    }

    /**
     * Get peak usage times
     */
    private function getPeakUsageTimes($dateRange)
    {
        // SQLite compatible query using strftime
        $hourlyData = AdminUserLog::whereBetween('logged_at', [$dateRange['start'], $dateRange['end']])
            ->where('action', 'login')
            ->selectRaw("strftime('%H', logged_at) as hour, COUNT(*) as count")
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        // Fill in missing hours with 0
        $hours = collect(range(0, 23))->map(function ($hour) use ($hourlyData) {
            $hourStr = str_pad($hour, 2, '0', STR_PAD_LEFT);
            $data = $hourlyData->firstWhere('hour', $hourStr);

            return [
                'hour' => $hourStr.':00',
                'count' => $data ? $data->count : 0,
            ];
        });

        return $hours;
    }

    /**
     * Get geographic distribution by IP
     */
    private function getGeographicDistribution($dateRange)
    {
        $ips = AdminUserSession::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->whereNotNull('ip_address')
            ->selectRaw('ip_address, COUNT(*) as count')
            ->groupBy('ip_address')
            ->orderByDesc('count')
            ->limit(20)
            ->get()
            ->map(function ($item) {
                return [
                    'ip' => $item->ip_address,
                    'count' => $item->count,
                    'location' => $this->getLocationFromIP($item->ip_address),
                ];
            });

        return $ips;
    }

    /**
     * Get summary statistics
     */
    private function getSummaryStats($dateRange)
    {
        return [
            'total_sessions' => AdminUserSession::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])->count(),
            'unique_users' => AdminUserSession::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])->distinct('user_id')->count('user_id'),
            'total_registered_users' => User::count(),
            'total_logins' => AdminUserLog::whereBetween('logged_at', [$dateRange['start'], $dateRange['end']])->where('action', 'login')->count(),
            'failed_logins' => AdminUserLog::whereBetween('logged_at', [$dateRange['start'], $dateRange['end']])->where('action', 'failed_login')->count(),
            'avg_session_duration' => $this->calculateAverageSessionDuration($dateRange),
            'most_active_day' => $this->getMostActiveDay($dateRange),
            'unique_browsers' => AdminUserSession::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])->distinct('browser')->count('browser'),
            'unique_devices' => AdminUserSession::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])->distinct('device')->count('device'),
        ];
    }

    /**
     * Get trend data
     */
    private function getTrends($dateRange)
    {
        $days = $dateRange['start']->diffInDays($dateRange['end']);

        if ($days <= 7) {
            // Daily trend - SQLite compatible
            $groupBy = "strftime('%Y-%m-%d', logged_at)";
            $format = 'M d';
        } elseif ($days <= 30) {
            // Weekly trend - SQLite compatible
            $groupBy = "strftime('%Y-%W', logged_at)";
            $format = 'W';
        } else {
            // Monthly trend - SQLite compatible
            $groupBy = "strftime('%Y-%m', logged_at)";
            $format = 'M';
        }

        $loginTrend = AdminUserLog::whereBetween('logged_at', [$dateRange['start'], $dateRange['end']])
            ->where('action', 'login')
            ->selectRaw($groupBy.' as period, COUNT(*) as count')
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        return [
            'logins' => $loginTrend,
        ];
    }

    /**
     * Helper: Format platform name
     */
    private function formatPlatformName($platform)
    {
        $platforms = [
            'windows' => 'Windows',
            'macos' => 'macOS',
            'linux' => 'Linux',
            'android' => 'Android',
            'ios' => 'iOS',
            'chrome_os' => 'Chrome OS',
        ];

        return $platforms[strtolower($platform)] ?? ucfirst($platform);
    }

    /**
     * Helper: Get device icon
     */
    private function getDeviceIcon($device)
    {
        $icons = [
            'desktop' => 'computer',
            'mobile' => 'smartphone',
            'tablet' => 'tablet',
            'tv' => 'tv',
            'watch' => 'watch',
        ];

        return $icons[strtolower($device)] ?? 'device_unknown';
    }

    /**
     * Helper: Get location from IP (simplified)
     */
    private function getLocationFromIP($ip)
    {
        // Check if it's a private IP
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return '내부 네트워크';
        }

        // For production, you would use a GeoIP service here
        // For now, return a placeholder
        return '외부';
    }

    /**
     * Helper: Calculate average session duration
     */
    private function calculateAverageSessionDuration($dateRange)
    {
        $sessions = AdminUserSession::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->whereNotNull('login_at')
            ->whereNotNull('last_activity_at')
            ->get();

        if ($sessions->isEmpty()) {
            return '0 min';
        }

        $totalMinutes = 0;
        foreach ($sessions as $session) {
            $duration = Carbon::parse($session->login_at)->diffInMinutes(Carbon::parse($session->last_activity_at));
            $totalMinutes += $duration;
        }

        $avgMinutes = round($totalMinutes / $sessions->count());

        if ($avgMinutes < 60) {
            return $avgMinutes.' min';
        } else {
            $hours = floor($avgMinutes / 60);
            $minutes = $avgMinutes % 60;

            return $hours.'h '.$minutes.'min';
        }
    }

    /**
     * Helper: Get most active day
     */
    private function getMostActiveDay($dateRange)
    {
        // SQLite compatible query using strftime
        $mostActive = AdminUserLog::whereBetween('logged_at', [$dateRange['start'], $dateRange['end']])
            ->where('action', 'login')
            ->selectRaw("strftime('%Y-%m-%d', logged_at) as date, COUNT(*) as count")
            ->groupBy('date')
            ->orderByDesc('count')
            ->first();

        if ($mostActive) {
            return [
                'date' => Carbon::parse($mostActive->date)->format('M d, Y'),
                'count' => $mostActive->count,
            ];
        }

        return [
            'date' => 'N/A',
            'count' => 0,
        ];
    }
}
