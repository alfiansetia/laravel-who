<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProblemResource\Pages;
use App\Filament\Resources\ProblemResource\RelationManagers;
use App\Models\Problem;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\HasManyRepeater;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProblemResource extends Resource
{
    protected static ?string $model = Problem::class;

    protected static ?string $navigationIcon = 'heroicon-o-trash';
    protected static ?string $navigationGroup = 'Problem';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('date')
                    ->label('Date')
                    ->default(date('Y-m-d'))
                    ->required(),
                TextInput::make('number')
                    ->label('Number')
                    ->default(strtoupper(date('Y-M')))
                    ->required(),
                Select::make('type')
                    ->label('Type')
                    ->options([
                        'dus' => 'Dus',
                        'unit' => 'Unit',
                    ])
                    ->default('unit')
                    ->required(),
                Select::make('stock')
                    ->label('Stock')
                    ->options([
                        'stock' => 'Stock',
                        'import' => 'Import',
                    ])
                    ->default('stock')
                    ->required(),
                TextInput::make('ri_po')->label('RI/PO')->nullable(),
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'done' => 'Done',
                    ])
                    ->default('pending')
                    ->required(),
                DatePicker::make('email_on')->label('Email On')->nullable(),
                TextInput::make('pic')->label('PIC')->nullable()->default('Karim'),

                Repeater::make('items')
                    ->label('Problem Items')
                    ->relationship('items')
                    ->schema([
                        Select::make('product_id')
                            ->label('Product')
                            ->relationship('product', 'code')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('qty')->label('Quantity')->integer()->required(),
                        TextInput::make('lot')->label('Lot')->nullable(),
                        Textarea::make('desc')->label('Description')->nullable(),
                    ])
                    ->columnSpan('full')
                    ->columns(2)
                    ->required()
                    ->addActionLabel('Add New Item'),
                Repeater::make('logs')
                    ->label('Problem Logs')
                    ->relationship('logs')
                    ->schema([
                        DatePicker::make('date')
                            ->label('Date')
                            ->default(date('Y-m-d'))
                            ->required(),
                        Textarea::make('desc')
                            ->label('Description')
                            ->nullable(),
                    ])
                    ->columnSpan('full')
                    ->columns(2)
                    // ->required()
                    ->addActionLabel('Add New Log'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('date')->label('Date')->date()->searchable(),
                TextColumn::make('number')->label('Number')->searchable(),
                // TextColumn::make('type')->label('Type'),
                TextColumn::make('stock')->label('Stock'),
                TextColumn::make('email_on')->label('Email On')->date(),
                TextColumn::make('pic')->label('PIC'),
                TextColumn::make('status')->label('Status'),
                // TextColumn::make('created_at')->label('Created At')->dateTime()->sortable(),
                TextColumn::make('items_count')
                    ->label('Total Items')
                    ->counts('items'),
                TextColumn::make('logs_count')
                    ->label('Total Logs')
                    ->counts('logs'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'done' => 'Done',
                    ]),
                SelectFilter::make('type')
                    ->options([
                        'dus' => 'dus',
                        'unit' => 'unit',
                    ]),
                // ::make('date')
                //     ->label('Date')
                //     ->date(),
            ])
            ->defaultSort('id')
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->searchable()
            ->defaultSort('id', 'desc');
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
            'index' => Pages\ListProblems::route('/'),
            // 'create' => Pages\CreateProblem::route('/create'),
            // 'view' => Pages\ViewProblem::route('/{record}'),
            // 'edit' => Pages\EditProblem::route('/{record}/edit'),
        ];
    }
}
