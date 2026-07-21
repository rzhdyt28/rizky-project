<?php

namespace App\Filament\Resources\Undangan;

use App\Core\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * KELOLA PELANGGAN — satu-satunya jalan admin bikin User+Tenant baru dari
 * Filament (sebelumnya cuma bisa lewat registrasi mandiri di frontend
 * publik). Meniru persis alur AuthController::register() (lihat
 * CreateCustomer::afterCreate()) supaya konsisten dengan pendaftaran
 * mandiri: assignRole('user') + Tenant::create(id slug+random, owner_user_id).
 *
 * Cuma menampilkan User berrole 'user' (customer) -- akun staff
 * (super-admin/admin) TIDAK terlihat/terkelola di sini.
 */
class CustomerResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Undangan - Pelanggan';
    protected static ?string $navigationLabel = 'Kelola Pelanggan';
    protected static ?string $modelLabel = 'Pelanggan';

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
            ->modifyQueryUsing(fn ($query) => $query->role('user'))
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nama')->searchable(),
                Tables\Columns\TextColumn::make('email')->searchable(),
                Tables\Columns\TextColumn::make('tenants.name')->label('Tenant')->badge(),
                Tables\Columns\TextColumn::make('created_at')->label('Daftar sejak')->dateTime('d M Y')->sortable(),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()]);
    }

    public static function getRelations(): array
    {
        return [
            CustomerResource\RelationManagers\TenantsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => CustomerResource\Pages\ListCustomers::route('/'),
            'create' => CustomerResource\Pages\CreateCustomer::route('/create'),
            'edit'   => CustomerResource\Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
