<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\User;
use App\Roles;
use Carbon\Carbon;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $role1 = new Roles();
        // $role1->name = 'User';
        // $role1->description = 'Rol User';
        // $role1->created_at = Carbon::now();
        // $role1->save();

        // $role2 = new Roles();
        // $role2->name = 'Moderador';
        // $role2->description = 'Rol Moderador';
        // $role2->created_at = Carbon::now();
        // $role2->save();

        $role3 = new Roles();
        $role3->name = 'Superadmin';
        $role3->description = 'Rol Superadmin';
        $role3->created_at = Carbon::now();
        $role3->save();
    }
}
