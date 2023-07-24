<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use GuzzleHttp\Client;

class Students extends Model
{
    protected $fillable = [
        'id_mektep',
        'id_class',
        'name',
        'surname',
        'lastname',
        'iin',
        'birthday',
        'pol',
        'national',
        'parent_ata_id',
        'parent_ana_id',
        'pay',
        'curdate'
    ];

    public static function getSchoolBoy($iin)
    {
        $client = new Client();
        $response = $client->get('https://mektep.edu.kz/api/v1/index.php', [
            'query' => [
                'action' => 'studentone',
                'auth' => $_ENV['AUTH_TOKEN'],
                'iin' => $iin
            ]
        ]);


        $data = $response->getBody()->getContents();
        $school_boy = json_decode($data, true);
        return $school_boy;
    }
}
