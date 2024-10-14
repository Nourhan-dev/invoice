<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class CreateAdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This method seeds the database with initial users, roles, and permissions.
     */
    public function run(): void
    {
        // Create Users
        $user1 = User::create([
            'name' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('123456'), // Hashing the password
        ]);

        $user2 = User::create([
            'name' => 'employee1',
            'email' => 'employee1@gmail.com',
            'password' => bcrypt('123456'), // Hashing the password
        ]);

        // Create Roles for web guard
        $roleAdminWeb = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $roleEmployeeWeb = Role::firstOrCreate(['name' => 'Employee', 'guard_name' => 'web']);

        // Create Roles for api guard
        $roleAdminApi = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'api']);
        $roleEmployeeApi = Role::firstOrCreate(['name' => 'Employee', 'guard_name' => 'api']);

        // Define Permissions for web guard
        $webPermissions = [
            ['name' => 'role-list', 'guard_name' => 'web'],
            ['name' => 'role-create', 'guard_name' => 'web'],
            ['name' => 'role-edit', 'guard_name' => 'web'],
            ['name' => 'role-delete', 'guard_name' => 'web'],
            ['name' => 'invoice-list', 'guard_name' => 'web'],
            ['name' => 'invoice-create', 'guard_name' => 'web'],
            ['name' => 'invoice-edit', 'guard_name' => 'web'],
            ['name' => 'invoice-delete', 'guard_name' => 'web'],
            ['name' => 'user-list', 'guard_name' => 'web'],
            ['name' => 'user-create', 'guard_name' => 'web'],
            ['name' => 'user-edit', 'guard_name' => 'web'],
            ['name' => 'user-delete', 'guard_name' => 'web'],
        ];

        // Define Permissions for api guard
        $apiPermissions = [
            ['name' => 'role-list', 'guard_name' => 'api'],
            ['name' => 'role-create', 'guard_name' => 'api'],
            ['name' => 'role-edit', 'guard_name' => 'api'],
            ['name' => 'role-delete', 'guard_name' => 'api'],
            ['name' => 'invoice-list', 'guard_name' => 'api'],
            ['name' => 'invoice-create', 'guard_name' => 'api'],
            ['name' => 'invoice-edit', 'guard_name' => 'api'],
            ['name' => 'invoice-delete', 'guard_name' => 'api'],
            ['name' => 'user-list', 'guard_name' => 'api'],
            ['name' => 'user-create', 'guard_name' => 'api'],
            ['name' => 'user-edit', 'guard_name' => 'api'],
            ['name' => 'user-delete', 'guard_name' => 'api'],
        ];

        // Insert Permissions for web
        foreach ($webPermissions as $permission) {
            Permission::firstOrCreate($permission); // Create permissions if they don't exist
        }

        // Insert Permissions for api
        foreach ($apiPermissions as $permission) {
            Permission::firstOrCreate($permission); // Create permissions if they don't exist
        }

        // Assign Permissions to Roles for web and api guards
        $roleAdminWeb->syncPermissions(Permission::where('guard_name', 'web')->get());
        $roleAdminApi->syncPermissions(Permission::where('guard_name', 'api')->get());

        // Assign Roles to Users
        $user1->assignRole($roleAdminWeb); // Assign "Admin" role to admin user
        $user2->assignRole($roleEmployeeWeb); // Assign "Employee" role to employee user

        // Insert relationships manually between roles and permissions for specific invoices
        $roleHasPermissionsInvoice = [
            ['permission_id' => 5, 'role_id' => 2], // Invoice list permission for employee (web)
            ['permission_id' => 7, 'role_id' => 2], // Invoice edit permission for employee (web)
            ['permission_id' => 17, 'role_id' => 4], // Invoice list permission for admin (api)
            ['permission_id' => 19, 'role_id' => 4], // Invoice edit permission for admin (api)
        ];

        // Insert directly into the pivot table for role_has_permissions
        DB::table('role_has_permissions')->insert($roleHasPermissionsInvoice);

        // Define the model-role relationships for users
        $modelHasRoles = [
            [
                'role_id' => 3, // ID for Admin role
                'model_type' => 'App\\Models\\User',
                'model_id' => 1, // ID for the admin user
            ],
            [
                'role_id' => 4, // ID for Employee role
                'model_type' => 'App\\Models\\User',
                'model_id' => 2, // ID for the employee user
            ],
        ];

        // Insert directly into the pivot table for model_has_roles
        DB::table('model_has_roles')->insert($modelHasRoles);
    }
}
