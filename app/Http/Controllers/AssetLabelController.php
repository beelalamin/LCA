<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class AssetLabelController extends Controller
{
    protected function labelSettings(): array
    {
        return [
            'width' => (int) Setting::get('label_width', 144),
            'height' => (int) Setting::get('label_height', 72),
            'padding' => (int) Setting::get('label_padding', 5),
            'print_type' => Setting::get('print_type', 'both'),
        ];
    }

    public function barcode(Asset $asset)
    {
        $settings = $this->labelSettings();

        $pdf = Pdf::loadView('labels.asset', [
            'asset' => $asset,
            'type' => 'barcode',
            'settings' => $settings,
        ])->setPaper([0, 0, $settings['width'], $settings['height']], 'landscape');

        return $pdf->stream("{$asset->asset_tag}-barcode.pdf");
    }

    public function qrcode(Asset $asset)
    {
        $settings = $this->labelSettings();

        $pdf = Pdf::loadView('labels.asset', [
            'asset' => $asset,
            'type' => 'qrcode',
            'settings' => $settings,
        ])->setPaper([0, 0, $settings['width'], $settings['height']], 'landscape');

        return $pdf->stream("{$asset->asset_tag}-qrcode.pdf");
    }

    public function label(Asset $asset)
    {
        $settings = [
            'width' => (int) Setting::get('label_width', 144),
            'height' => (int) Setting::get('label_height', 72),
            'padding' => (int) Setting::get('label_padding', 5),
            'print_type' => Setting::get('print_type', 'both'),
        ];

        $pdf = Pdf::loadView('labels.asset', [
            'asset' => $asset,
            'type' => $settings['print_type'],
            'settings' => $settings,
        ])->setPaper([0, 0, $settings['width'], $settings['height']], 'landscape');

        return $pdf->stream("{$asset->asset_tag}-label.pdf");
    }

    public function bulkLabels(\Illuminate\Http\Request $request)
    {
        $ids = $request->get('ids', []);
        $assets = Asset::whereIn('id', $ids)->get();

        if ($assets->isEmpty()) {
            return back();
        }

        $settings = [
            'width' => (int) Setting::get('label_width', 144),
            'height' => (int) Setting::get('label_height', 72),
            'padding' => (int) Setting::get('label_padding', 5),
            'columns' => (int) Setting::get('columns_per_page', 1),
            'print_type' => Setting::get('print_type', 'both'),
        ];

        // For bulk, we might want to use A4 if columns > 1, 
        // but let's stick to the custom size per label and handle layout in CSS.
        // Actually, if it's 'one page', A4 is better.
        $paper = $settings['columns'] > 1 ? 'a4' : [0, 0, $settings['width'], $settings['height']];
        $orientation = $settings['columns'] > 1 ? 'portrait' : 'landscape';

        $pdf = Pdf::loadView('labels.bulk-assets', [
            'assets' => $assets,
            'type' => $settings['print_type'],
            'settings' => $settings,
        ])->setPaper($paper, $orientation);

        return $pdf->stream("bulk-labels.pdf");
    }
}
