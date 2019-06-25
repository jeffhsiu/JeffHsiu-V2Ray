<?php

use App\User;
use Illuminate\Database\Seeder;

class SystemUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if ( !User::where('id', 0)->exists()) {
            $user = User::create([
                'name' => 'System',
                backpack_authentication_column() => 'system@jeffhsiu.com',
                'password' => bcrypt(env('APP_KEY')),
            ]);

            $user->update(['id' => 0]);
        }
    }
}
