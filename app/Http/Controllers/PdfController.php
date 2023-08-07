<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Jobs\PdfEmptyCards;
use App\Services\UserService;
use App\Services\PdfService;

class PdfController extends Controller
{
    protected $UserService;
    
    protected $PdfService;

    public function __construct(UserService $UserService, PdfService $PdfService)
    {
        $this->UserService = $UserService;
        $this->PdfService = $PdfService;
    }
    public function index(Request $request){

        $start = $request->start;
        $end = $request->end;
        if ($this->isHave($start) === false || $this->isHave($end) === false){
            return response("Ошибка при проверке начальную или последнею цифру.");
        }
        PdfEmptyCards::dispatch($start, $end);
        return $request->all();
    }

    // Создаём новую карту
    public function actionSaveNewCard(Request $request){
        
        $processedData = $this->PdfService->cardUpdateAndSave($request);

        // Сохраняем нового пользователя или перезаписываем и увеличиваем перевыпуск на 1
        $this->UserService->saveNewUser($processedData);

        return response()->json(['massage' => $processedData]);
    }

    public function actionMassGeneratedSave(Request $request){

        $data = $request->all();

        $processedData = $this->PdfService->saveNewCardMass($data);

        return response()->json(['massage' => $processedData]);
    }

    public function actionMassPdfExportZip(Request $request){

        $data = $request->all();

        $processedData = $this->PdfService->massPdfExportService($data);

        return $processedData;
    }
}
