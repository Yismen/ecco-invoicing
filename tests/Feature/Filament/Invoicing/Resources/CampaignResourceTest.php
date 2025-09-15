<?php

use App\Filament\Invoicing\Resources\CampaignResource;
use App\Models\Campaign;
use App\Models\User;
use Filament\Facades\Filament;
use Spatie\Permission\Models\Permission;

describe('Campaign Resource', function () {
    beforeEach(function () {
        Filament::setCurrentPanel(
            Filament::getPanel('invoicing')
        );

        // $this->user = User::factory()->create();
        $this->model = Campaign::factory()->create();

        $this->routes = [
            'index' => CampaignResource::getUrl('index'),
            // 'create' => CampaignResource::getUrl('create'),
            // 'edit' => CampaignResource::getUrl('edit', ['record' => $this->model->getRouteKey()]),
            // 'view' => CampaignResource::getUrl('view', ['record' => $this->model->getRouteKey()]),
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
                // $permission = str($permission)->append('Campaign')->snake()->toString();
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
                ->assertSeeText('Campaigns')
                ->assertSeeText($this->model->name)
                ->assertSeeText($this->model->description);
        });
        // it('shows the create form', function () {
        //     $this->actingAs($this->user)
        //         ->get($this->routes['create'])
        //         ->assertSeeText('Create Campaign');
        // });
    });

    it('shows correct navigation sort', function () {
        expect(
            CampaignResource::getNavigationSort()
        )->toBe(4);
        // ->and(
        //     CampaignResource::getNavigationGroup()
        // )->toBe('Invoicing')
    });
});
