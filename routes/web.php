<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BuildHoldController;

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
Route::post('calculate', [BuildHoldController::class, 'calculate'])->name('calculate.build.hold');


