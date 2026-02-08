<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/



Route::middleware("is.pc")->group(function() {

    Route::middleware("check.pclogin")->group(function() {
        // レースカレンダー
        Route::controller(\App\Http\Controllers\RaceCalendarController::class)->group(function() {
            Route::get("/calendar", "index")->name("calendar.index");
            Route::post("/calendar/raceinfo", "getRaceInfo")->name("calendar.raceinfo");
            Route::post("/calendar/get/schedule.", "getScheduleData")->name("calendar.get.schedule");
            Route::post("/calendar/kick/racedata", "executeScrapingRace")->name("calendar.kick.racedata");
        });
        // レース詳細
        Route::controller(\App\Http\Controllers\RaceDetailController::class)->group(function() {
            Route::get("/detail/{race_id}", "index")->name("detail.index");
            Route::post("/detail/scoring/{race_id}", "scoring")->name("detail.scoring");
            Route::post("/detail/comment", "writeComment")->name("detail.comment");
        });

        // Chrome拡張機能
        Route::controller(\App\Http\Controllers\ExtensionController::class)->group(function() {
            Route::get("/extension", "index")->name("extension");
            Route::get("/extension/download", "download")->name("extension.download");
        });

        // パスワード変更
        Route::controller(\App\Http\Controllers\PasswordChangeController::class)->group(function() {
            Route::get("/password", "index")->name("passchg");
            Route::post("/password", "reset")->name("passchg.reset");
        });

    });

    // ログイン処理
    Route::controller(\App\Http\Controllers\LoginController::class)->group(function() {
        Route::get("/", "index")->name("login");
        Route::post("/login", "login")->name("login.login");
        Route::post("/logout", "logout")->name("logout");
    });
});


Route::get('{any}', function () {
    return view('mobile/index');
})->where('any','.*')->name("mobile");
