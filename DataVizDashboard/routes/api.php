<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DataController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/sector', [DataController::class, 'getSector']);
Route::get('/swot-pestle', [DataController::class, 'getSWOTPestle']);
Route::get('/city', [DataController::class, 'getCityData']);
Route::get('/country', [DataController::class, 'getCountry']);
Route::get('/region', [DataController::class, 'getRegion']);
Route::get('/pestle-data', [DataController::class, 'getPestleData']);
Route::get('/year-data', [DataController::class, 'getYearData']);

