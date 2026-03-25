<?php

namespace App\Services;

use App\Models\Asset;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\SvgWriter;
use Milon\Barcode\DNS1D;
use Illuminate\Support\Facades\Storage;

class LabelGenerationService
{
    public function generateBoth(Asset $asset): void
    {
        // Barcode
        $barcode = new DNS1D();
        $barcode->setStorPath(storage_path('app/public'));
        $barcodeSvg = $barcode->getBarcodeSVG($asset->asset_tag, 'C128', 2, 40);
        
        // Save barcode SVG
        Storage::disk('local')->put("labels/{$asset->id}/barcode.svg", $barcodeSvg);

        // QR Code
        $qrCode = new QrCode(data: $asset->asset_tag);
        $qrCode->setSize(200);
        
        $writer = new SvgWriter();
        $result = $writer->write($qrCode);
        
        // Save QR SVG
        Storage::disk('local')->put("labels/{$asset->id}/qrcode.svg", $result->getString());
    }
}
