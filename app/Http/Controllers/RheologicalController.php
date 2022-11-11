<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RheologicalController extends Controller
{
    public function index(Request $request)
    {
        $nParam = ['600', '300', '200', '100', '6', '3'];

        $n = [];
        $dialReading = $request->dial_reading_fann_data;

        if ($dialReading) {
            $n = [
                '600', '300', '200', '100', '6', '3'
            ];
        }

        $xChartValues = [];
        $yChartValues = [];

        foreach ($n as $ns) {
            $xChartValues[] = (double) $ns * 1.70333;
        }

        return view('rheological', get_defined_vars());
    }
}