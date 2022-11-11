<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RheologicalController extends Controller
{
    public function index(Request $request)
    {
        $n = [
            '600', '300', '200', '100', '6', '3'
        ];

        // echo "<pre>";
        $dialReading = $request->dial_reading_fann_data;
        // var_dump($dialReading);
        // die();

        return view('rheological', get_defined_vars());
    }
}