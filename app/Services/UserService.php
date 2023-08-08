<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Personal;
use App\Models\Teacher;
use App\Models\School;
use App\Models\Users;
class UserService
{
public function saveNewUser($data){

    $data = json_decode(json_encode($data), true);

    $iin = $data['iin'];

    // Проверка есть ли в базе
    $student = Users::where('iin', $iin)->first();

    if ($student) {
        // Обновим столбец 'status' на значение 2
        $student->update(['status' => 2]);
        $student->update(['iin_md5' => $data['iin_md5']]);

        $student->save();
    }else{
        return response()->json(['error' => '404'], 404); // Такой пользователь не найден!
    }

}
    public function getUsersService(Request $request){
        /*
         * перенести логику на сервис
         */
        $iin = $request->input('iin');
        $name = $request->input('name');
        $surname = $request->input('surname');
        $school = $request->input('school');
        $status = $request->input('status');

        switch ($status){
            case 'teacher':
                $user = Teacher::getTeacher($iin);
                $result =$this->validateUsers($user, $name, $surname, $school);
                return $result;
            case 'personal':
                $user = Personal::getPersonal($iin);
                $result =$this->validateUsers($user, $name, $surname, $school);
                return $result;
            case 'student':
                $user = Users::getSchoolBoy($iin);
                $result =$this->validateUsers($user, $name, $surname, $school);
                return $result;
        }
        return response()->json(['error' => '403'], 403); // Неверный запрос
    }

    // Валидация
    public function validateUsers($user, $name, $surname, $school){

        if($user['data'] == null){
            return response()->json(['error' => '404'], 404); // Такой пользователь не найден!
            exit();
        }
        switch (true) {
            case $user['data']['name'] != $name:
                return response()->json(['error' => '405'], 405); // Имя неверно!
                exit();
            case $user['data']['surname'] != $surname:
                return response()->json(['error' => '406'], 406); // Не правильная фамилия!
                exit();
            case $user['data']['id_mektep'] != $school:
                return response()->json(['error' => '407'], 407); // Не та школа!
                exit();
        }
        return $user;
    }

    // Сохраняем пользователя
    public function paidUserService(Request $request){

        $data = $request->only([
            'id_mektep',
            'name',
            'surname',
            'lastname',
            'iin',
            'birthday',
            'pol',
            'position',
            'national',
            'id_class',
            'parent_ata_id',
            'parent_ana_id'
        ]);

        $data['status'] = 1;
        $data['curdate'] = date('Y-m-d H:i:s');
// Сохраняем название класса
        if($data['position'] == 'student'){
            $idMektep = $data['id_class'];

            $student_query = DB::connection('mektep_edu')->table('mektep_class')
                ->select('class', 'group')
                ->where('id', $idMektep)
                ->get();
            $class = json_decode(json_encode($student_query), true);

            $data['class_litter'] = $class[0]['class'] . $class[0]['group'];

            if ( empty($data['class_litter'])) {
                return response()->json([
                    "message" => "Класс не найден"
                ], 407);
            }
        }

// Сохраняем название школы

        $school = School::select('name_rus')->where('id', $data['id_mektep'])->first();
        $name_rus = $school->name_rus;

        $data['mektep'] = $name_rus;

        if ( empty($data['mektep'])) {
            return response()->json([
                "message" => "Школа не найдена"
            ], 408);
        }

// Добавить проверку если оплата прошл

//        if ($paid){
        $data['paid'] = '1';
//        }

        // Проверка есть ли в базе такой пользователь
        $student = Users::where('iin', $data['iin'])->first();

// Если пользователь существует, увеличиваем значение поля "perevipusk" на 1

        if ($student) {
            // Обновляем запись в базе данных или создаем новую запись
            Users::updateOrCreate(['iin' => $data['iin']], $data);

            $student->increment('perevipusk');
            $student->save();

            return response()->json(['access' => "Перезапись прошла успешно! Количество перевыпусков: $student->perevipusk"], 200);
        }

        if(empty($student)) {
            $student = Users::create($data);
            return response()->json(['access' => '200'], 200); // Сохранение прошло успешно!
        }else{
            return response()->json(['error' => '403'], 403); // Неверный запрос
        }
    }
}