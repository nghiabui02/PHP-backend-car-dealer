<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class Brand extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'updated_at', 'created_at'];

    public static function getAllBrand(): \Illuminate\Support\Collection
    {
        return DB::table('brands')->get();
    }

    public static function createBrand($data)
    {
        try {
            DB::beginTransaction();
            $newBrand = DB::table('brands')->insertGetId([
                'name' => $data['name'],
                'image' => $data['image'],
                'created_at' => now(),
            ]);
            $brandCreated = DB::table('brands')->where('id', $newBrand)->first();
            DB::commit();
            return $brandCreated;
        } catch (QueryException) {
            DB::rollBack();
            return false;
        }
    }

    public static function updateBrand($id, $data)
    {
        try {
            DB::beginTransaction();
            DB::table('brands')->where('id', $id)->update([
                'name' => $data['name'],
                'image' => $data['image'],
                'updated_at' => now()
            ]);
            $dataUpdated = DB::table('brands')->where('id', $id)->first();
            DB::commit();
            return $dataUpdated;
        } catch (QueryException) {
            DB::rollBack();
            return false;
        }
    }

    public static function deleteBrand($id): void
    {
        DB::table('brands')->where('id', $id)->delete();
    }

    public static function getBrandById($id){
        return DB::table('brands')->where('id', $id)->first();
    }
}
