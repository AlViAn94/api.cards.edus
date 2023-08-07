<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Mpdf;

class PdfOneCard implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $student;
    protected $card_number;
    protected $status;

    public function __construct($student, $card_number, $status)
    {
        $this->student = $student;
        $this->card_number = $card_number;
        $this->status = $status;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $card_number = $this->card_number;
        $student = $this->student;
        $status = $this->status;
        $id = $student->id_mektep;

        $mpdf = new Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => [54, 86],
            'margin_top' => 15,
            'margin_left' => 0,
            'margin_right' => 0,
            'mirrorMargins' => false
        ]);


        $mpdf->AddPage();
        $code = $student->iin_md5;
        $nomer_code = $card_number;

        $html = '
            <link rel="stylesheet" href="/public/css/w3.css">
            <div style="text-align: center">
                <barcode code="' . $code . '" type="QR" class="barcode" size="1" error="M" disableborder="1" />
                <div style="text-align: left; margin-left: 55px;font-family: Segoe UI, Arial, sans-serif;">
                    <div style="margin-top: 60px;">
                        <p style="font-size: 10px; text-transform:uppercase; margin: 0; padding: 0;">'.$student->name.' '.$student->surname.'</p>
                        <p style="font-size: 10px; margin: 0; padding: 0;">NFC/RFID/QR/CODE128</p>
                    </div>
                </div>
            </div>
            ';

        $mpdf->defaultfooterline = 0;
        $footer = '
            <div style="position: absolute; margin-bottom: 10px; right: 26px; bottom: -10px; font-family: Segoe UI, Arial, sans-serif;">
                <p style="font-size: 8px; margin-left: 10px;">' . $nomer_code . '</p>
                <barcode code="'. $nomer_code .'" type="C128A" class="barcode" size="0.7" error="M" disableborder="1"/>
            </div>';

        $mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);

        $mpdf->SetHTMLFooter($footer);
        DB::table('cards_ready')->insert([
            'full_name' => $student->surname.' '.$student->name,
            'card_number' =>  $nomer_code,
            'status' => $status,
            'mektep_id' => $id
        ]);


        if(!file_exists(public_path('/PDF/SingleCards/mektepID'.$id.'/'.$status))){
            mkdir(public_path('/PDF/SingleCards/mektepID'.$id.'/'.$status), 0755, true);
        }

        $mpdf->Output(public_path('PDF/SingleCards/mektepID'.$id.'/'.$status.'/'.$student->iin.'.pdf'), 'F');
    }
}
