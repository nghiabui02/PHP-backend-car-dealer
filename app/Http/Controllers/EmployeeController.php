<?php

namespace App\Http\Controllers;

use App\Common\Common;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Kreait\Firebase\Factory;

class EmployeeController extends Controller
{
    protected $firebaseStorage;
    public function __construct()
    {
        $firebase = (new Factory)
            ->withServiceAccount(config('filesystems.disks.firebase.credentials'));

        $this->firebaseStorage = $firebase->createStorage();
    }
    public function getAllEmployees(): JsonResponse
    {
        $employees = Employee::getAllEmployees();
        if (count($employees) == 0) {
            return response()->json("no data");
        }
        return response()->json($employees);
    }

    public function getEmployeeById(int $id): JsonResponse
    {
        $employee = Employee::getEmployeeById($id);
        if (count($employee) == 0) {
            return response()->json("Not found");
        }
        return response()->json($employee);
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->all();
        $validator = Validator::make($data, [
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors());
        }
        $dataSend = $validator->validated();
        $images = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                if ($image) {
                    $timestamp = Carbon::now()->toDateString();
                    $fileName = $timestamp . '_' . $image->getClientOriginalName();
                    $bucket = $this->firebaseStorage->getBucket();
                    $images[] = Common::getEmployeeImageUrl($fileName, $bucket, $image);
                } else {
                    return response()->json('Images is required', 400);
                }
            }
        }
        if (!empty($dataSend['created_at'])) {
            $dataSend['created_at'] = now();
        }

        $dataSend['images'] = $images;
        $new_employee = Employee::createEmployee($dataSend);
        if ($new_employee) {
            return response()->json(['message' => 'Employee created successfully', 'data' => $new_employee], 201);
        }
        return response()->json(['message' => 'Employee not created'], 500);
    }

    /**
     * @throws ValidationException
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->all();
        $validator = Validator::make($data, [

        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $dataUpdate = $validator->validated();
        $images = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                if ($image) {
                    $timestamp = Carbon::now()->toDateString();
                    $fileName = $timestamp . '_' . $image->getClientOriginalName();
                    $bucket = $this->firebaseStorage->getBucket();
                    $images[] = Common::getEmployeeImageUrl($fileName, $bucket, $image);
                } else {
                    return response()->json('Images is required', 400);
                }
            }
        }

        $dataUpdate['images'] = $images;

        $employeeUpdated = Employee::updateEmployee($id, $dataUpdate);

        if ($employeeUpdated) {
            return response()->json(['message' => 'Employee updated successfully', 'data' => $employeeUpdated]);
        }
        return response()->json(['message' => 'Employee not updated'], 500);
    }

    public function delete(int $id): JsonResponse
    {
        Employee::deleteEmployee($id);
        return response()->json(['message' => 'Employee deleted'], 204);
    }

    /**
     * @throws ValidationException
     */
    public function changeEmployeeStatus(int $id, Request $request): JsonResponse
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'status' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $dataSend = $validator->validated();
        $status = $dataSend['status'];
        $employee = Employee::changeEmployeeStatus($id, $status);
        if ($employee) {
            return response()->json(['message' => 'Employee status updated successfully', 'data' => $employee]);
        }
        return response()->json(['message' => 'Employee status not updated'], 500);
    }

}
