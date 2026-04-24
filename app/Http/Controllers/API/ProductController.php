<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use App\DTOs\ProductDTO;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;
use Exception;

class ProductController extends Controller
{
    protected $productService;

    public function __construct(ProductService $productService) {
        $this->productService = $productService;
    }

   /**
     * PUBLIC: View all active products or search by name (Paginated).
     */
    public function GetActiveProducts(Request $request) {
        try {
            
            $products = $this->productService->getAllActiveProducts($request->query('search'));
            
            return response()->json([
                'status' => 'success',
                'message' => 'Products retrieved successfully',
           
                'data' => $products
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve products',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
    /**
     * PUBLIC: View a single active product.
     */
    public function ViewSingleProduct($id) {
        try {
            $product = $this->productService->getSingleProduct((int) $id);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Product details retrieved',
                'data' => $product
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * VENDOR: View all products belonging to the authenticated user.
     */
    public function viewProductsByVendor() {
        try {
            $products = $this->productService->getVendorProducts(Auth::id());
            
            return response()->json([
                'status' => 'success',
                'message' => 'Your products retrieved successfully',
                'data' => $products
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * VENDOR: Create a new product.
     */
    public function createProduct(Request $request) {
        try {


            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'price' => 'required|numeric|min:0',
                'stock_quantity' => 'required|integer|min:0',
                'status' => 'required|in:active,inactive'
            ]);



            $dto = new ProductDTO(...$request->only(['name', 'description', 'price', 'stock_quantity', 'status']));
            $product = $this->productService->createProduct($dto, Auth::id());

            return response()->json([
                'status' => 'success',
                'message' => 'Product created successfully',
                'data' => $product
            ], 201);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * VENDOR: Update an existing product.
     */
    public function updateProduct(Request $request, $id) {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'price' => 'required|numeric|min:0',
                'stock_quantity' => 'required|integer|min:0',
                'status' => 'required|in:active,inactive'
            ]);

            $dto = new ProductDTO(...$request->only(['name', 'description', 'price', 'stock_quantity', 'status']));
            $product = $this->productService->updateProduct((int) $id, $dto, Auth::id());

            return response()->json([
                'status' => 'success',
                'message' => 'Product updated successfully',
                'data' => $product
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 404);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * VENDOR: Delete a product.
     */
    public function DeleteProduct($id) {
        try {
            $this->productService->deleteProduct((int) $id, Auth::id());
            
            return response()->json([
                'status' => 'success',
                'message' => 'Product deleted successfully'
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}