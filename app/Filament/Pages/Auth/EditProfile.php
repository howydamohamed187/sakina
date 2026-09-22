<?php

namespace App\Filament\Pages\Auth;

use App\Filament\Forms\PhoneInput;
use App\Support\Locales;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;
use Filament\Support\Enums\MaxWidth;

class EditProfile extends BaseEditProfile
{
    public static function isSimple(): bool
    {
        return false;
    }

    public function getMaxWidth(): MaxWidth|string|null
    {
        return MaxWidth::Full;
    }

    public static function getLabel(): string
    {
        return __('app.profile');
    }

    public function form(Form $form): Form
    {
        return $form;
    }

    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        Section::make(__('app.profile_photo'))
                            ->schema([
                                FileUpload::make('avatar')
                                    ->label(__('app.fields.avatar'))
                                    ->image()
                                    ->avatar()
                                    ->imageEditor()
                                    ->circleCropper()
                                    ->directory('avatars')
                                    ->disk('public')
                                    ->visibility('public')
                                    ->maxSize(2048)
                                    ->columnSpanFull(),
                            ]),
                        Section::make(__('app.profile_info'))
                            ->schema([
                                $this->getNameFormComponent()
                                    ->label(__('app.fields.name'))
                                    ->extraInputAttributes(['dir' => Locales::isRtl() ? 'rtl' : 'ltr']),
                                $this->getEmailFormComponent()
                                    ->label(__('app.fields.email'))
                                    ->extraInputAttributes(['dir' => 'ltr']),
                                PhoneInput::make()->columnSpanFull(),
                            ])
                            ->columns(2),
                        Section::make(__('app.profile_password'))
                            ->description(__('app.profile_password_hint'))
                            ->schema([
                                $this->getPasswordFormComponent()
                                    ->label(__('app.fields.password'))
                                    ->dehydrateStateUsing(fn (?string $state): ?string => $state)
                                    ->extraInputAttributes(['dir' => 'ltr']),
                                $this->getPasswordConfirmationFormComponent()
                                    ->label(__('app.fields.password_confirmation'))
                                    ->extraInputAttributes(['dir' => 'ltr']),
                            ])
                            ->columns(2),
                    ])
                    ->operation('edit')
                    ->model($this->getUser())
                    ->statePath('data')
                    ->inlineLabel(false),
            ),
        ];
    }

    protected function hasFullWidthFormActions(): bool
    {
        return false;
    }
}
