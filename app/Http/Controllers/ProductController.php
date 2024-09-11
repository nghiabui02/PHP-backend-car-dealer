<?php

namespace App\Http\Controllers;

use App\Common\Common;
use App\Common\Firebase;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Kreait\Firebase\Factory;

class ProductController extends Controller
{
    protected $firebaseStorage;
    public function __construct()
    {
        $firebase = (new Factory)
            ->withServiceAccount(config('filesystems.disks.firebase.credentials'));

        $this->firebaseStorage = $firebase->createStorage();
    }

    public function index(Request $request): JsonResponse
    {
        $dataSearch = request()->all();
        $products = Product::getAllProduct($dataSearch);
        if ($products->isEmpty()) {
            return response()->json('No products found in store');
        }
        return response()->json($products);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'name' => 'required|string|max:15',
            'brand_id' => 'required|integer',
            'category_id' => 'required|integer',
            'color' => 'required|string|max:15',
            'price' => 'required',
            'import_price' => 'required',
            'torque' => 'required',
            'power' => 'required',
            'seating_capacity' => 'required',
            'top_speed' => 'required',
            'import_date' => 'required',
            'warranty_period' => 'required',
            'sale_date' => 'required',
            'manufacturing_year' => 'required',
            'sold_status' => 'nullable',
            'acceleration' => 'required',
            'torque_rpm' => 'required',
            'length' => 'nullable',
            'wheelbase' => 'nullable',
            'ground_clearance' => 'nullable',
            'trunk_capacity' => 'required|max:4',
            'fuel_tank_capacity' => 'required|max:3',
            'fuel_consumption_city' => 'nullable|max:3',
            'fuel_consumption_highway' => 'nullable|max:3',
            'fuel_consumption_combined' => 'nullable|max:3',
            'wheel_size' => "nullable|max:3",
            'description' => "nullable|string",
            'images' => 'required',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $dataSend = $validator->validated();
        $images = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                if ($image) {
                    $timestamp = Carbon::now()->toDateString();
                    $fileName = $timestamp . '_' . $image->getClientOriginalName();
                    $bucket = $this->firebaseStorage->getBucket();
                    $images[] = Common::getProductImageUrl($fileName, $bucket, $image);
                } else {
                    return response()->json('Images is required', 400);
                }
            }
        }

        $dataSend['images'] = $images;
        $new_product = Product::createProduct($dataSend);
        if ($new_product) {
            return response()->json(['message' => 'Product created successfully', 'data' => $new_product], 201);
        }
        return response()->json(['message' => 'Product not created'], 500);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $dataUpdate = $request->all();

        $validator = Validator::make($dataUpdate, [
            'name' => 'required|string|max:15',
            'brand_id' => 'required|integer',
            'category_id' => 'required|integer',
            'color' => 'required|string|max:15',
            'price' => 'required',
            'import_price' => 'required',
            'torque' => 'required',
            'power' => 'required',
            'seating_capacity' => 'required',
            'top_speed' => 'required',
            'import_date' => 'required',
            'warranty_period' => 'required',
            'sale_date' => 'required',
            'manufacturing_year' => 'required',
            'sold_status' => 'nullable',
            'acceleration' => 'required',
            'torque_rpm' => 'required',
            'length' => 'nullable',
            'wheelbase' => 'nullable',
            'ground_clearance' => 'nullable',
            'trunk_capacity' => 'required|max:4',
            'fuel_tank_capacity' => 'required|max:3',
            'fuel_consumption_city' => 'nullable|max:3',
            'fuel_consumption_highway' => 'nullable|max:3',
            'fuel_consumption_combined' => 'nullable|max:3',
            'wheel_size' => "nullable|max:3",
            'description' => "nullable|string",
            'images' => 'required',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $dataUpdate = $validator->validated();
        $images = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                if ($image) {
                    $timestamp = Carbon::now()->toDateString();
                    $fileName = $timestamp . '_' . $image->getClientOriginalName();
                    $bucket = $this->firebaseStorage->getBucket();
                    $images[] = Common::getProductImageUrl($fileName, $bucket, $image);
                } else {
                    return response()->json('Images is required', 400);
                }
            }
        }

        $dataUpdate['images'] = $images;

        $productUpdated = Product::updateProduct($id, $dataUpdate);
        return response()->json(['message' => 'Product updated successfully', 'data' => $productUpdated]);
    }

    public function destroy(int $id): JsonResponse
    {
        Product::deleteProduct($id);
        return response()->json(['message' => 'Product deleted successfully'], 204);
    }
}
