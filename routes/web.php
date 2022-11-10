<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BuildHoldController;
use App\Http\Controllers\BuildHoldDropController;
use App\Http\Controllers\HorizontalWellController;

use App\Http\Controllers\EquivalentCirculatingController;
use App\Http\Controllers\FlowrateMinimumController;
use App\Http\Controllers\PressureLossController;
use App\Http\Controllers\RheologicalController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [BuildHoldController::class, 'index']);

Route::group(['prefix' => 'well-trajectory'], function () {
	Route::get('build-hold', [BuildHoldController::class, 'index'])->name('build.hold');
	Route::get('build-hold-drop', [BuildHoldDropController::class, 'index'])->name('build.hold.drop');
	Route::get('horizontal-well', [HorizontalWellController::class, 'index'])->name('horizontal.well');
});

Route::group(['prefix' => 'hydraulic'], function () {
	Route::get('rheological', [RheologicalController::class, 'index'])->name('rheological');
});