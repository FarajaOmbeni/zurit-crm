<?php

use App\Models\Lead;
use App\Models\Product;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->manager = User::factory()->manager()->create();
    $this->teamMember = User::factory()->teamMember()->create(['manager_id' => $this->manager->id]);
    $this->product = Product::factory()->create();
});

// Index Tests
it('requires authentication to view leads', function () {
    $this->getJson('/api/leads')
        ->assertStatus(401);
});

it('allows authenticated user to view leads', function () {
    Lead::factory()->count(3)->create(['added_by' => $this->teamMember->id]);

    $this->actingAs($this->teamMember)
        ->getJson('/api/leads')
        ->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'company', 'email', 'status', 'value'],
            ],
        ]);
});

it('admin can view all leads', function () {
    Lead::factory()->count(5)->create();

    $response = $this->actingAs($this->admin)
        ->getJson('/api/leads')
        ->assertStatus(200);

    expect($response->json('data'))->toHaveCount(5);
});

it('team member can only view own leads', function () {
    $ownLead = Lead::factory()->create(['added_by' => $this->teamMember->id]);
    $otherLead = Lead::factory()->create(['added_by' => $this->admin->id]);

    $response = $this->actingAs($this->teamMember)
        ->getJson('/api/leads')
        ->assertStatus(200);

    $leadIds = collect($response->json('data'))->pluck('id')->toArray();
    expect($leadIds)->toContain($ownLead->id);
    expect($leadIds)->not->toContain($otherLead->id);
});

it('manager can view team leads', function () {
    $managerLead = Lead::factory()->create(['added_by' => $this->manager->id]);
    $teamLead = Lead::factory()->create(['added_by' => $this->teamMember->id]);
    $otherLead = Lead::factory()->create(['added_by' => $this->admin->id]);

    $response = $this->actingAs($this->manager)
        ->getJson('/api/leads')
        ->assertStatus(200);

    $leadIds = collect($response->json('data'))->pluck('id')->toArray();
    expect($leadIds)->toContain($managerLead->id);
    expect($leadIds)->toContain($teamLead->id);
    expect($leadIds)->not->toContain($otherLead->id);
});

it('excludes clients from leads by default', function () {
    $lead = Lead::factory()->create(['added_by' => $this->admin->id, 'is_client' => false]);
    $client = Lead::factory()->client()->create(['added_by' => $this->admin->id]);

    $response = $this->actingAs($this->admin)
        ->getJson('/api/leads')
        ->assertStatus(200);

    $leadIds = collect($response->json('data'))->pluck('id')->toArray();
    expect($leadIds)->toContain($lead->id);
    expect($leadIds)->not->toContain($client->id);
});

it('can filter leads by status', function () {
    $newLead = Lead::factory()->create(['added_by' => $this->admin->id, 'status' => 'new_lead']);
    $wonLead = Lead::factory()->won()->create(['added_by' => $this->admin->id]);

    $this->actingAs($this->admin)
        ->getJson('/api/leads?status=new_lead')
        ->assertStatus(200)
        ->assertJsonFragment(['id' => $newLead->id]);
});

it('can search leads by name, company, or email', function () {
    Lead::factory()->create([
        'added_by' => $this->admin->id,
        'company' => 'Acme Corp',
        'email' => 'contact@acme.com',
    ]);

    $this->actingAs($this->admin)
        ->getJson('/api/leads?search=Acme')
        ->assertStatus(200)
        ->assertJsonFragment(['company' => 'Acme Corp']);
});

// Store Tests
it('can create a new company lead', function () {
    $leadData = [
        'contact_type' => 'company',
        'company' => 'Test Company',
        'position' => 'CEO',
        'email' => 'test@company.com',
        'phone' => '123-456-7890',
        'city' => 'New York',
        'country' => 'USA',
        'source' => 'website',
        'sector' => 'technology',
        'value' => 50000,
    ];

    $this->actingAs($this->teamMember)
        ->postJson('/api/leads', $leadData)
        ->assertStatus(201)
        ->assertJsonFragment([
            'company' => 'Test Company',
            'email' => 'test@company.com',
            'status' => 'new_lead',
        ]);

    $this->assertDatabaseHas('leads', [
        'company' => 'Test Company',
        'added_by' => $this->teamMember->id,
    ]);
});

it('can create a new personal contact lead', function () {
    $leadData = [
        'contact_type' => 'personal',
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'phone' => '123-456-7890',
        'source' => 'referral',
    ];

    $this->actingAs($this->teamMember)
        ->postJson('/api/leads', $leadData)
        ->assertStatus(201)
        ->assertJsonFragment([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
});

it('validates required company name for company contacts', function () {
    $leadData = [
        'contact_type' => 'company',
        'email' => 'test@company.com',
    ];

    $this->actingAs($this->teamMember)
        ->postJson('/api/leads', $leadData)
        ->assertStatus(422)
        ->assertJsonValidationErrors(['company']);
});

it('validates required name for personal contacts', function () {
    $leadData = [
        'contact_type' => 'personal',
        'email' => 'test@example.com',
    ];

    $this->actingAs($this->teamMember)
        ->postJson('/api/leads', $leadData)
        ->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

it('prevents duplicate leads by email', function () {
    $existingLead = Lead::factory()->create([
        'email' => 'duplicate@example.com',
        'added_by' => $this->admin->id,
    ]);

    $this->actingAs($this->teamMember)
        ->postJson('/api/leads', [
            'contact_type' => 'company',
            'company' => 'New Company',
            'email' => 'duplicate@example.com',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

it('prevents duplicate leads by phone', function () {
    $existingLead = Lead::factory()->create([
        'phone' => '123-456-7890',
        'added_by' => $this->admin->id,
    ]);

    $this->actingAs($this->teamMember)
        ->postJson('/api/leads', [
            'contact_type' => 'company',
            'company' => 'New Company',
            'phone' => '123-456-7890',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['phone']);
});

it('associates lead with all active products by default', function () {
    $activeProduct1 = Product::factory()->create(['is_active' => true]);
    $activeProduct2 = Product::factory()->create(['is_active' => true]);
    $inactiveProduct = Product::factory()->inactive()->create();

    $leadData = [
        'contact_type' => 'company',
        'company' => 'Test Company',
        'email' => 'test@company.com',
    ];

    $response = $this->actingAs($this->teamMember)
        ->postJson('/api/leads', $leadData)
        ->assertStatus(201);

    $lead = Lead::find($response->json('id'));
    $productIds = $lead->products->pluck('id')->toArray();

    expect($productIds)->toContain($activeProduct1->id);
    expect($productIds)->toContain($activeProduct2->id);
    expect($productIds)->not->toContain($inactiveProduct->id);
});

it('can associate lead with specific products', function () {
    $product1 = Product::factory()->create();
    $product2 = Product::factory()->create();
    $product3 = Product::factory()->create();

    $leadData = [
        'contact_type' => 'company',
        'company' => 'Test Company',
        'email' => 'test@company.com',
        'product_ids' => [$product1->id, $product2->id],
    ];

    $response = $this->actingAs($this->teamMember)
        ->postJson('/api/leads', $leadData)
        ->assertStatus(201);

    $lead = Lead::find($response->json('id'));
    $productIds = $lead->products->pluck('id')->toArray();

    expect($productIds)->toContain($product1->id);
    expect($productIds)->toContain($product2->id);
    expect($productIds)->not->toContain($product3->id);
});

// Show Tests
it('can view a specific lead', function () {
    $lead = Lead::factory()->create(['added_by' => $this->teamMember->id]);

    $this->actingAs($this->teamMember)
        ->getJson("/api/leads/{$lead->id}")
        ->assertStatus(200)
        ->assertJsonFragment(['id' => $lead->id]);
});

it('loads lead with relationships', function () {
    $lead = Lead::factory()->create(['added_by' => $this->teamMember->id]);

    $this->actingAs($this->teamMember)
        ->getJson("/api/leads/{$lead->id}")
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
it('can update a lead', function () {
    $lead = Lead::factory()->create(['added_by' => $this->teamMember->id]);

    $this->actingAs($this->teamMember)
        ->putJson("/api/leads/{$lead->id}", [
            'company' => 'Updated Company',
            'value' => 75000,
        ])
        ->assertStatus(200)
        ->assertJsonFragment([
            'company' => 'Updated Company',
            'value' => '75000.00',
        ]);

    $this->assertDatabaseHas('leads', [
        'id' => $lead->id,
        'company' => 'Updated Company',
    ]);
});

it('validates status when updating lead', function () {
    $lead = Lead::factory()->create(['added_by' => $this->teamMember->id]);

    $this->actingAs($this->teamMember)
        ->putJson("/api/leads/{$lead->id}", [
            'status' => 'invalid_status',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['status']);
});

// Delete Tests
it('can delete a lead', function () {
    $lead = Lead::factory()->create(['added_by' => $this->teamMember->id]);

    $this->actingAs($this->teamMember)
        ->deleteJson("/api/leads/{$lead->id}")
        ->assertStatus(200)
        ->assertJson(['message' => 'Lead deleted successfully']);

    $this->assertDatabaseMissing('leads', ['id' => $lead->id]);
});

// Status Update Tests
it('can update lead status for a specific product', function () {
    $lead = Lead::factory()->create(['added_by' => $this->teamMember->id]);
    $product = Product::factory()->create();
    $lead->products()->attach($product->id, ['status' => 'new_lead', 'enrolled_at' => now()]);

    $this->actingAs($this->teamMember)
        ->patchJson("/api/leads/{$lead->id}/status", [
            'status' => 'negotiations',
            'product_id' => $product->id,
        ])
        ->assertStatus(200);

    $lead->refresh();
    expect($lead->getStatusForProduct($product->id))->toBe('negotiations');
});

it('converts lead to client when marked as won', function () {
    $lead = Lead::factory()->create([
        'added_by' => $this->teamMember->id,
        'is_client' => false,
    ]);
    $product = Product::factory()->create();
    $lead->products()->attach($product->id, ['status' => 'negotiations', 'enrolled_at' => now()]);

    $this->actingAs($this->teamMember)
        ->patchJson("/api/leads/{$lead->id}/status", [
            'status' => 'won',
            'product_id' => $product->id,
            'value' => 100000,
        ])
        ->assertStatus(200);

    $lead->refresh();
    expect($lead->is_client)->toBeTrue();
    expect($lead->status)->toBe('won');
    expect($lead->won_at)->not->toBeNull();
});

it('requires product_id when updating status', function () {
    $lead = Lead::factory()->create(['added_by' => $this->teamMember->id]);

    $this->actingAs($this->teamMember)
        ->patchJson("/api/leads/{$lead->id}/status", [
            'status' => 'won',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['product_id']);
});

// Mark as Won Tests
it('can mark lead as won', function () {
    $lead = Lead::factory()->create(['added_by' => $this->teamMember->id, 'is_client' => false]);

    $this->actingAs($this->teamMember)
        ->patchJson("/api/leads/{$lead->id}/mark-won", [
            'value' => 50000,
        ])
        ->assertStatus(200);

    $lead->refresh();
    expect($lead->status)->toBe('won');
    expect($lead->is_client)->toBeTrue();
    expect($lead->won_at)->not->toBeNull();
    expect((float) $lead->value)->toBe(50000.0);
});

// Mark as Lost Tests
it('can mark lead as lost with reason', function () {
    $lead = Lead::factory()->create(['added_by' => $this->teamMember->id]);

    $this->actingAs($this->teamMember)
        ->patchJson("/api/leads/{$lead->id}/mark-lost", [
            'lost_reason' => 'Budget constraints',
        ])
        ->assertStatus(200);

    $lead->refresh();
    expect($lead->status)->toBe('lost');
    expect($lead->lost_reason)->toBe('Budget constraints');
    expect($lead->actual_close_date)->not->toBeNull();
});

it('requires lost reason when marking as lost', function () {
    $lead = Lead::factory()->create(['added_by' => $this->teamMember->id]);

    $this->actingAs($this->teamMember)
        ->patchJson("/api/leads/{$lead->id}/mark-lost", [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['lost_reason']);
});

// Reassign Tests
it('admin can reassign leads to any user', function () {
    $lead = Lead::factory()->create(['added_by' => $this->teamMember->id]);
    $newUser = User::factory()->create();

    $this->actingAs($this->admin)
        ->patchJson("/api/leads/{$lead->id}/reassign", [
            'new_user_id' => $newUser->id,
        ])
        ->assertStatus(200);

    $lead->refresh();
    expect($lead->added_by)->toBe($newUser->id);
    expect($lead->reassigned_by)->toBe($this->admin->id);
    expect($lead->reassigned_at)->not->toBeNull();
});

it('stores original owner on first reassignment', function () {
    $lead = Lead::factory()->create(['added_by' => $this->teamMember->id, 'original_added_by' => null]);
    $newUser = User::factory()->create();

    $this->actingAs($this->admin)
        ->patchJson("/api/leads/{$lead->id}/reassign", [
            'new_user_id' => $newUser->id,
        ])
        ->assertStatus(200);

    $lead->refresh();
    expect($lead->original_added_by)->toBe($this->teamMember->id);
});

it('manager can only reassign to team members', function () {
    $lead = Lead::factory()->create(['added_by' => $this->teamMember->id]);
    $outsideUser = User::factory()->create(); // Not in manager's team

    $this->actingAs($this->manager)
        ->patchJson("/api/leads/{$lead->id}/reassign", [
            'new_user_id' => $outsideUser->id,
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['new_user_id']);
});

// Notes Tests
it('can add notes for a product', function () {
    $lead = Lead::factory()->create(['added_by' => $this->teamMember->id]);
    $product = Product::factory()->create();
    $lead->products()->attach($product->id, ['status' => 'new_lead', 'enrolled_at' => now()]);

    $this->actingAs($this->teamMember)
        ->postJson("/api/leads/{$lead->id}/products/{$product->id}/notes", [
            'note' => 'Called client, interested in demo',
        ])
        ->assertStatus(201)
        ->assertJsonFragment(['message' => 'Note added successfully.']);

    expect($lead->getNotesForProduct($product->id))->toContain('Called client, interested in demo');
});

it('can retrieve notes for a product', function () {
    $lead = Lead::factory()->create(['added_by' => $this->teamMember->id]);
    $product = Product::factory()->create();
    $lead->products()->attach($product->id, [
        'status' => 'new_lead',
        'notes' => 'Existing notes',
        'enrolled_at' => now(),
    ]);

    $this->actingAs($this->teamMember)
        ->getJson("/api/leads/{$lead->id}/products/{$product->id}/notes")
        ->assertStatus(200)
        ->assertJsonFragment(['notes' => 'Existing notes']);
});

// Kanban Tests
it('can retrieve kanban view data for a product', function () {
    $lead = Lead::factory()->create(['added_by' => $this->admin->id]);
    $product = Product::factory()->create();
    $lead->products()->attach($product->id, ['status' => 'negotiations', 'enrolled_at' => now()]);

    $this->actingAs($this->admin)
        ->getJson("/api/leads/kanban?product_id={$product->id}")
        ->assertStatus(200)
        ->assertJsonStructure([
            'leads',
            'statuses',
            'product_id',
            'product_name',
        ]);
});

it('groups leads by product-specific status in kanban', function () {
    $product = Product::factory()->create();

    $lead1 = Lead::factory()->create(['added_by' => $this->admin->id]);
    $lead1->products()->attach($product->id, ['status' => 'new_lead', 'enrolled_at' => now()]);

    $lead2 = Lead::factory()->create(['added_by' => $this->admin->id]);
    $lead2->products()->attach($product->id, ['status' => 'negotiations', 'enrolled_at' => now()]);

    $response = $this->actingAs($this->admin)
        ->getJson("/api/leads/kanban?product_id={$product->id}")
        ->assertStatus(200);

    $leads = $response->json('leads');
    expect($leads)->toHaveKey('new_lead');
    expect($leads)->toHaveKey('negotiations');
});

it('requires product_id for kanban view', function () {
    $this->actingAs($this->admin)
        ->getJson('/api/leads/kanban')
        ->assertStatus(422)
        ->assertJsonValidationErrors(['product_id']);
});
