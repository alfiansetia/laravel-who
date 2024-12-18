<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProblemLogResource\Pages;
use App\Filament\Resources\ProblemLogResource\RelationManagers;
use App\Models\ProblemLog;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProblemLogResource extends Resource
{
    protected static ?string $model = ProblemLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Problem';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('problem_id')
                    ->label('Problem')
                    ->relationship('problem', 'number')
                    ->searchable()
                    ->preload()
                    ->required(),
                DatePicker::make('date')
                    ->label('Date')
                    ->default(date('Y-m-d'))
                    ->required(),
                Textarea::make('desc')
                    ->label('Description')
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('problem.number')->label('Problem Number')->searchable(),
                TextColumn::make('date')->label('Date')->date()->searchable(),
                TextColumn::make('desc')->label('Description')->searchable(),
                // TextColumn::make('created_at')->label('Created At')->dateTime(),
            ])
            ->defaultSort('id')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->searchable();
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProblemLogs::route('/'),
            'create' => Pages\CreateProblemLog::route('/create'),
            'edit' => Pages\EditProblemLog::route('/{record}/edit'),
        ];
    }
}
