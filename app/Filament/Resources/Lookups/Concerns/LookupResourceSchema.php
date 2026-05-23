<?php

namespace App\Filament\Resources\Lookups\Concerns;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

trait LookupResourceSchema
{
    public static function getNavigationGroup(): ?string
    {
        return __('Lookups');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'asset_manager']) ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema(static::formSchema());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(static::tableColumns())
            ->defaultSort('sort_order')
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()
                        ->disabled(fn ($record) => method_exists($record, 'isInUse') && $record->isInUse())
                        ->tooltip(fn ($record) => (method_exists($record, 'isInUse') && $record->isInUse())
                            ? __('Cannot delete: this entry is in use. Deactivate it instead.')
                            : null)
                        ->before(function ($record, Tables\Actions\DeleteAction $action) {
                            if (method_exists($record, 'isInUse') && $record->isInUse()) {
                                Notification::make()
                                    ->title(__('Cannot delete: this entry is in use.'))
                                    ->body(__('It is referenced by existing records. Deactivate it instead to hide it from new selections while preserving history.'))
                                    ->danger()
                                    ->send();
                                $action->cancel();
                            }
                        }),
                ])->icon('heroicon-m-ellipsis-vertical'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function (Collection $records, Tables\Actions\DeleteBulkAction $action) {
                            $blocked = $records->filter(fn ($r) => method_exists($r, 'isInUse') && $r->isInUse());
                            if ($blocked->isNotEmpty()) {
                                Notification::make()
                                    ->title(__('Cannot delete: :count entries are in use.', ['count' => $blocked->count()]))
                                    ->body(__('Remove or reassign references first, or deactivate them instead.'))
                                    ->danger()
                                    ->send();
                                $action->cancel();
                            }
                        }),
                ]),
            ]);
    }

    protected static function formSchema(): array
    {
        return [
            Forms\Components\Tabs::make('Translations')
                ->tabs([
                    Forms\Components\Tabs\Tab::make(__('English'))->schema([
                        Forms\Components\TextInput::make('name.en')->label(__('Name (EN)'))->required(),
                        Forms\Components\Textarea::make('description.en')->label(__('Description (EN)')),
                    ]),
                    Forms\Components\Tabs\Tab::make(__('Arabic'))->schema([
                        Forms\Components\TextInput::make('name.ar')->label(__('Name (AR)')),
                        Forms\Components\Textarea::make('description.ar')->label(__('Description (AR)')),
                    ]),
                ])->columnSpanFull(),
        ];
    }

    protected static function tableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('name')
                ->label(__('Name'))
                ->searchable()
                ->formatStateUsing(fn ($state, $record) => $record->getTranslatedName()),
        ];
    }
}
