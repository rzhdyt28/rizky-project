<?php

namespace App\Filament\Resources\Undangan\InvitationResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class GiftsRelationManager extends RelationManager
{
    protected static string $relationship = 'gifts';
    protected static ?string $title = 'Hadiah Digital';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('type')->options([
                'bank' => 'Rekening Bank', 'ewallet' => 'E-Wallet',
                'qris' => 'QRIS', 'address' => 'Alamat Kirim',
            ])->required()->live(),
            Forms\Components\TextInput::make('provider')
                ->helperText('mis. BCA, OVO, Dana')
                ->visible(fn (Forms\Get $get) => in_array($get('type'), ['bank', 'ewallet'])),
            Forms\Components\TextInput::make('account_name')->label('Atas Nama')
                ->visible(fn (Forms\Get $get) => in_array($get('type'), ['bank', 'ewallet'])),
            Forms\Components\TextInput::make('account_number')->label('Nomor')
                ->visible(fn (Forms\Get $get) => in_array($get('type'), ['bank', 'ewallet'])),
            Forms\Components\FileUpload::make('qris_image')->image()->directory('undangan/qris')
                ->visible(fn (Forms\Get $get) => $get('type') === 'qris'),
            Forms\Components\Textarea::make('shipping_address')->rows(2)
                ->visible(fn (Forms\Get $get) => $get('type') === 'address'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('type')->badge(),
            Tables\Columns\TextColumn::make('provider'),
            Tables\Columns\TextColumn::make('account_number'),
        ])->headerActions([Tables\Actions\CreateAction::make()])
          ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }
}