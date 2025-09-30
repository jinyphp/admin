<?php

namespace Jiny\Admin\Http\Controllers\Admin\AdminUserLogs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Admin\Models\AdminUserLog;

/**
 * AdminUserLogs Delete Controller
 *
 * 로그 삭제 처리
 */
class AdminUserLogsDelete extends Controller
{
    /**
     * Remove the specified resource from storage.
     */
    public function __invoke(Request $request, $id)
    {
        try {
            $log = AdminUserLog::findOrFail($id);
            $log->delete();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Log entry deleted successfully.',
                ]);
            }

            return redirect()->route('admin.system.user.logs')
                ->with('success', 'Log entry deleted successfully.');

        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting log entry: '.$e->getMessage(),
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'Error deleting log entry: '.$e->getMessage());
        }
    }
}
