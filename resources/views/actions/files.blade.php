<div class="card">
    <div class="card-body">
        @includeIf("jiny-admin::actions.tree",[
            'color' => "100",
            'ref' => "/",
            'files' => $files
        ])
    </div>
</div>
