<?php

use App\Models\User;
use App\Models\Payment;
use Filament\Facades\Filament;
use Spatie\Permission\Models\Permission;
use App\Filament\Invoicing\Resources\PaymentResource;

describe('Payment Resource', function() {
    beforeEach(function () {
        Filament::setCurrentPanel(
            Filament::getPanel('invoicing')
        );

        $this->user = User::factory()->create();
        $this->model = Payment::factory()->create();

        $this->routes = [
            'index' => PaymentResource::getUrl('index'),
            'create' => PaymentResource::getUrl('create'),
            'edit' => PaymentResource::getUrl('edit', ['record' => $this->model->getRouteKey()]),
            'view' => PaymentResource::getUrl('view', ['record' => $this->model->getRouteKey()]),
        ];
    });

    it('redirects guests to login page', function ( $route) {
        $this->get($this->routes[$route])
            ->assertRedirect(route('filament.invoicing.auth.login'));
    })->with([
        'index',
        'create',
        'edit',
        'view',
    ]);

    it('forbids unauthorized users to access', function ( $route) {
        $this->actingAs($this->user)
            ->get($this->routes[$route])
            ->assertForbidden();
    })->with([
        'index',
        'create',
        'edit',
        'view',
    ]);

    describe('authorized users', function() {
        beforeEach(function () {

            $permissions = [
                'index' => 'viewAny',
                'create' => 'create',
                'edit' => 'update',
                'view' => 'view',
            ];

            foreach ($permissions as $route => $permission) {
                Permission::create([
                    'name' => $permission,
                    'guard_name' => 'web',
                ]);
                $this->user->givePermissionTo($permission);
            }
        });

        it('can access client resource endpoints', function ( $route) {
            $this->actingAs($this->user)
                ->get($this->routes[$route])
                ->assertOk();
        })->with([
            'index',
            'create',
            'edit',
            'view',
        ]);
    });
});


