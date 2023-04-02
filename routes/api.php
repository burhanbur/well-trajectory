<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\BuildHoldController;
use App\Http\Controllers\Api\BuildHoldDropController;
use App\Http\Controllers\Api\HorizontalWellController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('build-hold', [BuildHoldController::class, 'index'])->name('build.hold');
Route::post('download-result-build-hold', [BuildHoldController::class, 'downloadResult'])->name('download.result.build.hold');

Route::post('build-hold-drop', [BuildHoldDropController::class, 'index'])->name('build.hold.drop');
Route::post('download-result-build-hold-drop', [BuildHoldDropController::class, 'downloadResult'])->name('download.result.build.hold.drop');

Route::post('horizontal-well', [HorizontalWellController::class, 'index'])->name('horizontal.well');
Route::post('download-result-horizontal-well', [HorizontalWellController::class, 'downloadResult'])->name('download.result.horizontal.well');
