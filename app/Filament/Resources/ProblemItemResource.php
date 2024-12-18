<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProblemItemResource\Pages;
use App\Filament\Resources\ProblemItemResource\RelationManagers;
use App\Models\ProblemItem;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProblemItemResource extends Resource
{
    protected static ?string $model = ProblemItem::class;

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
                Select::make('product_id')
                    ->label('Product')
                    ->relationship('product', 'code')
                    ->required(),
                TextInput::make('qty')
                    ->label('Quantity')
                    ->integer()
                    ->default(0)
                    ->required(),
                TextInput::make('lot')
                    ->label('Lot')
                    ->nullable(),
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
                TextColumn::make('product.code')->label('Product Code')->searchable(),
                TextColumn::make('qty')->label('Quantity')->sortable(),
                TextColumn::make('lot')->label('Lot'),
                TextColumn::make('desc')->label('Description')->searchable(),
                // TextColumn::make('created_at')->label('Created At')->dateTime()->sortable(),
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
            'index' => Pages\ListProblemItems::route('/'),
            'create' => Pages\CreateProblemItem::route('/create'),
            'edit' => Pages\EditProblemItem::route('/{record}/edit'),
        ];
    }
}
