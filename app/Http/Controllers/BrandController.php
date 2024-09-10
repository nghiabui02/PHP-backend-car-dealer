<?php

namespace App\Http\Controllers;

use App\Common\Common;
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
        $imageUrl = '';

        if (file_exists($file)) {
            $fileName = $timestamp . '_' . $file->getClientOriginalName();
            $bucket = $this->firebaseStorage->getBucket();
            $imageUrl = Common::getBrandImageUrl($fileName, $bucket, $file);
        }

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
        $imageUrl = '';
        $timestamp = Carbon::now()->toDateString();
        $oldBrand = Brand::getBrandById($id);
        $file = $request->file('image');
        $rules = [
            'name' => 'unique:brands,name,' . $id . '|max:15',
        ];
        if (file_exists($file)) {
            $rules['image'] = 'image|mimes:jpeg,png,jpg|max:2048';
        } else {
            $rules['name'] = 'required|unique:brands,name,' . $id . '|max:15';
        }

        $validatedData = Validator::make($data, $rules);

        if ($validatedData->fails()) {
            return response()->json($validatedData->errors());
        }

        if (!$oldBrand) {
            return response()->json(['message' => 'Brand not found.'], 404);
        }

        if (file_exists($file)) {
            $firebase = (new Factory)
                ->withServiceAccount(config('filesystems.disks.firebase.credentials'))
                ->createStorage();
            $bucket = $firebase->getBucket();
            $file = $request->file('image');
            $fileName = $timestamp . '_' . $file->getClientOriginalName();
            $imageUrl = Common::getBrandImageUrl($fileName, $bucket, $file);
        }

        $brandData = $validatedData->validated();
        $brandData['image'] = $imageUrl;

        $brand = Brand::updateBrand($id, $brandData);
        return response()->json(['message' => 'Brand updated successfully.', 'data' => $brand], 201);
    }

    public function destroy(int $id): JsonResponse
    {
        Brand::deleteBrand($id);
        return response()->json(['message' => 'Brand deleted successfully.'], 204);
    }
}
