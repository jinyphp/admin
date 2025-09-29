<?php

namespace Jiny\admin\App\Services;

class JsonConfigService
{
    /**
     * 컨트롤러 디렉토리 경로로부터 JSON 설정 파일 로드
     *
     * @param  string  $controllerDir  컨트롤러의 __DIR__ 경로
     * @return array|null JSON 데이터를 배열로 반환, 파일이 없거나 오류시 null 반환
     */
    public function loadFromControllerPath(string $controllerDir): ?array
    {
        try {
            // 디렉토리 경로에서 마지막 경로명 추출
            $pathParts = explode(DIRECTORY_SEPARATOR, $controllerDir);
            $lastPathName = end($pathParts);

            // JSON 파일 경로 생성
            $jsonFilePath = $controllerDir.DIRECTORY_SEPARATOR.$lastPathName.'.json';

            // 파일 존재 여부 확인
            if (! file_exists($jsonFilePath)) {
                return null;
            }

            // JSON 파일 읽기
            $jsonContent = file_get_contents($jsonFilePath);

            // JSON 디코딩
            $jsonData = json_decode($jsonContent, true);

            // JSON 오류 확인
            if (json_last_error() !== JSON_ERROR_NONE) {
                return null;
            }

            return $jsonData;

        } catch (\Exception $e) {
            // 오류 발생시 null 반환
            return null;
        }
    }

    /**
     * 특정 경로의 JSON 파일 직접 로드
     *
     * @param  string  $jsonFilePath  JSON 파일의 전체 경로
     * @return array|null JSON 데이터를 배열로 반환, 파일이 없거나 오류시 null 반환
     */
    public function loadFromPath(string $jsonFilePath): ?array
    {
        try {
            // 파일 존재 여부 확인
            if (! file_exists($jsonFilePath)) {
                return null;
            }

            // JSON 파일 읽기
            $jsonContent = file_get_contents($jsonFilePath);

            // JSON 디코딩
            $jsonData = json_decode($jsonContent, true);

            // JSON 오류 확인
            if (json_last_error() !== JSON_ERROR_NONE) {
                return null;
            }

            return $jsonData;

        } catch (\Exception $e) {
            // 오류 발생시 null 반환
            return null;
        }
    }

    /**
     * JSON 설정을 파일에 저장
     *
     * @param  string  $jsonFilePath  JSON 파일의 전체 경로
     * @param  array  $data  저장할 데이터
     * @return bool 성공 여부
     */
    public function save(string $jsonFilePath, array $data): bool
    {
        try {
            $jsonContent = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

            if ($jsonContent === false) {
                return false;
            }

            return file_put_contents($jsonFilePath, $jsonContent) !== false;

        } catch (\Exception $e) {
            return false;
        }
    }
}
