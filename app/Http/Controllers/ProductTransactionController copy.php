<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\ProductTransaction;

class ProductTransactionController extends Controller
{
    // LIST
    public function index()
    {
        $transactions = ProductTransaction::with('product', 'user')
            ->latest()
            ->paginate(30);

        return view('products.transactions.index', compact('transactions'));
    }

    // CREATE FORM
    public function create(Request $request)
    {
        return view('products.transactions.form', compact('product'));
    }

    // STORE
    public function store(Request $request, Product $product)
    {
        $request->validate([
            'type' => 'required',
            'quantity_change' => 'required|integer|not_in:0',
            'note' => 'nullable|string'
        ]);

        $oldQty = $product->quantity;
        $change = $request->quantity_change;
        $newQty = $oldQty + $change;

        // Update product quantity
        $product->update(['quantity' => $newQty]);

        // Create transaction log
        ProductTransaction::create([
            'product_id'       => $product->id,
            'type'             => $request->type,
            'quantity_change'  => $change,
            'before_quantity'  => $oldQty,
            'after_quantity'   => $newQty,
            'note'             => $request->note,
            'created_by'          => auth()->id(),
        ]);

        return redirect()
            ->route('products.show', $product->id)
            ->with('success', 'Product transaction recorded.');
    }
}
