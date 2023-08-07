<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\StatisticController;
use Fruitcake\Cors\HandleCors;
use App\Services\PdfService;


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

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});

// Client side
Route::get('/users', [UserController::class, 'actionGetUsers']);
Route::get('/schools', [UserController::class, 'actionGetSchools']);
Route::get('/region', [UserController::class, 'actionRegions']);
Route::post('/save', [UserController::class, 'actionSavePadiUser']);

// Managerial side
    Route::post('login', [AuthController::class, 'login']);
    Route::get('logout', [AuthController::class, 'logout']);
    Route::get('/search', [UserController::class, 'actionGetSearchUser']);

    // Generation users
    Route::post('generation/one', [PdfController::class, 'actionSaveNewCard']);
    Route::post('generation/mass', [PdfController::class, 'actionMassGeneratedSave']);

    // Created pdf file
    Route::middleware([HandleCors::class])->group(function () {
        Route::post('mass/pdf', [PdfController::class, 'actionMassPdfExportZip']);
    });

    //  Registration cards
    Route::get('student/name', [CardController::class, "actionStudentName"]);
    Route::post('register/card', [CardController::class, 'index']);

    // Delivery card
    Route::get('delivery/card', [CardController::class, "actionDeliveryCard"]);

    // Completed
    Route::get('completed/card', [CardController::class, "actionCompletedCard"]);

    // Statistic
    Route::get('accepted/day', [StatisticController::class, "actionGetDayStat"]);
    Route::get('accepted/week', [StatisticController::class, "actionGetWeekStat"]);
    Route::get('accepted/month', [StatisticController::class, "actionGetMonthStat"]);

    Route::get('statistic/day', [StatisticController::class, "actionGetNewDayStat"]);
    Route::get('statistic/week', [StatisticController::class, "actionGetNewWeekStat"]);
    Route::get('statistic/month', [StatisticController::class, "actionGetNewMonthStat"]);



// Delete after project production
//Route::get('/create-random-users', [UserController::class, 'generateFakeData']);
Route::get('status/one', [UserController::class, 'actionStatusOne']);



