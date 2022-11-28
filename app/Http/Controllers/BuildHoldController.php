<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Exports\BuildHoldExport;

use Excel;

class BuildHoldController extends Controller
{
    public $errorMessage;

    public function __construct()
    {
        $this->errorMessage = 'Woops, looks like something went wrong.';
    }

    public function index(Request $request)
    {
        $depth = [];

        $mdChartValue = [];
        $tvdChartValue = [];

        $eob_md = 0;
        $eob_vd = 0;
        $eob_displacement = 0;

        $target_md = 0;
        $target_displacement = 0;

        $bur = $request->input('bur'); // build up rate
        $kop = $request->input('kop'); // kick of point

        $target = $request->input('target'); // target
        $n = $request->input('n'); // northing
        $e = $request->input('e'); // easting

        if ($bur || $kop || $target || $n || $e) {
            $pi = pi();

            try {
                // radius of curvature
                $r = ((180 * 100) / ($bur * $pi));
                
                // horizontal displacement
                $d2 = pow((pow($n, 2) + pow($e, 2)), 0.5);

                // line DC & DO
                if ($d2 > $r) {
                    $lineDC = $d2 - $r;
                    $lineDO = (double) $target - $kop;
                } else {
                    $lineDC = $r - $d2;
                    $lineDO = (double) $target - $kop;
                }

                $sudutDOC = (rad2deg(atan($lineDC / $lineDO)));
                $lineOC = ($lineDO/(cos(deg2rad($sudutDOC))));
                $sudutBOC = (rad2deg(acos($r / $lineOC)));

                if ($d2 > $r) {
                    $sudutBOD = ($sudutBOC - $sudutDOC);
                } else {
                    $sudutBOD = ($sudutBOC + $sudutDOC);
                }
            
                if ($d2 > $r) {
                    $maximum_angle_of_well = (90 - $sudutBOD);
                } else {
                    $maximum_angle_of_well = (90 - $sudutBOD);
                }
            
                if ($d2 > $r) {
                    $lineBC = (sqrt(($lineOC * $lineOC) - ($r * $r)));
                } else {
                    $lineBC = (sqrt(($lineOC * $lineOC) - ($r * $r)));
                }
            
                if ($d2 > $r) {
                    $lineEC = ($lineBC * (sin(deg2rad($maximum_angle_of_well))));
                } else {
                    $lineEC = ($lineBC * (sin(deg2rad($maximum_angle_of_well))));
                }

                $eob_md = ($kop + (($maximum_angle_of_well * 100) / $bur));

                $eob_vd = ($kop + ($r * sin(deg2rad($maximum_angle_of_well))));
                $eob_displacement = ($r * (1 - cos(deg2rad($maximum_angle_of_well))));

                $target_md = ($eob_md + $lineBC);
                $target_displacement = ($lineEC + $eob_displacement);

                $inc = 1;
                $inclanation = 0;

                // vertical
                for ($i=0; $i <= $kop; $i+=100) {
                    if ($i == $kop) {
                        $status = 'KOP';

                        // inclination condition
                        $inclCondition = $i <= $kop || $eob_md < $i;
                    } else {
                        $status = 'Vertical';

                        // inclination condition
                        $inclCondition = $i < $kop || $eob_md < $i;
                    }

                    // find inclination
                    if ($inclCondition) {

                    } else {
                        $inclanation = 2 + $inclanation;
                    }

                    $total_departure = 0;

                    $depth[$inc]['md'] = $i;
                    $depth[$inc]['inclination'] = $inclanation;
                    $depth[$inc]['tvd'] = $i;
                    $depth[$inc]['total_departure'] = $total_departure;
                    $depth[$inc]['status'] = $status;


                    // $mdChartValue[] = $total_departure;
                    // $tvdChartValue[] = $i;

                    $inc++;
                }

                $mdChartValue[] = $total_departure;
                $tvdChartValue[] = $i-100;

                // build
                for ($i = $i; $i <= $eob_md; $i+=100) {
                    $status = 'Build';

                    // inclination condition
                    if ($i == $kop) {
                        $inclCondition = $i <= $kop || $eob_md < $i;
                    } else {
                        $inclCondition = $i < $kop || $eob_md < $i;
                    }

                    // find inclination
                    if ($inclCondition) {

                    } else {
                        $inclanation = 2 + $inclanation;
                    }

                    // find tvd
                    $tvd = $kop + ($r * sin(deg2rad($inclanation))); // kenapa di inclanation 52 rumusnya berubah?

                    // find total_departure
                    $total_departure = $r * (1 - cos(deg2rad($inclanation))); // kenapa di inclanation 52 rumusnya berubah?

                    $depth[$inc]['md'] = $i;
                    $depth[$inc]['inclination'] = $inclanation;
                    $depth[$inc]['tvd'] = $tvd;
                    $depth[$inc]['total_departure'] = $total_departure;
                    $depth[$inc]['status'] = $status;

                    $mdChartValue[] = round($total_departure, 2);
                    $tvdChartValue[] = $tvd;

                    $inc++;
                }

                // end of build
                $status = 'End of Build';
                $inclanation = $inclanation + (($eob_md - ($i - 100)) * ((float) $bur / 100));
                $tvdEOB = $kop + ($r * sin(deg2rad($inclanation)));
                $total_departureEOB = $r * (1 - cos(deg2rad($inclanation)));

                $depth[$inc+1]['md'] = $eob_md;
                $depth[$inc+1]['inclination'] = $inclanation;
                $depth[$inc+1]['tvd'] = $tvdEOB;
                $depth[$inc+1]['total_departure'] = $total_departureEOB;
                $depth[$inc+1]['status'] = $status;

                $mdChartValue[] = round($total_departureEOB, 2);
                $tvdChartValue[] = $tvdEOB;

                $inc++;

                // hold
                for ($i = $i; $i <= $target_md; $i+=100) {
                    $status = 'Hold';

                    // find tvd
                    $tvd = (cos(deg2rad($inclanation))) * ($i - $eob_md) + $tvdEOB;

                    // find total_departure
                    $total_departure = $total_departureEOB + (sin(deg2rad($inclanation))) * ($i - $eob_md);

                    $depth[$inc+1]['md'] = $i;
                    $depth[$inc+1]['inclination'] = $inclanation;
                    $depth[$inc+1]['tvd'] = $tvd;
                    $depth[$inc+1]['total_departure'] = $total_departure;
                    $depth[$inc+1]['status'] = $status;

                    $mdChartValue[] = round($total_departure, 2);
                    $tvdChartValue[] = $tvd;

                    $inc++;
                }

                // target
                $status = 'Target';
                $tvd = (cos(deg2rad($inclanation))) * ($target_md - $eob_md) + $tvdEOB;
                $total_departure = $total_departureEOB + (sin(deg2rad($inclanation))) * ($target_md - $eob_md);

                $depth[$inc+1]['md'] = $target_md;
                $depth[$inc+1]['inclination'] = $inclanation;
                $depth[$inc+1]['tvd'] = $tvd;
                $depth[$inc+1]['total_departure'] = $total_departure;
                $depth[$inc+1]['status'] = $status;

                $mdChartValue[] = round($total_departure, 2);
                $tvdChartValue[] = $tvd;

                // 3d chart
                $plotlyChart = [];

                for ($i=0; $i < count((array) $mdChartValue); $i++) {
                    $plotlyChart[] = [
                        'x' => (string) $mdChartValue[$i],
                        'y' => (string) $tvdChartValue[$i],
                        'z' => '0',
                        'color' => '1'
                    ];
                }
            } catch (\Exception $ex) {
                return redirect()->back()->with('error', $this->errorMessage);
            } catch (\Throwable $err) {
                return redirect()->route('build.hold', 
                    [
                        'bur' => 0, 
                        'kop' => 0, 
                        'target' => 0, 
                        'n' => 0, 
                        'e' => 0
                    ])->with('error', 'Woops, looks like something went wrong.');
            }
        } else {
            $depth[0]['md'] = 0;
            $depth[0]['inclination'] = 0;
            $depth[0]['tvd'] = 0;
            $depth[0]['total_departure'] = 0;
            $depth[0]['status'] = '-';
        }

        return view('build-hold',[
            'chart' => $plotlyChart,
            'request' => $request,
            'depth' => $depth,
            'eob_md' => $eob_md,
            'eob_vd' => $eob_vd,
            'eob_displacement' => $eob_displacement,
            'target_md' => $target_md,
            'target_displacement' => $target_displacement,
            'mdChartValue' => $mdChartValue,
            'tvdChartValue' => $tvdChartValue,
            'bur' => $bur,
            'kop' => $kop,
            'target' => $target,
            'n' => $n,
            'e' => $e,
        ]);
    
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

            return Excel::download(new BuildHoldExport($params), 'build-hold-result.xlsx');   
        } catch (\Exception $ex) {
            return redirect()->back()->with('error', $this->errorMessage);
        } catch (\Throwable $err) {
            return redirect()->route('build.hold')->with('error', $this->errorMessage);
        }
    }
}
