<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Place;

class MapController extends Controller
{
    /**
     * Display the map
     *
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        return view('map.map')->with(['places' => Place::where('is_approved', true)->get()]);
    }
}
