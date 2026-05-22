<?php

namespace App\Filament\Resources\AssetResource\Pages;

use App\Filament\Resources\AssetResource;
use App\Models\Asset;
use App\Models\Assignment;
use App\Models\User;
use App\Models\Lookups\AssetAssignmentStatus;
use App\Models\Lookups\AssetCondition;
use App\Models\Lookups\AssetReturnReason;
use App\Models\Lookups\Status;
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
                Action::make('markAvailable')
                    ->label(__('Make Available'))
                    ->icon('heroicon-o-check-circle')
                    ->color('info')
                    ->visible(fn (Asset $record) => ! in_array($record->status?->code, ['available', 'assigned']))
                    ->requiresConfirmation()
                    ->action(fn (Asset $record) => $this->updateStatus($record, 'available')),

                Action::make('checkOut')
                    ->label(__('Check Out'))
                    ->icon('heroicon-o-arrow-right-start-on-rectangle')
                    ->color('success')
                    ->visible(fn (Asset $record) => in_array($record->status?->code, ['available', 'purchased', 'reserved']))
                    ->form([
                        Forms\Components\Select::make('user_id')
                            ->label(__('User'))
                            ->options(User::where('is_active', true)->orderBy('full_name')->pluck('full_name', 'id'))
                            ->required()
                            ->searchable(),
                        Forms\Components\Select::make('condition_out_id')
                            ->label(__('Condition'))
                            ->options(fn () => AssetCondition::active()->ordered()->get()
                                ->mapWithKeys(fn ($c) => [$c->id => $c->getTranslatedName()]))
                            ->required(),
                        Forms\Components\Textarea::make('notes_en')->label(__('Notes (EN)')),
                    ])
                    ->action(function (Asset $record, array $data) {
                        $assignedStatusId = AssetAssignmentStatus::where('code', 'assigned')->value('id');

                        Assignment::create([
                            'asset_id' => $record->id,
                            'user_id' => $data['user_id'],
                            'assigned_by' => auth()->id(),
                            'condition_out_id' => $data['condition_out_id'],
                            'checked_out_at' => now(),
                            'assignment_status_id' => $assignedStatusId,
                            'notes' => ['en' => $data['notes_en'] ?? null],
                            'is_active' => true,
                        ]);

                        $this->updateStatus($record, 'assigned');
                    }),

                Action::make('checkIn')
                    ->label(__('Check In'))
                    ->icon('heroicon-o-arrow-left-start-on-rectangle')
                    ->color('warning')
                    ->visible(fn (Asset $record) => $record->status?->code === 'assigned')
                    ->form([
                        Forms\Components\Select::make('condition_in_id')
                            ->label(__('Return Condition'))
                            ->options(fn () => AssetCondition::active()->ordered()->get()
                                ->mapWithKeys(fn ($c) => [$c->id => $c->getTranslatedName()]))
                            ->required(),
                        Forms\Components\Select::make('return_reason_id')
                            ->label(__('Return Reason'))
                            ->options(fn () => AssetReturnReason::active()->ordered()->get()
                                ->mapWithKeys(fn ($r) => [$r->id => $r->getTranslatedName()])),
                        Forms\Components\Textarea::make('notes_en')->label(__('Notes (EN)')),
                    ])
                    ->action(function (Asset $record, array $data) {
                        $assignment = $record->activeAssignment;
                        $returnedStatusId = AssetAssignmentStatus::where('code', 'returned')->value('id');

                        if ($assignment) {
                            $assignment->update([
                                'checked_in_at' => now(),
                                'condition_in_id' => $data['condition_in_id'],
                                'return_reason_id' => $data['return_reason_id'] ?? null,
                                'assignment_status_id' => $returnedStatusId,
                                'notes' => array_merge((array) $assignment->notes, ['en' => $data['notes_en'] ?? null]),
                                'is_active' => false,
                            ]);
                        }

                        $this->updateStatus($record, 'available');
                    }),

                Action::make('sendToRepair')
                    ->label(__('Send to Repair'))
                    ->icon('heroicon-o-wrench')
                    ->color('warning')
                    ->visible(fn (Asset $record) => ! in_array($record->status?->code, ['in_repair', 'assigned']))
                    ->requiresConfirmation()
                    ->action(fn (Asset $record) => $this->updateStatus($record, 'in_repair')),

                Action::make('retire')
                    ->label(__('Retire Asset'))
                    ->icon('heroicon-o-archive-box-x-mark')
                    ->color('danger')
                    ->visible(fn (Asset $record) => ! in_array($record->status?->code, ['retired', 'disposed']))
                    ->requiresConfirmation()
                    ->action(fn (Asset $record) => $this->updateStatus($record, 'retired')),

                Action::make('dispose')
                    ->label(__('Dispose Asset'))
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->visible(fn (Asset $record) => $record->status?->code !== 'disposed')
                    ->requiresConfirmation()
                    ->action(function (Asset $record) {
                        $record->update(['disposal_date' => now()->toDateString()]);
                        $this->updateStatus($record, 'disposed');
                    }),
            ])
                ->label(__('Update Status'))
                ->icon('heroicon-m-chevron-down')
                ->button(),
        ];
    }

    protected function updateStatus(Asset $record, string $newCode): void
    {
        $oldName = $record->status?->getTranslatedName() ?? '—';

        $newStatus = Status::forAssets()->where('code', $newCode)->first();

        if (! $newStatus) {
            Notification::make()->title(__('Status not found'))->danger()->send();
            return;
        }

        $record->update(['status_id' => $newStatus->id]);

        Notification::make()
            ->title(__('Status updated successfully'))
            ->body(__('Asset status changed from :old to :new', [
                'old' => $oldName,
                'new' => $newStatus->getTranslatedName(),
            ]))
            ->success()
            ->send();

        $this->refreshFormData(['status_id']);
    }
}
