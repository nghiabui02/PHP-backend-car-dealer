<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class ProductLog extends Model
{
    use HasFactory;

    protected $table = 'product_logs';

    public static function save_log($id ,$data): bool
    {
        try {
            DB::beginTransaction();
            DB::table('product_logs')
                ->insert([
                    'product_id' => $id,
                    'changes' => json_encode($data),
                    'updated_by' => auth()->user()->id ?? 'system',
                    'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                ]);
            DB::commit();
            return true;
        }  catch (QueryException $e) {
            \Log::error($e->getMessage());
            DB::rollBack();
            return false;
        }
    }

    public static function getAllLog()
    {
        return self::select('product_logs.*', 'users.name')
            ->join('users', 'users.id', '=', 'product_logs.updated_by')
            ->orderBy('product_logs.created_at', 'desc')
            ->get();
    }
}
