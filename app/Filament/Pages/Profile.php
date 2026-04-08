<?php

namespace App\Filament\Pages;

use App\Filament\Pages\Settings;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class Profile extends Page implements HasActions
{
    use InteractsWithActions;

    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static string $view = 'filament.pages.profile';
    protected static bool $shouldRegisterNavigation = false;

    public ?array $data = [];

    public function mount(): void
    {
        $user = auth()->user();
        
        $this->form->fill([
            'full_name' => $user->full_name,
            'email' => $user->email,
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
                    ->schema([
                        TextInput::make('full_name')
                            ->label(__('Full Name'))
                            ->required(),
                        TextInput::make('email')
                            ->label(__('Email'))
                            ->email()
                            ->required()
                            ->unique('users', 'email', ignorable: auth()->user()),
                    ]),
                Section::make(__('Update Password'))
                    ->description(__('Ensure your account is using a long, random password to stay secure.'))
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
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    public function deleteAccountAction(): Action
    {
        return Action::make('deleteAccount')
            ->label(__('Delete Account'))
            ->color('danger')
            ->icon('heroicon-o-trash')
            ->requiresConfirmation()
            ->modalHeading(__('Delete Account'))
            ->modalDescription(__('Are you sure you want to delete your account? This action cannot be undone.'))
            ->modalSubmitActionLabel(__('Yes, delete my account'))
            ->action(fn () => $this->deleteAccount());
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

        if (filled($data['new_password'])) {
            if (!Hash::check($data['current_password'], $user->password)) {
                $this->addError('data.current_password', __('The current password does not match.'));
                return;
            }
            $user->password = Hash::make($data['new_password']);
        }

        $user->full_name = $data['full_name'];
        $user->email = $data['email'];
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

    public function deleteAccount(): void
    {
        $user = auth()->user();
        
        if (!$user) {
            return;
        }

        $userId = $user->id;

        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        \Illuminate\Support\Facades\DB::table('users')->where('id', $userId)->delete();

        throw new \Illuminate\Http\Exceptions\HttpResponseException(
            new \Illuminate\Http\RedirectResponse(route('filament.admin.auth.login'))
        );
    }
}
