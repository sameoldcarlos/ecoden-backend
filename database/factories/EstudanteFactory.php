<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Estudante;
use Faker\Generator as Faker;


$factory->define(Estudante::class, function (Faker $faker) {
    return [
        'user_name' => $faker->userName,
        'name' => $faker->name,
        'surname' => $faker->lastName,
        'email' => $faker->unique()->safeEmail,
        'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
    ];
});
