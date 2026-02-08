<?php

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


Route::controller(\App\Http\Controllers\api\HorseRaceApi::class)->group(function() {

    Route::post('/horserace/logincheck', 'loginCheck');
    Route::post('/horserace/login', 'login');
    Route::middleware("check.login")->group(function() {
        Route::post('/horserace/schdule', 'getSchdule');
        Route::post('/horserace/racedata', 'getRaceData');

        Route::post('/horserace/kaisaibaba', 'getKaisaiBaba');
        Route::post('/horserace/racelist', 'getRaceList');
        Route::post('/horserace/scraping/racedata', 'scrapingRaceData');
        Route::post('/horserace/scraping/kaisaibaba', 'scrapingKaisaiBaba');

        Route::post('/horserace/writecomment', 'writeComment');

        Route::post('/horserace/profile', 'updateProfile');
    });
});


/*
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
*/
