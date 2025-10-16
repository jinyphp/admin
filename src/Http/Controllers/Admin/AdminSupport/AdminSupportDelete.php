<?php

namespace Jiny\Admin\Http\Controllers\Admin\AdminSupport;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Site\Models\SiteSupport;

/**
 * 지원 요청 삭제 컨트롤러
 */
class AdminSupportDelete extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function __invoke(Request $request, $id)
    {
        $support = SiteSupport::findOrFail($id);

        try {
            $support->delete();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => '지원 요청이 성공적으로 삭제되었습니다.'
                ]);
            }

            return redirect()->route('admin.support.index')
                ->with('success', '지원 요청이 성공적으로 삭제되었습니다.');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => '삭제 중 오류가 발생했습니다: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', '삭제 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }
}