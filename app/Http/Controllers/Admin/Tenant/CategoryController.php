<?php

namespace App\Http\Controllers\Admin\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryStoreRequest;
use App\Http\Requests\CategoryUpdateRequest;
use App\Jobs\CreateCategory;
use App\Jobs\DeleteCategory;
use App\Jobs\ListCategories;
use App\Jobs\ShowCategory;
use App\Jobs\UpdateCategory;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = ListCategories::dispatch();

        return Inertia::render('category.index', [
            'categories' => $categories,
        ]);
    }

    public function store(CategoryStoreRequest $request): RedirectResponse
    {
        CreateCategory::dispatch($request);

        return redirect()->route('category.index');
    }

    public function show(Request $request, Category $category)
    {
        ShowCategory::dispatch($category);

        return Inertia::render('category.show', [
            'category' => $category,
        ]);
    }

    public function update(CategoryUpdateRequest $request, Category $category): RedirectResponse
    {
        UpdateCategory::dispatch($request, $category);

        return redirect()->route('category.show', ['category' => $category]);
    }

    public function destroy(Request $request, Category $category): RedirectResponse
    {
        DeleteCategory::dispatch($category);

        return redirect()->route('category.index');
    }
}
