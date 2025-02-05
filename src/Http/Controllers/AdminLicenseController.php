<?php
namespace Jiny\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

use Jiny\WireTable\Http\Controllers\WireTablePopupForms;
use Jiny\License\Http\Controllers\LicenseController;
class AdminLicenseController extends LicenseController
{
    public function __construct()
    {
        parent::__construct();

        $this->setLayoutDefault("jiny-admin::table.layout");

        $this->viewFileTable = $this->packageName."::table_popup_forms.table";
    }

}
