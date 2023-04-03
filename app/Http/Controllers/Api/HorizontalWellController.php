<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Exports\HorizontalWellExport;
use App\Services\HorizontalWellService;

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
        $returnValue = [];
        
        $logic = new HorizontalWellService;
        $returnValue = $logic->calculate($request);

        return response()->json($returnValue);
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

            return Excel::download(new HorizontalWellExport($params), 'horizontal-well-result.xlsx'); 
        } catch (\Exception $ex) {
            $returnValue = [
                'error' => false,
                'message' => $ex->getMessage()
            ];
        }

        return response()->json($returnValue);
    }
}