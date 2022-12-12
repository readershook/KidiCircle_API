<?php



use App\Http\Controllers\v1\UserController;
 
Route::get('/user', [UserController::class, 'testfunction']);



Route::prefix('user')->group(function() {
	Route::post('/create', [UserController::class, 'create']);
	Route::post('/login', [UserController::class, 'login']);
	// Route::get('/create', [UserController::class, 'create']);

});