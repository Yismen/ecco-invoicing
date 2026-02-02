<?php

use App\Models\User;
use App\Models\Agent;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Campaign;
use App\Enums\InvoiceStatuses;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    // Create test data
    $this->client = Client::factory()->create(['name' => 'Test Client']);
    $this->project = Project::factory()->create(['client_id' => $this->client->id]);
    $this->agent = Agent::factory()->create(['name' => 'Test Agent']);
    $this->campaign = Campaign::factory()->create(['name' => 'Test Campaign']);

    // Create authenticated user with proper token
    $this->user = User::factory()->create();
    $this->token = $this->user->createToken('test-token', ['read:invoices']);
});

it('requires authentication to access outstanding invoices endpoint', function () {
    $response = $this->getJson('/api/v1/invoices/outstanding');

    $response->assertStatus(401);
});

it('requires read:invoices scope to access outstanding invoices endpoint', function () {
    // Create token without required scope
    $tokenWithoutScope = $this->user->createToken('test-token-no-scope', ['other:scope']);

    $response = $this->withToken($tokenWithoutScope->plainTextToken)
        ->getJson('/api/v1/invoices/outstanding');

    $response->assertStatus(403);
});

it('returns all outstanding invoices with valid authentication', function () {
    // Create test invoices with different statuses
    Invoice::factory()->create([
        'project_id' => $this->project->id,
        'agent_id' => $this->agent->id,
        'campaign_id' => $this->campaign->id,
    ]);

    Invoice::factory()->create([
        'project_id' => $this->project->id,
        'agent_id' => $this->agent->id,
        'campaign_id' => $this->campaign->id,
    ]);

    Invoice::factory()->create([
        'project_id' => $this->project->id,
        'agent_id' => $this->agent->id,
        'campaign_id' => $this->campaign->id,
    ]);

    // This one should NOT be included (Paid status)
    $paidInvoice = Invoice::factory()->create([
        'project_id' => $this->project->id,
        'agent_id' => $this->agent->id,
        'campaign_id' => $this->campaign->id,
    ]);
    // Force it to paid status
    $paidInvoice->updateQuietly(['status' => InvoiceStatuses::Paid]);

    $response = $this->withToken($this->token->plainTextToken)
        ->getJson('/api/v1/invoices/outstanding');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'id',
                    'number',
                    'date',
                    'due_date',
                    'status',
                    'status_label',
                    'status_color',
                    'project_id',
                    'project_name',
                    'client_id',
                    'client_name',
                    'agent_id',
                    'agent_name',
                    'campaign_id',
                    'campaign_name',
                    'subtotal_amount',
                    'tax_amount',
                    'total_amount',
                    'total_paid',
                    'balance_pending',
                    'created_at',
                    'updated_at',
                ]
            ],
            'message',
            'count',
        ])
        ->assertJson([
            'success' => true,
        ]);

    // Verify paid invoice is NOT in results
    $ids = collect($response->json('data'))->pluck('id');
    expect($ids->contains($paidInvoice->id))->toBeFalse();
});

it('filters outstanding invoices by client_id', function () {
    $client1 = Client::factory()->create(['name' => 'Client 1']);
    $client2 = Client::factory()->create(['name' => 'Client 2']);

    $project1 = Project::factory()->create(['client_id' => $client1->id]);
    $project2 = Project::factory()->create(['client_id' => $client2->id]);

    // Create invoices for different clients
    Invoice::factory()->count(2)->create([
        'project_id' => $project1->id,
        'agent_id' => $this->agent->id,
        'campaign_id' => $this->campaign->id,
    ]);

    Invoice::factory()->count(3)->create([
        'project_id' => $project2->id,
        'agent_id' => $this->agent->id,
        'campaign_id' => $this->campaign->id,
    ]);

    $response = $this->withToken($this->token->plainTextToken)
        ->getJson("/api/v1/invoices/outstanding?client_id={$client1->id}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'count' => 2,
        ])
        ->assertJsonPath('data.0.client_id', $client1->id)
        ->assertJsonPath('data.0.client_name', 'Client 1');
});

it('filters outstanding invoices by campaign_id', function () {
    $campaign1 = Campaign::factory()->create(['name' => 'Campaign 1']);
    $campaign2 = Campaign::factory()->create(['name' => 'Campaign 2']);

    // Create invoices for different campaigns
    Invoice::factory()->count(2)->create([
        'project_id' => $this->project->id,
        'agent_id' => $this->agent->id,
        'campaign_id' => $campaign1->id,
    ]);

    Invoice::factory()->count(4)->create([
        'project_id' => $this->project->id,
        'agent_id' => $this->agent->id,
        'campaign_id' => $campaign2->id,
    ]);

    $response = $this->withToken($this->token->plainTextToken)
        ->getJson("/api/v1/invoices/outstanding?campaign_id={$campaign1->id}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'count' => 2,
        ])
        ->assertJsonPath('data.0.campaign_id', $campaign1->id)
        ->assertJsonPath('data.0.campaign_name', 'Campaign 1');
});

it('filters outstanding invoices by single date', function () {
    $targetDate = '2025-06-15';

    // Create invoices on different dates
    Invoice::factory()->create([
        'project_id' => $this->project->id,
        'agent_id' => $this->agent->id,
        'campaign_id' => $this->campaign->id,
        'date' => $targetDate,
    ]);

    Invoice::factory()->create([
        'project_id' => $this->project->id,
        'agent_id' => $this->agent->id,
        'campaign_id' => $this->campaign->id,
        'date' => '2025-06-20',
    ]);

    $response = $this->withToken($this->token->plainTextToken)
        ->getJson("/api/v1/invoices/outstanding?date={$targetDate}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'count' => 1,
        ])
        ->assertJsonPath('data.0.date', $targetDate);
});

it('filters outstanding invoices by date range', function () {
    $startDate = '2025-06-01';
    $endDate = '2025-06-30';

    // Create invoices within and outside the range
    Invoice::factory()->create([
        'project_id' => $this->project->id,
        'agent_id' => $this->agent->id,
        'campaign_id' => $this->campaign->id,
        'date' => '2025-06-10',
    ]);

    Invoice::factory()->create([
        'project_id' => $this->project->id,
        'agent_id' => $this->agent->id,
        'campaign_id' => $this->campaign->id,
        'date' => '2025-06-25',
    ]);

    // Outside the range
    Invoice::factory()->create([
        'project_id' => $this->project->id,
        'agent_id' => $this->agent->id,
        'campaign_id' => $this->campaign->id,
        'date' => '2025-07-05',
    ]);

    $response = $this->withToken($this->token->plainTextToken)
        ->getJson("/api/v1/invoices/outstanding?date={$startDate},{$endDate}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'count' => 2,
        ]);

    // Verify all dates are within range
    $dates = collect($response->json('data'))->pluck('date');
    expect($dates->every(function ($date) use ($startDate, $endDate) {
        return $date >= $startDate && $date <= $endDate;
    }))->toBeTrue();
});

it('combines multiple filters correctly', function () {
    $client = Client::factory()->create(['name' => 'Specific Client']);
    $project = Project::factory()->create(['client_id' => $client->id]);
    $campaign = Campaign::factory()->create(['name' => 'Specific Campaign']);

    // Create invoice matching all filters
    Invoice::factory()->create([
        'project_id' => $project->id,
        'agent_id' => $this->agent->id,
        'campaign_id' => $campaign->id,
        'date' => '2025-06-15',
    ]);

    // Create invoices not matching all filters
    Invoice::factory()->create([
        'project_id' => $this->project->id, // Different client
        'agent_id' => $this->agent->id,
        'campaign_id' => $campaign->id,
        'date' => '2025-06-15',
    ]);

    Invoice::factory()->create([
        'project_id' => $project->id,
        'agent_id' => $this->agent->id,
        'campaign_id' => $this->campaign->id, // Different campaign
        'date' => '2025-06-15',
    ]);

    $response = $this->withToken($this->token->plainTextToken)
        ->getJson("/api/v1/invoices/outstanding?client_id={$client->id}&campaign_id={$campaign->id}&date=2025-06-15");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'count' => 1,
        ])
        ->assertJsonPath('data.0.client_name', 'Specific Client')
        ->assertJsonPath('data.0.campaign_name', 'Specific Campaign');
});

it('excludes paid invoices from results', function () {
    // Create several invoices
    Invoice::factory()->count(4)->create([
        'project_id' => $this->project->id,
        'agent_id' => $this->agent->id,
        'campaign_id' => $this->campaign->id,
    ]);

    // Create one and force to paid status
    $paidInvoice = Invoice::factory()->create([
        'project_id' => $this->project->id,
        'agent_id' => $this->agent->id,
        'campaign_id' => $this->campaign->id,
    ]);
    $paidInvoice->updateQuietly(['status' => InvoiceStatuses::Paid]);

    $response = $this->withToken($this->token->plainTextToken)
        ->getJson('/api/v1/invoices/outstanding');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
        ]);

    // Ensure no paid invoices in response
    $statuses = collect($response->json('data'))->pluck('status');
    expect($statuses->contains('paid'))->toBeFalse();

    // Ensure paid invoice ID is not in results
    $ids = collect($response->json('data'))->pluck('id');
    expect($ids->contains($paidInvoice->id))->toBeFalse();
});

it('returns correct response structure for outstanding invoices', function () {
    Invoice::factory()->create([
        'project_id' => $this->project->id,
        'agent_id' => $this->agent->id,
        'campaign_id' => $this->campaign->id,
    ]);

    $response = $this->withToken($this->token->plainTextToken)
        ->getJson('/api/v1/invoices/outstanding');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data',
            'message',
            'count',
        ]);

    expect($response->json('success'))->toBeTrue();
    expect($response->json('message'))->toBe('Outstanding invoices retrieved successfully');
    expect($response->json('count'))->toBeGreaterThan(0);
});

it('replaces relation IDs with relation names in response', function () {
    Invoice::factory()->create([
        'project_id' => $this->project->id,
        'agent_id' => $this->agent->id,
        'campaign_id' => $this->campaign->id,
    ]);

    $response = $this->withToken($this->token->plainTextToken)
        ->getJson('/api/v1/invoices/outstanding');

    $response->assertStatus(200);

    $invoice = $response->json('data.0');

    // Verify both IDs and names are present
    expect($invoice)->toHaveKeys([
        'project_id',
        'project_name',
        'client_id',
        'client_name',
        'agent_id',
        'agent_name',
        'campaign_id',
        'campaign_name',
    ]);

    // Verify names match the created records
    expect($invoice['project_name'])->toBe($this->project->name);
    expect($invoice['client_name'])->toBe($this->client->name);
    expect($invoice['agent_name'])->toBe('Test Agent');
    expect($invoice['campaign_name'])->toBe('Test Campaign');
});

it('returns empty array when no outstanding invoices exist', function () {
    // Only create paid invoices
    $invoices = Invoice::factory()->count(3)->create([
        'project_id' => $this->project->id,
        'agent_id' => $this->agent->id,
        'campaign_id' => $this->campaign->id,
    ]);

    // Force all to paid status
    foreach ($invoices as $invoice) {
        $invoice->updateQuietly(['status' => InvoiceStatuses::Paid]);
    }

    $response = $this->withToken($this->token->plainTextToken)
        ->getJson('/api/v1/invoices/outstanding');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'data' => [],
            'count' => 0,
        ]);
});

it('returns all invoices when no filters are provided', function () {
    Invoice::factory()->count(5)->create([
        'project_id' => $this->project->id,
        'agent_id' => $this->agent->id,
        'campaign_id' => $this->campaign->id,
    ]);

    $response = $this->withToken($this->token->plainTextToken)
        ->getJson('/api/v1/invoices/outstanding');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'count' => 5,
        ]);
});
