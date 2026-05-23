<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class Settings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static string $view = 'filament.pages.settings';
    protected static ?int $navigationSort = 4;

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }
    
    public ?array $data = [];

    public static function getNavigationLabel(): string
    {
        return __('Settings');
    }

    public function getTitle(): string
    {
        return __('Settings');
    }

    public function mount(): void
    {
        $this->data = [
            'label_width' => Setting::get('label_width', Setting::labelDefault('label_width')),
            'label_height' => Setting::get('label_height', Setting::labelDefault('label_height')),
            'label_padding' => Setting::get('label_padding', Setting::labelDefault('label_padding')),
            'columns_per_page' => Setting::get('columns_per_page', Setting::labelDefault('columns_per_page')),
            'print_type' => Setting::get('print_type', Setting::labelDefault('print_type')),
        ];

        $this->form->fill($this->data);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('Label Dimensions'))
                    ->description(__('Adjust the size and spacing of your asset labels.'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('label_width')
                                    ->label(__('Width (pt)'))
                                    ->numeric()
                                    ->required(),
                                TextInput::make('label_height')
                                    ->label(__('Height (pt)'))
                                    ->numeric()
                                    ->required(),
                                TextInput::make('label_padding')
                                    ->label(__('Padding (px)'))
                                    ->numeric()
                                    ->required(),
                            ]),
                    ]),
                Section::make(__('Layout & Printing'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('columns_per_page')
                                    ->label(__('Columns per Page'))
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(4)
                                    ->required(),
                                Select::make('print_type')
                                    ->label(__('Print Mode'))
                                    ->options([
                                        'both' => __('Both QR & Barcode'),
                                        'qrcode' => __('QR Code Only'),
                                        'barcode' => __('Barcode Only'),
                                    ])
                                    ->required(),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('reset')
                ->label(__('Reset to Default'))
                ->color('gray')
                ->requiresConfirmation()
                ->action(function () {
                    foreach (Setting::LABEL_DEFAULTS as $key => $value) {
                        Setting::set($key, $value);
                    }

                    $this->mount();
                    
                    Notification::make()
                        ->title(__('Settings reset to default'))
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('Save Settings'))
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();
        
        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }

        Notification::make()
            ->title(__('Settings saved successfully'))
            ->success()
            ->send();
    }

    public static function getNavigationGroup(): ?string
    {
        return __('System Administration');
    }
}
