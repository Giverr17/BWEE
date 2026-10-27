<?php

namespace App\Services;

use BaconQrCode\Common\ErrorCorrectionLevel;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

class QrCodeService
{
    /**
     * Generate an SVG string representation of a QR code for the given URL.
     * Uses BaconQrCode with size 512, margin 2, error correction level M.
     */
    public function generateSvg(string $url): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle(512, 2),
            new SvgImageBackEnd
        );

        $writer = new Writer($renderer);

        return $writer->writeString($url, 'utf-8', ErrorCorrectionLevel::M());
    }
}
