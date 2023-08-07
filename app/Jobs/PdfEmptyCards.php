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

class PdfEmptyCards implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    protected $start, $end;


    public function __construct($start, $end)
    {
        $this->start = $start;
        $this->end = $end;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $start = $this->start;
        $end = $this->end;

        $query = DB::table('cards')->select('card_number')->where('card_number', '>=',$start)->where('card_number', '<=', $end)->get();
        $mpdf = new Mpdf\Mpdf(['mode' => 'utf-8', 'format' => [54, 86]]);
        foreach ($query as $item) {
            $mpdf->AddPage();
            $mpdf->defaultfooterline = 0;
            print_r($item->card_number);
            $footer = '
                <div style="position: absolute;text-align:center; margin-bottom: 10px; right: 45px; bottom: -10px; font-family: "Segoe UI", Arial, sans-serif;">
                    <p style="font-size: 8px; bottom: 8px; font-weight: normal">000' . $item->card_number . '</p>
                    <barcode code="' . $item->card_number . '" type="C128A" class="barcode" size="0.8" error="M" disableborder="1"/>
                </div>';

            $mpdf->SetHtmlFooter($footer);
        }

        $mpdf->debug = true;
        $mpdf->Output('/home/tobirama/Desktop/PDFExample/'.$start.'-'.$end.'.pdf');
    }
}
