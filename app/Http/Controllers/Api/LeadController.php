<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class LeadController extends Controller
{
    /**
     * Display a listing of leads.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = $this->getAuthorizedLeadsQuery($user);

        // Exclude clients by default unless explicitly requested
        if (!$request->boolean('include_clients')) {
            $query->where('is_client', false);
        }

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('source')) {
            $query->where('source', $request->source);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $leads = $query->with(['addedBy', 'products'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json($leads);
    }

    /**
     * Store a newly created lead.
     */
    public function store(Request $request): JsonResponse
    {
        // Determine contact type for conditional validation
        $contactType = $request->input('contact_type', 'company');

        $validated = $request->validate([
            'contact_type' => ['nullable', Rule::in(['company', 'personal'])],
            'name' => $contactType === 'personal'
                ? ['required', 'string', 'max:255'] // Required for personal contacts
                : ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'company' => $contactType === 'company'
                ? ['required', 'string', 'max:255'] // Required for company contacts
                : ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'sector' => ['nullable', 'string', 'max:255'],
            'source' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['new_lead', 'initial_outreach', 'follow_ups', 'negotiations', 'won', 'lost'])],
            'value' => ['nullable', 'numeric', 'min:0'],
            'product' => ['nullable', 'string', 'max:255'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['integer', 'exists:products,id'],
            'expected_close_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        // Check for duplicates by email or phone
        $email = $validated['email'] ?? null;
        $phone = $validated['phone'] ?? null;

        if ($email) {
            $existingByEmail = Lead::with('addedBy')->where('email', $email)->first();
            if ($existingByEmail) {
                $salesperson = $existingByEmail->addedBy?->name ?? 'Unknown';
                return response()->json([
                    'message' => 'A lead with this email already exists.',
                    'errors' => [
                        'email' => ["This email is already assigned to a lead belonging to {$salesperson}."],
                    ],
                ], 422);
            }
        }

        if ($phone) {
            $existingByPhone = Lead::with('addedBy')->where('phone', $phone)->first();
            if ($existingByPhone) {
                $salesperson = $existingByPhone->addedBy?->name ?? 'Unknown';
                return response()->json([
                    'message' => 'A lead with this phone number already exists.',
                    'errors' => [
                        'phone' => ["This phone number is already assigned to a lead belonging to {$salesperson}."],
                    ],
                ], 422);
            }
        }

        $lead = Lead::create([
            ...$validated,
            'added_by' => Auth::id(),
            'status' => $validated['status'] ?? 'new_lead',
        ]);

        // Associate lead with selected products (or all active products if none selected)
        $productIds = $validated['product_ids'] ?? [];

        if (empty($productIds)) {
            // If no products selected, associate with all active products
            $products = Product::where('is_active', true)->pluck('id');
        } else {
            // Use selected products
            $products = collect($productIds);
        }

        foreach ($products as $productId) {
            $lead->products()->attach($productId, [
                'status' => 'new_lead',
                'enrolled_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $lead->load(['addedBy', 'products']);

        return response()->json($lead, 201);
    }

    /**
     * Display the specified lead.
     */
    public function show(string $id): JsonResponse
    {
        $lead = Lead::with(['addedBy', 'activities.user', 'tasks.createdBy', 'followUpSchedules', 'products'])
            ->findOrFail($id);

        $this->authorize('view', $lead);

        return response()->json($lead);
    }

    /**
     * Update the specified lead.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $lead = Lead::findOrFail($id);
        $this->authorize('update', $lead);

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'company' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'sector' => ['nullable', 'string', 'max:255'],
            'source' => ['nullable', 'string', 'max:255'],
            'status' => ['sometimes', Rule::in(['new_lead', 'initial_outreach', 'follow_ups', 'negotiations', 'won', 'lost'])],
            'value' => ['nullable', 'numeric', 'min:0'],
            'product' => ['nullable', 'string', 'max:255'],
            'expected_close_date' => ['nullable', 'date'],
            'actual_close_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $lead->update($validated);
        $lead->load('addedBy');

        return response()->json($lead);
    }

    /**
     * Remove the specified lead.
     */
    public function destroy(string $id): JsonResponse
    {
        $lead = Lead::findOrFail($id);
        $this->authorize('delete', $lead);

        $lead->delete();

        return response()->json(['message' => 'Lead deleted successfully'], 200);
    }

    /**
     * Update lead status for a specific product (updates pivot table).
     */
    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $lead = Lead::findOrFail($id);
        $this->authorize('updateStatus', $lead);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['new_lead', 'initial_outreach', 'follow_ups', 'negotiations', 'won', 'lost'])],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'value' => ['nullable', 'numeric', 'min:0'],
        ]);

        $productId = $validated['product_id'];
        $status = $validated['status'];
        $dealValue = $validated['value'] ?? null;

        // Verify lead has this product
        if (!$lead->products()->where('products.id', $productId)->exists()) {
            return response()->json([
                'message' => 'Lead does not have this product assigned.',
            ], 422);
        }

        // Update status in pivot table using Lead model method
        $updated = $lead->updateStatusForProduct($productId, $status);

        // If status is 'won' and value is provided, update the pivot table value
        if ($status === 'won' && $dealValue !== null) {
            $lead->updateValueForProduct($productId, $dealValue);
            // Also update the main lead value for overall revenue tracking
            $lead->value = $dealValue;
        }

        if (!$updated) {
            return response()->json([
                'message' => 'Failed to update status.',
            ], 500);
        }

        // Also update the main lead status to keep them in sync
        // Manually handle is_client conversion for 'won' status (instead of relying on observer)
        try {
            $lead->status = $status;

            if ($status === 'won' && !$lead->is_client) {
                $lead->is_client = true;
                if (is_null($lead->won_at)) {
                    $lead->won_at = now();
                }
            }

            $lead->save();
        } catch (\Exception $e) {
            Log::error('Error updating lead status', [
                'lead_id' => $lead->id,
                'status' => $status,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'message' => 'Failed to update lead status: ' . $e->getMessage(),
            ], 500);
        }

        // Reload lead with product pivot data
        $lead->load(['addedBy', 'products' => function ($query) use ($productId) {
            $query->where('products.id', $productId);
        }]);

        // Get product pivot data
        $productPivot = $lead->products->first();
        $leadArray = $lead->toArray();

        // Add product pivot data to response
        if ($productPivot) {
            // Helper to safely format dates (handles both string and Carbon instances)
            $formatDate = function ($date) {
                if (!$date) return null;
                if (is_string($date)) return $date;
                return $date->format('Y-m-d');
            };

            $formatDateTime = function ($dateTime) {
                if (!$dateTime) return null;
                if (is_string($dateTime)) return $dateTime;
                return $dateTime->toIso8601String();
            };

            $leadArray['product_pivot'] = [
                'status' => $productPivot->pivot->status,
                'notes' => $productPivot->pivot->notes,
                'value' => $productPivot->pivot->value,
                'expected_close_date' => $formatDate($productPivot->pivot->expected_close_date),
                'actual_close_date' => $formatDate($productPivot->pivot->actual_close_date),
                'won_at' => $formatDateTime($productPivot->pivot->won_at),
                'lost_reason' => $productPivot->pivot->lost_reason,
            ];
        }

        return response()->json($leadArray);
    }

    /**
     * Mark lead as won (converts to client).
     */
    public function markAsWon(Request $request, string $id): JsonResponse
    {
        $lead = Lead::findOrFail($id);
        $this->authorize('markAsWon', $lead);

        $validated = $request->validate([
            'value' => ['nullable', 'numeric', 'min:0'],
            'actual_close_date' => ['nullable', 'date'],
        ]);

        if (isset($validated['value'])) {
            $lead->value = $validated['value'];
        }
        if (isset($validated['actual_close_date'])) {
            $lead->actual_close_date = $validated['actual_close_date'];
        }

        $lead->markAsWon();
        $lead->load('addedBy');

        return response()->json($lead);
    }

    /**
     * Mark lead as lost.
     */
    public function markAsLost(Request $request, string $id): JsonResponse
    {
        $lead = Lead::findOrFail($id);
        $this->authorize('markAsLost', $lead);

        $validated = $request->validate([
            'lost_reason' => ['required', 'string'],
            'actual_close_date' => ['nullable', 'date'],
        ]);

        $lead->markAsLost($validated['lost_reason']);

        if (isset($validated['actual_close_date'])) {
            $lead->actual_close_date = $validated['actual_close_date'];
            $lead->save();
        }

        $lead->load('addedBy');

        return response()->json($lead);
    }

    /**
     * Reassign lead to another user.
     */
    public function reassign(Request $request, string $id): JsonResponse
    {
        $lead = Lead::findOrFail($id);
        $this->authorize('reassign', $lead);

        $validated = $request->validate([
            'new_user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $currentUser = Auth::user();
        $newUserId = $validated['new_user_id'];

        // Verify the target user is valid for this reassignment
        if ($currentUser->isManager() && !$currentUser->isAdmin()) {
            // Manager can only reassign to their team members or themselves
            $allowedUserIds = $currentUser->teamMembers()->pluck('id')->push($currentUser->id);
            if (!$allowedUserIds->contains($newUserId)) {
                return response()->json([
                    'message' => 'You can only reassign leads to members of your team.',
                    'errors' => [
                        'new_user_id' => ['The selected user is not in your team.'],
                    ],
                ], 422);
            }
        }

        // Store original owner on first reassignment
        if (!$lead->original_added_by) {
            $lead->original_added_by = $lead->added_by;
        }

        // Update assignment
        $lead->added_by = $newUserId;
        $lead->reassigned_by = $currentUser->id;
        $lead->reassigned_at = now();
        $lead->save();

        $lead->load(['addedBy', 'originalAddedBy', 'reassignedBy', 'products']);

        return response()->json($lead);
    }

    /**
     * Get kanban view data (grouped by product-specific status).
     */
    public function kanban(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ]);

        $user = Auth::user();
        $productId = $request->input('product_id');

        // Get the product to retrieve its price
        $product = Product::find($productId);

        // Get authorized leads query
        $authorizedQuery = $this->getAuthorizedLeadsQuery($user);

        // Get lead IDs that the user is authorized to see
        $authorizedLeadIds = $authorizedQuery->pluck('id');

        // Get leads with this product, filtered by authorization
        // Exclude 'lost' leads, but include 'won' leads (they're still in the pipeline)
        $leads = Lead::whereIn('id', $authorizedLeadIds)
            ->whereHas('products', function ($query) use ($productId) {
                $query->where('products.id', $productId)
                    ->whereNotNull('lead_product.status')
                    ->whereNotIn('lead_product.status', ['lost']);
            })
            ->with(['addedBy', 'products' => function ($query) use ($productId) {
                $query->where('products.id', $productId);
            }])
            ->get();

        // Transform leads to include product-specific status from pivot
        $leadsWithPivotStatus = $leads->map(function ($lead) use ($productId) {
            $productPivot = $lead->products->first();
            $pivotStatus = $productPivot?->pivot->status ?? null;

            // Convert lead to array and add product-specific pivot data
            $leadArray = $lead->toArray();

            // Add product-specific pivot information
            if ($productPivot) {
                // Helper function to safely format dates
                $formatDate = function ($date) {
                    if (!$date) return null;
                    if (is_string($date)) return $date;
                    if (method_exists($date, 'format')) {
                        return $date->format('Y-m-d');
                    }
                    return $date;
                };

                $formatDateTime = function ($dateTime) {
                    if (!$dateTime) return null;
                    if (is_string($dateTime)) return $dateTime;
                    if (method_exists($dateTime, 'toIso8601String')) {
                        return $dateTime->toIso8601String();
                    }
                    if (method_exists($dateTime, 'format')) {
                        return $dateTime->format('c');
                    }
                    return $dateTime;
                };

                $leadArray['product_pivot'] = [
                    'status' => $productPivot->pivot->status,
                    'notes' => $productPivot->pivot->notes,
                    'value' => $productPivot->pivot->value,
                    'expected_close_date' => $formatDate($productPivot->pivot->expected_close_date),
                    'actual_close_date' => $formatDate($productPivot->pivot->actual_close_date),
                    'won_at' => $formatDateTime($productPivot->pivot->won_at),
                    'lost_reason' => $productPivot->pivot->lost_reason,
                    'enrolled_at' => $formatDateTime($productPivot->pivot->enrolled_at),
                    'product_name' => $productPivot->name, // Add product name to pivot data
                ];
                // Use pivot status for grouping
                $leadArray['pivot_status'] = $productPivot->pivot->status;
            } else {
                $leadArray['product_pivot'] = null;
                $leadArray['pivot_status'] = null;
            }

            return $leadArray;
        });

        // Group by pivot status (product-specific status), filtering out null statuses
        $grouped = $leadsWithPivotStatus
            ->filter(function ($lead) {
                return !empty($lead['pivot_status']);
            })
            ->groupBy('pivot_status')
            ->map(function ($group) {
                return $group->values();
            });

        return response()->json([
            'leads' => $grouped,
            'statuses' => ['new_lead', 'initial_outreach', 'follow_ups', 'negotiations', 'won'],
            'product_id' => $productId,
            'product_name' => $product?->name,
            'product_price' => $product?->price ? (float) $product->price : 0,
        ]);
    }

    /**
     * Get notes for a lead-product combination.
     */
    public function getNotes(Request $request, string $id, string $productId): JsonResponse
    {
        $lead = Lead::findOrFail($id);
        $this->authorize('view', $lead);

        // Validate product_id
        $productId = (int) $productId;
        if (!Product::where('id', $productId)->exists()) {
            return response()->json([
                'message' => 'Product not found.',
            ], 404);
        }

        // Verify lead has this product
        if (!$lead->products()->where('products.id', $productId)->exists()) {
            return response()->json([
                'message' => 'Lead does not have this product assigned.',
                'notes' => null,
            ], 200);
        }

        $notes = $lead->getNotesForProduct($productId);

        return response()->json([
            'lead_id' => $lead->id,
            'product_id' => $productId,
            'notes' => $notes,
        ]);
    }

    /**
     * Add a note for a lead-product combination.
     */
    public function addNote(Request $request, string $id, string $productId): JsonResponse
    {
        $lead = Lead::findOrFail($id);
        $this->authorize('update', $lead);

        $validated = $request->validate([
            'note' => ['required', 'string', 'max:10000'],
        ]);

        // Validate product_id
        $productId = (int) $productId;
        if (!Product::where('id', $productId)->exists()) {
            return response()->json([
                'message' => 'Product not found.',
            ], 404);
        }

        // Verify lead has this product
        if (!$lead->products()->where('products.id', $productId)->exists()) {
            return response()->json([
                'message' => 'Lead does not have this product assigned.',
            ], 422);
        }

        // Add note using Lead model method
        $updated = $lead->addNoteForProduct($productId, $validated['note']);

        if (!$updated) {
            return response()->json([
                'message' => 'Failed to add note.',
            ], 500);
        }

        // Reload lead with product pivot data
        $lead->load(['products' => function ($query) use ($productId) {
            $query->where('products.id', $productId);
        }]);

        $productPivot = $lead->products->first();

        return response()->json([
            'message' => 'Note added successfully.',
            'lead_id' => $lead->id,
            'product_id' => $productId,
            'notes' => $productPivot?->pivot->notes,
        ], 201);
    }

    /**
     * Get pipeline statistics for the header (product-specific).
     */
    public function pipelineStats(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ]);

        $user = Auth::user();
        $productId = $request->input('product_id');

        // Get authorized leads query
        $authorizedQuery = $this->getAuthorizedLeadsQuery($user);
        $authorizedLeadIds = $authorizedQuery->pluck('id');

        // Total Pipeline Value (sum of pivot values for active leads with this product)
        $totalPipeline = Lead::whereIn('id', $authorizedLeadIds)
            ->whereHas('products', function ($query) use ($productId) {
                $query->where('products.id', $productId)
                    ->whereNotNull('lead_product.status')
                    ->whereNotIn('lead_product.status', ['lost', 'won']);
            })
            ->with(['products' => function ($query) use ($productId) {
                $query->where('products.id', $productId);
            }])
            ->get()
            ->sum(function ($lead) {
                $productPivot = $lead->products->first();
                return $productPivot?->pivot->value ?? 0;
            });

        // Total Leads (leads with this product, excluding lost/won)
        $totalLeads = Lead::whereIn('id', $authorizedLeadIds)
            ->whereHas('products', function ($query) use ($productId) {
                $query->where('products.id', $productId)
                    ->whereNotNull('lead_product.status')
                    ->whereNotIn('lead_product.status', ['lost', 'won']);
            })
            ->count();

        // Closed this month (won leads this month for this product)
        $closedThisMonth = Lead::whereIn('id', $authorizedLeadIds)
            ->whereHas('products', function ($query) use ($productId) {
                $query->where('products.id', $productId)
                    ->where('lead_product.status', 'won')
                    ->whereMonth('lead_product.won_at', now()->month)
                    ->whereYear('lead_product.won_at', now()->year);
            })
            ->count();

        // Total this month (total pipeline value for leads created this month with this product)
        $totalThisMonth = Lead::whereIn('id', $authorizedLeadIds)
            ->whereHas('products', function ($query) use ($productId) {
                $query->where('products.id', $productId)
                    ->whereNotNull('lead_product.status')
                    ->whereNotIn('lead_product.status', ['lost', 'won']);
            })
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->with(['products' => function ($query) use ($productId) {
                $query->where('products.id', $productId);
            }])
            ->get()
            ->sum(function ($lead) {
                $productPivot = $lead->products->first();
                return $productPivot?->pivot->value ?? 0;
            });

        return response()->json([
            'totalPipeline' => number_format($totalPipeline, 0, '.', ','),
            'totalLeads' => $totalLeads,
            'closedThisMonth' => $closedThisMonth,
            'totalThisMonth' => number_format($totalThisMonth, 0, '.', ','),
        ]);
    }

    /**
     * Import leads from CSV data.
     */
    public function import(Request $request): JsonResponse
    {
        // Determine contact type for all imported leads
        $contactType = $request->input('contact_type', 'company');

        $validated = $request->validate([
            'contact_type' => ['nullable', Rule::in(['company', 'personal'])],
            'leads' => ['required', 'array'],
            'leads.*.name' => $contactType === 'personal'
                ? ['required', 'string', 'max:255']
                : ['nullable', 'string', 'max:255'],
            'leads.*.position' => ['nullable', 'string', 'max:255'],
            'leads.*.company' => $contactType === 'company'
                ? ['required', 'string', 'max:255']
                : ['nullable', 'string', 'max:255'],
            'leads.*.email' => ['nullable', 'email', 'max:255'],
            'leads.*.phone' => ['nullable', 'string', 'max:255'],
            'leads.*.city' => ['nullable', 'string', 'max:255'],
            'leads.*.country' => ['nullable', 'string', 'max:255'],
            'leads.*.source' => ['nullable', 'string', 'max:255'],
            'leads.*.sector' => ['nullable', 'string', 'max:255'],
        ]);

        $user = Auth::user();
        $imported = 0;
        $skipped = 0;
        $errors = [];
        $duplicates = [];

        foreach ($validated['leads'] as $index => $leadData) {
            try {
                // Check for duplicates by email or phone
                $email = $leadData['email'] ?? null;
                $phone = $leadData['phone'] ?? null;
                $duplicateFound = false;
                $duplicateReason = '';

                // Check for duplicate email
                if ($email) {
                    $existingByEmail = Lead::with('addedBy')->where('email', $email)->first();
                    if ($existingByEmail) {
                        $duplicateFound = true;
                        $salesperson = $existingByEmail->addedBy?->name ?? 'Unknown';
                        $duplicateReason = "Email already exists (belongs to {$salesperson})";
                    }
                }

                // Check for duplicate phone (if email check didn't find a duplicate)
                if (!$duplicateFound && $phone) {
                    $existingByPhone = Lead::with('addedBy')->where('phone', $phone)->first();
                    if ($existingByPhone) {
                        $duplicateFound = true;
                        $salesperson = $existingByPhone->addedBy?->name ?? 'Unknown';
                        $duplicateReason = "Phone already exists (belongs to {$salesperson})";
                    }
                }

                // Skip if duplicate found
                if ($duplicateFound) {
                    $skipped++;
                    // Use name for personal contacts, company for company contacts
                    $identifier = $contactType === 'personal'
                        ? ($leadData['name'] ?? 'Unknown')
                        : ($leadData['company'] ?? 'Unknown');
                    $duplicates[] = [
                        'row' => $index + 2, // +2 because index is 0-based and we skip header row
                        'company' => $identifier, // Used as display name regardless of type
                        'email' => $email,
                        'phone' => $phone,
                        'reason' => $duplicateReason,
                    ];
                    continue;
                }

                // Create lead
                $lead = Lead::create([
                    'contact_type' => $contactType,
                    'name' => $leadData['name'] ?? null,
                    'position' => $leadData['position'] ?? null,
                    'company' => $leadData['company'] ?? null,
                    'email' => $email,
                    'phone' => $phone,
                    'city' => $leadData['city'] ?? null,
                    'country' => $leadData['country'] ?? null,
                    'source' => $leadData['source'] ?? null,
                    'sector' => $leadData['sector'] ?? null,
                    'status' => 'new_lead',
                    'added_by' => $user->id,
                    'is_client' => false,
                ]);

                // Associate with all active products
                $products = Product::where('is_active', true)->pluck('id');
                if ($products->isEmpty()) {
                    // Log warning if no active products found
                    Log::warning('No active products found when importing lead', ['lead_id' => $lead->id]);
                } else {
                    foreach ($products as $productId) {
                        $lead->products()->attach($productId, [
                            'status' => 'new_lead',
                            'enrolled_at' => now(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                $imported++;
            } catch (\Exception $e) {
                Log::error('Error importing lead', [
                    'row' => $index + 2,
                    'company' => $leadData['company'] ?? 'Unknown',
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $errors[] = [
                    'row' => $index + 2, // +2 because index is 0-based and we skip header row
                    'company' => $leadData['company'] ?? 'Unknown',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'message' => 'Import completed',
            'imported' => $imported,
            'skipped' => $skipped,
            'total' => count($validated['leads']),
            'duplicates' => $duplicates,
            'errors' => $errors,
        ], 200);
    }

    /**
     * Export all leads to CSV.
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        $query = $this->getAuthorizedLeadsQuery($user);

        // Exclude clients by default (export only leads, not clients)
        if (!$request->boolean('include_clients')) {
            $query->where('is_client', false);
        }

        // Get all leads (no pagination for export)
        $leads = $query->with(['addedBy'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Define CSV headers
        $headers = [
            'Name',
            'Position',
            'Company',
            'Email',
            'Phone',
            'City',
            'Country',
            'Source',
            'Sector',
        ];

        // Generate CSV content
        $csvData = [];
        $csvData[] = implode(',', array_map(function ($header) {
            return '"' . str_replace('"', '""', $header) . '"';
        }, $headers));

        foreach ($leads as $lead) {
            $row = [
                $lead->name ?? '',
                $lead->position ?? '',
                $lead->company ?? '',
                $lead->email ?? '',
                $lead->phone ?? '',
                $lead->city ?? '',
                $lead->country ?? '',
                $lead->source ?? '',
                $lead->sector ?? '',
            ];

            $csvData[] = implode(',', array_map(function ($field) {
                return '"' . str_replace('"', '""', $field) . '"';
            }, $row));
        }

        $csvContent = implode("\n", $csvData);

        // Generate filename with timestamp
        $filename = 'leads_export_' . now()->format('Y-m-d_His') . '.csv';

        return response($csvContent)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->header('Content-Transfer-Encoding', 'binary');
    }

    /**
     * Get authorized leads query based on user role.
     */
    protected function getAuthorizedLeadsQuery($user)
    {
        $query = Lead::query();

        if ($user->isAdmin()) {
            return $query;
        }

        if ($user->isManager()) {
            $teamMemberIds = $user->teamMembers()->pluck('id');
            return $query->whereIn('added_by', $teamMemberIds->push($user->id));
        }

        return $query->where('added_by', $user->id);
    }
}
