<?php

use App\Estudante;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EstudanteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(Estudante::class, 50)->create();
        DB::table('estudantes')->insert([
            'user_name' => 'sameoldcarlos',
            'name' => 'Carlos',
            'surname' => 'Alves',
            'email' => 'albertocarlos221@gmail.com',
            'password' => Hash::make('password'),
        ]);
    }
}
