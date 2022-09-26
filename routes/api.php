<?php

use App\Http\Controllers\Api\LoanController;
use App\Http\Controllers\Api\UserController;
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


Route::post('login', [UserController::class,'login']);
Route::post('registration', [UserController::class,'registration']);

Route::middleware(['jwt.verify'])->prefix('')->group(function () {
    // User
    Route::post('user/list', [ UserController::class,'list'])->middleware('role:super-admin');
    Route::post('user/profile', [ UserController::class,'profile']);
    Route::post('logout', [UserController::class,'logout']);
    Route::post('refresh', [UserController::class,'refresh']);

    // Loan
    Route::post('loan/store', [ LoanController::class,'store'])->middleware('role:customer');
    Route::post('loan/list', [ LoanController::class,'list']);
    Route::post('loan/show', [ LoanController::class,'show']);
    Route::post('loan/approve', [ LoanController::class,'approve'])->middleware('role:super-admin');
    Route::post('loan/repayment', [ LoanController::class,'repayment'])->middleware('role:customer');
});