<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GenerateCardController extends Controller
{
    public function findUser($data)
    {

        if($data['iin'] && $data['iin'] == 000000000000) {
            return response()->json([
                "message" => 'Не найден'
            ], 404);
        }

        $tableName = $this->setTableName($data['position']);

        $users_query = DB::connection('mektep_edu')->table($tableName)
            ->select('id', 'surname', 'name', 'lastname', 'id_mektep')
            ->selectRaw('CAST(iin AS CHAR) as iin');

        if($data['iin']) {
            $users_query->where('iin', trim($data['iin']));
        }
        else {
            $users_query->where('id', trim($data['id']));
        }

        $users = $users_query->get()->toArray();

        if(is_array($users) && count($users) > 0) {
            $user_cards_query = DB::table("cards_ready")
                ->select('id', 'full_name', 'user_id', 'card_number', 'nfc', 'mektep_id', 'is_active', 'created_at', 'qr_code')
                ->selectRaw('CAST(iin AS CHAR) as iin')
                ->selectRaw('MD5(iin) as iin_md5')
                ->where('status', '=', $data['position'])
                ->where('is_active', '=', 1);

            if($data['iin']) {
                $user_cards_query->where('iin', $users[0]->iin);
            }
            else {
                $user_cards_query->where('user_id', $users[0]->id);
            }
            $user_cards = $user_cards_query->orderBy('is_active', 'desc')->orderBy('created_at', 'desc')
                ->get()->toArray();

            $cards_mektep_ids = DB::table('mektep_id_cards')->whereIn('cards_ready_id', array_column($user_cards, 'id'))->get()->toArray();

            foreach ($user_cards as $index => $card) {
                $user_cards[$index]->mektep_ids = [];

                foreach ($cards_mektep_ids as $mektep_id_item) {
                    if($mektep_id_item->cards_ready_id == $card->id) {
                        $user_cards[$index]->mektep_ids[] = $mektep_id_item->mektep_id;
                    }
                }
            }

            $mektep_ids_condition = array_merge( array_column($users, 'id_mektep'), array_column($cards_mektep_ids, 'mektep_id') );

            $mektep_names = DB::connection('mektep_edu')->table('mektepter')
                ->select('id', 'name_rus as name')
                ->whereIn('id', $mektep_ids_condition)
                ->get()
                ->keyBy('id');

            return response()->json([
                "users" => $users,
                "cards" => $user_cards,
                "mektep_names" => $mektep_names
            ]);
        }
        else {
            return response()->json([
                "message" => 'Не найден'
            ], 404);
        }
    }

    public function updateCardMektepId(Request $request) {
        $request->validate([
            "card_number" => "numeric|digits_between:5,10",
        ]);

        $card = DB::table('cards_ready')
            ->where('card_number', '=', $request->card_number)
            ->where('is_active', '=', 1)
            ->first();

        if($card) {
            $tableName = $this->setTableName($card->status);

            if($card->status == 'student') {
                $student = DB::connection('mektep_edu')->table($tableName)
                    ->select('id_mektep')
                    ->where('id', '=', $card->user_id)
                    ->first();

                if($student) {
                    DB::table('mektep_id_cards')
                        ->where('cards_ready_id', '=', $card->id)
                        ->update(["mektep_id" => $student->id_mektep]);

                    DB::table('cards_ready')
                        ->where('id', '=', $card->id)
                        ->update(["mektep_id" => $student->id_mektep]);
                }
            }
            else {
                $users = DB::connection('mektep_edu')->table($tableName)
                    ->select('id_mektep')
                    ->where('iin', '=', $card->iin)
                    ->get()->toArray();

                DB::table('mektep_id_cards')
                    ->where('cards_ready_id', '=', $card->id)
                    ->delete();

                foreach($users as $user) {
                    DB::table('mektep_id_cards')->insert([
                        "cards_ready_id" => $card->id,
                        "card_number" => $card->card_number,
                        "mektep_id" => $user->id_mektep,
                        "created_at" => date("Y-m-d H:i:s"),
                    ]);
                }
            }

            return response()->json([
                "message" => 'Success',
            ], 200);
        }
        else {
            return response()->json([
                "message" => 'Карточка не найдена'
            ], 404);
        }
    }

    public function getMektepList() {
        $mektepter = DB::connection('mektep_edu')->table('mektepter')
            ->select('mektepter.id as id', 'mektepter.name_rus as name', 'edu_punkt.short_rus as punkt_name')
            ->leftJoin('edu_punkt', 'edu_punkt.id', '=', 'mektepter.edu_punkt')
            ->orderBy('mektepter.edu_punkt')
            ->get()->toArray();

        return response()->json([
            "mektep_list" => $mektepter
        ]);
    }

    public function getNewUserList() {
        $allNewUsers = DB::table('cards_new_users')
            ->select('id', 'full_name', 'iin', 'status', 'mektep_id')
            ->where('is_generated', '=', 0)
            ->orderBy('status', 'desc')
            ->get()->toArray();

        $list = [];
        foreach ($allNewUsers as $user) {
            $list[$user->mektep_id]['user_list'][] = $user;
        }

        $mektep_names = DB::connection('mektep_edu')->table('mektepter')
            ->select('mektepter.id as id', 'mektepter.name_rus as name', 'edu_punkt.short_rus as punkt_name')
            ->leftJoin('edu_punkt', 'edu_punkt.id', '=', 'mektepter.edu_punkt')
            ->whereIn('mektepter.id', array_keys($list))
            ->get()->toArray();

        foreach ($mektep_names as $mektep) {
            $list[$mektep->id]['punkt_name'] = $mektep->punkt_name;
            $list[$mektep->id]['mektep_name'] = $mektep->name;
            $list[$mektep->id]['id'] = $mektep->id;
        }

        return response()->json([
            "list" => $list
        ]);
    }

    public function getUserListByMektep(Request $request) {
        $request->validate([
            "type" => "required|string",
            "id_mektep" => "numeric|digits_between:1,10",
            "id_class" => "numeric|digits_between:1,10",
        ]);

        switch ($request->type) {
            case 'teacher' :
                $users = DB::connection('mektep_edu')->table('mektep_teacher')
                    ->select('id', 'surname', 'name', 'lastname')
                    ->selectRaw('CAST(iin AS CHAR) as iin')
                    ->where('id_mektep', '=', $request->id_mektep)
                    ->orderBy('surname_latin')->orderBy('name_latin')
                    ->get()->toArray();
                break;
            case 'student' :
                $users = DB::connection('mektep_edu')->table('mektep_students')
                    ->select('id', 'surname', 'name', 'lastname')
                    ->selectRaw('CAST(iin AS CHAR) as iin')
                    ->where('id_mektep', '=', $request->id_mektep)
                    ->where('id_class', '=', $request->id_class)
                    ->orderBy('surname_latin')->orderBy('name_latin')
                    ->get()->toArray();
                break;
            case 'personal' :
                $users = DB::connection('mektep_edu')->table('mektep_personal')
                    ->select('id', 'surname', 'name', 'lastname', 'specialty')
                    ->selectRaw('CAST(iin AS CHAR) as iin')
                    ->where('id_mektep', '=', $request->id_mektep)
                    ->orderBy('surname')->orderBy('name')
                    ->get()->toArray();
                break;
        }

        return response()->json([
            "users" => $users
        ]);
    }

    public function checkIINPostKz(Request $request) {
        $iin = $request->iin;

        $curl = curl_init();

        $data = array(
            'iinBin' => $iin
        );

        $headers = array(
            'Content-Type: application/json',
            'Accept: application/json'
        );

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://post.kz/mail-app/api/checkIinBin',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => $headers,
        ));

        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        return response($response);
    }

    public function getClassesByMektep($id_mektep, Request $request) {
        $classes = DB::connection('mektep_edu')->table('mektep_class')
            ->select('id', 'class', 'group')
            ->where('id_mektep', '=', $request->id_mektep)
            ->orderBy('class', 'desc')->orderBy('group')
            ->get()->toArray();

        return response()->json([
            "class_list" => $classes
        ]);
    }

    public function checkLastNum(){
        $last_num = DB::table('cards')->select('card_number')->orderBy('card_number', 'desc')->first();
        return $last_num;
    }

    protected function setTableName($type){
        $tableName = null;
        switch ($type) {
            case 'teacher' :
                $tableName = 'mektep_teacher';
                break;
            case 'student' :
                $tableName = 'mektep_students';
                break;
            case 'personal' :
                $tableName = 'mektep_personal';
                break;
        }
        return $tableName;
    }



}
