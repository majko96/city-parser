<?php

namespace App\Http\Controllers;

use App\Models\CityDetail;

class CityController extends Controller
{
    public function show($id)
    {
        $cityDetail = CityDetail::findOrFail($id);

        return view('city.detail', ['cityDetail' => $cityDetail]);
    }

    public function search()
    {
        return view('city.search');
    }
}
