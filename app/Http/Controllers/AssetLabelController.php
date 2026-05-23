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
            'width' => (int) Setting::get('label_width', Setting::labelDefault('label_width')),
            'height' => (int) Setting::get('label_height', Setting::labelDefault('label_height')),
            'padding' => (int) Setting::get('label_padding', Setting::labelDefault('label_padding')),
            'print_type' => Setting::get('print_type', Setting::labelDefault('print_type')),
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
        return $this->renderLabels(collect([$asset]), "{$asset->asset_tag}-label.pdf");
    }

    public function bulkLabels(\Illuminate\Http\Request $request)
    {
        $ids = $request->get('ids', []);
        $assets = Asset::whereIn('id', $ids)->get();

        if ($assets->isEmpty()) {
            return back();
        }

        return $this->renderLabels($assets, 'bulk-labels.pdf');
    }

    protected function renderLabels(\Illuminate\Support\Collection $assets, string $filename)
    {
        $settings = [
            'width' => (int) Setting::get('label_width', Setting::labelDefault('label_width')),
            'height' => (int) Setting::get('label_height', Setting::labelDefault('label_height')),
            'padding' => (int) Setting::get('label_padding', Setting::labelDefault('label_padding')),
            'columns' => (int) Setting::get('columns_per_page', Setting::labelDefault('columns_per_page')),
            'print_type' => Setting::get('print_type', Setting::labelDefault('print_type')),
        ];

        $paper = $settings['columns'] > 1 ? 'a4' : [0, 0, $settings['width'], $settings['height']];
        $orientation = $settings['columns'] > 1 ? 'portrait' : 'landscape';

        $pdf = Pdf::loadView('labels.bulk-assets', [
            'assets' => $assets,
            'type' => $settings['print_type'],
            'settings' => $settings,
        ])->setPaper($paper, $orientation);

        return $pdf->stream($filename);
    }
}
