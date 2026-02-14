<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

abstract class TestCase extends BaseTestCase
{
    public function userWithPermission(array $permissions, string $model): User
    {
        $user = User::factory()->create(['email' => str(str()->random())->append('@ecco.com.do')]);

        foreach ($permissions as $permission) {

            $permission = $permission.' '.str($model)->lower();

            Permission::create([
                'name' => $permission,
                'guard_name' => 'web',
            ]);

            $user->givePermissionTo($permission);
        }

        return $user;
    }

    public function superAdminUser(): User
    {
        $user = User::factory()->create(['email' => str(str()->random())->append('@ecco.com.do')]);

        $role = Role::create([
            'name' => 'super admin',
        ]);

        $user->assignRole($role);

        return $user;
    }
}
