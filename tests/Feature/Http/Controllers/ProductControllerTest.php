<?php

namespace Tests\Feature\Http\Controllers;

use App\Jobs\CreateProduct;
use App\Jobs\DeleteProduct;
use App\Jobs\ListProducts;
use App\Jobs\ShowProduct;
use App\Jobs\UpdateProduct;
use App\Models\Product;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Queue;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\ProductController
 */
final class ProductControllerTest extends TestCase
{
    use AdditionalAssertions, WithFaker;

    #[Test]
    public function index_displays_view(): void
    {
        Queue::fake();

        $response = $this->get(route('products.index'));

        $response->assertOk();
        $response->assertViewIs('product.index');
        $response->assertViewHas('products');

        Queue::assertPushed(ListProducts::class);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\ProductController::class,
            'store',
            \App\Http\Requests\ProductStoreRequest::class
        );
    }

    #[Test]
    public function store_redirects(): void
    {
        $name = fake()->name();
        $price = fake()->randomFloat(/** decimal_attributes **/);

        Queue::fake();

        $response = $this->post(route('products.store'), [
            'name' => $name,
            'price' => $price,
        ]);

        $response->assertRedirect(route('product.index'));

        Queue::assertPushed(CreateProduct::class, function ($job) use ($request) {
            return $job->request->is($request);
        });
    }


    #[Test]
    public function show_displays_view(): void
    {
        $product = Product::factory()->create();

        Queue::fake();

        $response = $this->get(route('products.show', $product));

        $response->assertOk();
        $response->assertViewIs('product.show');
        $response->assertViewHas('product');

        Queue::assertPushed(ShowProduct::class, function ($job) use ($id) {
            return $job->id->is($id);
        });
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\ProductController::class,
            'update',
            \App\Http\Requests\ProductUpdateRequest::class
        );
    }

    #[Test]
    public function update_redirects(): void
    {
        $product = Product::factory()->create();
        $name = fake()->name();
        $price = fake()->randomFloat(/** decimal_attributes **/);

        Queue::fake();

        $response = $this->put(route('products.update', $product), [
            'name' => $name,
            'price' => $price,
        ]);

        $response->assertRedirect(route('product.show', ['product' => $product]));

        Queue::assertPushed(UpdateProduct::class, function ($job) use ($request, $id) {
            return $job->request->is($request) && $job->id->is($id);
        });
    }


    #[Test]
    public function destroy_redirects(): void
    {
        $product = Product::factory()->create();

        Queue::fake();

        $response = $this->delete(route('products.destroy', $product));

        $response->assertRedirect(route('product.index'));

        Queue::assertPushed(DeleteProduct::class, function ($job) use ($id) {
            return $job->id->is($id);
        });
    }
}
