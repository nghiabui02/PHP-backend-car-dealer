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

    const WORKING_STATUS = [
        0 => 'working',
        1 => 'terminated', // Đã nghỉ việc (hoặc 'resigned', 'dismissed')
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function departments(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public static function getAllEmployees($dataSearch): \Illuminate\Support\Collection
    {
        $employees = DB::table('employees')
            ->select('employees.*', 'departments.name as department_name', 'users.name as full_name', 'users.first_name', 'users.last_name', 'employee_images.path')
            ->join('departments', 'employees.department_id', '=', 'departments.id')
            ->leftJoin('employee_images', 'employees.id', '=', 'employee_images.employee_id')
            ->join('users', 'employees.user_id', '=', 'users.id');

        if (!empty($dataSearch['name'])) {
            $employees->where(function($q) use ($dataSearch) {
                $q->where('users.first_name', 'LIKE', '%' . $dataSearch['name'] . '%')
                    ->orWhere('users.last_name', 'LIKE', '%' . $dataSearch['name'] . '%')
                    ->orWhere('users.name', 'LIKE', '%' . $dataSearch['name'] . '%');
            });
        }

        if (!empty($dataSearch['department_id'])) {
            $employees->where('employees.department_id', $dataSearch['department_id']);
        }

        if (!empty($dataSearch['position'])) {
            $employees->where('employees.position', $dataSearch['position']);
        }

        if (!empty($dataSearch['status'])) {
            $employees->where('employees.status', $dataSearch['status']);
        }

        if (!empty($dataSearch['birthday'])) {
            $employees->whereDate('employees.birthday', '=', $dataSearch['birthday']);
        }

        if (!empty($dataSearch['birthday_year'])) {
            $employees->whereYear('employees.birthday', '=', $dataSearch['birthday_year']);
        }

        if (!empty($dataSearch['birthday_month'])) {
            $employees->whereMonth('employees.birthday', '=', $dataSearch['birthday_month']);
        }

        if (!empty($dataSearch['hire_date'])) {
            $employees->whereYear('employees.hire_date', '=', $dataSearch['hire_date']);
        }

        $employees = $employees->get();
        $groupedEmployees = $employees->groupBy('id')->map(function ($employeeGroup) {
            $employees = $employeeGroup->first();
            $employees->paths = $employeeGroup->pluck('path')->filter()->all();
            unset($employees->path);
            return $employees;
        });
        return $groupedEmployees->values();
    }

    public static function getEmployeeById($id)
    {
        return DB::table('employees')
            ->select('employees.*', 'departments.name as department_name', 'users.name as full_name', 'users.first_name', 'users.last_name')
            ->join('departments', 'employees.department_id', '=', 'departments.id')
            ->join('users', 'employees.user_id', '=', 'users.id')
            ->where('employees.id', $id)
            ->first();
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

    public static function deleteEmployee($id): void
    {
        DB::table('employees')->where('id', $id)->delete();
    }

    public static function changeEmployeeStatus($id, $data) {
       DB::table('employees')->where('id', $id)->update([
           'status' => $data->status,
           'resignation_date' => $data->resignation_date,
       ]);
       return Employee::with('departments')->find($id);
    }
}
