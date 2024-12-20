<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AlamatResource\Pages;
use App\Filament\Resources\AlamatResource\RelationManagers;
use App\Models\Alamat;
use App\Services\DoService;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AlamatResource extends Resource
{
    protected static ?string $model = Alamat::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Fieldset::make('DO')->schema([
                    TextInput::make('get_do')->default('CENT/OUT/')
                        ->placeholder('Cari Nomor DO')->live()->debounce(1000),
                    Select::make('list_do')
                        ->placeholder('Select DO')->live()
                        ->searchable()
                        ->preload()
                        ->options(function (Get $get, Set $set) {
                            // $set('list_do', []);
                            if ($get('get_do') == 'CENT/OUT/' || $get('get_do') == 'CENT/OUT') {
                                return [];
                            } else {
                                try {
                                    $serv = new DoService();
                                    $data = $serv->search($get('get_do'));
                                    $col = collect($data['result']['records'] ?? [])
                                        ->mapWithKeys(function ($item) {
                                            return [$item['id'] => $item['name']];
                                        })
                                        ->toArray();
                                    return $col;
                                } catch (\Throwable $th) {
                                    return [];
                                }
                            }
                        })
                        ->afterStateUpdated(function (Get $get, Set $set) {
                            try {
                                $id = $get('list_do');
                                $serv = new DoService();
                                $res = $serv->detail(intval($id));
                                $data = $res['result'][0];
                                $set('tujuan', $res['result'][0]['partner_id'][1]);

                                $set('up', $res['result'][0]['delivery_manual']);
                                $set('alamat', $res['result'][0]['partner_address']);
                                $set('ekspedisi', $res['result'][0]['ekspedisi_id'][1]);
                                $set('do', $res['result'][0]['display_name']);
                                $set('epur', $res['result'][0]['no_aks']);
                            } catch (\Throwable $th) {
                                // return [];
                            }
                        })
                ]),
                Textarea::make('tujuan')->label('Tujuan')->required()->maxLength(255),
                Textarea::make('alamat')->label('Alamat')->required()->maxLength(255),
                TextInput::make('ekspedisi')->label('Ekspedisi')->required()->maxLength(255),
                TextInput::make('koli')->label('Jumlah Koli')->integer()->required()->minValue(1)->default(1),
                TextInput::make('up')->label('UP')->nullable()->maxLength(255),
                TextInput::make('telp')->label('Telp')->nullable()->maxLength(255),
                TextInput::make('do')->label('No DO')->required()->maxLength(255),
                TextInput::make('epur')->label('Epurchasing')->nullable()->maxLength(255),
                TextInput::make('untuk')->label('Untuk')->nullable()->maxLength(255),
                TextInput::make('nilai')->label('Nilai')->nullable()->maxLength(255),
                Textarea::make('note')->label('Note')->nullable()->maxLength(255),
                Textarea::make('note_wh')->label('Note WH')->nullable()->maxLength(255)->disabled(),
                Grid::make(3)->schema([
                    Toggle::make('is_do')
                        ->label('Surat Jalan')
                        ->default(false) // Nilai default adalah false (no)
                        ->afterStateHydrated(function (Toggle $component, $state) {
                            // Konversi nilai dari database (yes/no) menjadi boolean
                            $component->state($state === 'yes');
                        })
                        ->dehydrateStateUsing(fn(bool $state) => $state ? 'yes' : 'no'), // Konversi sebelum simpan,

                    Toggle::make('is_pk')
                        ->label('Packing Kayu')
                        ->default(false)
                        ->afterStateHydrated(function (Toggle $component, $state) {
                            $component->state($state === 'yes');
                        })
                        ->dehydrateStateUsing(fn(bool $state) => $state ? 'yes' : 'no'),

                    Toggle::make('is_banting')
                        ->label('Jangan Dibanting')
                        ->default(false)
                        ->afterStateHydrated(function (Toggle $component, $state) {
                            $component->state($state === 'yes');
                        })
                        ->dehydrateStateUsing(fn(bool $state) => $state ? 'yes' : 'no'),
                ]),
                Repeater::make('detail')
                    ->label('Products')
                    ->relationship('detail')
                    ->schema([
                        Select::make('product_id')
                            ->label('Product')
                            ->relationship('product', 'code')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('qty')->label('Quantity')->required(),
                        Textarea::make('desc')->label('Description')->nullable(),
                        Textarea::make('lot')->label('Lot')->nullable(),
                    ])
                    ->columnSpan('full')
                    ->columns(2)
                    ->addActionLabel('Add New Item'),


                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('do')->label('No Do')->searchable()->sortable(),
                TextColumn::make('tujuan')->label('Tujuan')->searchable()->sortable(),
                TextColumn::make('ekspedisi')->label('Ekspedisi')->searchable()->sortable(),
            ])
            ->filters([
                //
            ])
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
            'index' => Pages\ListAlamats::route('/'),
            // 'create' => Pages\CreateAlamat::route('/create'),
            // 'edit' => Pages\EditAlamat::route('/{record}/edit'),
        ];
    }


    // public static function infolist(Infolist $infolist): Infolist
    // {
    //     return $infolist
    //         ->schema([
    //             TextEntry::make('tujuan'),
    //             TextEntry::make('do'),
    //             TextEntry::make('alamat')
    //                 ->columnSpanFull(),
    //         ]);
    // }
}
