<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Kreait\Firebase\Factory;

class BrandController extends Controller
{
    protected $firebaseStorage;
    public function __construct()
    {
        $firebase = (new Factory)
            ->withServiceAccount(config('filesystems.disks.firebase.credentials'));

        // Khởi tạo Firebase Storage
        $this->firebaseStorage = $firebase->createStorage();
    }

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
            'image' => 'image|mimes:jpeg,png,jpg|max:2048',
        ]);
        $file = $request->file('image');
        $timestamp = Carbon::now()->toDateString();
        $fileName = $timestamp . '_' . $file->getClientOriginalName();
        $bucket = $this->firebaseStorage->getBucket();
        $filePath = 'logo_images/' . $fileName;
        $bucket->upload(file_get_contents($file), [
            'name' => $filePath
        ]);
        $getImage = $bucket->object($filePath);
        $getImage->update(['acl' => []], ['predefinedAcl' => 'PUBLICREAD']);
        $imageUrl = config('services.firebase.storage_url') . $bucket->name() . '/' . $filePath;
        if ($validatedData->fails()) {
            return response()->json($validatedData->errors());
        }
        $brandData = $validatedData->validated();
        $brandData['image'] = $imageUrl;
        $brand = Brand::createBrand($brandData);
        return response()->json(['message' => 'Brand added successfully.', 'data' => $brand], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->all();
        $rules = [
            'name' => 'required|unique:brands|max:15',
        ];
        if ($request->hasFile('image')) {
            $rules['image'] = 'image|mimes:jpeg,png,jpg|max:2048';
        }

        $validatedData = Validator::make($data, $rules);

        if ($validatedData->fails()) {
            return response()->json($validatedData->errors());
        }
        $brandData = $validatedData->validated();
        $oldBrand = Brand::getBrandById($id);
        if (!$oldBrand) {
            return response()->json(['message' => 'Brand not found.'], 404);
        }
        $brand = Brand::updateBrand($id, $brandData);
        return response()->json(['message' => 'Brand updated successfully.', 'data' => $brand], 201);
    }

    public function destroy(int $id): JsonResponse
    {
        Brand::deleteBrand($id);
        return response()->json(['message' => 'Brand deleted successfully.'], 204);
    }
}
