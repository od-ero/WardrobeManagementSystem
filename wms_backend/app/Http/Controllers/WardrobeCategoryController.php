<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WardrobeCategory;

class WardrobeCategoryController extends Controller
{
    //
    public function index(){
    $data = WardrobeCategory::select('id as value', 'name as label')
    ->get();

    return response()->json($data);}
}
