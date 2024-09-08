<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class Category extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'created_at', 'updated_at'];

    public static function getCategories(): \Illuminate\Support\Collection
    {
        return DB::table('categories')
            ->get();
    }

    public static function createCategories($data)
    {
        try {
            DB::beginTransaction();

            $categoryId = DB::table('categories')->insertGetId([
                'name' => $data['name'],
                'created_at' => now(),
            ]);
            $dataCreated = DB::table('categories')->where('id', $categoryId)->first();

            DB::commit();
            return $dataCreated;
        } catch (QueryException $e) {
            DB::rollBack();
            return false;
        }
    }

    public static function updateCategories($id ,$data)
    {
        try {
            DB::beginTransaction();
            DB::table('categories')->where('id', $id)->update([
                'name' => $data['name'],
                'updated_at' => now(),
            ]);
            $dataUpdated = DB::table('categories')->where('id', $id)->first();
            DB::commit();
            return $dataUpdated;
        }
        catch (QueryException) {
            DB::rollBack();
            return false;
        }
    }

    public static function deleteCategories($id): void
    {
        DB::table('categories')->where('id', $id)->delete();
    }
}
