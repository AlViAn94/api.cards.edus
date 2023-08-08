<?php

use App\Http\Controllers\StatisticController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\PdfController;
use Illuminate\Support\Facades\Route;
use Fruitcake\Cors\HandleCors;
use Illuminate\Http\Request;
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
Route::controller(AuthController::class)
    ->group(function (){
        Route::post('login', 'login');
        Route::get('logout', 'logout');
    });

Route::group(['middleware' => 'sanctumCustom'], function (){

    // Временный роут, удалить на продакшене
    Route::get('status/one', [UserController::class, 'actionStatusOne']);
    
Route::controller(UserController::class)
        ->group(function (){
            Route::get('/users', 'actionGetUsers');
            Route::get('/schools', 'actionGetSchools');
            Route::get('/region', 'actionRegions');
            Route::post('/save', 'actionSavePadiUser');
            Route::get('/search', 'actionGetSearchUser');
        });

Route::controller(PdfController::class)
        ->group(function (){
            Route::post('generation/mass', 'actionMassGeneratedSave');
            Route::post('mass/pdf', 'actionMassPdfExportZip');
        });

Route::controller(CardController::class)
        ->group(function (){
            Route::get('completed/card', "actionCompletedCard");
            Route::post('delivery/card', "actionDeliveryCard");
            Route::get('student/name', "actionStudentName");
            Route::post('register/card', 'index');
        });

Route::middleware([HandleCors::class])->group(function () {
    });
    Route::controller(StatisticController::class)
        ->group(function(){
            Route::get('statistic/{period}', "actionGetNewStatistic");
            Route::get('accepted/{period}', "actionGetStatistic");
        });
});




