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

class PdfAdminCards implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;
    protected $count;
    protected $status;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data, $count, $status)
    {
        $this->data = $data;
        $this->count = $count;
        $this->status = $status;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $data = $this->data;
            $count = $this->count;
            $status = $this->status;

            $mpdf = new Mpdf\Mpdf([
                'mode' => 'utf-8',
                'format' => [54, 86],
                'margin_top' => 15,
                'margin_left' => 0,
                'margin_right' => 0,
                'mirrorMargins' => false
            ]);

            for ($i = 0; $i < 3; $i++) {
                $mpdf->AddPage();
                $name = $data->name_kaz;
                $nomer_code = $count;
                $html = '
                    <link rel="stylesheet" href="/public/css/w3.css">
                    <div style="text-align: center">
                        <div style="text-align: left; margin-left: 55px;font-family: Segoe UI, Arial, sans-serif;">
                            <p style="margin: 30px 20px 0 0; font-family: Segoe UI, Arial, sans-serif; font-size: 8px"> ' . $name . ' </p>
                            <p style="margin: 30px 20px 0 0; font-family: Segoe UI, Arial, sans-serif; font-size: 8px"> ' . $name . ' </p>
                            <div style="margin-top: 40px; ">
                                <p style="font-size: 10px; text-transform:uppercase; margin: 0; padding: 0;">Келуші</p>
                                <p style="font-size: 10px; text-transform:uppercase; margin: 0; padding: 0;">Посетитель</p>
                                <p style="font-size: 10px; text-transform:uppercase; margin: 0; padding: 0;">Visitor</p>
                                <p style="font-size: 48px; text-transform:uppercase; margin: 30px 0 0 0; padding: 0;">'.($i+1).'</p>
                            </div>
                        </div>
                    </div>
                    ';
                $mpdf->defaultfooterline = 0;
                $footer = '
                    <div style="position: absolute; margin-bottom: 10px; right: 26px; bottom: -10px; font-family: Segoe UI, Arial, sans-serif;">
                        <p style="font-size: 8px; margin-left: 10px;">' . $nomer_code . '</p>
                        <barcode code="' . $nomer_code . '" type="C128A" class="barcode" size="0.7" error="M" disableborder="1"/>
                    </div>';

                $mpdf->WriteHTML($html);
                $mpdf->SetHTMLFooter($footer);

                DB::table('cards_ready')->insert([
                    'full_name' => $data->name_kaz . "//" . $data->name_rus,
                    'iin' => $data->bin,
                    'user_id' => $data->id,
                    'card_number' => $count,
                    'status' => $status,
                    'mektep_id' => $data->id
                ]);

                $count++;
            }

            if (!file_exists(public_path('PDF/Admin/mektepN' . $data->id . '/' . $status))) {
                mkdir(public_path('PDF/Admin/mektepN' . $data->id . '/' . $status), 0755, true);
            }

            $mpdf->Output(public_path('PDF/Admin/mektepN' . $data->id . '/' . $status . '/' . $count . '.pdf'), 'F');
        }catch (\Exception $e){
            \Log::info($e->getMessage());
        }
    }
}
