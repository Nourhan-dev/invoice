<?php
  
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This method seeds the permissions into the database.
     * It creates a predefined list of permissions that can be 
     * assigned to roles within the application.
     *
     * @return void
     */
    public function run(): void
    {
        // Define the list of permissions to be created
        $permissions = [
            'role-list',     // Permission to list roles
            'role-create',   // Permission to create new roles
            'role-edit',     // Permission to edit existing roles
            'role-delete',   // Permission to delete roles
            'invoice-list',  // Permission to list invoices
            'invoice-create',// Permission to create new invoices
            'invoice-edit',  // Permission to edit existing invoices
            'invoice-delete' // Permission to delete invoices
        ];
        
        // Loop through each permission and create it in the database
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]); // Create each permission
        }
    }
}
