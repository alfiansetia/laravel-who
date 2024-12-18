<?php

namespace App\Filament\Resources\ProblemLogResource\Pages;

use App\Filament\Resources\ProblemLogResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProblemLog extends EditRecord
{
    protected static string $resource = ProblemLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
