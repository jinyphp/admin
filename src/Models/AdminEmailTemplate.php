<?php

namespace Jiny\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdminEmailTemplate extends Model
{
    use HasFactory;

    protected $table = 'admin_email_templates';

    protected $fillable = [
        'name',
        'slug',
        'subject',
        'body',
        'variables',
        'type',
        'category',
        'is_active',
        'priority',
        'attachments',
        'from_name',
        'from_email',
        'reply_to',
        'cc',
        'bcc',
        'description',
        'metadata'
    ];

    protected $casts = [
        'variables' => 'array',
        'attachments' => 'array',
        'cc' => 'array',
        'bcc' => 'array',
        'metadata' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * 이메일 로그와의 관계
     */
    public function logs()
    {
        return $this->hasMany(AdminEmailLog::class, 'template_id');
    }

    /**
     * 활성 템플릿만 조회
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * 슬러그로 템플릿 찾기
     */
    public static function findBySlug($slug)
    {
        return static::where('slug', $slug)->first();
    }

    /**
     * 카테고리별 템플릿 조회
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * 변수 치환
     */
    public function render(array $data = [])
    {
        $subject = $this->subject;
        $body = $this->body;

        foreach ($data as $key => $value) {
            if (is_scalar($value)) {
                $subject = str_replace('{{' . $key . '}}', $value, $subject);
                $body = str_replace('{{' . $key . '}}', $value, $body);
            }
        }

        return [
            'subject' => $subject,
            'body' => $body,
            'type' => $this->type
        ];
    }
}