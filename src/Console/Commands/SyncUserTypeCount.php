<?php

namespace Jiny\Admin\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Jiny\Admin\Models\User;

class SyncUserTypeCount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:sync-usertype-count';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '각 사용자 타입의 cnt 필드를 실제 사용자 수와 동기화';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('사용자 타입 카운트 동기화를 시작합니다...');

        // 모든 사용자 타입 가져오기
        $userTypes = DB::table('admin_user_types')->get();

        $this->info('총 ' . $userTypes->count() . '개의 사용자 타입을 발견했습니다.');
        $this->newLine();

        // 각 타입별로 실제 사용자 수 계산 및 업데이트
        foreach ($userTypes as $userType) {
            // 해당 타입의 실제 사용자 수 계산
            $actualCount = User::where('utype', $userType->code)->count();
            
            // 현재 저장된 카운트
            $currentCount = $userType->cnt;

            // 카운트가 다른 경우에만 업데이트
            if ($currentCount != $actualCount) {
                DB::table('admin_user_types')
                    ->where('code', $userType->code)
                    ->update(['cnt' => $actualCount]);

                $this->info("✓ {$userType->code} ({$userType->name}): {$currentCount} → {$actualCount} (업데이트됨)");
            } else {
                $this->line("- {$userType->code} ({$userType->name}): {$actualCount} (변경 없음)");
            }
        }

        $this->newLine();
        
        // 추가로 utype이 null이거나 존재하지 않는 타입을 가진 사용자 확인
        $orphanedUsers = User::whereNull('utype')
            ->orWhereNotIn('utype', $userTypes->pluck('code'))
            ->count();

        if ($orphanedUsers > 0) {
            $this->warn("⚠️  타입이 지정되지 않았거나 잘못된 타입을 가진 사용자가 {$orphanedUsers}명 있습니다.");
            
            // 상세 정보 표시
            $orphaned = User::whereNull('utype')
                ->orWhereNotIn('utype', $userTypes->pluck('code'))
                ->select('id', 'name', 'email', 'utype')
                ->get();

            if ($this->confirm('문제가 있는 사용자 목록을 보시겠습니까?')) {
                $this->table(
                    ['ID', '이름', '이메일', '타입'],
                    $orphaned->map(function ($user) {
                        return [
                            $user->id,
                            $user->name,
                            $user->email,
                            $user->utype ?: '(없음)'
                        ];
                    })
                );
            }
        } else {
            $this->info('✓ 모든 사용자가 올바른 타입을 가지고 있습니다.');
        }

        $this->newLine();
        $this->info('사용자 타입 카운트 동기화가 완료되었습니다.');

        return Command::SUCCESS;
    }
}