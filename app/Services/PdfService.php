<?php

namespace App\Services;

use App\Http\Controllers\GenerateCardController;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use App\Services\UserService;
use Illuminate\Http\Request;
use App\Models\Users;
use ZipArchive;
use Mpdf;

class PdfService
{
    protected $userService;

    protected $generateCardController;

    public function __construct(UserService $userService, GenerateCardController $generateCardController)
    {
        $this->UserService = $userService;
        $this->GenerateCardController = $generateCardController;
    }

    public function createNewCard($card_number, $student, $position){

        $card_insert_id = DB::table('cards_ready')->insertGetId([
            'full_name' => $student['surname'].' '.$student['name'],
            'card_number' =>  $card_number,
            'iin' => $student['iin'],
            'user_id' => $student['id'],
            'status' => $position,
            'mektep_id' => $student['id_mektep'],

        ]);

        if (!isset($student['mektep_ids'])) {
            $student['mektep_ids'] = [$student['id_mektep']];
        }

        foreach ($student['mektep_ids'] as $mektep_id) {
            DB::table('mektep_id_cards')->insert([
                "cards_ready_id" => $card_insert_id,
                "card_number" => $card_number,
                "mektep_id" => $mektep_id,
                "created_at" => date("Y-m-d H:i:s"),
            ]);
        }

        return response()->json([
            "message" => "Карточка успешно добавлена!"
        ], 200);
    }
    public function getLastNum(){
        $last_num_ready = DB::table('cards_ready')
            ->select("card_number")
            ->where('card_number', '<', 9999999) // эта логика добавлена после того как мы допустили ошибку и теперь в таблице есть номер карты длиной больше семи, поэтому если у вам необходимо генерировать 8 значный номер то просто добавь еще один 9
            ->orderBy('card_number', 'desc')
            ->first();
        $last_num_empty = DB::table('cards')
            ->select('card_number')
            ->where('card_number', '<', 9999999)
            ->orderBy('card_number', 'desc')
            ->first();

        if ($last_num_empty > $last_num_ready){
            return $last_num_empty;
        }else{
            return $last_num_ready;
        }
    }

    // Массовая генерация карт
    public function saveNewCardMass($model){

        foreach ($model as $studentData) {
            switch ($studentData['position']){
                case 'student':
                    $studentQuery = DB::connection('mektep_edu')->table('mektep_students')
                        ->select('id', 'surname', 'name', 'id_mektep', 'iin', 'lastname', 'birthday', 'pol', 'national', 'parent_ata_id', 'parent_ana_id')
                        ->selectRaw('MD5(iin) as iin_md5');
                break;
                case 'teacher':
                    $studentQuery = DB::connection('mektep_edu')->table('mektep_teacher')
                        ->select('id', 'surname', 'name', 'id_mektep', 'iin', 'lastname', 'birthday', 'pol')
                        ->selectRaw('MD5(iin) as iin_md5');
                    break;
                case 'personal':
                    $studentQuery = DB::connection('mektep_edu')->table('mektep_personal')
                        ->select('id', 'surname', 'name', 'id_mektep', 'iin', 'lastname', 'birthday', 'pol')
                        ->selectRaw('MD5(iin) as iin_md5');
                    break;
            }



            if($studentData['iin']) {

                $studentQuery->where('iin', $studentData['iin']);
            }
            else {
                $studentQuery->where('id', $studentData['iin']);
            }

            $data = $studentQuery->first();

            $student = json_decode(json_encode($data), true);
            $student['position'] = $studentData['position'];
            $student['paid'] = '1';

            if ( !$student ) {
                return response()->json([
                    "message" => "Студент не найден"
                ], 404);
            }

// Сохраняем нового пользователя или перезаписываем и увеличиваем перевыпуск на 1

            $this->UserService->saveNewUser($student);

// Получаем последнюю активную карту

            $user_cards_query = DB::table("cards_ready")
                ->select('id', 'full_name', 'user_id', 'card_number', 'nfc', 'mektep_id', 'is_active', 'created_at')
                ->where('iin', '=' , $studentData['iin'])
                ->where('is_active', '=' , '1')
                ->get();

            $cardNumber = $user_cards_query->pluck('card_number')->first();

// Если нет карты
            if(empty($user_cards_query['0'])){
                $last = $this->getLastNum();
                $card_number = $last == null?2010000:$last->card_number+1;
                $this->createNewCard($card_number, $student, $student['position']);
                
                return response()->json([
                    "message" => "Генерация карточек успешно завершена!"
                ], 200);
            }

//  Меняем статус актив на 0
            $student_card = $user_cards_query->where('card_number', '=', $cardNumber)->first();

            if($student_card) {
                DB::table("cards_ready")
                    ->where('is_active', '=', 1)
                    ->where('card_number', '=', $cardNumber)
                    ->update(["is_active" => 0]);
            }

            $last = $this->getLastNum();
            $card_number = $last == null?2010000:$last->card_number+1;
            $this->createNewCard($card_number, $student, $student['position']);
        }

        return response()->json([
            "message" => "Генерация карточек успешно завершена!"
        ], 200);
    }

    public function massPdfExportService($data){

        if (count($data) === 1) {
            // Если только один пользователь, сразу генерируем и отправляем PDF
            $result = $this->mpdfCreated($data[0]);
            $pdfContents = $result['pdfContents'];
            $pdfName = 'file_user.pdf';
            $dbHost = env('APP_URL');
            // Формируем путь к файлу
            $zipLink['link'] = ($dbHost . $pdfName);

            // Создаём новый pdf файл или обновляем
            $pdfFilename = public_path('file_user.pdf');

            file_put_contents($pdfFilename, $pdfContents);

            return $zipLink;
        } else {
            // Иначе, создаем ZIP-архив
            $tempDir = sys_get_temp_dir() . '/pdf_temp';
            File::makeDirectory($tempDir, $mode = 0755, true, true);
            $zipName = 'pdf_files.zip';

            // Создаём новый zip файл или обновляем
            $zipFilename = public_path($zipName);

            $zip = new ZipArchive;
            if ($zip->open($zipFilename, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {

                return response()->json(['error' => 'Архив не создан!'], 400);
            }
// Массовая генерация
            foreach ($data as $k) {

                $result = $this->mpdfCreated($k);

                $pdfContents = $result['pdfContents'];
                $iin = $result['iin'];

                // Сохраняем PDF во временной директории
                $pdfFilename = $tempDir . '/' . $iin . '.pdf';

                file_put_contents($pdfFilename, $pdfContents);

                // Добавляем PDF в ZIP архив
                $zip->addFile($pdfFilename, $iin . '.pdf');
            }
                $zip->close();

            $dbHost = env('APP_URL');

            // Формируем путь к файлу
            $zipLink['link'] = ($dbHost . $zipName);

            return $zipLink;
            }
    }
    public function mpdfCreated($data){

            $studentData = $this->GenerateCardController->findUser($data);

            $result = $studentData->getData();

            $users = $result->users;
            $cards = $result->cards;

            foreach ($users as $user) {
                $surname = $user->surname;
                $name = $user->name;
            }

            foreach ($cards as $card) {
                $card_number = $card->card_number;
                $is_active = $card->is_active;
                $iin_md5 = $card->iin_md5;
                $iin = $card->iin;
            }
            if($is_active != 1){
                return response()->json(['error' => 'Нет активных карт!'], 400);
            }
        $defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new Mpdf\Mpdf([
            'mode' => 'utf=-8',
            'format' => [54, 86],
            'margin_top' => 15,
            'margin_left' => 0,
            'margin_right' => 0,
            'mirrorMargins' => false,
            'fontDir' => array_merge($fontDirs, [
                public_path('font'),
            ]),
            'fontdata' => $fontData + [ // lowercase letters only in font key
                    "dejavusanscondensed" => [
                        'R' => "DejaVuSansCondensed.ttf",
                        'B' => "DejaVuSansCondensed-Bold.ttf",
                        'I' => "DejaVuSansCondensed-Oblique.ttf",
                        'BI' => "DejaVuSansCondensed-BoldOblique.ttf",
                        'useOTL' => 0xFF,
                        'useKashida' => 75,
                    ]
                ],
            'default_font' => 'robotocondensed'
        ]);

        $mpdf->AddPage();

        $mpdf->defaultfooterline = 0;
        $html = '
            <link rel="stylesheet" href="'.(config('app.env') == 'production' ? "/cards_edu/public/css/w3.css" : "/public/css/w3.css").'">
            <div style="text-align: center">
                <barcode code="' . $iin_md5 . '" type="QR" class="barcode" size="1" error="M" disableborder="1" />
                <div style="text-align: left; margin-left: 55px;font-family: robotocondensed;">
                    <div style="margin-top: 60px;">
                        <p style="font-size: 10px; text-transform:uppercase; margin: 0; padding: 0;">'.$name.' '.$surname.'</p>
                        <p style="font-size: 10px; margin: 0; padding: 0;">NFC/RFID/QR/CODE128</p>
                    </div>
                </div>
            </div>
        ';


        $footer = '
            <div style="position: absolute; margin-bottom: 10px; right: 26px; bottom: -10px; font-family: robotocondensed;">
                <p style="font-size: 8px; margin-left: 10px;">' . $card_number . '</p>
                <barcode code="'. $card_number .'" type="C128A" class="barcode" size="0.7" error="M" disableborder="1"/>
            </div>';

        $mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);

        $mpdf->SetHTMLFooter($footer);

        $pdfContents = $mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN);

        $mpdf = null;

        return [
            "pdfContents" => $pdfContents,
            "iin" => $iin
        ];
    }
}