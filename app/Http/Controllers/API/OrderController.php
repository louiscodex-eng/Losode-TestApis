<?php

namespace App\Http\Controllers\Api;
use App\Models\Order;
use App\Http\Controllers\Controller;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService) {
        $this->orderService = $orderService;
    }

    public function store(Request $request) {
        try {
            if (!Auth::check()) {
        return response()->json([
        'status' => 'error',
        'message' => 'Please login.'], 401);
    }
            $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
            ]);

            $order = $this->orderService->placeOrder(
                Auth::id(), 
                $request->product_id,
                $request->quantity
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Order placed successfully',
                'data' => $order->load('product') 
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' =>$e->getMessage(),
               
            ], 422); 
        }
    }
}