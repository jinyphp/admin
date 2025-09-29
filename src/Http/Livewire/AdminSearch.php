<?php

namespace Jiny\Admin\Http\Livewire;

use Livewire\Component;

class AdminSearch extends Component
{
    public $jsonData;

    public $search = '';

    public $filter = [];

    public $sortBy = 'created_at';

    public $perPage = 10;

    public $filters = [];

    public function mount($jsonData = null)
    {
        $this->jsonData = $jsonData;

        // 페이지네이션 설정 초기화
        $this->initializePagination();

        // 검색 가능한 필드들을 필터로 초기화
        // index 안에 있는 searchable 확인, 없으면 최상위 searchable 확인
        $searchableFields = [];

        // searchable이 boolean이면 columns에서 searchable 필드 추출
        if (isset($jsonData['index']['searchable']) && $jsonData['index']['searchable'] === true) {
            // columns에서 searchable이 true인 필드 찾기
            if (isset($jsonData['index']['columns'])) {
                foreach ($jsonData['index']['columns'] as $column) {
                    if (isset($column['searchable']) && $column['searchable'] === true) {
                        $searchableFields[] = $column['field'];
                    }
                }
            }
        } elseif (isset($jsonData['index']['searchable']) && is_array($jsonData['index']['searchable'])) {
            // searchable이 배열로 직접 제공된 경우
            $searchableFields = $jsonData['index']['searchable'];
        } elseif (isset($jsonData['searchable'])) {
            // 이전 버전 호환성
            if (is_array($jsonData['searchable'])) {
                $searchableFields = $jsonData['searchable'];
            } elseif ($jsonData['searchable'] === true && isset($jsonData['columns'])) {
                // 최상위 레벨에서도 columns 확인
                foreach ($jsonData['columns'] as $column) {
                    if (isset($column['searchable']) && $column['searchable'] === true) {
                        $searchableFields[] = $column['field'];
                    }
                }
            }
        }

        if (!empty($searchableFields)) {
            foreach ($searchableFields as $field) {
                $this->filters['filter_'.$field] = '';
            }
        }
    }

    /**
     * 페이지네이션 설정 초기화
     * JSON 데이터에서 페이지네이션 설정을 읽어 초기화합니다.
     * 
     * @return void
     */
    private function initializePagination()
    {
        // index.pagination.perPage 확인
        if (isset($this->jsonData['index']['pagination']['perPage'])) {
            $this->perPage = $this->jsonData['index']['pagination']['perPage'];
        }
        // 기본값 10 유지
        else {
            $this->perPage = 10;
        }
    }

    public function updatedSearch($value)
    {
        // 검색어 변경 시 테이블로 이벤트 전달
        $this->dispatch('search-updated', search: $value);
    }

    public function updatedFilter($value, $key)
    {
        // 필터 변경 시 테이블로 이벤트 전달
        $this->dispatch('filter-updated', filter: $this->filter);
    }

    public function updatedSortBy($value)
    {
        // 정렬 변경 시 테이블로 이벤트 전달
        $this->dispatch('sort-updated', sortBy: $value);
    }

    public function updatedPerPage($value)
    {
        // 페이지당 개수 변경 시 테이블로 이벤트 전달
        $this->dispatch('perPage-updated', perPage: $value);
    }

    public function search()
    {
        // 검색 이벤트 발생 - 필터 조건 전달
        $this->dispatch('search-filters', filters: $this->filters);
    }

    public function resetFilters()
    {
        // 필터 값만 초기화 (구조는 유지)
        foreach ($this->filters as $key => $value) {
            $this->filters[$key] = '';
        }

        // 초기화 이벤트 발생
        $this->dispatch('search-reset');
    }

    public function resetSearch()
    {
        $this->search = '';
        $this->filter = [];
        $this->sortBy = 'created_at';

        // 초기화 이벤트 발생
        $this->dispatch('search-reset');
        $this->dispatch('search-updated', search: '');
        $this->dispatch('filter-updated', filter: []);
    }

    public function render()
    {
        $viewPath = $this->jsonData['index']['searchLayoutPath'] ?? 'jiny-admin::template.livewire.admin-search';

        return view($viewPath);
    }
}
