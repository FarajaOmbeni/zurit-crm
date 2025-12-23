<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    /**
     * Display a listing of products.
     */
    public function index()
    {
        $currentUser = auth()->user();

        // Only admins and managers can access product management
        if (!$currentUser->isAdmin() && !$currentUser->isManager()) {
            abort(403, 'Unauthorized access.');
        }

        $products = Product::orderBy('name')
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'category' => $product->category,
                    'description' => $product->description,
                    'is_active' => $product->is_active,
                    'created_at' => $product->created_at->format('Y-m-d H:i:s'),
                ];
            });

        return Inertia::render('Products/Index', [
            'products' => $products,
        ]);
    }

    /**
     * Store a newly created product.
     */
    public function store(Request $request)
    {
        $currentUser = auth()->user();

        // Only admins and managers can create products
        if (!$currentUser->isAdmin() && !$currentUser->isManager()) {
            abort(403, 'Unauthorized access.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'category' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        Product::create($validated);

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    /**
     * Update the specified product.
     */
    public function update(Request $request, Product $product)
    {
        $currentUser = auth()->user();

        // Only admins and managers can update products
        if (!$currentUser->isAdmin() && !$currentUser->isManager()) {
            abort(403, 'Unauthorized access.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'category' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $product->update($validated);

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified product.
     */
    public function destroy(Product $product)
    {
        $currentUser = auth()->user();

        // Only admins and managers can delete products
        if (!$currentUser->isAdmin() && !$currentUser->isManager()) {
            abort(403, 'Unauthorized access.');
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }
}
