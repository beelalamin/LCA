<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class AssetLabelController extends Controller
{
    public function barcode(Asset $asset)
    {
        $pdf = Pdf::loadView('labels.asset', [
            'asset' => $asset,
            'type' => 'barcode',
        ])->setPaper([0, 0, 144, 72], 'landscape');

        return $pdf->stream("{$asset->asset_tag}-barcode.pdf");
    }

    public function qrcode(Asset $asset)
    {
        $pdf = Pdf::loadView('labels.asset', [
            'asset' => $asset,
            'type' => 'qrcode',
        ])->setPaper([0, 0, 144, 72], 'landscape');

        return $pdf->stream("{$asset->asset_tag}-qrcode.pdf");
    }
}
