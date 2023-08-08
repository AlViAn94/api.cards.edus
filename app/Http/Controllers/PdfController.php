<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use App\Services\UserService;
use App\Services\PdfService;
use Illuminate\Http\Request;
use App\Jobs\PdfEmptyCards;

class PdfController extends Controller
{
    protected $userService;
    
    protected $pdfService;

    public function __construct(UserService $userService, PdfService $pdfService)
    {
        $this->UserService = $userService;
        $this->PdfService = $pdfService;
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
