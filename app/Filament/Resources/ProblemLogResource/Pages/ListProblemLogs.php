<?php

namespace App\Filament\Resources\ProblemLogResource\Pages;

use App\Filament\Resources\ProblemLogResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProblemLogs extends ListRecords
{
    protected static string $resource = ProblemLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
