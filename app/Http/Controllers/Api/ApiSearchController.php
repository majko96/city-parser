<?php

namespace App\Http\Controllers\Api;

use App\Models\CityDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ApiSearchController extends Controller
{

    /**
     * Show the form for creating a new resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $data = CityDetail::query()
            ->select('id', 'name')
            ->where('name', 'LIKE', '%' . $request->get('query') . '%')
            ->get();

        return response()->json($data);
    }
}
