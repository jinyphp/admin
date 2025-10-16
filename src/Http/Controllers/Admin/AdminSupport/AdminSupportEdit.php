<?php

namespace Jiny\Admin\Http\Controllers\Admin\AdminSupport;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Jiny\Site\Models\SiteSupport;
use Jiny\Admin\Services\JsonConfigService;
use App\Models\User;

/**
 * 지원 요청 수정/처리 컨트롤러
 */
class AdminSupportEdit extends Controller
{
    private $jsonData;

    public function __construct()
    {
        $this->middleware('admin');

        $jsonConfigService = new JsonConfigService;
        $this->jsonData = $jsonConfigService->loadFromControllerPath(__DIR__);
    }

    public function __invoke(Request $request, $id)
    {
        $support = SiteSupport::with(['user', 'assignedTo'])->findOrFail($id);

        if ($request->isMethod('GET')) {
            return $this->showEditForm($support);
        }

        return $this->handleUpdate($request, $support);
    }

    protected function showEditForm($support)
    {
        // JSON 데이터 확인
        if (! $this->jsonData) {
            return response('Error: JSON configuration file not found or invalid.', 500);
        }

        // template.edit view 경로 확인
        if (! isset($this->jsonData['template']['edit'])) {
            return response('Error: 화면을 출력하기 위한 template.edit 설정이 필요합니다.', 500);
        }

        // 관리자 목록 (담당자 배정용)
        $admins = User::where('isAdmin', true)
            ->where('is_blocked', false)
            ->select('id', 'name', 'email')
            ->get();

        return view($this->jsonData['template']['edit'], [
            'support' => $support,
            'admins' => $admins,
            'jsonData' => $this->jsonData,
            'controllerClass' => static::class,
        ]);
    }

    protected function handleUpdate(Request $request, $support)
    {
        $validator = $this->validateRequest($request);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $this->updateSupport($request, $support);

            return redirect()->route('admin.support.show', $support->id)
                ->with('success', '지원 요청이 성공적으로 수정되었습니다.');

        } catch (\Exception $e) {
            return back()
                ->with('error', '수정 중 오류가 발생했습니다: ' . $e->getMessage())
                ->withInput();
        }
    }

    protected function validateRequest(Request $request)
    {
        $rules = [
            'status' => 'required|string|in:pending,in_progress,resolved,closed',
            'priority' => 'required|string|in:urgent,high,normal,low',
            'assigned_to' => 'nullable|exists:users,id',
            'admin_reply' => 'nullable|string',
        ];

        $messages = [
            'status.required' => '상태를 선택해 주세요.',
            'status.in' => '올바른 상태를 선택해 주세요.',
            'priority.required' => '우선순위를 선택해 주세요.',
            'priority.in' => '올바른 우선순위를 선택해 주세요.',
            'assigned_to.exists' => '존재하지 않는 담당자입니다.',
        ];

        return Validator::make($request->all(), $rules, $messages);
    }

    protected function updateSupport(Request $request, $support)
    {
        $oldStatus = $support->status;
        $newStatus = $request->status;

        // 기본 업데이트 데이터
        $updateData = [
            'status' => $newStatus,
            'priority' => $request->priority,
            'admin_reply' => $request->admin_reply,
        ];

        // 담당자 배정
        if ($request->has('assigned_to')) {
            $updateData['assigned_to'] = $request->assigned_to;
        }

        // 상태 변경에 따른 타임스탬프 설정
        if ($oldStatus !== $newStatus) {
            if ($newStatus === 'resolved') {
                $updateData['resolved_at'] = now();
            } elseif ($newStatus === 'closed') {
                $updateData['closed_at'] = now();
            }
        }

        $support->update($updateData);

        return $support;
    }
}