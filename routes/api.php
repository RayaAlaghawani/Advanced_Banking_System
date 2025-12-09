
<?php

use App\Http\Controllers\Auth\CitizenAuthController;
use App\Http\Controllers\ComplaintController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
Route::post('/client/register', [CitizenAuthController::class, 'register']);
Route::post('/client/verify-email/{user_id}', [CitizenAuthController::class, 'verifyEmail']);
Route::post('login', [CitizenAuthController::class, 'login'])->
middleware('role.throttle');
Route::middleware('auth:sanctum')->group(function () {
    Route::post('createMainAccount', [\App\Http\Controllers\AccountController::class,
        'createMainAccount']);
    Route::post('createSubAccount', [\App\Http\Controllers\AccountController::class,
        'createSubAccount']);
    Route::post('logout',[CitizenAuthController::class,'logout']);
});

