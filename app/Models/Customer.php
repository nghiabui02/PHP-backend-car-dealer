<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Customer extends Model
{
    use HasFactory;

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }


    public static function createCustomer(array $data): bool
    {
        return DB::table('customers')->insert([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone_number' => $data['phone_number'],
            'product_id' => $data['product_id'],
            'created_at' => now(),
        ]);
    }

    public static function get_customers(): true|string
    {
        try {
            DB::beginTransaction();
            DB::table('customers')->get();
            DB::commit();
            return true;
        }
        catch (\Exception $e) {
            DB::rollBack();
            return $e->getMessage();
        }
    }

    public static function delete_customer($id): true|string
    {
        try {
            DB::beginTransaction();

            DB::table('customers')->where('id', $id)->delete();
            DB::commit();
            return true;
        }
        catch (\Exception $e) {
            DB::rollBack();
            return $e->getMessage();
        }
    }
}
