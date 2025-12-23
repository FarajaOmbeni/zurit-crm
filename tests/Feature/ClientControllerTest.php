<?php

use App\Models\Lead;
use App\Models\Product;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->manager = User::factory()->manager()->create();
    $this->teamMember = User::factory()->teamMember()->create(['manager_id' => $this->manager->id]);
});

// Index Tests
it('requires authentication to view clients', function () {
    $this->getJson('/api/clients')
        ->assertStatus(401);
});

it('allows authenticated user to view clients', function () {
    Lead::factory()->client()->count(3)->create(['added_by' => $this->teamMember->id]);

    $this->actingAs($this->teamMember)
        ->getJson('/api/clients')
        ->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'company', 'email', 'is_client'],
            ],
        ]);
});

it('admin can view all clients', function () {
    Lead::factory()->client()->count(5)->create();

    $response = $this->actingAs($this->admin)
        ->getJson('/api/clients')
        ->assertStatus(200);

    expect($response->json('data'))->toHaveCount(5);
});

it('team member can only view own clients', function () {
    $ownClient = Lead::factory()->client()->create(['added_by' => $this->teamMember->id]);
    $otherClient = Lead::factory()->client()->create(['added_by' => $this->admin->id]);

    $response = $this->actingAs($this->teamMember)
        ->getJson('/api/clients')
        ->assertStatus(200);

    $clientIds = collect($response->json('data'))->pluck('id')->toArray();
    expect($clientIds)->toContain($ownClient->id);
    expect($clientIds)->not->toContain($otherClient->id);
});

it('manager can view team clients', function () {
    $managerClient = Lead::factory()->client()->create(['added_by' => $this->manager->id]);
    $teamClient = Lead::factory()->client()->create(['added_by' => $this->teamMember->id]);
    $otherClient = Lead::factory()->client()->create(['added_by' => $this->admin->id]);

    $response = $this->actingAs($this->manager)
        ->getJson('/api/clients')
        ->assertStatus(200);

    $clientIds = collect($response->json('data'))->pluck('id')->toArray();
    expect($clientIds)->toContain($managerClient->id);
    expect($clientIds)->toContain($teamClient->id);
    expect($clientIds)->not->toContain($otherClient->id);
});

it('can filter clients by source', function () {
    Lead::factory()->client()->create([
        'added_by' => $this->admin->id,
        'source' => 'website',
    ]);
    Lead::factory()->client()->create([
        'added_by' => $this->admin->id,
        'source' => 'referral',
    ]);

    $this->actingAs($this->admin)
        ->getJson('/api/clients?source=website')
        ->assertStatus(200)
        ->assertJsonFragment(['source' => 'website']);
});

it('can search clients by name, company, or email', function () {
    Lead::factory()->client()->create([
        'added_by' => $this->admin->id,
        'company' => 'Acme Corp',
        'email' => 'contact@acme.com',
    ]);
    Lead::factory()->client()->create([
        'added_by' => $this->admin->id,
        'company' => 'Other Corp',
        'email' => 'info@other.com',
    ]);

    $this->actingAs($this->admin)
        ->getJson('/api/clients?search=Acme')
        ->assertStatus(200)
        ->assertJsonFragment(['company' => 'Acme Corp']);
});

it('can sort clients by company name ascending', function () {
    Lead::factory()->client()->create([
        'added_by' => $this->admin->id,
        'company' => 'Zebra Company',
    ]);
    Lead::factory()->client()->create([
        'added_by' => $this->admin->id,
        'company' => 'Alpha Company',
    ]);

    $response = $this->actingAs($this->admin)
        ->getJson('/api/clients?sort_by=company&sort_order=asc')
        ->assertStatus(200);

    $companies = collect($response->json('data'))->pluck('company')->toArray();
    expect($companies[0])->toBe('Alpha Company');
});

it('can sort clients by company name descending', function () {
    Lead::factory()->client()->create([
        'added_by' => $this->admin->id,
        'company' => 'Alpha Company',
    ]);
    Lead::factory()->client()->create([
        'added_by' => $this->admin->id,
        'company' => 'Zebra Company',
    ]);

    $response = $this->actingAs($this->admin)
        ->getJson('/api/clients?sort_by=company&sort_order=desc')
        ->assertStatus(200);

    $companies = collect($response->json('data'))->pluck('company')->toArray();
    expect($companies[0])->toBe('Zebra Company');
});

it('can sort clients by won_at date', function () {
    Lead::factory()->client()->create([
        'added_by' => $this->admin->id,
        'won_at' => now()->subDays(5),
    ]);
    Lead::factory()->client()->create([
        'added_by' => $this->admin->id,
        'won_at' => now()->subDays(1),
    ]);

    $response = $this->actingAs($this->admin)
        ->getJson('/api/clients?sort_by=won_at&sort_order=desc')
        ->assertStatus(200);

    $wonDates = collect($response->json('data'))->pluck('won_at')->toArray();
    // Most recent should be first
    expect(strtotime($wonDates[0]))->toBeGreaterThan(strtotime($wonDates[1]));
});

it('defaults to sorting by created_at descending', function () {
    $oldClient = Lead::factory()->client()->create([
        'added_by' => $this->admin->id,
        'created_at' => now()->subDays(5),
    ]);
    $newClient = Lead::factory()->client()->create([
        'added_by' => $this->admin->id,
        'created_at' => now(),
    ]);

    $response = $this->actingAs($this->admin)
        ->getJson('/api/clients')
        ->assertStatus(200);

    $clientIds = collect($response->json('data'))->pluck('id')->toArray();
    expect($clientIds[0])->toBe($newClient->id);
});

// Show Tests
it('can view a specific client', function () {
    $client = Lead::factory()->client()->create(['added_by' => $this->teamMember->id]);

    $this->actingAs($this->teamMember)
        ->getJson("/api/clients/{$client->id}")
        ->assertStatus(200)
        ->assertJsonFragment([
            'id' => $client->id,
            'is_client' => true,
        ]);
});

it('returns 404 for lead that is not a client', function () {
    $lead = Lead::factory()->create([
        'added_by' => $this->teamMember->id,
        'is_client' => false,
    ]);

    $this->actingAs($this->teamMember)
        ->getJson("/api/clients/{$lead->id}")
        ->assertStatus(404);
});

it('loads client with relationships', function () {
    $client = Lead::factory()->client()->create(['added_by' => $this->teamMember->id]);

    $this->actingAs($this->teamMember)
        ->getJson("/api/clients/{$client->id}")
        ->assertStatus(200)
        ->assertJsonStructure([
            'id',
            'added_by',
            'activities',
            'tasks',
            'products',
        ]);
});

// Update Tests
it('can update a client', function () {
    $client = Lead::factory()->client()->create(['added_by' => $this->teamMember->id]);

    $this->actingAs($this->teamMember)
        ->patchJson("/api/clients/{$client->id}", [
            'company' => 'Updated Client Company',
            'value' => 100000,
        ])
        ->assertStatus(200)
        ->assertJsonFragment([
            'company' => 'Updated Client Company',
            'value' => '100000.00',
        ]);

    $this->assertDatabaseHas('leads', [
        'id' => $client->id,
        'company' => 'Updated Client Company',
    ]);
});

it('cannot update a lead that is not a client', function () {
    $lead = Lead::factory()->create([
        'added_by' => $this->teamMember->id,
        'is_client' => false,
    ]);

    $this->actingAs($this->teamMember)
        ->patchJson("/api/clients/{$lead->id}", [
            'company' => 'Should Not Update',
        ])
        ->assertStatus(404);
});

it('validates email format when updating client', function () {
    $client = Lead::factory()->client()->create(['added_by' => $this->teamMember->id]);

    $this->actingAs($this->teamMember)
        ->patchJson("/api/clients/{$client->id}", [
            'email' => 'invalid-email',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

it('validates value is positive when updating client', function () {
    $client = Lead::factory()->client()->create(['added_by' => $this->teamMember->id]);

    $this->actingAs($this->teamMember)
        ->patchJson("/api/clients/{$client->id}", [
            'value' => -1000,
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['value']);
});

// Stats Tests
it('can retrieve client stats', function () {
    Lead::factory()->client()->count(5)->create(['added_by' => $this->admin->id]);
    Lead::factory()->count(3)->create(['added_by' => $this->admin->id, 'is_client' => false]);

    $response = $this->actingAs($this->admin)
        ->getJson('/api/clients/stats')
        ->assertStatus(200)
        ->assertJsonStructure([
            'avgProgress',
            'activeClients',
            'totalLeads',
            'completed',
        ]);

    expect($response->json('activeClients'))->toBe(5);
    expect($response->json('totalLeads'))->toBe(3);
});

it('calculates average progress correctly', function () {
    // Create leads with different statuses
    Lead::factory()->create([
        'added_by' => $this->admin->id,
        'status' => 'new_lead',
        'is_client' => false,
    ]); // 10%
    Lead::factory()->create([
        'added_by' => $this->admin->id,
        'status' => 'negotiations',
        'is_client' => false,
    ]); // 75%
    Lead::factory()->client()->create([
        'added_by' => $this->admin->id,
    ]); // 100% (won)

    $response = $this->actingAs($this->admin)
        ->getJson('/api/clients/stats')
        ->assertStatus(200);

    // Average: (10 + 75 + 100) / 3 = 61.67, rounded = 62
    expect($response->json('avgProgress'))->toBe(62);
});

it('team member sees only own stats', function () {
    Lead::factory()->client()->count(2)->create(['added_by' => $this->teamMember->id]);
    Lead::factory()->client()->count(3)->create(['added_by' => $this->admin->id]);

    $response = $this->actingAs($this->teamMember)
        ->getJson('/api/clients/stats')
        ->assertStatus(200);

    expect($response->json('activeClients'))->toBe(2);
});

it('manager sees team stats', function () {
    Lead::factory()->client()->count(2)->create(['added_by' => $this->manager->id]);
    Lead::factory()->client()->count(1)->create(['added_by' => $this->teamMember->id]);
    Lead::factory()->client()->count(3)->create(['added_by' => $this->admin->id]);

    $response = $this->actingAs($this->manager)
        ->getJson('/api/clients/stats')
        ->assertStatus(200);

    // Manager should see own (2) + team member (1) = 3
    expect($response->json('activeClients'))->toBe(3);
});

it('counts completed clients with actual_close_date', function () {
    Lead::factory()->client()->create([
        'added_by' => $this->admin->id,
        'actual_close_date' => now(),
    ]);
    Lead::factory()->client()->create([
        'added_by' => $this->admin->id,
        'actual_close_date' => now()->subDays(5),
    ]);
    Lead::factory()->client()->create([
        'added_by' => $this->admin->id,
        'actual_close_date' => null,
    ]);

    $response = $this->actingAs($this->admin)
        ->getJson('/api/clients/stats')
        ->assertStatus(200);

    expect($response->json('completed'))->toBe(2);
});

// Authorization Tests
it('prevents unauthorized access to other users clients', function () {
    $otherUserClient = Lead::factory()->client()->create(['added_by' => $this->admin->id]);

    $this->actingAs($this->teamMember)
        ->getJson("/api/clients/{$otherUserClient->id}")
        ->assertStatus(403);
});

it('prevents unauthorized update of other users clients', function () {
    $otherUserClient = Lead::factory()->client()->create(['added_by' => $this->admin->id]);

    $this->actingAs($this->teamMember)
        ->patchJson("/api/clients/{$otherUserClient->id}", [
            'company' => 'Unauthorized Update',
        ])
        ->assertStatus(403);
});

it('manager can access team member clients', function () {
    $teamClient = Lead::factory()->client()->create(['added_by' => $this->teamMember->id]);

    $this->actingAs($this->manager)
        ->getJson("/api/clients/{$teamClient->id}")
        ->assertStatus(200);
});

// Pagination Tests
it('paginates clients correctly', function () {
    Lead::factory()->client()->count(25)->create(['added_by' => $this->admin->id]);

    $response = $this->actingAs($this->admin)
        ->getJson('/api/clients?per_page=10')
        ->assertStatus(200);

    expect($response->json('data'))->toHaveCount(10);
    expect($response->json('total'))->toBe(25);
});
