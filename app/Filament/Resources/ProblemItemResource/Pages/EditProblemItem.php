<?php

namespace App\Filament\Resources\ProblemItemResource\Pages;

use App\Filament\Resources\ProblemItemResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProblemItem extends EditRecord
{
    protected static string $resource = ProblemItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
