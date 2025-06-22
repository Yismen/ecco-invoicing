<?php

use App\Filament\Invoicing\Resources\ProjectResource;
use App\Models\Project;
use App\Models\User;
use Filament\Facades\Filament;
use Spatie\Permission\Models\Permission;

describe('Project Resource', function () {
    beforeEach(function () {
        Filament::setCurrentPanel(
            Filament::getPanel('invoicing')
        );
        $this->model = Project::factory()->create();

        $this->routes = [
            'index' => ProjectResource::getUrl('index'),
            'create' => ProjectResource::getUrl('create'),
            'edit' => ProjectResource::getUrl('edit', ['record' => $this->model->getRouteKey()]),
            // 'view' => ProjectResource::getUrl('view', ['record' => $this->model->getRouteKey()]),
        ];
    });

    it('redirects guests to login page', function ($route) {
        $this->get($this->routes[$route])
            ->assertRedirect(route('filament.invoicing.auth.login'));
    })->with([
        'index',
        'create',
        'edit',
        // 'view',
    ]);

    it('forbids unauthorized users to access', function ($route) {
        $this->actingAs($this->user)
            ->get($this->routes[$route])
            ->assertForbidden();
    })->with([
        'index',
        'create',
        'edit',
        // 'view',
    ]);

    describe('authorized users', function () {
        beforeEach(function () {
            $permissions = [
                'index' => 'viewAny',
                'create' => 'create',
                'edit' => 'update',
                // 'view' => 'view',
            ];

            foreach ($permissions as $route => $permission) {
                // $permission = str($permission)->append('Project')->snake()->toString();
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
            // 'view',
        ]);

        it('show the correct table', function () {
            $this->actingAs($this->user)
                ->get($this->routes['index'])
                ->assertSeeText('Projects')
                ->assertSeeText($this->model->name)
                ->assertSeeText($this->model->client->name)
                // ->assertSeeText($this->model->address)
                // ->assertSeeText($this->model->phone)
                // ->assertSeeText($this->model->email)
                // ->assertSeeText($this->model->tax_rate) // Assuming tax_rate is stored as a decimal;
                // ->assertSeeText($this->model->invoce_net_days)
                // ->assertSeeText($this->model->invoices_count)
                // ->assertSeeText($this->model->invoice_notes)
                // ->assertSeeText($this->model->invoice_terms)
                ;
        });
        it('shows the create form', function () {
            $this->actingAs($this->user)
                ->get($this->routes['create'])
                ->assertSeeText('Create Project');
        });
    });

    it('shows correct navigation sort', function () {
        expect(
            ProjectResource::getNavigationSort()
        )->toBe(2)
        // ->and(
        //     ProjectResource::getNavigationGroup()
        // )->toBe('Invoicing')
        ;
    });

});
