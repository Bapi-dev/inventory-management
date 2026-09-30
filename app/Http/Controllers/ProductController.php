<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    public function index(Request $request)
    {
        $query = Product::with('category');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->boolean('low_stock')) {
            $query->whereColumn('current_stock', '<=', 'min_stock_level');
        }

        $products = $query->latest()->paginate(12)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:50|unique:products,sku',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'size' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:50',
            'unit' => 'required|string|max:20',
            'selling_price' => 'required|numeric|min:0',
            'wholesale_price' => 'required|numeric|min:0',
            'min_stock_level' => 'required|integer|min:0',
            'opening_stock' => 'nullable|integer|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        $openingStock = (int) ($validated['opening_stock'] ?? 0);
        unset($validated['opening_stock']);

        $validated['current_stock'] = 0; // Will be set via stock movement if opening stock > 0
        $product = Product::create($validated);

        if ($openingStock > 0) {
            $this->inventoryService->recordMovement(
                product: $product,
                movementType: 'opening_stock',
                quantity: $openingStock,
                referenceType: 'OpeningStock',
                referenceId: $product->id,
                date: now()->toDateTimeString(),
                notes: 'Initial Opening Stock upon Product creation',
                userId: Auth::id()
            );
        }

        return redirect()->route('products.index')->with('success', 'Product created successfully!');
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:50|unique:products,sku,' . $product->id,
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'size' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:50',
            'unit' => 'required|string|max:20',
            'selling_price' => 'required|numeric|min:0',
            'wholesale_price' => 'required|numeric|min:0',
            'min_stock_level' => 'required|integer|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        $product->update($validated);

        return redirect()->route('products.index')->with('success', 'Product updated successfully!');
    }

    public function destroy(Product $product)
    {
        if ($product->orderItems()->count() > 0) {
            return back()->with('error', 'Cannot delete product "' . $product->name . '" because it is used in existing orders.');
        }

        $product->delete();
        return redirect()->route('products.index')->with('success', 'Product deleted successfully!');
    }
}
