{{-- Ipwhitelist 상세 보기 --}}
<div class="bg-white shadow overflow-hidden sm:rounded-lg">
    {{-- 헤더 섹션 --}}
    <div class="px-4 py-5 sm:px-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900">
            Ipwhitelist Information
        </h3>
        <p class="mt-1 max-w-2xl text-sm text-gray-500">
            Details and information about this ipwhitelist.
        </p>
    </div>
    
    {{-- 상세 정보 --}}
    <div class="border-t border-gray-200">
        <dl>
            {{-- ID --}}
            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">
                    ID
                </dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    {{ $data->id ?? '-' }}
                </dd>
            </div>
            
            {{-- Name --}}
            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">
                    Name
                </dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    {{ $data->name ?? '-' }}
                </dd>
            </div>
            
            {{-- Description --}}
            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">
                    Description
                </dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    {{ $data->description ?? '-' }}
                </dd>
            </div>
            
            {{-- Status --}}
            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">
                    Status
                </dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    @if(isset($data->status) && $data->status)
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                            Active
                        </span>
                    @else
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                            Inactive
                        </span>
                    @endif
                </dd>
            </div>
            
            {{-- Created At --}}
            @if(isset($data->created_at))
            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">
                    Created
                </dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    {{ \Carbon\Carbon::parse($data->created_at)->format('Y-m-d H:i:s') }}
                </dd>
            </div>
            @endif
            
            {{-- Updated At --}}
            @if(isset($data->updated_at))
            <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">
                    Last Updated
                </dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    {{ \Carbon\Carbon::parse($data->updated_at)->format('Y-m-d H:i:s') }}
                </dd>
            </div>
            @endif
            
            {{-- 추가 필드들을 여기에 추가하세요 --}}
            {{-- 
            <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                <dt class="text-sm font-medium text-gray-500">
                    Field Label
                </dt>
                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                    {{ $data->field_name ?? '-' }}
                </dd>
            </div>
            --}}
        </dl>
    </div>
    
    {{-- 액션 버튼들 --}}
    <div class="bg-gray-50 px-4 py-4 sm:px-6">
        <div class="flex justify-end space-x-3">
            <a href="{{ route('admin.system.ipwhitelists') }}"
               class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Back to List
            </a>
            <a href="{{ route('admin.system.ipwhitelists.edit', $data->id) }}"
               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Edit
            </a>
        </div>
    </div>
</div>