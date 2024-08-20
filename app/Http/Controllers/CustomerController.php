<?php

namespace App\Http\Controllers;

use App\Jobs\SendEmailToCustomer;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    public function createNewCustomer(Request $request): JsonResponse
    {
        $data = $request->all();
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone_number' => 'required|string|max:20',
            'product_id' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $customer = Customer::createCustomer($data);
        SendEmailToCustomer::dispatch($data);
        return response()

            ->json(['message' => 'Create customer successfully', 'customer' => $customer], 201);
    }


    public function getCustomers(): JsonResponse
    {
        $customers = Customer::get_customers();
        return response()->json($customers);
    }

    public function destroy(int $id): JsonResponse
    {
        Customer::delete_customer($id);
        return response()->json('Delete customer successfully', 204);
    }
}
