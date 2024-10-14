<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * This method is responsible for calling all the individual seeders
     * that populate the database with initial data.
     *
     * @return void
     */
    public function run()
    {
        // Call individual seeders to seed the database
        $this->call([
            PermissionTableSeeder::class,    // Seeder for creating initial permissions
            CreateAdminUserSeeder::class,    // Seeder for creating the admin and employee users
            RoleHasPermissionsSeeder::class,  // Seeder for linking roles with their corresponding permissions
        ]);
    }
}
