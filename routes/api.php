<?php

use App\Http\Controllers\AnswerController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\SecureRoute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware(['auth:sanctum', SecureRoute::class])->group(function () {
    Route::apiResource('questions', QuestionController::class)->except(['index', 'show']);
    Route::apiResource('answers', AnswerController::class)->except(['index', 'show']);
    Route::put('answers/{answer}/incrementvote', [AnswerController::class, 'incrementVote']);
    Route::put('answers/{answer}/decrementvote', [AnswerController::class, 'decrementVote']);
    Route::apiResource('users', UserController::class);
    Route::apiResource('tags', TagController::class)->except(['index']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('/questions/{question}/answers', [AnswerController::class, 'store']);
    Route::put('questions/{question}', [QuestionController::class, 'update']);
    Route::put('answers/{answer}', [AnswerController::class, 'update']);
    Route::post('/answers/{answer}/validate', [AnswerController::class, 'validateAnswer']);
    


});

Route::withoutMiddleware([SecureRoute::class])->group(function () {
    Route::apiResource('questions', QuestionController::class)->only(['index', 'show']);
    Route::apiResource('answers', AnswerController::class)->only(['index', 'show']);
    Route::apiResource('tags', TagController::class)->only(['index']);
    Route::apiResource('users', UserController::class)->only(['index']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

});
/*Route::apiResource('questions', QuestionController::class)->only(['index', 'show']);
Route::apiResource('answers', AnswerController::class)->only(['index', 'show']);
//Route::put('answers/{answer}/validate', [AnswerController::class, 'is_validated']);
Route::apiResource('tags', TagController::class);



Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);*/
