<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BuildHoldDropController extends Controller
{
    public function index(Request $request)
    {
        
        return view('build-hold-drop', get_defined_vars());
    }
}