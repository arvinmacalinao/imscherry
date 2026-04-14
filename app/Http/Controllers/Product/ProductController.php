<?php

namespace App\Http\Controllers\Product;

use App\Models\Unit;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\ProductTransactionBatch;
use Picqer\Barcode\BarcodeGeneratorHTML;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::select('id', 'name')
            ->limit(1)
            ->get();

        return view('products.index', [
            'products' => $products,
        ]);
    }

    public function create(Request $request)
    {
        $categories = Category::all(['id', 'name']);

        if ($request->has('category')) {
            $categories = Category::whereSlug($request->get('category'))->get();
        }

        return view('products.create', [
            'categories' => $categories,
        ]);
    }

    public function store(StoreProductRequest $request)
    {
        // $existingProduct = Product::where('sku', $request->get('sku'))->first();
        
        // if ($existingProduct) {
        //     $newSKU = $this->generateUniqueCode($existingProduct);
            
        //     $request->merge(['sku' => $newSKU]);
        // }
        try {
            $product = Product::create($request->all());

            return redirect()
                ->back()
                ->with('success', 'Product has been created with SKU: ' . $product->sku);

        } catch (\Exception $e) {
            // Handle any unexpected errors
            return back()->withErrors(['error' => 'Something went wrong while creating the product']);
        }
    }

    // Helper method to generate a unique product code
    private function generateUniqueCode($existingProduct)
    {
        do {
            $prefix = strtoupper(preg_replace('/[^A-Z]/', '', collect(explode(' ', $existingProduct->name))
                ->map(fn($word) => substr($word, 0, 1))
                ->implode('')
            ));
        
            $random = strtoupper(Str::random(10 - strlen($prefix)));
            $sku = $prefix . $random;
        
        } while (Product::where('sku', $sku)->exists());
    
        return $sku;
    }


    public function show(Product $product)
    {
        $transactions = ProductTransactionBatch::with(['type','items', 'items.product', 'user_created'])
            ->whereHas('items', function ($query) use ($product) {
                $query->where('product_id', $product->id);
            })
            ->latest()
            ->get();
    
        return view('products.show', [
            'product' => $product,
            'transactions' => $transactions,
        ]);
    }


    public function edit(Product $product)
    {
        return view('products.edit', [
            'categories' => Category::all(),
            'product' => $product
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $product->update($request->except('product_image'));

        // if ($request->hasFile('product_image')) {

        //     // Delete old image if exists
        //     if ($product->product_image) {
        //         \Storage::disk('public')->delete('products/' . $product->product_image);
        //     }

        //     // Prepare new image
        //     $file = $request->file('product_image');
        //     $fileName = hexdec(uniqid()) . '.' . $file->getClientOriginalExtension();

        //     // Store new image to public storage
        //     $file->storeAs('products/', $fileName, 'public');

        //     // Save new image name to database
        //     $product->update([
        //         'product_image' => $fileName
        //     ]);
        // }

        return redirect()
            ->route('products.index')
            ->with('success', 'Product has been updated!');
    }

    public function destroy(Product $product)
    {
        $product->deleted_by = Auth::id(); // Tag the user
        $product->save();
        
        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('success', 'Product has been deleted!');
    }
}
 