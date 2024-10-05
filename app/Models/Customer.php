<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Customer extends Model
{
    use HasFactory;
    protected $table = 'customers';

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }


    public static function createCustomer(array $data)
    {
        $customer = DB::table('customers')->insertGetId([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone_number' => $data['phone_number'],
            'product_id' => $data['product_id'],
            'created_at' => now(),
        ]);
        return self::select('*')->where('id', $customer)->first();
    }

    public static function get_customers(): string|\Illuminate\Support\Collection
    {
        try {
            DB::beginTransaction();
            $customer = DB::table('customers')->get();
            DB::commit();
            return $customer;
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

    public static function find_customer($data)
    {
        $customer_info = self::select('*');

        if (isset($data['phone_number'])) {
            $result = $customer_info->where('phone_number', $data['phone_number'])->first();
            if ($result) {
                return $result->toArray();
            }
        }
        if (isset($data['email'])) {
            $result = $customer_info->where('email', $data['email'])->first();
            if ($result) {
                return $result->toArray();
            }
        }
        return null;
    }

    public static function update_customer($data): true|string
    {
        try {
            DB::beginTransaction();
            $data['updated_at'] = now();
            $data['created_at'] = Carbon::parse($data['created_at'])->format('Y-m-d H:i:s');

            DB::table('customers')->where('id', $data['id'])->update($data);
            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            return $e->getMessage();
        }
    }
}
