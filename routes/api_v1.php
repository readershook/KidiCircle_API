<?php



use App\Http\Controllers\v1\UserController;
use App\Http\Controllers\v1\{CommonController, AuthController};
 
Route::get('/user', [UserController::class, 'testfunction']);



Route::prefix('user')->group(function() {
	Route::post('/create', [UserController::class, 'create']);
	Route::post('/login', [UserController::class, 'login']);

	Route::prefix('otp')->group(function() {
		Route::post('/send', [AuthController::class, 'sendOtp']);
		Route::post('/verify', [AuthController::class, 'verifyOTP']);
	});
	

});


Route::get('/languages/all', [CommonController::class, 'getAllLanguages']);
Route::get('/categories/all', [CommonController::class, 'getAllCategories']);
Route::get('/avatar/all', [CommonController::class, 'getAllAvatar']);