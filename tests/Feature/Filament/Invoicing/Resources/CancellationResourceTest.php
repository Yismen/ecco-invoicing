<?php

use App\Filament\Invoicing\Resources\CancellationResource;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Item;
use App\Models\Cancellation;
use App\Models\User;
use Filament\Facades\Filament;
use Spatie\Permission\Models\Permission;

describe('Cancellation Resource', function () {
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
        $this->model = Cancellation::factory()->create([
            'invoice_id' => $invoice->id,
        ]);

        $this->routes = [
            'index' => CancellationResource::getUrl('index'),
            // 'create' => CancellationResource::getUrl('create'),
            // 'edit' => CancellationResource::getUrl('edit', ['record' => $this->model->getRouteKey()]),
            // 'view' => CancellationResource::getUrl('view', ['record' => $this->model->getRouteKey()]),
        ];
    });

    it('redirects guests to login page', function ($route) {
        $this->get($this->routes[$route])
            ->assertRedirect(route('filament.invoicing.auth.login'));
    })->with([
        'index',
        // 'create',
        // 'edit',
        // 'view',
    ]);

    it('forbids unauthorized users to access', function ($route) {
        $this->actingAs($this->user)
            ->get($this->routes[$route])
            ->assertForbidden();
    })->with([
        'index',
        // 'create',
        // 'edit',
        // 'view',
    ]);

    describe('authorized users', function () {
        beforeEach(function () {

            $permissions = [
                'index' => 'viewAny',
                // 'create' => 'create',
                // 'edit' => 'update',
                // 'view' => 'view',
            ];

            foreach ($permissions as $route => $permission) {
                // $permission = str($permission)->append('Cancellation')->snake()->toString();
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
            // 'edit',
            // 'view',
        ]);

        it('show the correct table', function () {
            $this->actingAs($this->user)
                ->get($this->routes['index'])
                ->assertSeeText('Cancellations')
                ->assertSeeText($this->model->invoice->number)
                ->assertSeeText($this->model->amount)
                // ->assertSeeText($this->model->date)
                ->assertSeeText($this->model->reference);
        });
    });

    it('shows correct navigation sort', function () {
        expect(
            CancellationResource::getNavigationSort()
        )->toBe(7);
        // ->and(
        //     CancellationResource::getNavigationGroup()
        // )->toBe('Invoicing')
    });
});
