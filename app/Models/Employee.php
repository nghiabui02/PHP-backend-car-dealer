<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class Employee extends Model
{
    use HasFactory;
    const POSITIONS = [
      0 => 'admin',
      1 => 'manager',
      2 => 'employee',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function departments(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public static function getAllEmployees(): \Illuminate\Support\Collection
    {
        return DB::table('employees')
            ->select('departments.name as department', 'users.name as name', 'employees.*')
            ->join('departments', 'employees.department_id', '=', 'departments.id')
            ->join('users', 'employees.user_id', '=', 'users.id')
            ->get();
    }

    public static function getEmployeeById($id)
    {
        return Employee::with('departments')->find($id);
    }

    public static function createEmployee($data) {
        try {
            DB::beginTransaction();
            $employee = Arr::except($data, ['images', 'first_name', 'last_name', 'password', 'username', 'name']);
            $employee['created_at'] = now();
            $employee = DB::table('employees')->insertGetId($employee);

            if (isset($data['images']) && is_array($data['images'])) {
                $images = array_map(function ($image) use ($employee) {
                    return [
                        'employee_id' => $employee,
                        'path' => $image,
                        'created_at' => now(),
                    ];
                }, $data['images']);
                Db::table('employee_images')->insert($images);
            }

            $employeeCreated = Employee::with('departments')->find($employee)->first();
            $images = Db::table('employee_images')
                ->where('employee_id')
                ->pluck('path');
            $employeeCreated->images = $images;
            DB::commit();
            return $employeeCreated;
        } catch (QueryException $e) {
            \Log::error($e->getMessage());
            DB::rollBack();
            return null;
        }
    }

    public static function updateEmployee($id, $data) {
        try {
            DB::beginTransaction();
            $employee = Arr::except($data, ['images']);
            $employee['updated_at'] = now();
            DB::table('employees')->where('id', $id)->update($employee);
            if (isset($data['images']) && is_array($data['images'])) {
                Db::table('employee_images')->where('employee_id', $id)->delete();
                $images = array_map(function ($image) use ($employee) {
                    return [
                        'employee_id' => $employee,
                        'path' => $image,
                        'updated_at' => now(),
                    ];
                }, $data['images']);
                Db::table('employee_images')->insert($images);
            }
            $employeeUpdated = Employee::with('departments')->find($id);
            $images = Db::table('employee_images')
                ->where('employee_id', $id)
                ->pluck('path');
            $employeeUpdated->images = $images;
            DB::commit();
            return $employeeUpdated;
        } catch (QueryException $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            return null;
        }
    }

    public static function deleteEmployee($id) {
        DB::table('employees')->where('id', $id)->delete();
    }

    public static function changeEmployeeStatus($id, $status) {
       DB::table('employees')->where('id', $id)->update(['status' => $status]);
       return Employee::with('departments')->find($id);
    }
}
