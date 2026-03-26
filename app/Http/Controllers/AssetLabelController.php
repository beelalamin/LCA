<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class AssetLabelController extends Controller
{
    public function show(Asset $asset)
    {
        $pdf = Pdf::loadView('labels.asset', [
            'asset' => $asset,
        ])->setPaper([0, 0, 144, 72], 'landscape'); // 2x1 inch label approx

        return $pdf->stream("{$asset->asset_tag}-label.pdf");
    }
}
