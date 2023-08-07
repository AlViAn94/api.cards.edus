<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\UserService;
use App\Services\PdfService;
use App\Models\Users;
use Carbon\Carbon;

class StatisticController extends Controller
{
    // Statistic for one day
    public function actionGetDayStat(){

        $today = Carbon::today();

        $userCount = Users::whereDate('updated_at', $today)
            ->where('status', '>=', 3)
            ->count();

        return response()->json(['count' => $userCount]);
    }
    public function actionGetWeekStat(){

        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $userCount = Users::whereBetween('updated_at', [$startOfWeek, $endOfWeek])
            ->where('status', '>=', 3)
            ->count();

        return response()->json(['count' => $userCount]);
    }
    public function actionGetMonthStat(){

        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $userCount = Users::whereBetween('updated_at', [$startOfMonth, $endOfMonth])
            ->where('status', '>=', 3)
            ->count();

        return response()->json(['count' => $userCount]);
    }
    public function actionGetNewDayStat(){

        $today = Carbon::today();

        $userCount = Users::whereDate('updated_at', $today)
            ->count();

        return response()->json(['count' => $userCount]);
    }
    public function actionGetNewWeekStat(){

        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();

        $userCount = Users::whereBetween('updated_at', [$startOfWeek, $endOfWeek])
            ->count();

        return response()->json(['count' => $userCount]);
    }
    public function actionGetNewMonthStat(){

        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $userCount = Users::whereBetween('updated_at', [$startOfMonth, $endOfMonth])
            ->count();

        return response()->json(['count' => $userCount]);
    }

}