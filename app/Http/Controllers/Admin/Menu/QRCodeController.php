<?php

namespace App\Http\Controllers\Admin\Menu;

use App\Actions\QrCode\GenerateQrCode;
use App\Http\Controllers\Controller;
use App\Http\Requests\QRCodeStoreRequest;
use App\Jobs\TrackPageView;
use App\Models\Menu;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QRCodeController extends Controller
{
    public function __construct(private readonly GenerateQrCode $generateQrCode) {}

    /**
     * Generate (or regenerate) the QR code for a menu.
     */
    public function generate(QRCodeStoreRequest $request, Menu $menu): RedirectResponse
    {
        $this->generateQrCode->execute($menu, $request->validated('parameters', []));

        return back()->with('success', 'QR generado correctamente.');
    }

    /**
     * Download the existing QR code image as PNG.
     */
    public function download(Menu $menu): StreamedResponse
    {
        $qr = $menu->qrCode()->firstOrFail();

        abort_unless($qr->image_url && Storage::disk('public')->exists($qr->image_url), 404);

        $tenantId = tenant()?->id;
        if ($tenantId) {
            TrackPageView::dispatch(
                $tenantId, 'menu', $menu->id, 'qr_download',
                request()->ip(), request()->userAgent(), request()->header('referer'),
            );
        }

        $filename = "menu-{$menu->id}-qr.png";

        return Storage::disk('public')->download($qr->image_url, $filename, [
            'Content-Type' => 'image/png',
        ]);
    }

    /**
     * Delete the QR code for a menu.
     */
    public function destroy(Menu $menu): RedirectResponse
    {
        $qr = $menu->qrCode;
        if ($qr) {
            if ($qr->image_url) {
                Storage::disk('public')->delete($qr->image_url);
            }
            $qr->delete();
        }

        return back()->with('success', 'QR eliminado.');
    }
}
