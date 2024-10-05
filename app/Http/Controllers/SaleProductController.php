<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\SaleProduct;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;


class SaleProductController extends Controller
{
    public function getAllProductSold(): JsonResponse
    {
        $dataSearch = [];
        $dataSearch['sold_status'] = 1;
        $productsSold = Product::getAllProduct($dataSearch);
        if (count($productsSold) == 0) {
            return response()->json(['message' => 'No data']);
        }
        return response()->json(['data' => $productsSold]);
    }

    public function SaleProduct(Request $request): JsonResponse
    {
        $data = $request->all();
        $validateData = Validator::make($data, [
            'product_id'    => 'required|integer',
            "email"         => 'required|string|email|max:255',
            "first_name"    => 'required|string',
            "last_name"     => 'required|string',
            "phone_number"  => 'required|string',
            "sale_date"     => 'required',
        ]);

        if ($validateData->fails()) {
            return response()->json(['message' => $validateData->errors()]);
        }

        $getProductSale = Product::find($data['product_id']);

        if ($getProductSale == null) {
            return response()->json(['message' => 'Not found this product']);
        }
        if ($getProductSale['sold_status'] == 1) {
            return response()->json(['message' => 'This product is already sold!']);
        }
        $dataSend = $validateData->validated();
        $customerData = [
            'email' => $dataSend['email'],
            'first_name' => $dataSend['first_name'],
            'last_name' => $dataSend['last_name'],
            'phone_number' => $dataSend['phone_number'],
            'product_id' => $getProductSale['id'],
        ];
        unset($dataSend['email'], $dataSend['first_name'], $dataSend['last_name'], $dataSend['phone_number']);
        $customerInfo = Customer::find_customer($customerData);
        if ($customerInfo == null) {
            $createCustomer = Customer::createCustomer($customerData);
            $dataSend['customer_id'] = $createCustomer['id'];
        } else {
            $customerInfo['product_id'] = $customerData['product_id'];
            Customer::update_customer($customerInfo);
            $dataSend['customer_id'] = $customerInfo['id'];
        }
        $sale_price = $getProductSale['price'] - $getProductSale['import_price'];
        $dataSend['sale_price'] = $sale_price;




        $transaction = SaleProduct::saleProduct($dataSend);
        return response()->json(['message' => 'Ok', 'data' => $transaction]);
    }


}
