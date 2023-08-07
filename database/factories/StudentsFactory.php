<?php

use Faker\Generator as Faker;
use App\Models\Students;

$factory->define(Students::class, function (Faker $faker) {
$statuses = [1, 2, 3, 4];
$steps = ['student', 'teacher', 'personal'];

return [
'id_mektep' => $faker->numberBetween(1, 100),
'id_class' => $faker->numberBetween(1, 100000),
'name' => $faker->firstName,
'surname' => $faker->lastName,
'lastname' => $steps[array_rand($steps)],
'iin' => $faker->numerify('###########'),
'birthday' => $faker->date('Y-m-d', '2014-07-20'),
'pol' => $faker->randomElement(['М', 'Ж']),
'national' => $faker->numberBetween(1, 100),
'parent_ata_id' => $faker->numberBetween(1, 100000),
'parent_ana_id' => $faker->numberBetween(1, 100000),
'paid' => $faker->numberBetween(1, 2),
'curdate' => $faker->date('Y-m-d', '2023-07-24'),
'created_at' => $faker->dateTimeThisYear,
'updated_at' => $faker->dateTimeThisYear,
'status' => $statuses[array_rand($statuses)],
'perevipusk' => null,
'step' => $steps[array_rand($steps)],
];
});
