<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Database\QueryException;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }
    public static function getAllProduct($dataSearch): Collection
    {
        $products = self::select(
                'products.*',
                'products_images.path',
                'categories.name as category_name',
                'brands.name as brand_name'
            )
            ->leftJoin('products_images', 'products.id', '=', 'products_images.product_id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('brands', 'products.brand_id', '=', 'brands.id');
            if (!empty($dataSearch['name'])) {
                $products->where('products.name', 'LIKE', '%' . $dataSearch['name'] . '%')
                    ->orWhere('products.brand_id', 'LIKE', '%' . $dataSearch['brand'] . '%');
            }
            if (!empty($dataSearch['brand'])) {
                $brands = is_array($dataSearch['brand']) ? $dataSearch['brands'] : explode(',', $dataSearch['brand']);
                $products->whereIn('products.brand_id', $brands);
            }
            if (!empty($dataSearch['category'])) {
                $categories = is_array($dataSearch['category']) ? $dataSearch['category'] : explode(',', $dataSearch['category']);
                $products->whereIn('products.category_id', $categories);
            }
            if (!empty($dataSearch['min_price']) && !empty($dataSearch['max_price'])) {
                $products->whereBetween('products.price', [$dataSearch['min_price'], $dataSearch['max_price']]);
            }
            if (isset($dataSearch['sold_status'])) {
                $products->where('products.sold_status', $dataSearch['sold_status']);
            }
            if (!empty($dataSearch['color'])) {
                $colors = is_array($dataSearch['color']) ? $dataSearch['color'] : explode(',', $dataSearch['color']);
                $colors = array_filter(array_map('trim', $colors));
                $products->whereIn('products.color', $colors);
            }
            $products = $products->get();

            $groupedProducts = $products->groupBy('id')->map(function ($productGroup) {
                $product = $productGroup->first();
                $product->paths = $productGroup->pluck('path')->filter()->all();
                unset($product->path);
                return $product;
            });

        return $groupedProducts->values();
    }

    public static function createProduct(array $data)
    {
        try {
            DB::beginTransaction();
            $productData = Arr::except($data, ['images']);
            $productData['sold_status'] = 0;
            $productData['created_at'] = now();
            $productId = DB::table('products')->insertGetId($productData);

            if (isset($data['images']) && is_array($data['images'])) {
                $imagesData = array_map(function ($path) use ($productId) {
                    return [
                        'product_id' => $productId,
                        'path' => $path,
                        'created_at' => now(),
                    ];
                }, $data['images']);
                DB::table('products_images')->insert($imagesData);
            }

            $productCreated = DB::table('products')->where('id', $productId)->first();

            $images = DB::table('products_images')
                ->where('product_id', $productId)
                ->pluck('path');

            $productCreated->images = $images;
            DB::commit();
            return $productCreated;
        } catch (QueryException $e) {
            \Log::error($e->getMessage());
            DB::rollBack();
            return false;
        }
    }

    public static function updateProduct(int $id, array $data)
    {
        try {
            DB::beginTransaction();
            $productData = Arr::except($data, ['images']);
            $productData['updated_at'] = now();

            DB::table('products')->where('id', $id)->update($productData);

            if (isset($data['images']) && is_array($data['images'])) {
                DB::table('products_images')->where('product_id', $id)->delete();
                $imagesData = array_map(function ($path) use ($id) {
                    return [
                        'product_id' => $id,
                        'path' => $path,
                        'updated_at' => now(),
                    ];
                }, $data['images']);

                DB::table('products_images')->insert($imagesData);
            }

            $productUpdated = DB::table('products')->where('id', $id)->first();

            $images = DB::table('products_images')
                ->where('product_id', $id)
                ->pluck('path');

            $productUpdated->images = $images;

            DB::commit();
            return $productUpdated;
        } catch (QueryException $e) {
            DB::rollBack();
            return false;
        }
    }

    public static function deleteProduct(int $id): int
    {
        try {
            DB::beginTransaction();
            DB::table('products_images')->where('product_id', $id)->delete();
            $deleted = DB::table('products')->where('id', $id)->delete();
            DB::commit();

            return $deleted;
        } catch (\Exception $e) {
            DB::rollBack();
            return 0;
        }
    }

    public static function findProductById(int $id)
    {
        $product = self::select('products.*',
            'products_images.path',
            'categories.name as category_name',
            'brands.name as brand_name'
        )
            ->leftJoin('products_images', 'products.id', '=', 'products_images.product_id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
            ->where('products.id', $id);
        $product = $product->get();

        $groupedProducts = $product->groupBy('id')->map(function ($productGroup) {
            $product = $productGroup->first();
            $product->paths = $productGroup->pluck('path')->filter()->all();
            unset($product->path);
            return $product;
        });

        return $groupedProducts->values();
    }
}
