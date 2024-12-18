<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BastResource\Pages;
use App\Filament\Resources\BastResource\RelationManagers;
use App\Models\Bast;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BastResource extends Resource
{
    protected static ?string $model = Bast::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('do')->label('No Do')->searchable(),
                TextColumn::make('name')->label('Kepada')->searchable(),
                TextColumn::make('city')->label('Kota')->searchable(),
            ])
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
            'index' => Pages\ListBasts::route('/'),
            'create' => Pages\CreateBast::route('/create'),
            'edit' => Pages\EditBast::route('/{record}/edit'),
        ];
    }
}
