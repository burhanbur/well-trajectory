<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BuildHoldDropController extends Controller
{
    public function index(Request $request)
    {   
        $depth = [];

        $mdChartValue = [];
        $tvdChartValue = [];

        $eob_md = 0;
        $eob_vd = 0;
        $eob_displacement = 0;

        $sod_md = 0;
        $sod_vd = 0;
        $sod_displacement = 0;

        $eod_md = 0;
        $eod_vd = 0;
        $eod_displacement = 0;

        $kop = $request->input('kop'); // kick of point
        $bur = $request->input('bur'); // build up rate
        $dor = $request->input('dor'); // drop off rate

        $target = $request->input('target'); // target end of drop
        $n = $request->input('n'); // northing
        $e = $request->input('e'); // easting

        if ($kop && $bur && $dor && $target && $n && $e) {
            $pi = pi();

            $horizontal_displacement = pow((pow($n, 2) + pow($e, 2)), 0.5);
            $radius_curvature_bur = (180 * 100) / ($bur * $pi);
            $radius_curvature_dor = (180 * 100) / ($dor * $pi);

            $sudut_B = 0;
            $line_OF = 0;
            $line_OG = 0;
            $sudut_FOG = 0;
            $max_inclination = 0;

            $eod_vd = (double) $target;

            if ($radius_curvature_bur + $radius_curvature_dor <= $horizontal_displacement) {
                $line_x = $horizontal_displacement - ($radius_curvature_bur + $radius_curvature_dor);
                $sudut_B = rad2deg(tan($line_x / ($target - $kop)));
                $line_OF = ($target - $kop) / cos(deg2rad($sudut_B));
                $line_OG = pow(pow($line_OF, 2) - pow(($radius_curvature_bur + $radius_curvature_dor), 2), 0.5);
                $sudut_FOG = rad2deg(asin(($radius_curvature_bur + $radius_curvature_dor) / $line_OF));
                $max_inclination = $sudut_FOG + $sudut_B;
                
                // eob
                $eob_md = $kop + (($max_inclination * 100) / $bur);
                $eob_vd = $kop + ($radius_curvature_bur * sin(deg2rad($max_inclination)));
                $eob_displacement = $radius_curvature_bur * (1 - cos(deg2rad($max_inclination)));

                // sod
                $sod_displacement = $horizontal_displacement - ($radius_curvature_dor * (1 - cos(deg2rad($max_inclination))));
                $sod_md = $eob_md + $line_OG;
                $sod_vd = $eob_vd + ($line_OG * cos(deg2rad($max_inclination)));

                // eod
                $eod_md = $sod_md + (($max_inclination * 100) / $dor);
                $eod_displacement = $sod_displacement + ($radius_curvature_dor * (1 - cos(deg2rad($max_inclination))));
            } else {
                $line_x = rad2deg(
                    (
                        atan(
                            ($target - $kop) / ($radius_curvature_bur + $radius_curvature_dor) - $horizontal_displacement
                        )
                    ) - (
                        acos(
                            (
                                $radius_curvature_bur + $radius_curvature_dor) / ($target - $kop) * sin(
                                atan(
                                    ($radius_curvature_bur + $radius_curvature_dor) / (($target - $kop) - $horizontal_displacement)
                                )
                            )
                        )
                    )
                );

                // eob
                $eob_md = $kop + (($line_x * 100) / $bur);;
                $eob_vd = $kop + ($radius_curvature_bur * sin(deg2rad($line_x)));
                $eob_displacement = $radius_curvature_bur * (1 - cos(deg2rad($line_x)));

                // sod
                $sod_displacement = $horizontal_displacement - ($radius_curvature_dor * (1 - cos(deg2rad($line_x))));
                $sod_md = $eob_md + (($sod_displacement - $eob_displacement) / (sin(deg2rad($line_x))));
                $sod_vd = $target - ($radius_curvature_dor * (1 - sin(deg2rad($line_x))));

                // eod
                $eod_md = $sod_md + (($pi / 180) * $radius_curvature_dor * $line_x);
                $eod_displacement = $radius_curvature_dor * (1 - cos(deg2rad($line_x))) + $sod_displacement;
            }

            $inc = 1;
            $inclination = 0;

            // vertical
            for ($i=0; $i <= $kop; $i+=100) {
                if ($i == $kop) {
                    $status = 'KOP';
                } else {
                    $status = 'Vertical';
                }

                $total_departure = 0;

                $depth[$inc]['md'] = $i;
                $depth[$inc]['inclination'] = $inclination;
                $depth[$inc]['tvd'] = $i;
                $depth[$inc]['total_departure'] = $total_departure;
                $depth[$inc]['status'] = $status;

                $inc++;
            }

            $tvdKOP = $i-100;

            $mdChartValue[] = $total_departure;
            $tvdChartValue[] = $i-100;

            // build
            for ($i = $i; $i <= $eob_md; $i+=100) {
                $status = 'Build';

                // find inclination
                if ($i > $kop && $eob_md > $i) {
                    $inclination = $bur + $inclination;
                }

                // find tvd
                $tvd = $radius_curvature_bur * sin(deg2rad($inclination)) + $tvdKOP;

                // find total_departure
                $total_departure = $radius_curvature_bur * (1 - cos(deg2rad($inclination)));

                $depth[$inc]['md'] = $i;
                $depth[$inc]['inclination'] = $inclination;
                $depth[$inc]['tvd'] = $tvd;
                $depth[$inc]['total_departure'] = $total_departure;
                $depth[$inc]['status'] = $status;

                $mdChartValue[] = round($total_departure, 2);
                $tvdChartValue[] = $tvd;

                $inc++;
            }

            // end of build
            $status = 'End of Build';
            $inclinationEOB = ($eob_md - ($i - 100)) * ($bur / 100) + $inclination;
            $tvdEOB = ($radius_curvature_bur * sin(deg2rad($inclinationEOB))) + $tvdKOP;
            $total_departureEOB = $radius_curvature_bur * (1 - cos(deg2rad($inclinationEOB)));

            $depth[$inc+1]['md'] = $eob_md;
            $depth[$inc+1]['inclination'] = $inclinationEOB;
            $depth[$inc+1]['tvd'] = $tvdEOB;
            $depth[$inc+1]['total_departure'] = $total_departureEOB;
            $depth[$inc+1]['status'] = $status;

            $mdChartValue[] = round($total_departureEOB, 2);
            $tvdChartValue[] = $tvdEOB;

            $inc++;

            // hold
            for ($i = $i; $i <= $sod_md; $i+=100) {
                $first = $i;
                $status = 'Hold';

                // find tvd
                if ($i == $first) {
                    $tvd = (cos(deg2rad($inclinationEOB)) * ($i - $eob_md)) + $tvdEOB;
                } else {
                    $tvd = (cos(deg2rad($inclinationEOB)) * ($i - ($i - 100))) + $tvd;
                }

                // find total_departure
                $total_departure = $total_departureEOB + (sin(deg2rad($inclinationEOB)) * ($i - $eob_md));

                $depth[$inc+1]['md'] = $i;
                $depth[$inc+1]['inclination'] = $inclinationEOB;
                $depth[$inc+1]['tvd'] = $tvd;
                $depth[$inc+1]['total_departure'] = $total_departure;
                $depth[$inc+1]['status'] = $status;

                $mdChartValue[] = round($total_departure, 2);
                $tvdChartValue[] = $tvd;

                $inc++;
            }

            // start of drop
            // $status = 'Start of Drop';
            
            // $inclinationEOB = ($eob_md - ($i - 100)) * ($bur / 100) + $inclination;
            // $tvdEOB = ($radius_curvature_bur * sin(deg2rad($inclinationEOB))) + $tvdKOP;
            // $total_departureEOB = $radius_curvature_bur * (1 - cos(deg2rad($inclinationEOB)));

            // $depth[$inc+1]['md'] = $eob_md;
            // $depth[$inc+1]['inclination'] = $inclinationEOB;
            // $depth[$inc+1]['tvd'] = $tvdEOB;
            // $depth[$inc+1]['total_departure'] = $total_departureEOB;
            // $depth[$inc+1]['status'] = $status;

            // $mdChartValue[] = round($total_departureEOB, 2);
            // $tvdChartValue[] = $tvdEOB;

            // $inc++;

        }

        // echo "<pre>";
        // var_dump($eod_md);
        // var_dump($eod_vd);
        // var_dump($eod_displacement);
        // die();

        return view('build-hold-drop', get_defined_vars());
    }
}