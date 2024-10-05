<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class SaleProduct extends Model
{
    use HasFactory;
    protected $table = 'transactions';


    public static function saleProduct($data)
    {
        try {
            $data['created_at'] = now();
            DB::beginTransaction();
            DB::table('products')
                ->where('id', $data['product_id'])
                ->update(['sold_status' => 1]);
            $transactionId = DB::table('transactions')->insertGetId($data);
            $transaction = DB::table('transactions')->where('id', $transactionId)->first();
            DB::commit();
            return $transaction;
        } catch (QueryException $e) {
            DB::rollBack();
            return false;
        }
    }

    public static function transactionList($dataSearch): \Illuminate\Support\Collection
    {
        $transactions = DB::table('transactions')->select(
            'transactions.*',
            'products.id as product_id',
            'products.name as product_name',
            'products.brand_id',
            'brands.name as brand_name',
            'customers.phone_number',
            'customers.first_name',
            'customers.last_name',
            'customers.email',
        )
            ->leftJoin('products', 'transactions.product_id', '=', 'products.id')
            ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
            ->leftJoin('customers', 'transactions.customer_id', '=', 'customers.id');

        if (!empty($dataSearch['phone_number_customer'])) {
            $transactions = $transactions->where('customers.phone_number', 'LIKE', '%' . $dataSearch['phone_number_customer'] . '%');
        }
        if (!empty($dataSearch['product_name'])) {
            $transactions = $transactions->where('products.name', 'LIKE', '%' . $dataSearch['product_name'] . '%');
        }
        if (!empty($dataSearch['brand_id'])) {
            $transactions = $transactions->where('products.brand_id', 'LIKE', '%' . $dataSearch['brand_id'] . '%');
        }
        if (!empty($dataSearch['category_id'])) {
            $transactions = $transactions->where('products.category_id', 'LIKE', '%' . $dataSearch['category_id'] . '%');
        }
        if (!empty($dataSearch['email'])) {
            $transactions = $transactions->where('customers.email', 'LIKE', '%' . $dataSearch['email'] . '%');
        }
        return $transactions->get();
    }
}
