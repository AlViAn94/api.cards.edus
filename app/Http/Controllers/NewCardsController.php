<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Students;
use App\Models\School;
use App\Models\Teacher;
use App\Models\Personal;
class NewCardsController extends Controller
{
    public function actionGetUsers(Request $request){

        $iin = $request->input('iin');
        $name = $request->input('name');
        $surname = $request->input('surname');
        $school = $request->input('school');
        $status = $request->input('status');

        switch ($status){
            case 'teacher':
                $user = Teacher::getTeacher($iin);
                $result = $this->validateUsers($user, $name, $surname, $school);
                return $result;
            case 'personal':
                $user = Personal::getPersonal($iin);
                $result = $this->validateUsers($user, $name, $surname, $school);
                return $result;
            case 'student':
                $user = Students::getSchoolBoy($iin);
                $result = $this->validateUsers($user, $name, $surname, $school);
                return $result;
        }
        return response()->json(['error' => 'Неверный запрос'], 406);
    }

    public function actionRegions(){

        $region = School::getRegion();

        return $region;
    }

    public function actionGetSchools(){

        $schools = School::getSchoolAll();

        return $schools;
    }

    public function validateUsers($user, $name, $surname, $school){
        /*
         * если верну старый метод получение ученика в mektep api то убрать одну ['data']!
         */
//        echo '<pre>' . print_r($user, true); exit();
        if($user['data'] == null){
            return response()->json(['error' => 'Такой пользователь не найден!'], 400);
            exit();
        }
        switch (true) {
            case $user['data']['name'] != $name:
                return response()->json(['error' => 'Имя не верно!'], 401);
                exit();
            case $user['data']['surname'] != $surname:
                return response()->json(['error' => 'Не правильная фамилия!'], 402);
                exit();
            case $user['data']['id_mektep'] != $school:
                return response()->json(['error' => 'Не та школа!'], 403);
                exit();
        }
        return $user;
    }
    public function saveNewCards(Request $request){
//        $data = $request->json()->all();
//        echo '<pre>' . print_r($data, true); exit();
        $data['id_mektep'] = $request->input('id_mektep');
        $data['name'] = $request->input('name');
        $data['surname'] = $request->input('surname');
        $data['lastname'] = $request->input('lastname');
        $data['iin'] = $request->input('iin');
        $data['birthday'] = $request->input('birthday');
        $data['pol'] = $request->input('pol');
        $status = $request->input('status');

        if ($status == 'student'){
            $data['national'] = $request->input('national');
            $data['id_class'] = $request->input('id_class');
            $data['parent_ata_id'] = $request->input('parent_ata_id');
            $data['parent_ana_id'] = $request->input('parent_ana_id');
        }

        $data['pay'] = '1';
        $data['curdate'] = date('Y-m-d');

        switch ($status) {
            case 'teacher':
                $user = Teacher::where('iin', $data['iin'])->first();
//                echo '<pre>' . print_r($user,true); exit();
                if($user){
                    return response()->json(['massage' => 'Такой пользователь существует! '], 404);
                }else{
                    Teacher::create($data);
                    return response()->json(['access' => 'Сохранение прошло успешно!'], 200);
                }

            case 'personal':
                $user = Personal::where('iin', $data['iin'])->first();
                if($user){
                    return response()->json(['massage' => 'Такой пользователь существует! '], 404);
                }else{
                    Personal::create($data);
                    return response()->json(['access' => 'Сохранение прошло успешно!'], 200);
                }

            case 'student':
                $user = Students::where('iin', $data['iin'])->first();
                if($user){
                    return response()->json(['massage' => 'Такой пользователь существует! '], 404);
                }else{
                    Students::create($data);
                    return response()->json(['access' => 'Сохранение прошло успешно!'], 200);
                }
        }

        return response()->json(['error' => 'Неверный запрос'], 406);
    }
}
