<?php

namespace App\Http\Controllers;

use App\Models\ProductLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductLogController extends Controller
{
    public function getAllProductLogs(): JsonResponse
    {
        $productLogs = ProductLog::getAllLog();
        return response()->json([$productLogs]);
    }
}
