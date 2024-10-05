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
}
