<?php

use App\Filament\Invoicing\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Models\User;
use Filament\Facades\Filament;
use Spatie\Permission\Models\Permission;

describe('Invoice Resource', function () {
    beforeEach(function () {
        Filament::setCurrentPanel(
            Filament::getPanel('invoicing')
        );

        // $this->user = User::factory()->create();
        $this->model = Invoice::factory()->create();

        $this->routes = [
            'index' => InvoiceResource::getUrl('index'),
            'create' => InvoiceResource::getUrl('create'),
            'edit' => InvoiceResource::getUrl('edit', ['record' => $this->model->getRouteKey()]),
            'view' => InvoiceResource::getUrl('view', ['record' => $this->model->getRouteKey()]),
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
                // $permission = str($permission)->append('Invoice')->snake()->toString();
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
    });

    it('shows correct navigation sort', function () {
        expect(
            InvoiceResource::getNavigationSort()
        )->toBe(6)
        // ->and(
        //     InvoiceResource::getNavigationGroup()
        // )->toBe('Invoicing')
        ;
    });
});
