<x-flex>
    <div class="flex-fill">
        <h2>화면 단위 설정</h2>
        <p>컨트롤러에 적용된 화면을 변경할 수 있습니다. 입력값은 라라벨의 blade 경로 입니다.</p>
    </div>
    <div class="flex-fill">
        <div class="mb-3">
            <label class="form-label">Layout</label>
            {!! xInputText()
                ->setWire('model.defer',"forms.view.layout")
                ->setWidth("standard")
            !!}
        </div>


        <div class="mb-3">
            <label class="form-label">
                Main
            </label>
            {!! xInputText()
                ->setWire('model.defer',"forms.view.main")
                ->setWidth("standard")
            !!}
        </div>


    </div>
    <div class="flex-fill">

        <div class="mb-3">
            <label class="form-label">
                Table
            </label>
            {!! xInputText()
                ->setWire('model.defer',"forms.view.table")
                ->setWidth("standard")
            !!}
        </div>

        <div class="mb-3">
            <label class="form-label">
                Filter
            </label>
            {!! xInputText()
                ->setWire('model.defer',"forms.view.filter")
                ->setWidth("standard")
            !!}
        </div>

        <div class="mb-3">
            <label class="form-label">
                List
            </label>
            {!! xInputText()
                ->setWire('model.defer',"forms.view.list")
                ->setWidth("standard")
            !!}
        </div>

    </div>
</x-flex-between>



