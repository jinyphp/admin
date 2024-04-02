<?php

namespace Jiny\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;


class AdminSetup extends Controller
{
    public function __construct()
    {
    }


    public function index(Request $request)
    {
        $dbInfo = [];
        $dbInfo['host'] = env('DB_HOST');
        $dbInfo['database'] = env('DB_DATABASE');
        $dbInfo['username'] = env('DB_USERNAME');
        $dbInfo['password'] = env('DB_PASSWORD');



        //phpinfo();

        // Create a new PDO instance
        // $pdo = new \PDO("mysql:host=".$dbInfo['host'].";dbname=".$dbInfo['database'],
        // $dbInfo['username'],
        // $dbInfo['password']);

        // Set PDO attributes to throw exceptions for errors
        //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


        return view("jiny-admin::setup.index");
    }



}
