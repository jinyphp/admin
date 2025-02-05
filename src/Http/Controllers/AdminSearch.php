<?php
namespace Jiny\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminSearch extends Controller
{
    public function search(Request $request)
    {
        // $query = $request->get('query');
        // $results = YourModel::where('name', 'LIKE', "%{$query}%")
        //                     ->orWhere('description', 'LIKE', "%{$query}%")
        //                     ->get();
        //
        // $results = [
        //     'status' => 'success',
        //     'message' => '검색 성공',
        //     'results' => [],
        // ];
        // return response()->json($results);
    }
}
