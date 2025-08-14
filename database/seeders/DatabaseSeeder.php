<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Michael',
            'employeeID' => '12025',
            'password' => '00000',
            'role' => 'admin',
            'approved' => true,
        ]);
        User::factory()->create([
            'name' => 'Freddy',
            'employeeID' => '12345',
            'password' => '00000',
            'role' => 'user',
            'checked' => true,
        ]);
        User::factory()->create([
            'name' => 'Feisal',
            'employeeID' => '54321',
            'password' => '00000',
            'role' => 'user',
        ]);
        User::factory()->create([
            'name' => 'Zainal',
            'employeeID' => '99999',
            'password' => '00000',
            'role' => 'guest',
        ]);

        $this->call([
            EquipmentSeeder::class,
            UnitSeeder::class,
            MasterListSeeder::class,
            StandardSeeder::class,
            ResultSeeder::class,
        ]);
    }
}
