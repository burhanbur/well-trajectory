<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RheologicalController extends Controller
{
    public function index(Request $request)
    {
        return view('rheological', get_defined_vars());
    }
}