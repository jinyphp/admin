<x-wire-table>
    <x-wire-thead>
        {{-- 테이블 제목 --}}
        <th width='50'>Id</th>
        <th >
            이메일
        </th>
        <th>IP 주소</th>

        <th width='200'>등록일자</th>

    </x-wire-thead>
    <tbody>
        @if(!empty($rows))
            @foreach ($rows as $item)
            <x-wire-tbody-item :selected="$selected" :item="$item">
                {{-- 테이블 리스트 --}}
                <td width='50'>{{$item->id}}</td>
                <td >
                    {{$item->email}}
                </td>
                <td>
                    <x-click wire:click="edit({{$item->id}})">
                        {{$item->ip_address}}
                    </x-click>
                </td>

                <td width='200'>{{$item->created_at}}</td>

            </x-wire-tbody-item>
            @endforeach
        @endif
    </tbody>
</x-wire-table>
