<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class OrderService
{
    /**
     * Places an order and manages inventory with concurrency protection.
     */
    public function placeOrder(int $userId, int $productId, int $quantity)
    {
        return DB::transaction(function () use ($userId, $productId, $quantity) {
            
            // 1. Pessimistic Lock: Prevent other processes from reading/writing this row
            // until this transaction finishes. This solves the "Concurrency" requirement.
            $product = Product::where('id', $productId)->lockForUpdate()->first();

            if (!$product) {
                throw new Exception("Product not found.");
            }

            // 2. Inventory Check
            if ($product->stock_quantity < $quantity) {
                throw new Exception("Insufficient stock. Only {$product->stock_quantity} remaining.");
            }

            // 3. Deduct Stock
            $product->decrement('stock_quantity', $quantity);

            // 4. Create Order
            return Order::create([
                'user_id' => $userId,
                'product_id' => $productId,
                'quantity' => $quantity,
                'total_price' => $product->price * $quantity,
                'status' => 'completed'
            ]);
        });
    }
}