<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function index(): JsonResponse
    {
        $products = Product::getAllProduct();
        if ($products->isEmpty()) {
            return response()->json('No products found in store');
        }
        return response()->json($products);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'brand_id' => 'required|integer',
            'category_id' => 'required|integer',
            'price' => 'required|numeric',
            'sale_date' => 'nullable|date',
            'import_date' => 'required|date',
            'warranty_period' => 'required|integer',
            'seating_capacity' => 'required|integer',
            'power' => 'required|numeric',
            'torque' => 'required|numeric',
            'manufacturing_year' => 'required|integer',
            'top_speed' => 'required|numeric',
            'color' => 'required|string|max:50',
            'paths' => 'required|array',
            'paths.*' => 'url',
        ]);
        Product::createProduct($validated);
        return response()->json(['message' => 'Product created successfully'], 201);
    }

    public function update(Request $request, int $id): int
    {
        $data = request()->all();
        $validated = $request->validate([]);
        return Product::updateProduct($id, $validated);
    }

    public function destroy(int $id): JsonResponse
    {
        Product::deleteProduct($id);
        return response()->json(['message' => 'Product deleted successfully'], 204);
    }
}
