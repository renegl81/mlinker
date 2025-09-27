<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Jobs\CreateProduct;
use App\Jobs\DeleteProduct;
use App\Jobs\ListProducts;
use App\Jobs\ShowProduct;
use App\Jobs\UpdateProduct;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        ListProducts::dispatch();

        return Inertia::render('product.index', [
            'products' => $products,
        ]);
    }

    public function store(ProductStoreRequest $request): RedirectResponse
    {
        CreateProduct::dispatch($request);

        return redirect()->route('product.index');
    }

    public function show(Request $request, Product $product)
    {
        ShowProduct::dispatch($id);

        return Inertia::render('product.show', [
            'product' => $product,
        ]);
    }

    public function update(ProductUpdateRequest $request, Product $product): RedirectResponse
    {
        UpdateProduct::dispatch($request, $id);

        return redirect()->route('product.show', ['product' => $product]);
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        DeleteProduct::dispatch($id);

        return redirect()->route('product.index');
    }
}
