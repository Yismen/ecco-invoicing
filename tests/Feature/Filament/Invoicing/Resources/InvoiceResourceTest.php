<?php

use App\Filament\Invoicing\Resources\Invoices\InvoiceResource;
use App\Models\Invoice;
use App\Models\User;
use Filament\Facades\Filament;

describe('Invoice Resource', function () {
    beforeEach(function () {
        Filament::setCurrentPanel(
            Filament::getPanel('invoicing')
        );

        $this->user = User::factory()->create();
        $this->model = Invoice::factory()->create();

        $this->routes = [
            'index' => InvoiceResource::getUrl('index'),
            'view' => InvoiceResource::getUrl('view', ['record' => $this->model->getRouteKey()]),
        ];
    });

    it('redirects guests to login page', function ($route) {
        $this->get($this->routes[$route])
            ->assertRedirect(route('filament.invoicing.auth.login'));
    })->with([
        'index',
        'view',
    ]);

    it('forbids unauthorized users to access', function ($route) {
        $this->actingAs($this->user)
            ->get($this->routes[$route])
            ->assertForbidden();
    })->with([
        'index',
        'view',
    ]);

    describe('authorized users', function () {
        beforeEach(function () {

            $permissions = [
                'index' => 'viewAny',
                'view' => 'view',
            ];

            $this->user = $this->userWithPermission(array_values($permissions), 'invoice');
        });

        it('can access client resource endpoints', function ($route) {
            $this->actingAs($this->user)
                ->get($this->routes[$route])
                ->assertOk();
        })->with([
            'index',
            'view',
        ]);
    });

    it('shows correct navigation sort', function () {
        expect(
            InvoiceResource::getNavigationSort()
        )->toBe(1);
        // ->and(
        //     InvoiceResource::getNavigationGroup()
        // )->toBe('Invoicing')
    });
});
