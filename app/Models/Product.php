<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Database\QueryException;


class Product extends Model
{
    use HasFactory;
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public static function getAllProduct(): Collection
    {
        $products = DB::table('products')
            ->leftJoin('products_images', 'products.id', '=', 'products_images.product_id')
            ->select(
                'products.id',
                'products.name',
                'products.brand_id',
                'products.category_id',
                'products.price',
                'products.sale_date',
                'products.import_date',
                'products.warranty_period',
                'products.seating_capacity',
                'products.power',
                'products.torque',
                'products.manufacturing_year',
                'products.top_speed',
                'products.color',
                'products.created_at',
                'products.updated_at',
                'products_images.path'
            )
            ->get();

        $groupedProducts = $products->groupBy('id')->map(function ($productGroup) {
            $product = $productGroup->first();
            $product->paths = $productGroup->pluck('path')->filter()->all();
            unset($product->path);
            return $product;
        });

        return $groupedProducts->values();
    }

    public static function createProduct($data): bool
    {
        try {
            DB::beginTransaction();
            $productId = DB::table('products')->insertGetId([
                'name' => $data['name'],
                'brand_id' => $data['brand_id'],
                'category_id' => $data['category_id'],
                'price' => $data['price'],
                'sale_date' => $data['sale_date'],
                'import_date' => $data['import_date'],
                'warranty_period' => $data['warranty_period'],
                'seating_capacity' => $data['seating_capacity'],
                'power' => $data['power'],
                'torque' => $data['torque'],
                'manufacturing_year' => $data['manufacturing_year'],
                'top_speed' => $data['top_speed'],
                'color' => $data['color'],
                'created_at' => now(),
            ]);

            if (isset($data['paths']) && is_array($data['paths'])) {
                $imagesData = array_map(function ($path) use ($productId) {
                    return [
                        'product_id' => $productId,
                        'path' => $path,
                        'created_at' => now(),
                    ];
                }, $data['paths']);
                DB::table('products_images')->insert($imagesData);
            }
            DB::commit();
            return true;
        } catch (QueryException $e) {
            DB::rollBack();
            return false;
        }
    }

    public static function updateProduct(int $id, array $attributes): int
    {
        return DB::table('products')->where('id', $id)->update($attributes);
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
        }    }
}
