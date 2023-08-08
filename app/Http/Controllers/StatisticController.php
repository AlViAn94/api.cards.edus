<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Users;
use Carbon\Carbon;

class StatisticController extends Controller
{
// Statistic accepted for day/week/month
    public function actionGetStatistic($period){

        switch ($period){
            case 'day':
                $today = Carbon::today();
                $userCount = Users::whereDate('updated_at', $today)
                    ->where('status', '>=', 3)
                    ->count();
                return response()->json(['count' => $userCount]);

            case 'week':
                $startOfWeek = Carbon::now()->startOfWeek();
                $endOfWeek = Carbon::now()->endOfWeek();
                $userCount = Users::whereBetween('updated_at', [$startOfWeek, $endOfWeek])
                    ->where('status', '>=', 3)
                    ->count();
                return response()->json(['count' => $userCount]);

            case 'month':
                $startOfMonth = Carbon::now()->startOfMonth();
                $endOfMonth = Carbon::now()->endOfMonth();
                $userCount = Users::whereBetween('updated_at', [$startOfMonth, $endOfMonth])
                    ->where('status', '>=', 3)
                    ->count();
                return response()->json(['count' => $userCount]);
        }
    }

// Statistic for day/week/month
    public function actionGetNewStatistic($period)
    {
        switch ($period) {
            case 'day':
                $today = Carbon::today();
                $userCount = Users::whereDate('updated_at', $today)
                    ->count();
                return response()->json(['count' => $userCount]);
                echo '<pre>' . print_r($userCount, true); exit();

            case 'week':
                $startOfWeek = Carbon::now()->startOfWeek();
                $endOfWeek = Carbon::now()->endOfWeek();
                $userCount = Users::whereBetween('updated_at', [$startOfWeek, $endOfWeek])
                    ->count();
                return response()->json(['count' => $userCount]);

            case 'month':
                $startOfMonth = Carbon::now()->startOfMonth();
                $endOfMonth = Carbon::now()->endOfMonth();
                $userCount = Users::whereBetween('updated_at', [$startOfMonth, $endOfMonth])
                    ->count();
                return response()->json(['count' => $userCount]);
        }
    }

}