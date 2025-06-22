<?php

use App\Models\Item;
use App\Models\User;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\InvoiceItem;
use Filament\Facades\Filament;
use Spatie\Permission\Models\Permission;
use App\Filament\Invoicing\Resources\PaymentResource;

describe('Payment Resource', function () {
    beforeEach(function () {
        Filament::setCurrentPanel(
            Filament::getPanel('invoicing')
        );

        $invoice = Invoice::factory()
            ->create();
        $item = Item::factory()->create(['price' => 500]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'item_id' => $item->id,
            'quantity' => 1,
            'item_price' => $item->price,
        ]);

        // $this->user = User::factory()->create();
        $this->model = Payment::factory()->create([
            'invoice_id' => $invoice->id,
            'amount' => 200.00,
        ]);

        $this->routes = [
            'index' => PaymentResource::getUrl('index'),
            // 'create' => PaymentResource::getUrl('create'),
            'edit' => PaymentResource::getUrl('edit', ['record' => $this->model->getRouteKey()]),
            'view' => PaymentResource::getUrl('view', ['record' => $this->model->getRouteKey()]),
        ];
    });

    it('redirects guests to login page', function ($route) {
        $this->get($this->routes[$route])
            ->assertRedirect(route('filament.invoicing.auth.login'));
    })->with([
        'index',
        // 'create',
        'edit',
        'view',
    ]);

    it('forbids unauthorized users to access', function ($route) {
        $this->actingAs($this->user)
            ->get($this->routes[$route])
            ->assertForbidden();
    })->with([
        'index',
        // 'create',
        'edit',
        'view',
    ]);

    describe('authorized users', function () {
        beforeEach(function () {

            $permissions = [
                'index' => 'viewAny',
                // 'create' => 'create',
                'edit' => 'update',
                'view' => 'view',
            ];

            foreach ($permissions as $route => $permission) {
                // $permission = str($permission)->append('Payment')->snake()->toString();
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
            // 'create',
            'edit',
            'view',
        ]);

        it('show the correct table', function () {
            $this->actingAs($this->user)
                ->get($this->routes['index'])
                ->assertSeeText('Payments')
                ->assertSeeText($this->model->invoice->number)
                ->assertSeeText($this->model->amount)
                // ->assertSeeText($this->model->date)
                ->assertSeeText($this->model->reference)
                ;
        });
    });

    it('shows correct navigation sort', function () {
        expect(
            PaymentResource::getNavigationSort()
        )->toBe(7)
        // ->and(
        //     PaymentResource::getNavigationGroup()
        // )->toBe('Invoicing')
        ;
    });
});
