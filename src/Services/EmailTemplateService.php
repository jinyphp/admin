<?php

namespace Jiny\Admin\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * 이메일 템플릿 서비스
 * 
 * 이메일 템플릿을 관리하고 렌더링합니다.
 * 
 * @package Jiny\Admin
 * @since 1.0.0
 */
class EmailTemplateService
{
    /**
     * 캐시 키 접두사
     */
    const CACHE_PREFIX = 'email_template:';
    
    /**
     * 캐시 유효 시간 (초)
     */
    const CACHE_TTL = 3600; // 1시간

    /**
     * 템플릿 가져오기 (slug로)
     * 
     * @param string $slug
     * @return object|null
     */
    public function getTemplate(string $slug)
    {
        return Cache::remember(
            self::CACHE_PREFIX . $slug,
            self::CACHE_TTL,
            function () use ($slug) {
                return DB::table('admin_email_templates')
                    ->where('slug', $slug)
                    ->where('is_active', true)
                    ->first();
            }
        );
    }

    /**
     * 템플릿 가져오기 (ID로)
     * 
     * @param int $id
     * @return object|null
     */
    public function getTemplateById(int $id)
    {
        return Cache::remember(
            self::CACHE_PREFIX . 'id:' . $id,
            self::CACHE_TTL,
            function () use ($id) {
                return DB::table('admin_email_templates')
                    ->where('id', $id)
                    ->where('is_active', true)
                    ->first();
            }
        );
    }

    /**
     * 템플릿 렌더링
     * 
     * @param object $template
     * @param array $variables
     * @return array
     */
    public function render($template, array $variables = []): array
    {
        if (!$template) {
            throw new Exception('Template not found');
        }

        // 기본 변수 추가
        $variables = array_merge($this->getDefaultVariables(), $variables);

        // 제목 렌더링
        $subject = $this->renderString($template->subject ?? '', $variables);
        
        // 본문 렌더링
        $body = $this->renderString($template->body ?? '', $variables);

        // 레이아웃 적용
        if ($template->layout) {
            $layout = $this->getLayout($template->layout);
            if ($layout) {
                $body = $this->applyLayout($layout, $body, $variables);
            }
        }

        return [
            'subject' => $subject,
            'body' => $body,
            'type' => $template->type ?? 'html'
        ];
    }

    /**
     * 문자열 렌더링 (변수 치환)
     * 
     * @param string $string
     * @param array $variables
     * @return string
     */
    protected function renderString(string $string, array $variables): string
    {
        // {{variable}} 형식의 변수를 치환
        foreach ($variables as $key => $value) {
            if (is_scalar($value)) {
                $string = str_replace('{{' . $key . '}}', $value, $string);
                $string = str_replace('{{ ' . $key . ' }}', $value, $string);
            }
        }

        // 조건문 처리 {{#if variable}} ... {{/if}}
        $string = $this->processConditions($string, $variables);

        // 반복문 처리 {{#each items}} ... {{/each}}
        $string = $this->processLoops($string, $variables);

        return $string;
    }

    /**
     * 조건문 처리
     * 
     * @param string $string
     * @param array $variables
     * @return string
     */
    protected function processConditions(string $string, array $variables): string
    {
        $pattern = '/\{\{#if\s+(.+?)\}\}(.*?)\{\{\/if\}\}/s';
        
        $string = preg_replace_callback($pattern, function ($matches) use ($variables) {
            $condition = $matches[1];
            $content = $matches[2];
            
            // 변수 값 확인
            if (isset($variables[$condition]) && $variables[$condition]) {
                return $content;
            }
            
            return '';
        }, $string);

        return $string;
    }

    /**
     * 반복문 처리
     * 
     * @param string $string
     * @param array $variables
     * @return string
     */
    protected function processLoops(string $string, array $variables): string
    {
        $pattern = '/\{\{#each\s+(.+?)\}\}(.*?)\{\{\/each\}\}/s';
        
        $string = preg_replace_callback($pattern, function ($matches) use ($variables) {
            $arrayKey = $matches[1];
            $content = $matches[2];
            
            if (!isset($variables[$arrayKey]) || !is_array($variables[$arrayKey])) {
                return '';
            }
            
            $result = '';
            foreach ($variables[$arrayKey] as $item) {
                $itemContent = $content;
                if (is_array($item) || is_object($item)) {
                    foreach ((array)$item as $key => $value) {
                        if (is_scalar($value)) {
                            $itemContent = str_replace('{{' . $key . '}}', $value, $itemContent);
                        }
                    }
                } else {
                    $itemContent = str_replace('{{item}}', $item, $itemContent);
                }
                $result .= $itemContent;
            }
            
            return $result;
        }, $string);

        return $string;
    }

    /**
     * 기본 변수 가져오기
     * 
     * @return array
     */
    protected function getDefaultVariables(): array
    {
        return [
            'app_name' => config('app.name'),
            'app_url' => config('app.url'),
            'current_year' => date('Y'),
            'current_date' => now()->format('Y-m-d'),
            'current_time' => now()->format('H:i:s'),
            'current_datetime' => now()->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * 레이아웃 가져오기
     * 
     * @param string $layout
     * @return object|null
     */
    protected function getLayout(string $layout)
    {
        return Cache::remember(
            self::CACHE_PREFIX . 'layout:' . $layout,
            self::CACHE_TTL,
            function () use ($layout) {
                return DB::table('admin_email_layouts')
                    ->where('slug', $layout)
                    ->where('is_active', true)
                    ->first();
            }
        );
    }

    /**
     * 레이아웃 적용
     * 
     * @param object $layout
     * @param string $content
     * @param array $variables
     * @return string
     */
    protected function applyLayout($layout, string $content, array $variables): string
    {
        $layoutContent = $layout->content ?? '{{content}}';
        
        // 콘텐츠 삽입
        $layoutContent = str_replace('{{content}}', $content, $layoutContent);
        $layoutContent = str_replace('{{ content }}', $content, $layoutContent);
        
        // 레이아웃 변수 렌더링
        return $this->renderString($layoutContent, $variables);
    }

    /**
     * 템플릿 생성
     * 
     * @param array $data
     * @return int
     */
    public function createTemplate(array $data): int
    {
        $id = DB::table('admin_email_templates')->insertGetId([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'description' => $data['description'] ?? null,
            'subject' => $data['subject'],
            'body' => $data['body'],
            'type' => $data['type'] ?? 'html',
            'layout' => $data['layout'] ?? null,
            'variables' => isset($data['variables']) ? json_encode($data['variables']) : null,
            'is_active' => $data['is_active'] ?? true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 캐시 클리어
        $this->clearCache($data['slug']);

        return $id;
    }

    /**
     * 템플릿 업데이트
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateTemplate(int $id, array $data): bool
    {
        $template = DB::table('admin_email_templates')->where('id', $id)->first();
        
        if (!$template) {
            return false;
        }

        $updateData = [
            'updated_at' => now(),
        ];

        if (isset($data['name'])) $updateData['name'] = $data['name'];
        if (isset($data['slug'])) $updateData['slug'] = $data['slug'];
        if (isset($data['description'])) $updateData['description'] = $data['description'];
        if (isset($data['subject'])) $updateData['subject'] = $data['subject'];
        if (isset($data['body'])) $updateData['body'] = $data['body'];
        if (isset($data['type'])) $updateData['type'] = $data['type'];
        if (isset($data['layout'])) $updateData['layout'] = $data['layout'];
        if (isset($data['variables'])) $updateData['variables'] = json_encode($data['variables']);
        if (isset($data['is_active'])) $updateData['is_active'] = $data['is_active'];

        $result = DB::table('admin_email_templates')
            ->where('id', $id)
            ->update($updateData);

        // 캐시 클리어
        $this->clearCache($template->slug);
        if (isset($data['slug']) && $data['slug'] !== $template->slug) {
            $this->clearCache($data['slug']);
        }

        return $result > 0;
    }

    /**
     * 템플릿 삭제
     * 
     * @param int $id
     * @return bool
     */
    public function deleteTemplate(int $id): bool
    {
        $template = DB::table('admin_email_templates')->where('id', $id)->first();
        
        if (!$template) {
            return false;
        }

        $result = DB::table('admin_email_templates')->where('id', $id)->delete();

        // 캐시 클리어
        $this->clearCache($template->slug);

        return $result > 0;
    }

    /**
     * 캐시 클리어
     * 
     * @param string|null $slug
     * @return void
     */
    public function clearCache(string $slug = null): void
    {
        if ($slug) {
            Cache::forget(self::CACHE_PREFIX . $slug);
        } else {
            // 모든 템플릿 캐시 클리어
            Cache::flush();
        }
    }

    /**
     * 템플릿 테스트 발송
     * 
     * @param int $templateId
     * @param string $toEmail
     * @param array $variables
     * @return bool
     */
    public function testTemplate(int $templateId, string $toEmail, array $variables = []): bool
    {
        try {
            $template = $this->getTemplateById($templateId);
            
            if (!$template) {
                throw new Exception('Template not found');
            }

            $rendered = $this->render($template, $variables);

            // 테스트 이메일 발송
            \Mail::raw($rendered['body'], function ($message) use ($toEmail, $rendered) {
                $message->to($toEmail)
                    ->subject('[TEST] ' . $rendered['subject']);
            });

            Log::info('Test email sent', [
                'template_id' => $templateId,
                'to' => $toEmail,
            ]);

            return true;

        } catch (Exception $e) {
            Log::error('Failed to send test email', [
                'template_id' => $templateId,
                'to' => $toEmail,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}