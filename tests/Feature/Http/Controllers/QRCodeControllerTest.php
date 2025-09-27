<?php

namespace Tests\Feature\Http\Controllers;

use App\Jobs\CreateQRCode;
use App\Jobs\DeleteQRCode;
use App\Jobs\ListQRCodes;
use App\Jobs\ShowQRCode;
use App\Jobs\UpdateQRCode;
use App\Models\MenuCard;
use App\Models\QRCode;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Queue;
use JMac\Testing\Traits\AdditionalAssertions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see \App\Http\Controllers\QRCodeController
 */
final class QRCodeControllerTest extends TestCase
{
    use AdditionalAssertions, WithFaker;

    #[Test]
    public function index_displays_view(): void
    {
        Queue::fake();

        $response = $this->get(route('q-r-codes.index'));

        $response->assertOk();
        $response->assertViewIs('qrcode.index');
        $response->assertViewHas('qrcodes');

        Queue::assertPushed(ListQRCodes::class);
    }


    #[Test]
    public function store_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\QRCodeController::class,
            'store',
            \App\Http\Requests\QRCodeStoreRequest::class
        );
    }

    #[Test]
    public function store_redirects(): void
    {
        $menu_card = MenuCard::factory()->create();
        $parameters = fake()->;

        Queue::fake();

        $response = $this->post(route('q-r-codes.store'), [
            'menu_card_id' => $menu_card->id,
            'parameters' => $parameters,
        ]);

        $response->assertRedirect(route('qrcode.index'));

        Queue::assertPushed(CreateQRCode::class, function ($job) use ($request) {
            return $job->request->is($request);
        });
    }


    #[Test]
    public function show_displays_view(): void
    {
        $qRCode = QRCode::factory()->create();

        Queue::fake();

        $response = $this->get(route('q-r-codes.show', $qRCode));

        $response->assertOk();
        $response->assertViewIs('qrcode.show');
        $response->assertViewHas('qrcode');

        Queue::assertPushed(ShowQRCode::class, function ($job) use ($id) {
            return $job->id->is($id);
        });
    }


    #[Test]
    public function update_uses_form_request_validation(): void
    {
        $this->assertActionUsesFormRequest(
            \App\Http\Controllers\QRCodeController::class,
            'update',
            \App\Http\Requests\QRCodeUpdateRequest::class
        );
    }

    #[Test]
    public function update_redirects(): void
    {
        $qRCode = QRCode::factory()->create();
        $menu_card = MenuCard::factory()->create();
        $parameters = fake()->;

        Queue::fake();

        $response = $this->put(route('q-r-codes.update', $qRCode), [
            'menu_card_id' => $menu_card->id,
            'parameters' => $parameters,
        ]);

        $response->assertRedirect(route('qrcode.show', ['qrcode' => $qrcode]));

        Queue::assertPushed(UpdateQRCode::class, function ($job) use ($request, $id) {
            return $job->request->is($request) && $job->id->is($id);
        });
    }


    #[Test]
    public function destroy_redirects(): void
    {
        $qRCode = QRCode::factory()->create();

        Queue::fake();

        $response = $this->delete(route('q-r-codes.destroy', $qRCode));

        $response->assertRedirect(route('qrcode.index'));

        Queue::assertPushed(DeleteQRCode::class, function ($job) use ($id) {
            return $job->id->is($id);
        });
    }
}
