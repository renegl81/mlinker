<?php

namespace App\Actions\QrCode;

use App\Models\Menu;
use App\Models\QRCode;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GenerateQrCode
{
    /**
     * Generate (or regenerate) a QR code for a menu and persist the PNG to public storage.
     *
     * @param  array{size?: int, margin?: int, foreground?: string, background?: string, label?: string|null}  $parameters
     */
    public function execute(Menu $menu, array $parameters = []): QRCode
    {
        $parameters = array_merge([
            'size' => 400,
            'margin' => 12,
            'foreground' => '#000000',
            'background' => '#FFFFFF',
            'label' => null,
        ], $parameters);

        $url = $this->buildPublicMenuUrl($menu);

        $builder = Builder::create()
            ->writer(new PngWriter)
            ->writerOptions([])
            ->data($url)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::High)
            ->size((int) $parameters['size'])
            ->margin((int) $parameters['margin'])
            ->roundBlockSizeMode(RoundBlockSizeMode::Margin)
            ->foregroundColor($this->hexToColor($parameters['foreground']))
            ->backgroundColor($this->hexToColor($parameters['background']));

        if (! empty($parameters['label'])) {
            $builder->labelText((string) $parameters['label']);
        }

        $result = $builder->build();

        $tenantId = tenant('id');
        $directory = "tenant{$tenantId}/qr-codes";
        $filename = "menu-{$menu->id}-".Str::random(8).'.png';
        $path = "{$directory}/{$filename}";

        Storage::disk('public')->put($path, $result->getString());

        $qr = QRCode::updateOrCreate(
            ['menu_id' => $menu->id],
            [
                'parameters' => $parameters,
                'image_url' => $path,
                'url' => $url,
            ],
        );

        // Limpia versiones anteriores del mismo menú (solo deja la actual)
        $this->pruneOldImages($directory, $filename, $menu->id);

        return $qr;
    }

    protected function buildPublicMenuUrl(Menu $menu): string
    {
        $tenant = tenant();
        $domain = $tenant?->domains()->first()?->domain;

        if ($domain) {
            $scheme = parse_url(config('app.url'), PHP_URL_SCHEME) ?: 'http';

            return "{$scheme}://{$domain}/menu/{$menu->id}";
        }

        return route('tenant_public.tenant_menu_show', ['menu' => $menu->id]);
    }

    protected function hexToColor(string $hex): Color
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        return new Color(
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        );
    }

    protected function pruneOldImages(string $directory, string $keep, int $menuId): void
    {
        $disk = Storage::disk('public');
        if (! $disk->exists($directory)) {
            return;
        }

        foreach ($disk->files($directory) as $file) {
            if (basename($file) === $keep) {
                continue;
            }
            if (str_starts_with(basename($file), "menu-{$menuId}-")) {
                $disk->delete($file);
            }
        }
    }
}
