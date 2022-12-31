<?php

use App\Http\Controllers\v1\UserController;
use App\Http\Controllers\v1\{CommonController, AuthController, KidsController, Videostudio, ContentController};
use App\Http\Controllers\v1\Admin\{ContentController as AdminContentController};
 
Route::get('/user', [UserController::class, 'testfunction']);



Route::prefix('user')->group(function () {

	Route::middleware(['auth:api'])->group(function () {
		Route::post('/password_update', [AuthController::class, 'updatePassword']);
		Route::get('/detail', [UserController::class, 'getUser']);
		Route::put('/update', [UserController::class, 'updateUser']);
		Route::post('/update-profile-pic', [UserController::class, 'updateProfilePic']);
		Route::post('/verify_pin', [UserController::class, 'verifyPin']);
		

	});

	Route::post('/create', [UserController::class, 'create']);
	Route::post('/login', [UserController::class, 'login']);

	Route::prefix('otp')->group(function () {
		Route::post('/send', [AuthController::class, 'sendOtp']);
		Route::post('/verify', [AuthController::class, 'verifyOTP']);
	});
	

});


Route::prefix('content')->group(function () {
	Route::post('/', [ContentController::class, 'save']);
	Route::post('/assets', [ContentController::class, 'uploadAssets']);
	Route::post('/{content_id}/slides', [ContentController::class, 'saveSlides']);
	

});




Route::prefix('kids')->group(function () {
	Route::post('/add', [KidsController::class, 'createKids']);
	Route::get('/list', [KidsController::class, 'listKids']);
	Route::post('/addcontent', [KidsController::class, 'addContent']);
	Route::delete('/removecontent', [KidsController::class, 'removeContent']);
	Route::put('/{id}', [KidsController::class, 'updateKidInfo']);

});


Route::get('/languages/all', [CommonController::class, 'getAllLanguages']);
Route::get('/categories/all', [CommonController::class, 'getAllCategories']);
Route::get('/avatar/all', [CommonController::class, 'getAllAvatar']);



Route::prefix('admin')->group(function () {

	Route::prefix('content')->group(function () {
		Route::post('upload', [AdminContentController::class, 'uploadContentFile']);
	});
	
});





Route::prefix('video-studio')->group(function() {
	Route::post('/acquire', [Videostudio::class, 'acquire']);
	Route::post('/upload', [Videostudio::class, 'upload']);
	Route::post('/merge', [Videostudio::class, 'merge']);
	Route::post('/status', [Videostudio::class, 'status']);
	Route::post('/test', [Videostudio::class, 'test']);

});
