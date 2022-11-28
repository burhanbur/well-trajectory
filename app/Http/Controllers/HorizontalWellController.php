<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Exports\HorizontalWellExport;

use Excel;

class HorizontalWellController extends Controller
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

        $kop = $request->input('kop'); // kick off point
        $target = $request->input('target'); // target
        $e = $request->input('e'); // east

        if ($kop || $target || $e) {
            $pi = pi();
            
            try {
                
            } catch (\Exception $ex) {
               return redirect()->back()->with('error', $this->errorMessage);
            } catch (\Throwable $err) {
                return redirect()->route('build.hold', 
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
            $depth[0]['description'] = '-';
        }

        return view('horizontal-well', get_defined_vars());
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

            return Excel::download(new HorizontalWellExport($params), 'horizontal-well-result.xlsx');   
        } catch (\Exception $ex) {
            return redirect()->back()->with('error', $this->errorMessage);
        } catch (\Throwable $err) {
            return redirect()->route('horizontal.well')->with('error', $this->errorMessage);
        }
    }
}