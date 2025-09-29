<?php

namespace Jiny\Admin\Http\Controllers\Admin\AdminPasswordLogs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * AdminPasswordLogs Unblock Controller
 *
 * IP 차단 해제 처리
 */
class AdminPasswordLogsUnblock extends Controller
{
    /**
     * Unblock an IP address
     */
    public function __invoke(Request $request, $id)
    {
        try {
            // 로그 조회
            $log = DB::table('password_logs')->where('id', $id)->first();

            if (! $log) {
                return response()->json([
                    'success' => false,
                    'message' => '로그를 찾을 수 없습니다.',
                ], 404);
            }

            if ($log->status !== 'blocked') {
                return response()->json([
                    'success' => false,
                    'message' => '이미 차단 해제되었거나 차단되지 않은 IP입니다.',
                ], 400);
            }

            // 차단 해제 처리
            DB::table('password_logs')
                ->where('id', $id)
                ->update([
                    'status' => 'resolved',
                    'resolved_at' => now(),
                    'resolved_by' => auth()->id(),
                    'updated_at' => now(),
                ]);

            // 동일한 IP의 다른 차단 기록도 해제
            DB::table('password_logs')
                ->where('ip_address', $log->ip_address)
                ->where('status', 'blocked')
                ->where('id', '!=', $id)
                ->update([
                    'status' => 'resolved',
                    'resolved_at' => now(),
                    'resolved_by' => auth()->id(),
                    'updated_at' => now(),
                ]);

            return response()->json([
                'success' => true,
                'message' => 'IP 차단이 해제되었습니다.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '차단 해제 중 오류가 발생했습니다: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk unblock IP addresses
     */
    public function bulk(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer',
        ]);

        try {
            $ids = $request->input('ids');

            // 차단된 로그만 조회
            $logs = DB::table('password_logs')
                ->whereIn('id', $ids)
                ->where('status', 'blocked')
                ->get();

            if ($logs->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => '차단 해제할 항목이 없습니다.',
                ], 400);
            }

            // IP 주소 목록 추출
            $ipAddresses = $logs->pluck('ip_address')->unique();

            // 차단 해제 처리
            $count = DB::table('password_logs')
                ->whereIn('ip_address', $ipAddresses)
                ->where('status', 'blocked')
                ->update([
                    'status' => 'resolved',
                    'resolved_at' => now(),
                    'resolved_by' => auth()->id(),
                    'updated_at' => now(),
                ]);

            return response()->json([
                'success' => true,
                'message' => $count.'개의 IP 차단이 해제되었습니다.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '차단 해제 중 오류가 발생했습니다: '.$e->getMessage(),
            ], 500);
        }
    }
}
