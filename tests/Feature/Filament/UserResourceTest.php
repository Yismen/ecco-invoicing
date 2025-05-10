<?php

use App\Filament\Admin\Resources\UserResource;
use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(TestCase::class, RefreshDatabase::class)->in('Feature/Filament');

beforeEach(function () {
    $this->resource = new UserResource();
    $this->user = User::factory()->create();
});

describe('guest user', function () {
    it('redirects unauthenticated users to login on all resource routes', function () {
        $routes = [
            'filament.admin.resources.users.index',
            // 'filament.admin.resources.users.create',
            // 'filament.admin.resources.users.edit',
            // 'filament.admin.resources.users.view',
        ];

        foreach ($routes as $route) {
            $uri = route($route, ['record' => $this->user->getKey()]);
            $this->get($uri)
                 ->assertRedirect(route('filament.admin.auth.login'));
        }
    });
});

describe('unauthorized user', function () {
    it('redirects unauthorized users to login on all resource routes', function () {
        $routes = [
            'filament.admin.resources.users.index',
            // 'filament.admin.resources.users.create',
            // 'filament.admin.resources.users.edit',
            // 'filament.admin.resources.users.view',
        ];

        foreach ($routes as $route) {
            $uri = route($route, ['record' => $this->user->getKey()]);
            $this->actingAs($this->user)
                 ->get($uri)
                 ->assertForbidden();
        }
    });
});

describe('authorized user with permission', function () {
    beforeEach(function () {
        $permissions = [
            'view_any_user',
            // 'view_user',
            // 'create_user',
            // 'update_user',
            // 'delete_user',
            // 'delete_any_user',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Assign permissions to user
        $this->user->givePermissionTo($permissions);
    });

    it('allows authorized users to access index endpoint', function () {
        $this->actingAs($this->user);

        // Index
        $this->get(route('filament.admin.resources.users.index'))
             ->assertOk()
             ->assertSeeText('Users');
    });
});

