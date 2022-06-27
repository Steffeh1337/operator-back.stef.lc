<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

use App\Http\Controllers\NotificationsController;
use App\Http\Controllers\ReviewsController;
use App\Http\Controllers\AppointmentController;

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

// AUTH
Route::middleware('api')->controller(AuthController::class)->group(function () {
	Route::post('auth/logout', 'logout');
	Route::get('auth/refresh-login', 'refresh');
	Route::get('user/find-details', 'me');
	Route::post('user/password', 'changePassword');
	Route::post('user/profile-update', 'updateProfile');
	Route::post('user/schedule', 'updateSchedule');
});

// NOTIFICATIONS
Route::middleware('api')->controller(NotificationsController::class)->group(function () {
	Route::get('notifications', 'index');
	Route::get('notification/{notification}', 'find');
	Route::get('notifications-count', 'count');
	Route::get('notification-read/{notification}', 'markAsRead');
});

// REVIEWS
Route::middleware('api')->controller(ReviewsController::class)->group(function () {
	Route::get('reviews', 'index');
	Route::post('appointment/review/{appointment}', 'addReview');
});

// APPOINTMENTS
Route::middleware('api')->controller(AppointmentController::class)->group(function () {
	Route::get('appointments/get-data', 'getFiltersData');
	Route::post('appointments', 'index');
	Route::post('appointment/cancel/{appointment}', 'cancel');
});
