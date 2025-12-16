<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ProductSelectionController extends Controller
{
    /**
     * Display the product selection page.
     */
    public function index(Request $request): Response
    {
        $user = Auth::user();

        // Get all active products
        $products = Product::where('is_active', true)
            ->orderBy('name', 'asc')
            ->get()
            ->map(function ($product) use ($user) {
                // Build authorized user IDs list
                $authorizedUserIds = collect([$user->id]);
                if ($user->isManager()) {
                    $authorizedUserIds = $authorizedUserIds->merge(
                        $user->teamMembers()->pluck('id')
                    );
                }

                // Get lead counts for this product based on user permissions
                // Use DB query for more reliable counting
                $leadQuery = DB::table('lead_product')
                    ->join('leads', 'lead_product.lead_id', '=', 'leads.id')
                    ->where('lead_product.product_id', $product->id);

                // Apply user authorization
                if (!$user->isAdmin()) {
                    $leadQuery->whereIn('leads.added_by', $authorizedUserIds);
                }

                // Count active leads (not won or lost) for this product
                $activeLeadsCount = (clone $leadQuery)
                    ->whereNotIn('lead_product.status', ['won', 'lost'])
                    ->whereNotNull('lead_product.status')
                    ->count();

                // Count total leads for this product
                $totalLeadsCount = (clone $leadQuery)->count();

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'category' => $product->category,
                    'price' => $product->price,
                    'active_leads_count' => $activeLeadsCount,
                    'total_leads_count' => $totalLeadsCount,
                ];
            });

        return Inertia::render('Pipeline/ProductSelection', [
            'products' => $products,
        ]);
    }

    /**
     * Store the selected product in session and redirect to pipeline.
     */
    public function select(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ]);

        // Verify product is active
        $product = Product::where('id', $validated['product_id'])
            ->where('is_active', true)
            ->firstOrFail();

        // Store selected product ID in session
        $request->session()->put('selected_product_id', $product->id);
        $request->session()->put('selected_product_name', $product->name);

        return redirect()->route('pipeline');
    }
}
