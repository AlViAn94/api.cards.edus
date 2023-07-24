<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use GuzzleHttp\Client;

class school extends Model
{
    public static function getSchoolAll(){

        $client = new Client();
        $response = $client->get('https://mektep.edu.kz/api/v1/index.php', [
            'query' => [
                'action' => 'schools',
                'auth' => $_ENV['AUTH_TOKEN']
            ]
        ]);

        $data = $response->getBody()->getContents();

        $schools = json_decode($data, true);

        return $schools;
    }
    public static function getRegion(){

        $client = new Client();
        $response = $client->get('https://mektep.edu.kz/api/v1/index.php', [
            'query' => [
                'action' => 'region',
                'auth' => $_ENV['AUTH_TOKEN']
            ]
        ]);

        $data = $response->getBody()->getContents();

        $region = json_decode($data, true);

        return $region;
    }
}
