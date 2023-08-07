<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use GuzzleHttp\Client;

class Users extends Model
{
    protected $table = 'users_status';
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
        'paid',
        'status',
        'position',
        'iin_md5',
        'mektep',
        'class_litter',
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
    public static function getSortUsers(Request $request)
    {

        $search = $request->input('search');
        $status = $request->input('status');
        $page = $request->input('page', 1);
        $sort = $request->input('sort');
        $asc = $request->input('asc');
        $pageSize = 10;

        if (empty($sort)) {
            $sort = 'created_at';
        }
        $users = Users::where(function ($query) use ($search, $status, $sort, $asc) {
            $query
                ->where('status', $status)
                ->where(function ($query) use ($search) {
                    $query
                        ->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('lastname', 'LIKE', "%{$search}%")
                        ->orWhere('surname', 'LIKE', "%{$search}%")
                        ->orWhere('mektep', 'LIKE', "%{$search}%")
                        ->orWhere('iin', 'LIKE', "%{$search}%")
                        ->orWhere('class_litter', 'LIKE', "%{$search}%");
                });
        })
            ->orderBy('status', 'asc') // Сортировка по статусу по возрастанию
            ->orderBy($sort, $asc ? 'asc' : 'desc') // Затем сортировка по другому полю
            ->paginate($pageSize, ['*'], 'page', $page);

        return $users;
        }
    }
