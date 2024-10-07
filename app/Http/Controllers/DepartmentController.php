<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class DepartmentController extends Controller
{
    public function getDepartments(): JsonResponse
    {
        $departments = Department::getAllDepartments();
        if (count($departments) > 0) {
            return response()->json($departments);
        } else {
            return response()->json();
        }
    }

    public function getDepartmentById(int $id): JsonResponse
    {
        $department = Department::getDepartmentById($id);
        return response()->json($department);
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255|unique:departments',
            'manager_id' => 'required|integer|exists:users,id'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        $dataCreate = $validator->validated();
        $department = Department::createDepartment($dataCreate);
        return response()->json($department);
    }

    /**
     * @throws ValidationException
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255|unique:departments,name,'.$id,
            'manager_id' => 'required|integer|exists:users,id'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        $dataUpdate = $validator->validated();
        $department = Department::updateDepartment($id, $dataUpdate);
        return response()->json($department);
    }

    public function destroy(int $id): JsonResponse
    {
        Department::deleteDepartment($id);
        return response()->json(['message' => 'Delete department success'], 204);
    }
}
