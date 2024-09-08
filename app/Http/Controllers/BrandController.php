<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class BrandController extends Controller
{
    public function index(): JsonResponse
    {
        $brands = Brand::getAllBrand();
        return response()->json($brands);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->all();
        $validatedData = Validator::make($data, [
            'name' => 'required|unique:brands|max:15',
        ]);
        if ($validatedData->fails()) {
            return response()->json($validatedData->errors());
        }
        $brandData = $validatedData->validated();
        $brand = Brand::createBrand($brandData);
        return response()->json(['message' => 'Brand added successfully.', 'data' => $brand], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->all();
        $validatedData = Validator::make($data, [
            'name' => 'required|unique:brands|max:15',
        ]);
        if ($validatedData->fails()) {
            return response()->json($validatedData->errors());
        }
        $brandData = $validatedData->validated();
        $brand = Brand::updateBrand($id, $brandData);
        return response()->json(['message' => 'Brand updated successfully.', 'data' => $brand], 201);
    }

    public function destroy(int $id): JsonResponse
    {
        Brand::deleteBrand($id);
        return response()->json(['message' => 'Brand deleted successfully.'], 204);
    }
}
