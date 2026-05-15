<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MemberCheckoutController extends Controller
{
    use ApiClient;

    public function index(Request $request)
    {
        $response  = $this->apiGet('/equipment/checkouts/my', [
            'status'   => $request->status,
            'page'     => $request->page ?? 1,
            'per_page' => 10,
        ]);

        $checkouts = $this->makePaginator($response, 10, $request->url(), $request->query());

        return view('member.checkouts.index', compact('checkouts'));
    }
}
