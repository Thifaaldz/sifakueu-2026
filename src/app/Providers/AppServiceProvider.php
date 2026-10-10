<?php

namespace App\Providers;

use App\Policies\ActivityPolicy;
use App\Support\Livewire\ReportBusinessValidationErrors;
use App\Support\Tenancy\TenantContext;
use Filament\Actions\MountableAction;
use Filament\Notifications\Livewire\Notifications;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\VerticalAlignment;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\ValidationException;
use Livewire\ComponentHookRegistry;
use Spatie\Activitylog\Models\Activity;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TenantContext::class);

        // Harus didaftarkan sebelum Livewire boot agar hook terpasang pada mount/hydrate.
        ComponentHookRegistry::register(ReportBusinessValidationErrors::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Activity::class, ActivityPolicy::class);
        Page::formActionsAlignment(Alignment::Right);
        Notifications::alignment(Alignment::End);
        Notifications::verticalAlignment(VerticalAlignment::End);
        Page::$reportValidationErrorUsing = function (ValidationException $exception) {
            Notification::make()
                ->title($exception->getMessage())
                ->danger()
                ->send();
        };
        // Data terbaru tampil paling atas; resource yang mengatur defaultSort sendiri tetap menimpa ini.
        Table::configureUsing(fn (Table $table) => $table->defaultSort(
            fn (Builder $query): Builder => $query->orderByDesc($query->getModel()->getQualifiedKeyName())
        ));
        MountableAction::configureUsing(function (MountableAction $action) {
            $action->modalFooterActionsAlignment(Alignment::Right);
        });
    }
}
