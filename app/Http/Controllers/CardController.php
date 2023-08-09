<?php

namespace App\Http\Controllers;

use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Users;

class CardController extends Controller
{
    /**
     * @throws ValidationException
     */

// Registration card NFC
    public function index(Request $request){

        $cardData = DB::table('cards_ready')
            ->where('card_number', $request->card_number)
            ->first();
        
        if ($cardData->nfc){
            return response()->json([
                "message" => "Старая карта!"
            ], 400);
        }
        if ($cardData) {
            DB::table('cards_ready')
                ->where('card_number', $request->card_number)
                ->update([
                    'nfc' => $request->nfc,
                    'updated_at' => now(),
                ]);

// Status updated to three
            $student = Users::where('iin', $cardData->iin)->first();

            if ($student) {
                $student->update(['status' => 3]);
            }else{
                return response()->json([
                    "message" => "Статус не обновлён!"
                ], 400);
            }
            return response("success", 200);
        } else {
            return response('error', 400);
        }
    }

// Get to name for card number
    public function actionStudentName(Request $request){

        if ($request->card_number != null) {
            $query = DB::table('cards_ready')->select("full_name", "nfc", 'iin')->where("card_number", "=", $request->card_number)->first();

            $status = Users::where('iin', '=', $query->iin)->select('status')->first();

            $result = $status->status;

            //Если
            if($result == '1' || $result == '3' || $result == '4'){
                return response(['error' => 'Сгенерируйте карту!'], 400);
            }
        }else{
            return response('Номер карты не найдена', 403);
        }

        if ($query){
            return response([
                "name" => $query->full_name,
                "nfc" => $query->nfc,
            ], 200);
        }else{
            return response("К этой карте никто не зарегистрировано", 400);
        }
    }

// Status updated to four
    public function actionDeliveryCard(Request $request){

        $data = $request->all();

        foreach ($data as $k) {
            $student = Users::where('iin', $k['iin'])->first();
            if ($student) {
                // Обновим столбец 'status' на значение 4
                $student->update(['status' => 4]);
            }else{
                return response()->json([
                    "message" => "Студент не найден"
                ], 404);
            }
        }
        return response()->json(["success"],200);
    }

// Status updated to five
    public function actionCompletedCard(Request $request){

        $student = Users::where('iin', $request->iin)->first();

        if ($student) {
            // Обновим столбец 'status' на значение 5
            $student->update(['status' => 5]);
            return response()->json(["success"],200);
        }else{
            return response()->json([
                "message" => "Студент не найден"
            ], 404);
        }
    }
}
