<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use GuzzleHttp\Client;

class Teacher extends Model
{
    protected $fillable = [
        'id_mektep',
        'name',
        'surname',
        'lastname',
        'iin',
        'birthday',
        'pol',
        'pay',
        'curdate'
    ];
    public static function getTeacher($iin)
    {
        $client = new Client();
        $response = $client->get('https://mektep.edu.kz/api/v1/index.php', [
            'query' => [
                'action' => 'teacher',
                'auth' => $_ENV['AUTH_TOKEN'],
                'iin' => $iin
            ]
        ]);

        $data = $response->getBody()->getContents();
        $teacher = json_decode($data, true);

        return $teacher;
    }
}
