<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::getCategories();
        return response()->json($categories, 200);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->all();
        $validation = Validator::make($data, [
            'name' => 'required | unique:categories | max: 15',
        ]);
        if ($validation->fails()) {
            return response()->json($validation->errors(), 400);
        }
        $dataSend = $validation->validated();
        $response = Category::createCategories($dataSend);
        return response()->json(['message' => 'Create a category success', 'data' => $response], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $data = $request->all();

        $validation = Validator::make($data, [
            'name' => 'required | string | max:15',
            'updated_at' => now()
        ]);
        if ($validation->fails()) {
            return response()->json($validation->errors(), 400);
        }
        $dataSend = $validation->validated();
        $dataUpdated = Category::updateCategories($id ,$dataSend);
        return response()->json(['message' => 'Update a category success', 'data' => $dataUpdated], 200);
    }

    public function destroy($id): JsonResponse
    {
        Category::deleteCategories($id);
        return response()->json(['message' => 'Delete a category success'], 204);
    }
}
