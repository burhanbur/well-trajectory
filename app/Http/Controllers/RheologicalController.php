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
        $model = $request->model;

        if ($dialReading && !in_array(0, $dialReading)) {
            $n = [
                '600', '300', '200', '100', '6', '3'
            ];
        }

        // chart
        $xChartValues = [];
        $yChartValues = [];

        if ($model == 'semua') {
            $yChartValues['fann_data'] = [];
            $yChartValues['power_law'] = [];
            $yChartValues['herschel_buckley'] = [];
            $yChartValues['bingham_plastic'] = [];
            $yChartValues['newtonian_model'] = [];
        }

        for($i=0; $i < count((array) $n); $i++) {
            switch ($model) {
                case 'fann_data':
                    $yChartValues[] = 0.01065 * (double) @$dialReading[$i] * 0.0069444444443639;
                    
                    break;

                case 'power_law':
                    $cColumn = $n[$i] * 1.70333;
                    $dColumn = log10(((double) @$request->dial_reading_fann_data[0] * 1.70333) / ((double) @$request->dial_reading_fann_data[1] * 1.70333)) * 3.32192809;
                    $eColumn = ((510 * (double) @$request->dial_reading_fann_data[0]) / (pow((1.703 * $n[0]), $dColumn))) * 0.001;
                    $fColumn = $eColumn * (pow($cColumn, $dColumn));
                    $gColumn = $fColumn * 0.000145038;

                    $yChartValues[] = $gColumn;
                    break;

                case 'herschel_buckley':
                    $dColumnParam2 = (2 * (double) @$request->dial_reading_fann_data[5]) - (double) @$request->dial_reading_fann_data[4];
                    $dColumnParam = $dColumnParam2 * 0.47880258888889;
                    $eColumn = 3.32192809 * (log10(((double) @$request->dial_reading_fann_data[0] - $dColumnParam2) / ((double) @$request->dial_reading_fann_data[1] - $dColumnParam2)));
                    $fColumn = 500 * (((double) @$request->dial_reading_fann_data[1] - $dColumnParam2) / (pow(511, $eColumn))) * 0.001;

                    $cColumn = $n[$i] * 1.70333;
                    $gColumn = ($dColumnParam + ($fColumn * pow($cColumn, $eColumn)));
                    $hColumn = $gColumn * 0.000145038;

                    $yChartValues[] = $hColumn;
                    break;

                case 'bingham_plastic':
                    $dColumnParam = ((300 / ($n[0] - $n[1])) * ((double) @$request->dial_reading_fann_data[0] - (double) @$request->dial_reading_fann_data[1]) * 0.001);
                    $dColumnParam2 = ((300 / ($n[0] - $n[1])) * ((double) @$request->dial_reading_fann_data[0] - (double) @$request->dial_reading_fann_data[1]));
                    $eColumn = ((double) @$request->dial_reading_fann_data[1] - $dColumnParam2) * 0.47880258888889;

                    $cColumn = $n[$i] * 1.70333;
                    $fColumn = ($eColumn + ($dColumnParam * $cColumn));
                    $gColumn = $fColumn * 0.000145038;

                    $yChartValues[] = $gColumn;
                    break;

                case 'newtonian_model':
                    $cColumn = $n[$i] * 1.70333;
                    $dColumn = ((300 / $n[0]) * (double) @$dialReading[0]) * 0.001;
                    $eColumn = $dColumn * $cColumn;
                    $fColumn = $eColumn * 0.000145038;

                    $yChartValues[] = $fColumn;
                    break;

                case 'semua':
                    // fann data
                    $yChartValues['fann_data'][] = 0.01065 * (double) @$dialReading[$i] * 0.0069444444443639;

                    // power law
                    $cColumn = $n[$i] * 1.70333;
                    $dColumn = log10(((double) @$request->dial_reading_fann_data[0] * 1.70333) / ((double) @$request->dial_reading_fann_data[1] * 1.70333)) * 3.32192809;
                    $eColumn = ((510 * (double) @$request->dial_reading_fann_data[0]) / (pow((1.703 * $n[0]), $dColumn))) * 0.001;
                    $fColumn = $eColumn * (pow($cColumn, $dColumn));
                    $gColumn = $fColumn * 0.000145038;

                    $yChartValues['power_law'][] = $gColumn;

                    // herschel buckley
                    $dColumnParam2 = (2 * (double) @$request->dial_reading_fann_data[5]) - (double) @$request->dial_reading_fann_data[4];
                    $dColumnParam = $dColumnParam2 * 0.47880258888889;
                    $eColumn = 3.32192809 * (log10(((double) @$request->dial_reading_fann_data[0] - $dColumnParam2) / ((double) @$request->dial_reading_fann_data[1] - $dColumnParam2)));
                    $fColumn = 500 * (((double) @$request->dial_reading_fann_data[1] - $dColumnParam2) / (pow(511, $eColumn))) * 0.001;

                    $cColumn = $n[$i] * 1.70333;
                    $gColumn = ($dColumnParam + ($fColumn * pow($cColumn, $eColumn)));
                    $hColumn = $gColumn * 0.000145038;

                    $yChartValues['herschel_buckley'][] = $hColumn;

                    // bingham plastic
                    $dColumnParam = ((300 / ($n[0] - $n[1])) * ((double) @$request->dial_reading_fann_data[0] - (double) @$request->dial_reading_fann_data[1]) * 0.001);
                    $dColumnParam2 = ((300 / ($n[0] - $n[1])) * ((double) @$request->dial_reading_fann_data[0] - (double) @$request->dial_reading_fann_data[1]));
                    $eColumn = ((double) @$request->dial_reading_fann_data[1] - $dColumnParam2) * 0.47880258888889;

                    $cColumn = $n[$i] * 1.70333;
                    $fColumn = ($eColumn + ($dColumnParam * $cColumn));
                    $gColumn = $fColumn * 0.000145038;

                    $yChartValues['bingham_plastic'][] = $gColumn;

                    // newtonian model
                    $cColumn = $n[$i] * 1.70333;
                    $dColumn = ((300 / $n[0]) * (double) @$dialReading[0]) * 0.001;
                    $eColumn = $dColumn * $cColumn;
                    $fColumn = $eColumn * 0.000145038;

                    $yChartValues['newtonian_model'][] = $fColumn;
                    break;
                
                default:

                    break;
            }
        }

        foreach ($n as $ns) {
            $xChartValues[] = round((double) $ns * 1.70333, 3);
        }

        return view('rheological', get_defined_vars());
    }
}