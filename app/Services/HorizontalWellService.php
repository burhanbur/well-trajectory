<?php 

namespace App\Services;

use Illuminate\Http\Request;

class HorizontalWellService
{
	public function calculate(Request $request)
	{
		$returnValue = [];

		$depth = [];
        $chart = [];

        $mdChartValue = [];
        $tvdChartValue = [];

        $kop = $request->input('kop'); // kick off point
        $target = $request->input('target'); // target
        $e = $request->input('e'); // east

        $horizontal_displacement = 0;
        $rb = 0;
        $l = 0;
        $md = 0;
        $bur = 0;
        $eob = 0;
        $n = 0;

        if ($kop || $target || $e) {
            $pi = pi();
            
            try {
                $horizontal_displacement = sqrt((pow(0, 2) + pow($e, 2)));
                $rb = $target - $kop;
                $l = (90/180) * $pi * $rb;
                $md = $kop + $l + ($horizontal_displacement - $rb);
                $bur = (180 / $pi) * (1 / $rb) * 100;
                $eob = $kop + $l;

                $inc = 1;
                $inclination = 0;
                $horizontal_departure = 0;

                for ($i=0; $i <= $md; $i+=100) {
                    $depth[$inc]['md'] = $i;

                    // inclination
                    if ($i > $kop && $i < $eob) {
                        $inclination = $bur + $inclination;
                    } else {
                        // kop
                        if ($i-100 == $kop) {
                            $inclination = $bur + $inclination;
                        } else {
                            if ($i == $eob || $i > $eob) {
                                $inclination = $inclination;
                            } else {
                                // vertical
                                $inclination = 0;
                            }
                        }
                    }

                    $depth[$inc]['inclination'] = $inclination;

                    // tvd
                    if ($inclination == 0) {
                        $tvd = $i;
                    } else {
                        $tvd = $kop + $rb * sin(deg2rad($inclination));
                    }

                    $depth[$inc]['tvd'] = $tvd;

                    // horizontal departure                    
                    if ($inclination == 0) {
                        $horizontal_departure = 0;
                    } else {
                        if ($i == $eob || $i > $eob) {
                            $horizontal_departure = $horizontal_departure + 100;
                        } else {
                            $horizontal_departure = $rb * (1 - cos(deg2rad($inclination)));
                        }
                    }

                    $depth[$inc]['horizontal_departure'] = $horizontal_departure;

                    // status
                    if ($i == $md) {
                        $status = 'Target';
                    } else {
                        if ($tvd == $kop) {
                            $status = 'KOP';
                        } else {
                            if ($i > $eob) {
                                $status = 'Hold';
                            } else {
                                if (abs($i - $eob) < 100) {
                                    $status = 'End of Build';
                                } else {
                                    if ($horizontal_departure == 0) {
                                        $status = 'Vertical';
                                    } else {
                                        if ($i > $eob) {
                                            $status = 'Hold';
                                        } else {
                                            $status = 'Build';
                                        }
                                    }
                                }
                            }
                        }
                    }

                    $depth[$inc]['status'] = $status;

                    $mdChartValue[] = round($horizontal_departure, 2);
                    $tvdChartValue[] = $tvd;

                    $inc++;
                }

                // target
                $depth[$inc]['md'] = $md;
                $depth[$inc]['inclination'] = $inclination;
                $depth[$inc]['tvd'] = $tvd;
                $depth[$inc]['horizontal_departure'] = $horizontal_departure + 100;
                $depth[$inc]['status'] = 'Target';

                $mdChartValue[] = round($horizontal_departure + 100, 2);
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
                return redirect()->route('horizontal.well', 
                    [
                        'kop' => 0, 
                        'target' => 0, 
                        'e' => 0
                    ])->with('error', $this->errorMessage);
            }
        } else {
            $depth[0]['md'] = 0;
            $depth[0]['inclination'] = 0;
            $depth[0]['tvd'] = 0;
            $depth[0]['horizontal_departure'] = 0;
            $depth[0]['status'] = '-';
        }

        $returnValue = [
        	'depth' => $depth,
        	'chart' => $chart,
        	'kop' => $kop,
        	'target' => $target,
        	'e' => $e,
        	'horizontal_displacement' => $horizontal_displacement,
        	'rb' => $rb,
        	'l' => $l,
        	'md' => $md,
        	'bur' => $bur,
        	'eob' => $eob,
        	'n' => $n
        ];

		return $returnValue;
	}
}