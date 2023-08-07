<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use App\Services\UserService;
use Illuminate\Http\Request;
use App\Models\Users;
use App\Models\School;
use Faker\Factory as Faker;
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


    public function generateFakeData(Request $request){   // Фейкер

        if($request->key != '1213'){
            return response()->json(['error' => 'В доступе отказано! Введите ключ!'], 406);
        }

        $faker = Faker::create();

        $statuses = [1, 2, 3];
        $position = ['student', 'teacher', 'personal'];

        for ($i = 0; $i < 100; $i++) {
            $student = [
                'id_mektep' => $faker->numberBetween(1, 100),
                'id_class' => $faker->numberBetween(1, 100000),
                'name' => $faker->firstName,
                'surname' => $faker->lastName,
                'lastname' => $position[array_rand($position)],
                'iin' => $faker->numerify('############'),
                'birthday' => $faker->date('Y-m-d', '2014-07-20'),
                'pol' => $faker->randomElement(['М', 'Ж']),
                'national' => $faker->numberBetween(1, 100),
                'parent_ata_id' => $faker->numberBetween(1, 100000),
                'parent_ana_id' => $faker->numberBetween(1, 100000),
                'paid' => '1',
                'created_at' => $faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'updated_at' => $faker->dateTimeThisYear->format('Y-m-d H:i:s'),
                'status' => $faker->randomElement($statuses),
                'perevipusk' => null,
                'position' => $position[array_rand($position)],
                'mektep' => 'Демо школа',
                'iin_md5' => '45a988ea679e76a8adef092bcb5a3ccc'
            ];

            Users::create($student);
        }
        return response()->json(['access' => 'Случайные студенты успешно созданы!'], 200);
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
