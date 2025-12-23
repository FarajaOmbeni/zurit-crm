<?php

use App\Models\Product;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
    $this->manager = User::factory()->manager()->create();
    $this->teamMember = User::factory()->teamMember()->create();
});

// Index Tests
it('requires authentication to view products', function () {
    $this->getJson('/api/products')
        ->assertStatus(401);
});

it('allows authenticated user to view products', function () {
    Product::factory()->count(5)->create();

    $this->actingAs($this->teamMember)
        ->getJson('/api/products')
        ->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'price', 'category', 'is_active'],
            ],
        ]);
});

it('shows only active products by default', function () {
    $activeProduct = Product::factory()->create(['is_active' => true]);
    $inactiveProduct = Product::factory()->inactive()->create();

    $response = $this->actingAs($this->admin)
        ->getJson('/api/products')
        ->assertStatus(200);

    $productIds = collect($response->json('data'))->pluck('id')->toArray();
    expect($productIds)->toContain($activeProduct->id);
    expect($productIds)->not->toContain($inactiveProduct->id);
});

it('can include inactive products when requested', function () {
    $activeProduct = Product::factory()->create(['is_active' => true]);
    $inactiveProduct = Product::factory()->inactive()->create();

    $response = $this->actingAs($this->admin)
        ->getJson('/api/products?include_inactive=true')
        ->assertStatus(200);

    $productIds = collect($response->json('data'))->pluck('id')->toArray();
    expect($productIds)->toContain($activeProduct->id);
    expect($productIds)->toContain($inactiveProduct->id);
});

it('can search products by name', function () {
    Product::factory()->create(['name' => 'Premium Software Package']);
    Product::factory()->create(['name' => 'Basic Hardware Bundle']);

    $this->actingAs($this->admin)
        ->getJson('/api/products?search=Software')
        ->assertStatus(200)
        ->assertJsonFragment(['name' => 'Premium Software Package']);
});

it('can search products by category', function () {
    Product::factory()->create(['name' => 'Product 1', 'category' => 'software']);
    Product::factory()->create(['name' => 'Product 2', 'category' => 'software']);

    $this->actingAs($this->admin)
        ->getJson('/api/products?search=software')
        ->assertStatus(200)
        ->assertJsonCount(2, 'data');
});

it('can filter products by category', function () {
    Product::factory()->create(['category' => 'software']);
    Product::factory()->create(['category' => 'hardware']);
    Product::factory()->create(['category' => 'software']);

    $response = $this->actingAs($this->admin)
        ->getJson('/api/products?category=software')
        ->assertStatus(200);

    expect($response->json('data'))->toHaveCount(2);
});

it('orders products by name alphabetically', function () {
    Product::factory()->create(['name' => 'Zebra Product']);
    Product::factory()->create(['name' => 'Alpha Product']);
    Product::factory()->create(['name' => 'Beta Product']);

    $response = $this->actingAs($this->admin)
        ->getJson('/api/products')
        ->assertStatus(200);

    $names = collect($response->json('data'))->pluck('name')->toArray();
    expect($names[0])->toBe('Alpha Product');
    expect($names[1])->toBe('Beta Product');
    expect($names[2])->toBe('Zebra Product');
});

// Store Tests
it('can create a new product', function () {
    $productData = [
        'name' => 'New Product',
        'price' => 1999.99,
        'category' => 'software',
        'description' => 'A great product',
        'is_active' => true,
    ];

    $this->actingAs($this->admin)
        ->postJson('/api/products', $productData)
        ->assertStatus(201)
        ->assertJsonFragment([
            'name' => 'New Product',
            'price' => '1999.99',
            'category' => 'software',
        ]);

    $this->assertDatabaseHas('products', [
        'name' => 'New Product',
        'price' => 1999.99,
    ]);
});

it('validates required fields when creating product', function () {
    $this->actingAs($this->admin)
        ->postJson('/api/products', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'price']);
});

it('validates price is numeric and positive', function () {
    $this->actingAs($this->admin)
        ->postJson('/api/products', [
            'name' => 'Test Product',
            'price' => -100,
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['price']);
});

it('sets product as active by default', function () {
    $productData = [
        'name' => 'Default Active Product',
        'price' => 999,
    ];

    $response = $this->actingAs($this->admin)
        ->postJson('/api/products', $productData)
        ->assertStatus(201);

    expect($response->json('is_active'))->toBeTrue();
});

it('can create product with is_active set to false', function () {
    $productData = [
        'name' => 'Inactive Product',
        'price' => 999,
        'is_active' => false,
    ];

    $response = $this->actingAs($this->admin)
        ->postJson('/api/products', $productData)
        ->assertStatus(201);

    expect($response->json('is_active'))->toBeFalse();
});

// Show Tests
it('can view a specific product', function () {
    $product = Product::factory()->create();

    $this->actingAs($this->teamMember)
        ->getJson("/api/products/{$product->id}")
        ->assertStatus(200)
        ->assertJsonFragment([
            'id' => $product->id,
            'name' => $product->name,
        ]);
});

it('returns 404 for non-existent product', function () {
    $this->actingAs($this->admin)
        ->getJson('/api/products/99999')
        ->assertStatus(404);
});

// Update Tests
it('can update a product', function () {
    $product = Product::factory()->create([
        'name' => 'Original Name',
        'price' => 100,
    ]);

    $this->actingAs($this->admin)
        ->putJson("/api/products/{$product->id}", [
            'name' => 'Updated Name',
            'price' => 200,
        ])
        ->assertStatus(200)
        ->assertJsonFragment([
            'name' => 'Updated Name',
            'price' => '200.00',
        ]);

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'name' => 'Updated Name',
        'price' => 200,
    ]);
});

it('can deactivate a product', function () {
    $product = Product::factory()->create(['is_active' => true]);

    $this->actingAs($this->admin)
        ->putJson("/api/products/{$product->id}", [
            'is_active' => false,
        ])
        ->assertStatus(200);

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'is_active' => false,
    ]);
});

it('can reactivate a product', function () {
    $product = Product::factory()->inactive()->create();

    $this->actingAs($this->admin)
        ->putJson("/api/products/{$product->id}", [
            'is_active' => true,
        ])
        ->assertStatus(200);

    $this->assertDatabaseHas('products', [
        'id' => $product->id,
        'is_active' => true,
    ]);
});

it('validates price when updating product', function () {
    $product = Product::factory()->create();

    $this->actingAs($this->admin)
        ->putJson("/api/products/{$product->id}", [
            'price' => 'not-a-number',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['price']);
});

it('validates price is not negative when updating', function () {
    $product = Product::factory()->create();

    $this->actingAs($this->admin)
        ->putJson("/api/products/{$product->id}", [
            'price' => -50,
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['price']);
});

// Delete Tests
it('can delete a product', function () {
    $product = Product::factory()->create();

    $this->actingAs($this->admin)
        ->deleteJson("/api/products/{$product->id}")
        ->assertStatus(200)
        ->assertJson(['message' => 'Product deleted successfully']);

    $this->assertDatabaseMissing('products', ['id' => $product->id]);
});

it('returns 404 when deleting non-existent product', function () {
    $this->actingAs($this->admin)
        ->deleteJson('/api/products/99999')
        ->assertStatus(404);
});

// Pagination Tests
it('paginates products correctly', function () {
    Product::factory()->count(25)->create();

    $response = $this->actingAs($this->admin)
        ->getJson('/api/products?per_page=10')
        ->assertStatus(200);

    expect($response->json('data'))->toHaveCount(10);
    expect($response->json('total'))->toBe(25);
});

it('respects custom per_page parameter', function () {
    Product::factory()->count(20)->create();

    $response = $this->actingAs($this->admin)
        ->getJson('/api/products?per_page=5')
        ->assertStatus(200);

    expect($response->json('data'))->toHaveCount(5);
});
