<?php

namespace App\Http\Controllers;

use App\Http\Requests\QRCodeStoreRequest;
use App\Http\Requests\QRCodeUpdateRequest;
use App\Jobs\CreateQRCode;
use App\Jobs\DeleteQRCode;
use App\Jobs\ListQRCodes;
use App\Jobs\ShowQRCode;
use App\Jobs\UpdateQRCode;
use App\Models\QRCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class QRCodeController extends Controller
{
    public function index(Request $request)
    {
        $qrcodes = ListQRCodes::dispatch();

        return Inertia::render('qrcode.index', [
            'qrcodes' => $qrcodes,
        ]);
    }

    public function store(QRCodeStoreRequest $request): RedirectResponse
    {
        CreateQRCode::dispatch($request);

        return redirect()->route('qrcode.index');
    }

    public function show(Request $request, QRCode $qRCode)
    {
        ShowQRCode::dispatch($id);

        return Inertia::render('qrcode.show', [
            'qrcode' => $qrcode,
        ]);
    }

    public function update(QRCodeUpdateRequest $request, QRCode $qRCode): RedirectResponse
    {
        UpdateQRCode::dispatch($request, $id);

        return redirect()->route('qrcode.show', ['qrcode' => $qrcode]);
    }

    public function destroy(Request $request, QRCode $qRCode): RedirectResponse
    {
        DeleteQRCode::dispatch($id);

        return redirect()->route('qrcode.index');
    }
}
