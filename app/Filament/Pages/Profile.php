<?php

namespace App\Filament\Pages;

use App\Models\Lookups\Department;
use App\Models\Lookups\JobTitle;
use App\Models\Lookups\OfficeLocation;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class Profile extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static string $view = 'filament.pages.profile';
    protected static bool $shouldRegisterNavigation = false;

    public ?array $data = [];

    public function mount(): void
    {
        $user = auth()->user();

        $this->form->fill([
            'employee_number'          => $user->employee_number,
            'full_name'                => $user->full_name,
            'full_name_ar'             => $user->full_name_ar,
            'email'                    => $user->email,
            'phone'                    => $user->phone,
            'photo_path'               => $user->photo_path,
            'preferred_locale'         => $user->preferred_locale,
            'department_id'            => $user->department_id,
            'job_title_id'             => $user->job_title_id,
            'office_location_id'       => $user->office_location_id,
            'joining_date'             => $user->joining_date,
            'leaving_date'             => $user->leaving_date,
            'emergency_contact_name'   => $user->emergency_contact_name,
            'emergency_contact_phone'  => $user->emergency_contact_phone,
            'notes'                    => $user->notes,
        ]);
    }

    public static function getNavigationLabel(): string
    {
        return __('Profile');
    }

    public function getTitle(): string
    {
        return __('Profile');
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('Personal Information'))
                    ->columns(2)
                    ->schema([
                        FileUpload::make('photo_path')
                            ->label(__('Photo'))
                            ->image()
                            ->disk('public')
                            ->directory('users')
                            ->columnSpanFull(),
                        TextInput::make('employee_number')
                            ->label(__('Employee Number'))
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('full_name')
                            ->label(__('Full Name (EN)'))
                            ->required(),
                        TextInput::make('full_name_ar')
                            ->label(__('Full Name (AR)')),
                        TextInput::make('email')
                            ->label(__('Email'))
                            ->email()
                            ->required()
                            ->unique('users', 'email', ignorable: auth()->user()),
                        TextInput::make('phone')
                            ->label(__('Phone')),
                        Select::make('preferred_locale')
                            ->label(__('Preferred Locale'))
                            ->options(['en' => 'English', 'ar' => 'العربية']),
                    ]),

                Section::make(__('Role & Organization'))
                    ->columns(2)
                    ->schema([
                        Select::make('department_id')
                            ->label(__('Department'))
                            ->options(fn () => Department::active()->ordered()->get()
                                ->mapWithKeys(fn ($d) => [$d->id => $d->getTranslatedName()]))
                            ->searchable()
                            ->preload()
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText(__('Read-only. Contact an administrator to update.')),
                        Select::make('job_title_id')
                            ->label(__('Job Title'))
                            ->options(fn () => JobTitle::active()->ordered()->get()
                                ->mapWithKeys(fn ($j) => [$j->id => $j->getTranslatedName()]))
                            ->searchable()
                            ->preload()
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText(__('Read-only. Contact an administrator to update.')),
                        Select::make('office_location_id')
                            ->label(__('Office Location'))
                            ->options(fn () => OfficeLocation::active()->ordered()->get()
                                ->mapWithKeys(fn ($l) => [$l->id => $l->getTranslatedName()]))
                            ->searchable()
                            ->preload()
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText(__('Read-only. Contact an administrator to update.')),
                        DatePicker::make('joining_date')
                            ->label(__('Joining Date'))
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText(__('Read-only. Contact an administrator to update.')),
                    ])
                    ->description(__('These fields are maintained by HR / administrators and cannot be edited from your profile. Submit a request to your administrator to change them.')),

                Section::make(__('Emergency Contact'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('emergency_contact_name')->label(__('Emergency Contact Name')),
                        TextInput::make('emergency_contact_phone')->label(__('Emergency Contact Phone')),
                    ]),

                Section::make(__('Notes'))
                    ->schema([
                        Textarea::make('notes')->label(__('Notes'))->columnSpanFull(),
                    ])->collapsed(),

                Section::make(__('Update Password'))
                    ->description(__('Ensure your account is using a long, random password to stay secure.'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('current_password')
                            ->label(__('Current Password'))
                            ->password()
                            ->autocomplete('current-password'),
                        TextInput::make('new_password')
                            ->label(__('New Password'))
                            ->password()
                            ->rule(Password::default())
                            ->autocomplete('new-password')
                            ->dehydrated(fn ($state) => filled($state)),
                        TextInput::make('new_password_confirmation')
                            ->label(__('Confirm New Password'))
                            ->password()
                            ->same('new_password')
                            ->requiredWith('new_password'),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('Save Changes'))
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $user = auth()->user();

        if (filled($data['new_password'] ?? null)) {
            if (! Hash::check($data['current_password'] ?? '', $user->password)) {
                $this->addError('data.current_password', __('The current password does not match.'));
                return;
            }
            $user->password = Hash::make($data['new_password']);
        }

        $user->fill([
            'full_name'                => $data['full_name'],
            'full_name_ar'             => $data['full_name_ar'] ?? null,
            'email'                    => $data['email'],
            'phone'                    => $data['phone'] ?? null,
            'photo_path'               => $data['photo_path'] ?? null,
            'preferred_locale'         => $data['preferred_locale'] ?? 'en',
            'emergency_contact_name'   => $data['emergency_contact_name'] ?? null,
            'emergency_contact_phone'  => $data['emergency_contact_phone'] ?? null,
            'notes'                    => $data['notes'] ?? null,
        ]);

        $user->save();

        Notification::make()
            ->title(__('Profile updated successfully'))
            ->success()
            ->send();

        $this->data['current_password'] = '';
        $this->data['new_password'] = '';
        $this->data['new_password_confirmation'] = '';
        $this->form->fill($this->data);
    }
}
