<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use App\Services\UserService;
use Illuminate\Http\Request;
use App\Models\School;
use App\Models\Users;
class UserController extends Controller
{
    protected $UserService;

    public function __construct(UserService $UserService)
    {
        $this->UserService = $UserService;
    }

    //Поиск пользователя в БД школы
    public function actionGetUsers(Request $request){

        $result = $this->UserService->getUsersService($request);

        return $result;

    }

    public function actionRegions(){

        $region = School::getRegion();

        return $region;
    }

    public function actionGetSchools(){

        $schools = School::getSchoolAll();

        return $schools;
    }

    // Оплата и сохранение
    public function actionSavePadiUser(Request $request){ // Сохраняем пользователя
        
        $result =$this->UserService->paidUserService($request);

        return response()->json(['result' => $result]);
    }

    // Поиск, сортировка, пагинация
    public function actionGetSearchUser(Request $request) {

        $result = Users::getSortUsers($request);

        return $result;
    }

    public function actionStatusOne(Request $request){

        $status = $request->status;

        if($request->key != '1213'){
            return response()->json(['error' => 'В доступе отказано! Введите ключ!'], 406);
        }
        $students = Users::all();

        if ($students->isEmpty()) {
            return response()->json(["message" => "Студенты не найдены"], 404);
        }

        try {
            foreach ($students as $student) {
                $student->update(['status' => $status]);
            }

            return response()->json(["success" => true], 200);
        } catch (\Exception $e) {
            return response()->json(["message" => "Произошла ошибка при обновлении статусов"], 500);
        }
    }

}
