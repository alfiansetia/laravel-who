<?php

namespace App\Filament\Resources\ProblemItemResource\Pages;

use App\Filament\Resources\ProblemItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProblemItems extends ListRecords
{
    protected static string $resource = ProblemItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
