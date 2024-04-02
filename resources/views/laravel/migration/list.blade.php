<x-datatable>
    <thead>
        <tr>
            <th width='20'>
                <input type='checkbox' class="form-check-input" wire:model="selectedall">
            </th>
            <th width='100'>순번</th>
            <th width='200'>일자</th>
            <th width='100'>타입</th>
            <th width='200'>테이블</th>
            <th>Migration</th>
            <th width='100'>batch</th>
            <th width='100'>rollback</th>
        </tr>
    </thead>
    <tbody>
    @if(!empty($rows))
        @foreach ($rows as $item)

        {{-- row-selected --}}
        @if(in_array($item->id, $selected))
        <tr class="row-selected">
        @else
        <tr>
        @endif

            <td width='20'>
                <input type='checkbox' name='ids' value="{{$item->id}}"
                class="form-check-input"
                wire:model="selected">
            </td>

            <td width='100'>{{$item->id}}</td>
            <td width='200'>{{$_rows[ $item->id ]['date']}}</td>
            <td width='100'>{{$_rows[ $item->id ]['type']}}</td>
            <td width='200'>{{$_rows[ $item->id ]['table']}}</td>
            <td>{{$item->migration}}</td>
            <td width='100'>{{$item->batch}}</td>
            <td width='100'>Rollback</td>
        </tr>
        @endforeach
    @else
        목록이 없습니다.
    @endif
    </tbody>
</x-datatable>
