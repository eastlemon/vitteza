<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * System settings (Spec §15.4, §16.2): finance formula and rating weights.
 * Access — admin only. Livewire form (public properties + wire:model).
 */
class SettingsPage extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-cog-6-tooth';

    public static function getNavigationLabel(): string
    {
        return __('settings.nav');
    }

    protected static ?string $slug = 'settings';

    protected static ?int $navigationSort = 8;

    protected string $view = 'filament.pages.settings';

    public string $income_share = '0.20';
    public string $logist_share = '0.50';
    public string $income_trigger_status = 'paid';
    public string $rating_w_trips = '0.40';
    public string $rating_w_income = '0.60';

    public function mount(): void
    {
        $this->income_share = Setting::getString('income_share', '0.20');
        $this->logist_share = Setting::getString('logist_share', '0.50');
        $this->income_trigger_status = Setting::getString('income_trigger_status', 'paid');
        $this->rating_w_trips = Setting::getString('rating_w_trips', '0.40');
        $this->rating_w_income = Setting::getString('rating_w_income', '0.60');
    }

    public function save(): void
    {
        Setting::set('income_share', $this->income_share);
        Setting::set('logist_share', $this->logist_share);
        Setting::set('income_trigger_status', $this->income_trigger_status);
        Setting::set('rating_w_trips', $this->rating_w_trips);
        Setting::set('rating_w_income', $this->rating_w_income);

        Notification::make()->title(__('settings.saved'))->success()->send();
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() === true;
    }
}
