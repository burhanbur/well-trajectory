<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Exports\BuildHoldDropExport;
use App\Services\BuildHoldDropService;

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
        $returnValue = [];
        
        $logic = new BuildHoldDropService;
        $returnValue = $logic->calculate($request);

        return response()->json($returnValue);
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
            return redirect()->back()->with('error', $this->errorMessage);
        }
    }
}