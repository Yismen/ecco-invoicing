<?php

use App\Filament\Invoicing\Resources\AgentResource;
use App\Models\Agent;
use App\Models\User;
use Filament\Facades\Filament;
use Spatie\Permission\Models\Permission;

describe('Agent Resource', function () {
    beforeEach(function () {
        Filament::setCurrentPanel(
            Filament::getPanel('invoicing')
        );

        // $this->user = User::factory()->create();
        $this->model = Agent::factory()->create();

        $this->routes = [
            'index' => AgentResource::getUrl('index'),
            // 'create' => AgentResource::getUrl('create'),
            // 'edit' => AgentResource::getUrl('edit', ['record' => $this->model->getRouteKey()]),
            // 'view' => AgentResource::getUrl('view', ['record' => $this->model->getRouteKey()]),
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
        // dd('$this->user');
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
                // $permission = str($permission)->append('Agent')->snake()->toString();
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
                ->assertSeeText('Agents')
                ->assertSeeText($this->model->name)
                ->assertSeeText($this->model->email)
                ->assertSeeText($this->model->phone);
        });
        // it('shows the create form', function () {
        //     $this->actingAs($this->user)
        //         ->get($this->routes['create'])
        //         ->assertSeeText('Create Agent');
        // });
    });

    it('shows correct navigation sort', function () {
        expect(
            AgentResource::getNavigationSort()
        )->toBe(3);
        // ->and(
        //     AgentResource::getNavigationGroup()
        // )->toBe('Invoicing')
    });
});
