<?php

use App\Models\Agent;
use App\Models\Project;
use App\Rules\UniqueByParentRelationship;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->agent = Agent::factory()
        ->has(Project::factory()->count(1), 'project')
        ->create(['name' => 'Test Agent']);

    $this->builder = Agent::query();
});

it('parses the table and unique field correctly when the table name is passed', function () {
    $rule = new UniqueByParentRelationship(
        table: 'agents', // || 'agents
        uniqueField: 'name',
        parentField: 'project_id',
        parentId: $this->agent->project_id,
    );

    expect($rule->getTable())->toBe('agents');
});

it('parses the table and unique field correctly when the model class is passed', function () {
     $rule = new UniqueByParentRelationship(
        table: Agent::class, // || 'agents
        uniqueField: 'name',
        parentField: 'project_id',
        parentId: $this->agent->project_id,
    );

    expect($rule->getTable())->toBe('agents');
});

it('parses the table and unique field correctly when query builder instance is passed', function () {
     $rule = new UniqueByParentRelationship(
        table: Agent::query(), // || 'agents
        uniqueField: 'name',
        parentField: 'project_id',
        parentId: $this->agent->project_id,
    );

    expect($rule->getTable())->toBe('agents');
});

it('fails when parent ID is null', function () {
    expect(Validator::make(
        ['name' => 'Test Agent'],
        ['name' => new UniqueByParentRelationship(
            table: 'agents', // || 'agents
            uniqueField: 'name',
            parentField: 'project_id',
            parentId: null,
        )]
    )->fails())
        ->toBeTrue();
});

it('passes when not duplicated', function () {
    expect(Validator::make(
        ['name' => 'Different Agent'],
        ['name' => new UniqueByParentRelationship(
            table: 'agents', // || 'agents
            uniqueField: 'name',
            parentField: 'project_id',
            parentId: $this->agent->project_id,
        )]
    )->passes())
        ->toBeTrue();
});

it('fails when duplicated on the same parent', function () {
    expect(Validator::make(
        ['name' => 'Test Agent'],
        ['name' => new UniqueByParentRelationship(
            table: 'agents', // || 'agents
            uniqueField: 'name',
            parentField: 'project_id',
            parentId: $this->agent->project_id,
        )]
    )->fails())
        ->toBeTrue();
});

it('passes when duplicated but for another parent', function () {
    $anotherProject = Project::factory()->create();
    expect(Validator::make(
        ['name' => 'Test Agent'],
        ['name' => new UniqueByParentRelationship(
            table: 'agents', // || 'agents
            uniqueField: 'name',
            parentField: 'project_id',
            parentId: $anotherProject->id,
        )]
    )->passes())
        ->toBeTrue();
});

it('passes when duplicated but the record is the same', function () {
    expect(Validator::make(
        ['name' => 'Test Agent'],
        ['name' => new UniqueByParentRelationship(
            table: 'agents', // || 'agents
            uniqueField: 'name',
            parentField: 'project_id',
            parentId: $this->agent->project_id,
            recordToIgnore: $this->agent
        )]
    )->passes())
        ->toBeTrue();
});

it('fails when duplicated and the record is different', function () {
    $anotherAgent = Agent::factory()->create([
        'name' => 'Test Agent',
        'project_id' => $this->agent->project_id,
    ]);

    expect(Validator::make(
        ['name' => 'Test Agent'],
        ['name' => new UniqueByParentRelationship(
            table: 'agents', // || 'agents
            uniqueField: 'name',
            parentField: 'project_id',
            parentId: $this->agent->project_id,
            recordToIgnore: $anotherAgent
        )]
    )->fails())
        ->toBeTrue();
});
