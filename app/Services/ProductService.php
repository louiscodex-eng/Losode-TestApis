<?php

namespace App\Services;
use InvalidArgumentException;
use Exception;
use App\Models\Product;
use App\DTOs\ProductDTO;
use Illuminate\Support\Facades\Log;


class ProductService
{
    // --- Vendor Methods ---
/////create, update, delete, get vendor's products
 public function createProduct(ProductDTO $dto, int $vendorId)
    {
        try {
            // Check for missing fields (Manual Integrity Check)
            if (empty($dto->name) || empty($dto->description)) {
                throw new InvalidArgumentException("Product name and description are required.");
            }

            // Type Check: Ensure price is a valid number
            if (!is_numeric($dto->price) || $dto->price < 0) {
                throw new InvalidArgumentException("The price must be a valid positive number.");
            }

            // Create Product Record
            $product = Product::create([
                'vendor_id' => $vendorId,
                'name' => $dto->name,
                'description' => $dto->description,
                'price' => $dto->price,
                'stock_quantity' => $dto->stock_quantity,
                'status' => $dto->status,
            ]);

            if (!$product) {
                throw new Exception("Failed to save the product to the database.");
            }
             //remove all product listing cache to ensure new product appears immediately
            \Illuminate\Support\Facades\Cache::flush();
            return $product;

        } catch (InvalidArgumentException $e) {
            // Log specific validation-style errors
            Log::warning("Product Creation Validation Error: " . $e->getMessage());
            throw $e; 

        } catch (Exception $e) {
            // Log unexpected errors 
            Log::error("Product Service Database Error: " . $e->getMessage());
            throw new Exception("A database error occurred while creating the product.");
        }
    }

   /**
     * Update an existing product with ownership check and validation.
     */
    public function updateProduct(int $productId, ProductDTO $dto, int $vendorId)
    {
        try {
            // 1. Ownership & Existence Check
            $product = Product::where('id', $productId)
                              ->where('vendor_id', $vendorId)
                              ->first();

            if (!$product) {
                throw new InvalidArgumentException("Product not found or you do not have permission to edit it.");
            }

            // 2. Data Validation
            if (empty($dto->name) || empty($dto->description)) {
                throw new InvalidArgumentException("Product name and description cannot be empty.");
            }

            if (!is_numeric($dto->price) || $dto->price < 0) {
                throw new InvalidArgumentException("Price must be a valid positive number.");
            }

            // 3. Update execution
            $product->update([
                'name' => $dto->name,
                'description' => $dto->description,
                'price' => $dto->price,
                'stock_quantity' => $dto->stock_quantity,
                'status' => $dto->status,
            ]);

              //remove all product listing cache to ensure new product appears immediately
            \Illuminate\Support\Facades\Cache::flush();
            return $product;

        } catch ( InvalidArgumentException $e) {
            throw $e; 
        } catch (Exception $e) {
            Log::error("Update Product Error: " . $e->getMessage());
            throw new Exception("An unexpected error occurred while updating the product.");
        }
    }

    /**
     * Delete a product only if it belongs to the authenticated vendor.
     */
    public function deleteProduct(int $productId, int $vendorId)
    {
        try {
            $product = Product::where('id', $productId)
                              ->where('vendor_id', $vendorId)
                              ->first();

            if (!$product) {
                throw new InvalidArgumentException("Product not found or unauthorized.");
            }

              //remove all product listing cache to ensure new product appears immediately
            \Illuminate\Support\Facades\Cache::flush();
            return $product->delete();

        } catch (InvalidArgumentException $e) {
            throw $e;
        } catch (Exception $e) {
            Log::error("Delete Product Error: " . $e->getMessage());
            throw new Exception("Could not delete product. It might be linked to existing orders.");
        }
    }

    /**
     * Fetch all products for a specific vendor.
     */
    public function getVendorProducts(int $vendorId)
    {
        try {
            return Product::where('vendor_id', $vendorId)
                          ->orderBy('created_at', 'desc')
                          ->get();
        } catch (Exception $e) {
            Log::error("Fetch Vendor Products Error: " . $e->getMessage());
            throw new Exception("Unable to retrieve your products at this time.");
        }
    }

  /**
     * Fetch all active products with optional search filtering, pagination, and caching.
     */
    public function getAllActiveProducts(?string $search = null)
    {
        try {
            // Create a unique cache key based on the current page and search term
            $page = request()->get('page', 1);
            $cacheKey = "products_active_page_{$page}_search_{$search}";

            return \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addMinutes(60), function () use ($search) {
                $query = Product::where('status', 'active');

                if ($search) {
                    // ILIKE handles case-insensitive search for Postgres
                    $query->where('name', 'ILIKE', "%{$search}%");
                }

                // Swapped .get() for .paginate(10)
                return $query->orderBy('name', 'asc')->paginate(10);
            });

        } catch (Exception $e) {
            \Illuminate\Support\Facades\Log::error("Public Products Fetch Error: " . $e->getMessage());
            throw new Exception("Unable to retrieve products at the moment.");
        }
    }
    /**
     * Fetch a single product by ID, ensuring it is currently active.
     */
    public function getSingleProduct(int $id)
    {
        try {
            $product = Product::where('id', $id)
                              ->where('status', 'active')
                              ->first();

            if (!$product) {
                throw new InvalidArgumentException("Product not found or is no longer available.");
            }

            return $product;

        } catch (InvalidArgumentException $e) {
            throw $e;
        } catch (Exception $e) {
            Log::error("Public Single Product Fetch Error: " . $e->getMessage());
            throw new Exception("An error occurred while fetching the product details.");
        }
    }
}