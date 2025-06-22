<?php

use App\Filament\Invoicing\Resources\ClientResource;
use App\Models\Client;
use App\Models\User;
use Filament\Facades\Filament;
use Spatie\Permission\Models\Permission;

describe('Client Resource', function () {
    beforeEach(function () {
        Filament::setCurrentPanel(
            Filament::getPanel('invoicing')
        );

        // $this->user = User::factory()->create();
        $this->model = Client::factory()->create();

        $this->routes = [
            'index' => ClientResource::getUrl('index'),
            'create' => ClientResource::getUrl('create'),
            'edit' => ClientResource::getUrl('edit', ['record' => $this->model->getRouteKey()]),
            'view' => ClientResource::getUrl('view', ['record' => $this->model->getRouteKey()]),
        ];
    });

    it('redirects guests to login page', function ($route) {
        $this->get($this->routes[$route])
            ->assertRedirect(route('filament.invoicing.auth.login'));
    })->with([
        'index',
        'create',
        'edit',
        'view',
    ]);

    it('forbids unauthorized users to access', function ($route) {
        $this->actingAs($this->user)
            ->get($this->routes[$route])
            ->assertForbidden();
    })->with([
        'index',
        'create',
        'edit',
        'view',
    ]);

    describe('authorized users', function () {
        beforeEach(function () {

            $permissions = [
                'index' => 'viewAny',
                'create' => 'create',
                'edit' => 'update',
                'view' => 'view',
            ];

            foreach ($permissions as $route => $permission) {
                // $permission = str($permission)->append('Client')->snake()->toString();
                Permission::create([
                    'name' => $permission,
                    'guard_name' => 'web',
                ]);
                $this->user->givePermissionTo($permission);
            }
        });

        it('can access client resource endpoints', function ($route) {
            $this->actingAs($this->user)
                ->get($this->routes[$route])
                ->assertOk();
        })->with([
            'index',
            'create',
            'edit',
            'view',
        ]);

        it('show the correct table', function () {
            $this->actingAs($this->user)
                ->get($this->routes['index'])
                ->assertSeeText('Clients')
                ->assertSeeText($this->model->name);
        });
        it('shows the create form', function () {
            $this->actingAs($this->user)
                ->get($this->routes['create'])
                ->assertSeeText('Create Client');
        });
    });

    it('shows correct navigation sort', function () {
        expect(
            ClientResource::getNavigationSort()
        )->toBe(1)
        // ->and(
        //     ClientResource::getNavigationGroup()
        // )->toBe('Invoicing')
        ;
    });

});
