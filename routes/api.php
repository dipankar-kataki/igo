<?php

use App\Http\Controllers\Customer\AuthController;
use App\Http\Controllers\Customer\BookRideController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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


Route::prefix('customer')->group(function(){
    Route::prefix('login')->group(function(){
        Route::post('get-otp', [AuthController::class, 'getOtp']);
        Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
    });

    Route::post('signup', [AuthController::class, 'signup']);

    Route::group(['middleware' => ['auth:sanctum']], function () {

        Route::prefix('ride')->group(function(){
            Route::post('book-ride', [BookRideController::class, 'bookRide']);
        });
        
        /************************************* Logout Api's ********************************************* */
        Route::get('logout',function(){
            auth()->user()->tokens()->delete();
            return response()->json([
                'status' => 'Success',
                'message' => 'Logout successfull.',
                'data' => null,
                'token' => 'null',
                'http_status_code' => 200
            ]);
        });
        
    });
});