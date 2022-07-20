<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Database\Seeders\UserSeeder;
use Database\Seeders\ConnectionTypesSeeder;
use Database\Seeders\RequestsSeeder;
use Database\Seeders\ConnectionsInCommonSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UserSeeder::class);
        $this->call(ConnectionTypesSeeder::class);
        $this->call(RequestsSeeder::class);
        $this->call(ConnectionsInCommonSeeder::class);
    }
}
