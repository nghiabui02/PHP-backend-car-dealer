<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class Department extends Model
{
    use HasFactory;
    protected $table = 'departments';
    protected $fillable = ['name', 'created_at', 'updated_at', 'manager_id'];

    public function employees(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public static function getAllDepartments(): \Illuminate\Database\Eloquent\Collection
    {
        return Department::with('employees')->get();
    }

    public static function getDepartmentById(int $id): Department
    {
        return Department::with('employees')->find($id);
    }

    public static function createDepartment($data)
    {
        DB::beginTransaction();
        try {
            $data['created_at'] = now();
            $department = DB::table('departments')->insertGetId($data);
            $departmentCreated = DB::table('departments')->where('id', $department)->first();
            DB::commit();
            return $departmentCreated;
        } catch (QueryException $e) {
            \Log::error($e->getMessage());
            DB::rollBack();
            return false;
        }
    }

    public static function updateDepartment($id, $data)
    {
        DB::beginTransaction();
        try {
            $data['updated_at'] = now();
            DB::table('departments')->where('id', $id)->update($data);
            $departmentUpdated = DB::table('departments')->where('id', $id)->first();
            DB::commit();
            return $departmentUpdated;
        } catch (QueryException $e) {
            \Log::error($e->getMessage());
            DB::rollBack();
            return false;
        }
    }

    public static function deleteDepartment(int $id): void
    {
        DB::table('departments')->where('id', $id)->delete();
    }

}
