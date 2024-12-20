<?php

namespace App\Filament\Resources\ProblemResource\Pages;

use App\Filament\Resources\ProblemResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListProblems extends ListRecords
{
    protected static string $resource = ProblemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }


    public function getTabs(): array
    {
        return [
            'All' => Tab::make(),
            'Dus' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('type', 'dus')),
            'Unit' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('type', 'unit')),
        ];
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return 'Dus';
    }
}
