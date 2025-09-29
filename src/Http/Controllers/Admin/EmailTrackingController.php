<?php

namespace Jiny\Admin\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Admin\Models\AdminEmailLog;
use Illuminate\Support\Facades\Storage;

class EmailTrackingController extends Controller
{
    /**
     * 이메일 열람 트래킹 픽셀
     */
    public function pixel(Request $request, $token)
    {
        $emailLog = AdminEmailLog::where('tracking_token', $token)->first();
        
        if ($emailLog) {
            // 열람 정보 업데이트
            $openDetails = $emailLog->open_details ?? [];
            $openDetails[] = [
                'opened_at' => now()->toDateTimeString(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'referer' => $request->header('referer'),
            ];
            
            $emailLog->update([
                'opened_at' => $emailLog->opened_at ?? now(),
                'first_opened_at' => $emailLog->first_opened_at ?? now(),
                'open_count' => $emailLog->open_count + 1,
                'open_details' => $openDetails,
            ]);
            
            \Log::info('Email opened', [
                'email_id' => $emailLog->id,
                'token' => $token,
                'ip' => $request->ip(),
            ]);
        }
        
        // 1x1 투명 GIF 픽셀 반환
        $pixel = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
        
        return response($pixel, 200)
            ->header('Content-Type', 'image/gif')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }
    
    /**
     * 링크 클릭 트래킹
     */
    public function link(Request $request, $token, $linkId)
    {
        $emailLog = AdminEmailLog::where('tracking_token', $token)->first();
        
        if ($emailLog) {
            // 링크 클릭 정보 업데이트
            $linkClicks = $emailLog->link_clicks ?? [];
            $linkClicks[] = [
                'link_id' => $linkId,
                'clicked_at' => now()->toDateTimeString(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->get('url'),
            ];
            
            $emailLog->update([
                'link_clicks' => $linkClicks,
                'total_clicks' => $emailLog->total_clicks + 1,
            ]);
            
            \Log::info('Email link clicked', [
                'email_id' => $emailLog->id,
                'token' => $token,
                'link_id' => $linkId,
                'url' => $request->get('url'),
            ]);
        }
        
        // 원래 URL로 리다이렉트
        $url = $request->get('url', '/');
        return redirect($url);
    }
    
    /**
     * 이메일 통계 조회
     */
    public function stats($emailId)
    {
        $emailLog = AdminEmailLog::find($emailId);
        
        if (!$emailLog) {
            return response()->json(['error' => 'Email not found'], 404);
        }
        
        return response()->json([
            'email_id' => $emailLog->id,
            'subject' => $emailLog->subject,
            'sent_at' => $emailLog->sent_at,
            'status' => $emailLog->status,
            'tracking' => [
                'opened' => $emailLog->opened_at ? true : false,
                'first_opened_at' => $emailLog->first_opened_at,
                'last_opened_at' => $emailLog->opened_at,
                'open_count' => $emailLog->open_count,
                'total_clicks' => $emailLog->total_clicks,
                'unique_clicks' => $emailLog->link_clicks ? count(array_unique(array_column($emailLog->link_clicks, 'link_id'))) : 0,
            ],
            'details' => [
                'opens' => $emailLog->open_details ?? [],
                'clicks' => $emailLog->link_clicks ?? [],
            ],
        ]);
    }
}