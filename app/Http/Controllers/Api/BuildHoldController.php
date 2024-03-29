<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Exports\BuildHoldExport;
use App\Services\BuildHoldService;

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
        $returnValue = [];

        $logic = new BuildHoldService;
        $returnValue = $logic->calculate($request);

        $response = [
            'status' => 'OK',
            'data' => $returnValue
        ];

        return response()->json($response);
    }

    public function downloadResult(Request $request)
    {
        $returnValue = [];
        $data = $request->all();

        $validator = Validator::make($data, [
            'depth' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            $returnValue = [
                'error' => false,
                'message' => $validator->errors()->first()
            ];
        }

        try {
            $params = $data['depth'];

            return Excel::download(new BuildHoldExport($params), 'build-hold-result.xlsx');   
        } catch (\Exception $ex) {
            $returnValue = [
                'error' => false,
                'message' => $ex->getMessage()
            ];
        }

        return response()->json($returnValue);
    }
}
