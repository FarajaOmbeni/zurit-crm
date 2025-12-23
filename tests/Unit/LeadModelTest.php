<?php

use App\Models\Lead;
use App\Models\Product;
use App\Models\User;

// Mark as Won Tests
it('marks lead as won and sets is_client to true', function () {
    $lead = Lead::factory()->create([
        'status' => 'negotiations',
        'is_client' => false,
    ]);

    $lead->markAsWon();

    expect($lead->status)->toBe('won');
    expect($lead->is_client)->toBeTrue();
    expect($lead->won_at)->not->toBeNull();
    expect($lead->actual_close_date)->not->toBeNull();
});

it('sets won_at timestamp when marking as won', function () {
    $lead = Lead::factory()->create();

    $lead->markAsWon();

    expect($lead->won_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

it('sets actual_close_date when marking as won', function () {
    $lead = Lead::factory()->create();

    $lead->markAsWon();

    expect($lead->actual_close_date)->toBeInstanceOf(\Illuminate\Support\Carbon::class);
});

// Mark as Lost Tests
it('marks lead as lost with reason', function () {
    $lead = Lead::factory()->create(['status' => 'negotiations']);

    $lead->markAsLost('Budget constraints');

    expect($lead->status)->toBe('lost');
    expect($lead->lost_reason)->toBe('Budget constraints');
    expect($lead->actual_close_date)->not->toBeNull();
});

it('does not set is_client when marking as lost', function () {
    $lead = Lead::factory()->create(['is_client' => false]);

    $lead->markAsLost('Not interested');

    expect($lead->is_client)->toBeFalse();
});

// Scope Tests
it('filters leads by status using byStatus scope', function () {
    Lead::factory()->create(['status' => 'new_lead']);
    Lead::factory()->create(['status' => 'negotiations']);
    Lead::factory()->create(['status' => 'won']);

    $negotiationLeads = Lead::byStatus('negotiations')->get();

    expect($negotiationLeads)->toHaveCount(1);
    expect($negotiationLeads->first()->status)->toBe('negotiations');
});

it('filters won leads using won scope', function () {
    Lead::factory()->won()->count(3)->create();
    Lead::factory()->create(['status' => 'negotiations']);

    $wonLeads = Lead::won()->get();

    expect($wonLeads)->toHaveCount(3);
    expect($wonLeads->every(fn($lead) => $lead->status === 'won'))->toBeTrue();
});

it('filters lost leads using lost scope', function () {
    Lead::factory()->lost()->count(2)->create();
    Lead::factory()->create(['status' => 'new_lead']);

    $lostLeads = Lead::lost()->get();

    expect($lostLeads)->toHaveCount(2);
    expect($lostLeads->every(fn($lead) => $lead->status === 'lost'))->toBeTrue();
});

it('filters active leads using active scope', function () {
    Lead::factory()->create(['status' => 'new_lead']);
    Lead::factory()->create(['status' => 'negotiations']);
    Lead::factory()->won()->create();
    Lead::factory()->lost()->create();

    $activeLeads = Lead::active()->get();

    expect($activeLeads)->toHaveCount(2);
    expect($activeLeads->every(fn($lead) => !in_array($lead->status, ['won', 'lost'])))->toBeTrue();
});

it('filters clients using clients scope', function () {
    Lead::factory()->client()->count(3)->create();
    Lead::factory()->count(2)->create(['is_client' => false]);

    $clients = Lead::clients()->get();

    expect($clients)->toHaveCount(3);
    expect($clients->every(fn($lead) => $lead->is_client === true))->toBeTrue();
});

it('filters leads using leads scope', function () {
    Lead::factory()->count(4)->create(['is_client' => false]);
    Lead::factory()->client()->count(2)->create();

    $leads = Lead::leads()->get();

    expect($leads)->toHaveCount(4);
    expect($leads->every(fn($lead) => $lead->is_client === false))->toBeTrue();
});

it('filters new leads using newLeads scope', function () {
    Lead::factory()->count(3)->create(['status' => 'new_lead']);
    Lead::factory()->create(['status' => 'negotiations']);

    $newLeads = Lead::newLeads()->get();

    expect($newLeads)->toHaveCount(3);
    expect($newLeads->every(fn($lead) => $lead->status === 'new_lead'))->toBeTrue();
});

// Product-Specific Methods Tests
it('can get status for a specific product', function () {
    $lead = Lead::factory()->create();
    $product = Product::factory()->create();
    $lead->products()->attach($product->id, [
        'status' => 'negotiations',
        'enrolled_at' => now(),
    ]);

    $status = $lead->getStatusForProduct($product->id);

    expect($status)->toBe('negotiations');
});

it('returns null for product status when product not associated', function () {
    $lead = Lead::factory()->create();
    $product = Product::factory()->create();

    $status = $lead->getStatusForProduct($product->id);

    expect($status)->toBeNull();
});

it('can get notes for a specific product', function () {
    $lead = Lead::factory()->create();
    $product = Product::factory()->create();
    $lead->products()->attach($product->id, [
        'status' => 'new_lead',
        'notes' => 'Important client notes',
        'enrolled_at' => now(),
    ]);

    $notes = $lead->getNotesForProduct($product->id);

    expect($notes)->toBe('Important client notes');
});

it('can update status for a specific product', function () {
    $lead = Lead::factory()->create();
    $product = Product::factory()->create();
    $lead->products()->attach($product->id, [
        'status' => 'new_lead',
        'enrolled_at' => now(),
    ]);

    $updated = $lead->updateStatusForProduct($product->id, 'negotiations');

    expect($updated)->toBeTrue();
    expect($lead->getStatusForProduct($product->id))->toBe('negotiations');
});

it('sets won_at when updating product status to won', function () {
    $lead = Lead::factory()->create();
    $product = Product::factory()->create();
    $lead->products()->attach($product->id, [
        'status' => 'negotiations',
        'enrolled_at' => now(),
    ]);

    $lead->updateStatusForProduct($product->id, 'won');

    $productPivot = $lead->products()->where('products.id', $product->id)->first();
    expect($productPivot->pivot->won_at)->not->toBeNull();
    expect($productPivot->pivot->actual_close_date)->not->toBeNull();
});

it('throws exception for invalid status when updating product', function () {
    $lead = Lead::factory()->create();
    $product = Product::factory()->create();
    $lead->products()->attach($product->id, [
        'status' => 'new_lead',
        'enrolled_at' => now(),
    ]);

    expect(fn() => $lead->updateStatusForProduct($product->id, 'invalid_status'))
        ->toThrow(\InvalidArgumentException::class);
});

it('can add notes for a specific product', function () {
    $lead = Lead::factory()->create();
    $product = Product::factory()->create();
    $lead->products()->attach($product->id, [
        'status' => 'new_lead',
        'enrolled_at' => now(),
    ]);

    $updated = $lead->addNoteForProduct($product->id, 'New note added');

    expect($updated)->toBeTrue();
    expect($lead->getNotesForProduct($product->id))->toContain('New note added');
});

it('appends notes when adding to existing notes', function () {
    $lead = Lead::factory()->create();
    $product = Product::factory()->create();
    $lead->products()->attach($product->id, [
        'status' => 'new_lead',
        'notes' => 'Existing notes',
        'enrolled_at' => now(),
    ]);

    $lead->addNoteForProduct($product->id, 'Additional notes');

    $notes = $lead->getNotesForProduct($product->id);
    expect($notes)->toContain('Existing notes');
    expect($notes)->toContain('Additional notes');
});

it('can replace notes for a specific product', function () {
    $lead = Lead::factory()->create();
    $product = Product::factory()->create();
    $lead->products()->attach($product->id, [
        'status' => 'new_lead',
        'notes' => 'Old notes',
        'enrolled_at' => now(),
    ]);

    $updated = $lead->setNotesForProduct($product->id, 'Completely new notes');

    expect($updated)->toBeTrue();
    expect($lead->getNotesForProduct($product->id))->toBe('Completely new notes');
});

it('can get value for a specific product', function () {
    $lead = Lead::factory()->create();
    $product = Product::factory()->create();
    $lead->products()->attach($product->id, [
        'status' => 'new_lead',
        'value' => 50000.50,
        'enrolled_at' => now(),
    ]);

    $value = $lead->getValueForProduct($product->id);

    expect($value)->toBe(50000.50);
});

it('can update value for a specific product', function () {
    $lead = Lead::factory()->create();
    $product = Product::factory()->create();
    $lead->products()->attach($product->id, [
        'status' => 'new_lead',
        'value' => 10000,
        'enrolled_at' => now(),
    ]);

    $updated = $lead->updateValueForProduct($product->id, 25000);

    expect($updated)->toBeTrue();
    expect($lead->getValueForProduct($product->id))->toBe(25000.0);
});

it('can mark specific product as won', function () {
    $lead = Lead::factory()->create();
    $product = Product::factory()->create();
    $lead->products()->attach($product->id, [
        'status' => 'negotiations',
        'enrolled_at' => now(),
    ]);

    $updated = $lead->markProductAsWon($product->id, 75000);

    expect($updated)->toBeTrue();
    expect($lead->getStatusForProduct($product->id))->toBe('won');
    expect($lead->getValueForProduct($product->id))->toBe(75000.0);

    $productPivot = $lead->products()->where('products.id', $product->id)->first();
    expect($productPivot->pivot->won_at)->not->toBeNull();
});

it('can mark specific product as lost with reason', function () {
    $lead = Lead::factory()->create();
    $product = Product::factory()->create();
    $lead->products()->attach($product->id, [
        'status' => 'negotiations',
        'enrolled_at' => now(),
    ]);

    $updated = $lead->markProductAsLost($product->id, 'Price too high');

    expect($updated)->toBeTrue();
    expect($lead->getStatusForProduct($product->id))->toBe('lost');
    expect($lead->getLostReasonForProduct($product->id))->toBe('Price too high');

    $productPivot = $lead->products()->where('products.id', $product->id)->first();
    expect($productPivot->pivot->actual_close_date)->not->toBeNull();
});

it('can get all product data for a lead', function () {
    $lead = Lead::factory()->create();
    $product = Product::factory()->create();
    $lead->products()->attach($product->id, [
        'status' => 'negotiations',
        'notes' => 'Test notes',
        'value' => 50000,
        'enrolled_at' => now(),
    ]);

    $productData = $lead->getProductData($product->id);

    expect($productData)->toBeArray();
    expect($productData['status'])->toBe('negotiations');
    expect($productData['notes'])->toBe('Test notes');
    expect($productData['value'])->toBe('50000.00');
});

it('returns null when getting product data for non-associated product', function () {
    $lead = Lead::factory()->create();
    $product = Product::factory()->create();

    $productData = $lead->getProductData($product->id);

    expect($productData)->toBeNull();
});

// Relationship Tests
it('belongs to user who added it', function () {
    $user = User::factory()->create();
    $lead = Lead::factory()->create(['added_by' => $user->id]);

    expect($lead->addedBy->id)->toBe($user->id);
});

it('can have many products', function () {
    $lead = Lead::factory()->create();
    $product1 = Product::factory()->create();
    $product2 = Product::factory()->create();

    $lead->products()->attach($product1->id, ['status' => 'new_lead', 'enrolled_at' => now()]);
    $lead->products()->attach($product2->id, ['status' => 'new_lead', 'enrolled_at' => now()]);

    expect($lead->products)->toHaveCount(2);
});

it('can track original owner when reassigned', function () {
    $originalUser = User::factory()->create();
    $newUser = User::factory()->create();

    $lead = Lead::factory()->create([
        'added_by' => $originalUser->id,
        'original_added_by' => null,
    ]);

    // Simulate reassignment
    $lead->original_added_by = $lead->added_by;
    $lead->added_by = $newUser->id;
    $lead->save();

    expect($lead->added_by)->toBe($newUser->id);
    expect($lead->original_added_by)->toBe($originalUser->id);
    expect($lead->originalAddedBy->id)->toBe($originalUser->id);
});
