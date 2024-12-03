<?php

namespace App\Filament\Resources\ReviewResource\Pages;

use App\Filament\Resources\ReviewResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
Use App\Models\Review;


class ListReviews extends ListRecords
{
    protected static string $resource = ReviewResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Reviews')->modifyQueryUsing(function ($query) {
                $query->withoutTrashed();
            })->badge(function () {
                $count = Review::count();
                return $count > 0 ? $count : null;
            }),
            'pending' => Tab::make('Pending')->modifyQueryUsing(function ($query) {
                $query->withoutTrashed()->where('status', 'pending');
            })->badge(function () {
                $count = Review::where('status', 'pending')->count();
                return $count > 0 ? $count : null;
            })->badgeColor('warning'),
            'approved' => Tab::make('Approved')->modifyQueryUsing(function ($query) {
                $query->withoutTrashed()->where('status', 'approved');
            })->badge(function () {
                $count = Review::where('status', 'approved')->count();
                return $count > 0 ? $count : null;
            })->badgeColor('success'),
            'rejected' => Tab::make('Rejected')->modifyQueryUsing(function ($query) {
                $query->withoutTrashed()->where('status', 'rejected');
            })->badge(function () {
                $count = Review::where('status', 'rejected')->count();
                return $count > 0 ? $count : null;
            })->badgeColor('danger'),
            'deleted' => Tab::make('Deleted')->modifyQueryUsing(fn ($query) => $query->onlyTrashed())
            ->badge(function () {
                $count = Review::onlyTrashed()->count();
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
