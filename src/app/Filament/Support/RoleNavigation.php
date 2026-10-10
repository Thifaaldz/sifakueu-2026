<?php

namespace App\Filament\Support;

use App\Filament\Pages\AksesRolePage;
use App\Filament\Pages\SifakDashboard;
use App\Support\Access\RoleFeatureRegistry;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;

/**
 * Sidebar per role: hanya fitur milik role login (lihat RoleFeatureRegistry), berlabel Bahasa Indonesia.
 */
class RoleNavigation
{
    public static function build(NavigationBuilder $builder): NavigationBuilder
    {
        $builder->items([
            NavigationItem::make('Beranda')
                ->icon('heroicon-o-home')
                ->activeIcon('heroicon-s-home')
                ->url(fn () => SifakDashboard::getUrl())
                ->isActiveWhen(fn () => request()->routeIs(SifakDashboard::getRouteName())),
        ]);

        $groups = [];

        foreach (RoleFeatureRegistry::visibleFor(auth()->user()) as $group => $items) {
            $groups[] = NavigationGroup::make($group)
                ->collapsible()
                ->items(array_map(fn (array $item) => NavigationItem::make($item['label'])
                    ->icon($item['icon'] ?? 'heroicon-o-squares-2x2')
                    ->url($item['url'])
                    ->isActiveWhen(fn () => request()->routeIs($item['resource']::getRouteBaseName() . '.*')), $items));
        }

        $groups[] = NavigationGroup::make('Bantuan')
            ->collapsible()
            ->items([
                NavigationItem::make('Modul & Hak Akses')
                    ->icon('heroicon-o-question-mark-circle')
                    ->url(fn () => AksesRolePage::getUrl())
                    ->isActiveWhen(fn () => request()->routeIs(AksesRolePage::getRouteName())),
            ]);

        return $builder->groups($groups);
    }
}
