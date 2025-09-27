<?php

namespace Tests\Feature\Http\Controllers;

use App\Jobs\CreateCategory;
use App\Jobs\DeleteCategory;
use App\Jobs\ListCategories;
use App\Jobs\ShowCategory;
use App\Jobs\UpdateCategory;
use App\Models\Category;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Queue;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\CategoryController
 */
final class CategoryControllerTest extends TestCase
{
    use AdditionalAssertions, WithFaker;

    #[Test]
    public function index_displays_view(): void
    {
        Queue::fake();

        $response = $this->get(route('categories.index'));

        $response->assertOk();
        $response->assertViewIs('category.index');
        $response->assertViewHas('categories');

        Queue::assertPushed(ListCategories::class);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\CategoryController::class,
            'store',
            \App\Http\Requests\CategoryStoreRequest::class
        );
    }

    #[Test]
    public function store_redirects(): void
    {
        $name = fake()->name();

        Queue::fake();

        $response = $this->post(route('categories.store'), [
            'name' => $name,
        ]);

        $response->assertRedirect(route('category.index'));

        Queue::assertPushed(CreateCategory::class, function ($job) use ($request) {
            return $job->request->is($request);
        });
    }


    #[Test]
    public function show_displays_view(): void
    {
        $category = Category::factory()->create();

        Queue::fake();

        $response = $this->get(route('categories.show', $category));

        $response->assertOk();
        $response->assertViewIs('category.show');
        $response->assertViewHas('category');

        Queue::assertPushed(ShowCategory::class, function ($job) use ($id) {
            return $job->id->is($id);
        });
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\CategoryController::class,
            'update',
            \App\Http\Requests\CategoryUpdateRequest::class
        );
    }

    #[Test]
    public function update_redirects(): void
    {
        $category = Category::factory()->create();
        $name = fake()->name();

        Queue::fake();

        $response = $this->put(route('categories.update', $category), [
            'name' => $name,
        ]);

        $response->assertRedirect(route('category.show', ['category' => $category]));

        Queue::assertPushed(UpdateCategory::class, function ($job) use ($request, $id) {
            return $job->request->is($request) && $job->id->is($id);
        });
    }


    #[Test]
    public function destroy_redirects(): void
    {
        $category = Category::factory()->create();

        Queue::fake();

        $response = $this->delete(route('categories.destroy', $category));

        $response->assertRedirect(route('category.index'));

        Queue::assertPushed(DeleteCategory::class, function ($job) use ($id) {
            return $job->id->is($id);
        });
    }
}
