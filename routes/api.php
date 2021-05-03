<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('auth')->group(function(){
	Route::post('login', [AuthController::class, 'login'])->name('auth.login');
	Route::post('register', [AuthController::class, 'register'])->name('auth.register');
	Route::post('activate-account', [AuthController::class, 'activate_account'])->name('auth.activate_account');
	//protected routes... needs auth bearer tokens...
	Route::group(['middleware' => 'auth:api'], function(){
		Route::post('invite-user', [AuthController::class, 'invite_user'])->name('auth.invite_user');

		Route::post('update-profile', [AuthController::class, 'update_profile'])->name('auth.update_profile');
		
		Route::post('logout', [AuthController::class, 'logout'])->name('auth.logout');
	});
});