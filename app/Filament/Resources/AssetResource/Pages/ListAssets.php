<?php

namespace App\Filament\Resources\AssetResource\Pages;

use App\Filament\Resources\AssetResource;
use App\Imports\AssetsImport;
use App\Exports\AssetsTemplateExport;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ListAssets extends ListRecords
{
    protected static string $resource = AssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('downloadTemplate')
                ->label(__('Download Template'))
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(function () {
                    return Excel::download(
                        new AssetsTemplateExport(),
                        'assets_import_template.xlsx'
                    );
                }),

            Actions\Action::make('importAssets')
                ->label(__('Import Assets'))
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->form([
                    Forms\Components\FileUpload::make('file')
                        ->label(__('Excel File'))
                        ->disk('local')
                        ->directory('imports')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                            'text/csv',
                        ])
                        ->required()
                        ->helperText(__('Upload an .xlsx or .csv file. Download the template first for the correct format.'))
                        ->maxSize(10240), // 10MB
                ])
                ->modalHeading(__('Import Assets from Excel'))
                ->modalDescription(__('Upload a completed Excel file using the provided template format. The first row must be the header row. Asset tags will be auto-generated.'))
                ->modalSubmitActionLabel(__('Import'))
                ->action(function (array $data) {
                    try {
                        $import = new AssetsImport();

                        $filePath = Storage::disk('local')->path($data['file']);

                        Excel::import($import, $filePath);

                        $failures = $import->failures();
                        $importedCount = $import->getImportedCount();
                        $skippedCount = $import->getSkippedCount();

                        // Clean up uploaded file
                        Storage::disk('local')->delete($data['file']);

                        if ($failures->isNotEmpty()) {
                            $errorMessages = $failures->take(5)->map(function ($failure) {
                                return __('Row :row: :errors', [
                                    'row' => $failure->row(),
                                    'errors' => implode(', ', $failure->errors()),
                                ]);
                            })->join("\n");

                            Notification::make()
                                ->title(__(':count assets imported successfully', ['count' => $importedCount]))
                                ->body(__(':skipped rows skipped. Errors:', ['skipped' => $failures->count()]) . "\n" . $errorMessages)
                                ->warning()
                                ->persistent()
                                ->send();
                        } else {
                            Notification::make()
                                ->title(__(':count assets imported successfully', ['count' => $importedCount]))
                                ->success()
                                ->send();
                        }
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title(__('Import Failed'))
                            ->body($e->getMessage())
                            ->danger()
                            ->persistent()
                            ->send();
                    }
                }),

            Actions\CreateAction::make(),
        ];
    }
}
