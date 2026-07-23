<?php

namespace App\Filament\Resources\Skripsi;

use App\Modules\Skripsi\Core\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * Kelola akun login project Skripsi (database & auth terpisah dari
 * Undangan/Portfolio — lihat App\Modules\Skripsi\Core\Models\User).
 * Admin bisa buat/edit/hapus user dari sini tanpa perlu lewat halaman
 * registrasi publik di /skripsi/register.
 */
class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Skripsi';
    protected static ?string $navigationLabel = 'Kelola Pengguna';
    protected static ?string $modelLabel = 'Pengguna Skripsi';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label('Nama')->required(),
            Forms\Components\TextInput::make('email')->email()->required()
                ->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('password')
                ->label('Password')
                ->password()->revealable()
                ->required(fn (string $context) => $context === 'create')
                ->dehydrated(fn (?string $state) => filled($state))
                ->helperText('Kosongkan kalau tidak mau ubah password.'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('created_at')->label('Daftar sejak')->dateTime('d M Y')->sortable(),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => UserResource\Pages\ListUsers::route('/'),
            'create' => UserResource\Pages\CreateUser::route('/create'),
            'edit'   => UserResource\Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
