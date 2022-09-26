<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Faker\Generator;

class UserSeeder extends Seeder
{
    /**
     * Create the initial roles and permissions.
     *
     * @return void
     */
    public function run()
    {
        $faker = app(Generator::class);

        // create Super Admin users
        $roleSuperAdmin=Role::create(['name' => 'super-admin']);
        $user = User::create([
            'name' => 'Super Administrator',
            'email' => "superadmin@demo.com",
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
            'password' =>Hash::make('123456'),
            'status' => 'active'
        ]);
        $user->assignRole($roleSuperAdmin);

        // create customer users
        $roleCustomer=Role::create(['name' => 'customer']);

        for($i=1; $i<=10; $i++){
            $user = User::create([
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'email_verified_at' => now(),
                'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
                'remember_token' => Str::random(10),
                'status' => 'active'
            ]);
            $user->assignRole($roleCustomer);
        }

    }
}
