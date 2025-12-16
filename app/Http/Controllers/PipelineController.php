<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PipelineController extends Controller
{
    /**
     * Display the pipeline page.
     * Requires a selected product in the session.
     */
    public function index(Request $request): Response|RedirectResponse
    {
        // Check if product is selected in session
        $selectedProductId = $request->session()->get('selected_product_id');
        $selectedProductName = $request->session()->get('selected_product_name');

        // If no product is selected, redirect to product selection page
        if (!$selectedProductId) {
            return redirect()->route('pipeline.select-product');
        }

        return Inertia::render('Pipeline/Index', [
            'selectedProductId' => $selectedProductId,
            'selectedProductName' => $selectedProductName,
        ]);
    }
}
