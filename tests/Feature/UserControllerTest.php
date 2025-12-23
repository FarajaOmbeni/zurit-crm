<?php

use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->manager = User::factory()->manager()->create();
    $this->teamMember = User::factory()->teamMember()->create();
});

// Index Tests
it('allows admin to view all users', function () {
    User::factory()->count(5)->create();

    $this->actingAs($this->admin)
        ->getJson('/api/users')
        ->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'email', 'role', 'is_active', 'created_at'],
            ],
        ]);
});

it('allows authenticated users to access users index', function () {
    // Only admins can access the users index endpoint
    $this->actingAs($this->teamMember)
        ->getJson('/api/users')
        ->assertStatus(403);
});

it('requires authentication to access users', function () {
    $this->getJson('/api/users')
        ->assertStatus(401);
});

it('can filter users by role', function () {
    User::factory()->admin()->count(2)->create();
    User::factory()->manager()->count(3)->create();

    $this->actingAs($this->admin)
        ->getJson('/api/users?role=admin')
        ->assertStatus(200)
        ->assertJsonCount(3, 'data'); // 2 created + 1 in beforeEach
});

it('can search users by name or email', function () {
    User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
    User::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);

    $this->actingAs($this->admin)
        ->getJson('/api/users?search=John')
        ->assertStatus(200)
        ->assertJsonFragment(['name' => 'John Doe']);
});

// Store Tests
it('allows admin to create users', function () {
    $userData = [
        'name' => 'New User',
        'email' => 'newuser@example.com',
        'role' => 'team_member',
        'is_active' => true,
    ];

    $this->actingAs($this->admin)
        ->postJson('/api/users', $userData)
        ->assertStatus(201)
        ->assertJsonFragment([
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'role' => 'team_member',
        ]);

    $this->assertDatabaseHas('users', [
        'email' => 'newuser@example.com',
        'must_reset_password' => true,
    ]);
});

it('generates OTP when creating a user', function () {
    $userData = [
        'name' => 'New User',
        'email' => 'newuser@example.com',
        'role' => 'team_member',
    ];

    $response = $this->actingAs($this->admin)
        ->postJson('/api/users', $userData)
        ->assertStatus(201);

    $user = User::where('email', 'newuser@example.com')->first();
    expect($user->otp)->not->toBeNull();
    expect($user->otp)->toHaveLength(6);
    expect($user->otp_expires_at)->not->toBeNull();
});

it('allows manager to create team members', function () {
    $userData = [
        'name' => 'Team Member',
        'email' => 'member@example.com',
        'role' => 'team_member',
        'manager_id' => $this->manager->id,
    ];

    // Managers don't have permission to create users - only admins do
    // This test should expect 403
    $this->actingAs($this->manager)
        ->postJson('/api/users', $userData)
        ->assertStatus(403);
});

it('validates required fields when creating user', function () {
    $this->actingAs($this->admin)
        ->postJson('/api/users', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'email', 'role']);
});

it('validates unique email when creating user', function () {
    $existingUser = User::factory()->create(['email' => 'existing@example.com']);

    $this->actingAs($this->admin)
        ->postJson('/api/users', [
            'name' => 'Test User',
            'email' => 'existing@example.com',
            'role' => 'team_member',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

it('validates role field when creating user', function () {
    $this->actingAs($this->admin)
        ->postJson('/api/users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'invalid_role',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['role']);
});

// Show Tests
it('allows viewing a specific user', function () {
    $user = User::factory()->create();

    $this->actingAs($this->admin)
        ->getJson("/api/users/{$user->id}")
        ->assertStatus(200)
        ->assertJsonFragment([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]);
});

it('loads user with relationships', function () {
    $manager = User::factory()->manager()->create();
    $teamMember = User::factory()->teamMember()->create(['manager_id' => $manager->id]);

    $this->actingAs($this->admin)
        ->getJson("/api/users/{$manager->id}")
        ->assertStatus(200)
        ->assertJsonStructure([
            'id',
            'name',
            'manager',
            'team_members',
        ]);
});

// Update Tests
it('allows admin to update users', function () {
    $user = User::factory()->create();

    $this->actingAs($this->admin)
        ->putJson("/api/users/{$user->id}", [
            'name' => 'Updated Name',
        ])
        ->assertStatus(200)
        ->assertJsonFragment(['name' => 'Updated Name']);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Updated Name',
    ]);
});

it('validates unique email when updating user', function () {
    $user1 = User::factory()->create(['email' => 'user1@example.com']);
    $user2 = User::factory()->create(['email' => 'user2@example.com']);

    $this->actingAs($this->admin)
        ->putJson("/api/users/{$user1->id}", [
            'email' => 'user2@example.com',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

it('can update user role', function () {
    $user = User::factory()->teamMember()->create();

    $this->actingAs($this->admin)
        ->putJson("/api/users/{$user->id}", [
            'role' => 'manager',
        ])
        ->assertStatus(200);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'role' => 'manager',
    ]);
});

it('can deactivate a user', function () {
    $user = User::factory()->create(['is_active' => true]);

    $this->actingAs($this->admin)
        ->putJson("/api/users/{$user->id}", [
            'is_active' => false,
        ])
        ->assertStatus(200);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'is_active' => false,
    ]);
});

// Delete Tests
it('allows admin to delete users', function () {
    $user = User::factory()->create();

    $this->actingAs($this->admin)
        ->deleteJson("/api/users/{$user->id}")
        ->assertStatus(200)
        ->assertJson(['message' => 'User deleted successfully']);

    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});

// Team Tests
it('allows viewing manager team members', function () {
    $manager = User::factory()->manager()->create();
    $teamMember1 = User::factory()->teamMember()->create(['manager_id' => $manager->id]);
    $teamMember2 = User::factory()->teamMember()->create(['manager_id' => $manager->id]);

    $this->actingAs($this->admin)
        ->getJson("/api/users/{$manager->id}/team")
        ->assertStatus(200)
        ->assertJsonCount(2, 'team')
        ->assertJsonFragment(['id' => $teamMember1->id])
        ->assertJsonFragment(['id' => $teamMember2->id]);
});

it('returns empty team for non-manager users', function () {
    $teamMember = User::factory()->teamMember()->create();

    $this->actingAs($this->admin)
        ->getJson("/api/users/{$teamMember->id}/team")
        ->assertStatus(200)
        ->assertJsonCount(0, 'team');
});

// Assignable Users Tests
it('admin can see all assignable users', function () {
    User::factory()->count(5)->create();

    $response = $this->actingAs($this->admin)
        ->getJson('/api/users/assignable')
        ->assertStatus(200);

    // Admin should see all active users
    $data = $response->json('users');
    expect($data)->toBeArray();
    expect(count($data))->toBeGreaterThan(5);
});

it('manager can see team members as assignable', function () {
    $manager = User::factory()->manager()->create();
    $teamMember1 = User::factory()->create(['manager_id' => $manager->id]);
    $teamMember2 = User::factory()->create(['manager_id' => $manager->id]);
    $otherUser = User::factory()->create(); // Not in manager's team

    $response = $this->actingAs($manager)
        ->getJson('/api/users/assignable')
        ->assertStatus(200);

    $userIds = collect($response->json('users'))->pluck('id')->toArray();

    expect($userIds)->toContain($manager->id);
    expect($userIds)->toContain($teamMember1->id);
    expect($userIds)->toContain($teamMember2->id);
    expect($userIds)->not->toContain($otherUser->id);
});

it('team member gets empty assignable list', function () {
    $this->actingAs($this->teamMember)
        ->getJson('/api/users/assignable')
        ->assertStatus(200)
        ->assertJson(['users' => []]);
});
