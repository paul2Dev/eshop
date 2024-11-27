<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;


class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Orders')->modifyQueryUsing(function ($query) {
                $query->withoutTrashed();
            })->badge(function () {
                $count = Order::count();
                return $count > 0 ? $count : null;
            }),
            'pending' => Tab::make('Pending')->modifyQueryUsing(function ($query) {
                $query->withoutTrashed()->where('status', 'pending');
            })->badge(function () {
                $count = Order::where('status', 'pending')->count();
                return $count > 0 ? $count : null;
            })->badgeColor('warning'),
            'completed' => Tab::make('Completed')->modifyQueryUsing(function ($query) {
                $query->withoutTrashed()->where('status', 'completed');
            })->badge(function () {
                $count = Order::where('status', 'completed')->count();
                return $count > 0 ? $count : null;
            })->badgeColor('success'),
            'canceled' => Tab::make('Canceled')->modifyQueryUsing(function ($query) {
                $query->withoutTrashed()->where('status', 'canceled');
            })->badge(function () {
                $count = Order::where('status', 'canceled')->count();
                return $count > 0 ? $count : null;
            })->badgeColor('danger'),
            'processing' => Tab::make('Processing')->modifyQueryUsing(function ($query) {
                $query->withoutTrashed()->where('status', 'processing');
            })->badge(function () {
                $count = Order::where('status', 'processing')->count();
                return $count > 0 ? $count : null;
            })->badgeColor('info'),
            'deleted' => Tab::make('Deleted')->modifyQueryUsing(function ($query) {
                $query->onlyTrashed();
            })->badge(function () {
                $count = Order::onlyTrashed()->count();
                return $count > 0 ? $count : null;
            })->badgeColor('danger'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
