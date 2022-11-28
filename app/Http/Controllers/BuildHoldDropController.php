<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Exports\BuildHoldDropExport;

use Excel;

class BuildHoldDropController extends Controller
{
    public $errorMessage;

    public function __construct()
    {
        $this->errorMessage = 'Woops, looks like something went wrong.';
    }

    public function index(Request $request)
    {   
        $depth = [];
        $chart = [];

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

        $kop = $request->input('kop'); // kick off point
        $bur = $request->input('bur'); // build up rate
        $dor = $request->input('dor'); // drop off rate

        $target = $request->input('target'); // target end off drop
        $n = $request->input('n'); // northing
        $e = $request->input('e'); // easting

        if ($kop || $bur || $dor || $target || $n || $e) {
            $pi = pi();

            try {
                $horizontal_displacement = pow((pow($n, 2) + pow($e, 2)), 0.5);
                $radius_curvature_bur = (180 * 100) / ($bur * $pi);
                $radius_curvature_dor = (180 * 100) / ($dor * $pi);

                $sudut_B = 0;
                $line_OF = 0;
                $line_OG = 0;
                $sudut_FOG = 0;
                $max_inclination = 0;

                $eod_vd = (double) $target;

                if (($radius_curvature_bur + $radius_curvature_dor) < $horizontal_displacement) {
                    $line_x = $horizontal_displacement - $radius_curvature_bur - $radius_curvature_dor;
                } else {
                    $line_x = $radius_curvature_bur - $horizontal_displacement + $radius_curvature_dor;
                }

                $sudut_B = rad2deg(atan($line_x / ($target - $kop)));
                $line_OF = ($target - $kop) / cos(deg2rad($sudut_B));
                $line_OG = pow(pow($line_OF, 2) - pow(($radius_curvature_bur + $radius_curvature_dor), 2), 0.5);
                $sudut_FOG = rad2deg(asin(($radius_curvature_bur + $radius_curvature_dor) / $line_OF));

                if (($radius_curvature_bur + $radius_curvature_dor) < $horizontal_displacement) {
                    $max_inclination = $sudut_FOG + $sudut_B;
                } else {
                    $max_inclination = $sudut_FOG - $sudut_B;;
                }

                // eob
                $eob_md = $kop + $max_inclination / ($bur / 100);;
                $eob_vd = $kop + $radius_curvature_bur * sin(deg2rad($max_inclination));
                $eob_displacement = $radius_curvature_bur * (1 - cos(deg2rad($max_inclination)));

                // sod
                $sod_md = $eob_md + $line_OG;
                $sod_vd = $eob_vd + $line_OG * cos(deg2rad($max_inclination));
                $sod_displacement = $eob_displacement + $line_OG * sin(deg2rad($max_inclination));

                // eod
                $eod_md = $sod_md + $max_inclination / ($dor / 100);
                $eod_displacement = $horizontal_displacement;

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
                    // if ($i == $first) {
                        $tvd = (cos(deg2rad($inclinationEOB)) * ($i - $eob_md)) + $tvdEOB;
                    // } else {
                    //     $tvd = (cos(deg2rad($inclinationEOB)) * ($i - ($i - 100))) + $tvd;
                    // }

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
                $status = 'Start of Drop';

                $tvdSOD = $tvd = (cos(deg2rad($inclinationEOB)) * ($sod_md - ($i - 100))) + $tvd;
                $total_departureSOD = $horizontal_displacement - ($radius_curvature_dor * (1 - cos(deg2rad($max_inclination))));

                $depth[$inc+1]['md'] = $sod_md;
                $depth[$inc+1]['inclination'] = $inclinationEOB;
                $depth[$inc+1]['tvd'] = $tvdSOD;
                $depth[$inc+1]['total_departure'] = $total_departureSOD;
                $depth[$inc+1]['status'] = $status;

                $mdChartValue[] = round($total_departureSOD, 2);
                $tvdChartValue[] = $tvdSOD;

                $inc++;

                // drop
                for ($i = $i; $i <= $eod_md; $i+=100) {
                    $first = $i;
                    $status = 'Drop';

                    // if ($i == $first) {
                        $inclination = $inclinationEOB - (($i - $sod_md) * ($dor / 100));
                        $tvd = $sod_vd + ($radius_curvature_dor * (sin(deg2rad($max_inclination)) - sin(deg2rad($inclination))));
                    // } else {
                    //     $inclination = '';
                    //     $tvd = (cos(deg2rad($inclinationEOB)) * ($i - ($i - 100))) + $tvd;
                    // }

                    // find total_departure
                    $total_departure = $total_departureSOD + ($radius_curvature_dor * (cos(deg2rad($inclination)) - cos(deg2rad($max_inclination))));

                    $depth[$inc+1]['md'] = $i;
                    $depth[$inc+1]['inclination'] = $inclination;
                    $depth[$inc+1]['tvd'] = $tvd;
                    $depth[$inc+1]['total_departure'] = $total_departure;
                    $depth[$inc+1]['status'] = $status;

                    $mdChartValue[] = round($total_departure, 2);
                    $tvdChartValue[] = $tvd;

                    $inc++;
                }

                // target
                $status = 'Target';

                $inclination = $inclination - (($eod_md - ($i - 100)) * ($dor / 100));
                $tvd = $sod_vd + ($radius_curvature_dor * (sin(deg2rad($max_inclination)) - sin(deg2rad($inclination))));
                $total_departure = $total_departureSOD + ($radius_curvature_dor * (cos(deg2rad($inclination)) - cos(deg2rad($max_inclination))));

                $depth[$inc+1]['md'] = $eod_md;
                $depth[$inc+1]['inclination'] = abs($inclination);
                $depth[$inc+1]['tvd'] = $tvd;
                $depth[$inc+1]['total_departure'] = $total_departure;
                $depth[$inc+1]['status'] = $status;

                $mdChartValue[] = round($total_departure, 2);
                $tvdChartValue[] = $tvd;

                // 3d chart
                for ($i=0; $i < count((array) $mdChartValue); $i++) {
                    $chart[] = [
                        'x' => (string) $mdChartValue[$i],
                        'y' => (string) $tvdChartValue[$i],
                        'z' => '0',
                        'color' => '1'
                    ];
                }
            } catch (\Exception $ex) {
                return redirect()->back()->with('error', $this->errorMessage);
            } catch (\Throwable $err) {
                return redirect()->route('build.hold.drop', 
                    [
                        'bur' => 0, 
                        'kop' => 0, 
                        'dor' => 0, 
                        'target' => 0, 
                        'n' => 0, 
                        'e' => 0
                    ])->with('error', $this->errorMessage);
            }
        } else {
            $depth[0]['md'] = 0;
            $depth[0]['inclination'] = 0;
            $depth[0]['tvd'] = 0;
            $depth[0]['total_departure'] = 0;
            $depth[0]['status'] = '-';
        }

        return view('build-hold-drop', get_defined_vars());
    }

    public function downloadResult(Request $request)
    {
        $data = $request->all();

        $validator = Validator::make($data, [
            'depth' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return redirect()->back()->with('error', $validator->errors()->first());
        }

        try {
            $params = json_decode($data['depth']);

            return Excel::download(new BuildHoldDropExport($params), 'build-hold-drop-result.xlsx');   
        } catch (\Exception $ex) {
            return redirect()->back()->with('error', $this->errorMessage);
        } catch (\Throwable $err) {
            return redirect()->route('build.hold.drop')->with('error', $this->errorMessage);
        }
    }
}