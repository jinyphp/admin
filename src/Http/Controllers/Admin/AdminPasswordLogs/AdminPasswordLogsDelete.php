<?php

namespace Jiny\Admin\Http\Controllers\Admin\AdminPasswordLogs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * AdminPasswordLogs Delete Controller
 *
 * 비밀번호 로그 삭제 처리
 */
class AdminPasswordLogsDelete extends Controller
{
    /**
     * Remove the specified resource from storage.
     */
    public function __invoke(Request $request, $id)
    {
        try {
            // 로그 존재 확인
            $log = DB::table('password_logs')->where('id', $id)->first();

            if (! $log) {
                return response()->json([
                    'success' => false,
                    'message' => '비밀번호 로그를 찾을 수 없습니다.',
                ], 404);
            }

            // 차단된 상태의 로그는 삭제 전 확인 필요
            if ($log->status === 'blocked') {
                if (! $request->has('confirm')) {
                    return response()->json([
                        'success' => false,
                        'message' => '차단된 IP의 로그를 삭제하시겠습니까? 차단이 자동으로 해제됩니다.',
                        'requireConfirm' => true,
                    ], 200);
                }
            }

            // 로그 삭제
            DB::table('password_logs')->where('id', $id)->delete();

            return response()->json([
                'success' => true,
                'message' => '비밀번호 로그가 삭제되었습니다.',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '로그 삭제 중 오류가 발생했습니다: '.$e->getMessage(),
            ], 500);
        }
    }
}
