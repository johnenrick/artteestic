<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(UserTypeSeeder::class);
        Model::unguard();

        DB::table('users')->delete();

        $users = array(
                ['username' => 'ryan', 'email' => 'ryanchenkie@gmail.com', 'password' => Hash::make('secret'), 'user_type_id' => 1],
                ['username' => 'chris', 'email' => 'chris@scotch.io', 'password' => Hash::make('secret'), 'user_type_id' => 1],
                ['username' => 'holly', 'email' => 'holly@scotch.io', 'password' => Hash::make('secret'), 'user_type_id' => 1],
                ['username' => 'adnan', 'email' => 'adnan@scotch.io', 'password' => Hash::make('secret'), 'user_type_id' => 1],
        );

        // Loop through each user above and create the record for them in the database
        foreach ($users as $user)
        {
            User::create($user);
        }

        Model::reguard();

    }
}
