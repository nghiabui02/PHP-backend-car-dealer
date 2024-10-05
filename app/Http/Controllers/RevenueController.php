<?php

namespace App\Http\Controllers;

use App\Models\SaleProduct;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RevenueController extends Controller
{
    public function getTotalRevenue(Request $request): JsonResponse
    {
        $requestData = $request->all();
        $type = $requestData['type'] ?? null;
        $date = $requestData['date'] ?? null;
        $startDate = isset($requestData['startDate']) ? Carbon::parse($requestData['startDate'])->format('Y-m-d') : null;
        $endDate = isset($requestData['endDate']) ? Carbon::parse($requestData['endDate'])->format('Y-m-d') : null;
        $data = SaleProduct::calculateRevenue($type, $date, $startDate, $endDate);
        return response()->json(['revenues' => $data]);
    }
}
