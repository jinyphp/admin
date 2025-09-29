<?php

namespace Jiny\Admin\Http\Livewire\Admin\AdminTemplates\Settings;

use Livewire\Component;

class SettingsButton extends Component
{
    public function openSettings()
    {
        $this->dispatch('openTableSettings');
    }

    public function render()
    {
        return view('jiny-admin::template.settings.settings-button');
    }
}
