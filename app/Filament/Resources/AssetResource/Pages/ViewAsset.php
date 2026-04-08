<?php

namespace App\Filament\Resources\AssetResource\Pages;

use App\Filament\Resources\AssetResource;
use App\Models\Asset;
use App\Models\Assignment;
use App\Models\Employee;
use App\Enums\AssetStatus;
use App\Enums\ConditionRating;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewAsset extends ViewRecord
{
    protected static string $resource = AssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            
            ActionGroup::make([
                // Change to AVAILABLE (General)
                Action::make('markAvailable')
                    ->label(__('Make Available'))
                    ->icon('heroicon-o-check-circle')
                    ->color('info')
                    ->visible(fn (Asset $record) => !in_array($record->status, [AssetStatus::AVAILABLE, AssetStatus::ASSIGNED]))
                    ->requiresConfirmation()
                    ->action(fn (Asset $record) => $this->updateStatus($record, AssetStatus::AVAILABLE)),

                // Check Out (to ASSIGNED)
                Action::make('checkOut')
                    ->label(__('Check Out'))
                    ->icon('heroicon-o-arrow-right-start-on-rectangle')
                    ->color('success')
                    ->visible(fn (Asset $record) => in_array($record->status, [AssetStatus::AVAILABLE, AssetStatus::PURCHASED]))
                    ->form([
                        Forms\Components\Select::make('employee_id')
                            ->label(__('Employee'))
                            ->options(Employee::where('is_active', true)->pluck('full_name_en', 'id'))
                            ->required()
                            ->searchable(),
                        Forms\Components\Select::make('condition_out')
                            ->label(__('Condition'))
                            ->options(ConditionRating::class)
                            ->default(ConditionRating::GOOD->value)
                            ->required(),
                        Forms\Components\Textarea::make('notes')
                            ->label(__('Notes'))
                    ])
                    ->action(function (Asset $record, array $data) {
                        Assignment::create([
                            'asset_id' => $record->id,
                            'employee_id' => $data['employee_id'],
                            'assigned_by' => auth()->id(),
                            'condition_out' => $data['condition_out'],
                            'checked_out_at' => now(),
                            'notes' => $data['notes'],
                            'is_active' => true,
                        ]);

                        $this->updateStatus($record, AssetStatus::ASSIGNED);
                    }),

                // Check In (from ASSIGNED)
                Action::make('checkIn')
                    ->label(__('Check In'))
                    ->icon('heroicon-o-arrow-left-start-on-rectangle')
                    ->color('warning')
                    ->visible(fn (Asset $record) => $record->status === AssetStatus::ASSIGNED)
                    ->form([
                        Forms\Components\Select::make('condition_in')
                            ->label(__('Return Condition'))
                            ->options(ConditionRating::class)
                            ->default(ConditionRating::GOOD->value)
                            ->required(),
                        Forms\Components\Textarea::make('notes')
                            ->label(__('Notes'))
                    ])
                    ->action(function (Asset $record, array $data) {
                        $assignment = $record->activeAssignment;
                        if ($assignment) {
                            $assignment->update([
                                'checked_in_at' => now(),
                                'condition_in' => $data['condition_in'],
                                'notes' => $data['notes'],
                                'is_active' => false,
                            ]);
                        }

                        $this->updateStatus($record, AssetStatus::AVAILABLE);
                    }),

                // Move to REPAIR
                Action::make('sendToRepair')
                    ->label(__('Send to Repair'))
                    ->icon('heroicon-o-wrench')
                    ->color('warning')
                    ->visible(fn (Asset $record) => $record->status !== AssetStatus::IN_REPAIR && $record->status !== AssetStatus::ASSIGNED)
                    ->requiresConfirmation()
                    ->action(fn (Asset $record) => $this->updateStatus($record, AssetStatus::IN_REPAIR)),

                // Retire
                Action::make('retire')
                    ->label(__('Retire Asset'))
                    ->icon('heroicon-o-archive-box-x-mark')
                    ->color('danger')
                    ->visible(fn (Asset $record) => $record->status !== AssetStatus::RETIRED && $record->status !== AssetStatus::DISPOSED)
                    ->requiresConfirmation()
                    ->action(fn (Asset $record) => $this->updateStatus($record, AssetStatus::RETIRED)),

                // Dispose
                Action::make('dispose')
                    ->label(__('Dispose Asset'))
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->visible(fn (Asset $record) => $record->status !== AssetStatus::DISPOSED)
                    ->requiresConfirmation()
                    ->action(fn (Asset $record) => $this->updateStatus($record, AssetStatus::DISPOSED)),

            ])
            ->label(__('Update Status'))
            ->icon('heroicon-m-chevron-down')
            ->button()
        ];
    }

    protected function updateStatus(Asset $record, AssetStatus $status): void
    {
        $oldStatus = $record->status;
        $record->update(['status' => $status->value]);

        Notification::make()
            ->title(__('Status updated successfully'))
            ->body(__('Asset status changed from :old to :new', [
                'old' => $oldStatus->getLabel(),
                'new' => $status->getLabel()
            ]))
            ->success()
            ->send();
            
        $this->refreshFormData(['status']);
    }
}
