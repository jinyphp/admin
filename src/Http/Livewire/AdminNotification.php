<?php

namespace Jiny\Admin\Http\Livewire;

use Livewire\Component;

class AdminNotification extends Component
{
    public $notifications = [];
    
    protected $listeners = [
        'notifySuccess' => 'addSuccessNotification',
        'notifyError' => 'addErrorNotification',
        'notifyWarning' => 'addWarningNotification',
        'notifyInfo' => 'addInfoNotification',
    ];
    
    public function mount()
    {
        // 세션에서 notification 플래시 메시지 확인
        if (session()->has('notification')) {
            $notification = session('notification');
            $this->addNotification(
                $notification['message'] ?? '',
                $notification['type'] ?? 'info',
                $notification['title'] ?? ''
            );
        }
    }
    
    public function addSuccessNotification($message, $title = '성공')
    {
        $this->addNotification($message, 'success', $title);
    }
    
    public function addErrorNotification($message, $title = '오류')
    {
        $this->addNotification($message, 'error', $title);
    }
    
    public function addWarningNotification($message, $title = '경고')
    {
        $this->addNotification($message, 'warning', $title);
    }
    
    public function addInfoNotification($message, $title = '알림')
    {
        $this->addNotification($message, 'info', $title);
    }
    
    public function addNotification($message, $type = 'info', $title = '')
    {
        $id = uniqid();
        
        $this->notifications[] = [
            'id' => $id,
            'type' => $type,
            'title' => $title ?: $this->getDefaultTitle($type),
            'message' => $message,
        ];
        
        $this->dispatch('notification-added', ['id' => $id]);
    }
    
    public function dismissNotification($id)
    {
        $this->notifications = array_filter($this->notifications, function($notification) use ($id) {
            return $notification['id'] !== $id;
        });
        
        $this->notifications = array_values($this->notifications);
    }
    
    private function getDefaultTitle($type)
    {
        return match($type) {
            'success' => '성공',
            'error' => '오류',
            'warning' => '경고',
            'info' => '알림',
            default => '알림',
        };
    }
    
    public function render()
    {
        return view('jiny-admin::livewire.admin-notification');
    }
}