<?php

namespace App\Filament\Resources\Undangan;

use App\Core\Models\Plan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PlanResource extends Resource
{
    protected static ?string $model = Plan::class;
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'Undangan - Komersial';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Paket')->schema([
                Forms\Components\TextInput::make('name')->required(),
                Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('price')->numeric()->prefix('Rp')->required(),
                Forms\Components\TextInput::make('duration_days')->numeric()->default(365),
                Forms\Components\Toggle::make('is_active')->default(true),
            ])->columns(2),

            Forms\Components\Section::make('Kuota')->schema([
                Forms\Components\TextInput::make('max_invitations')->numeric()->default(1)->label('Maks. undangan'),
                Forms\Components\TextInput::make('max_guests')->numeric()->default(100)->label('Maks. tamu'),
                Forms\Components\TextInput::make('max_photos')->numeric()->default(10)->label('Maks. foto galeri'),
                Forms\Components\TextInput::make('max_love_stories')->numeric()->default(0)->label('Maks. kisah cinta'),
            ])->columns(4),

            Forms\Components\Section::make('Fitur Section Undangan')
                ->description('Toggle ON = section tampil untuk pelanggan paket ini; OFF = tersembunyi otomatis.')
                ->schema([
                    Forms\Components\Toggle::make('gallery_enabled')->label('Galeri foto'),
                    Forms\Components\Toggle::make('love_story_enabled')->label('Kisah cinta'),
                    Forms\Components\Toggle::make('gift_enabled')->label('Hadiah digital'),
                    Forms\Components\Toggle::make('countdown_enabled')->label('Countdown timer'),
                    Forms\Components\Toggle::make('video_enabled')->label('Video prewedding'),
                    Forms\Components\Toggle::make('co_host_enabled')->label('Turut mengundang'),
                    Forms\Components\Toggle::make('maps_enabled')->label('Peta lokasi (embed)'),
                    Forms\Components\Toggle::make('music_enabled')->label('Musik latar'),
                ])->columns(4),

            Forms\Components\Section::make('Fitur Kustomisasi & Premium')->schema([
                Forms\Components\Toggle::make('custom_font_enabled')->label('Custom font'),
                Forms\Components\Toggle::make('custom_background_enabled')->label('Custom background'),
                Forms\Components\Toggle::make('custom_ornament_enabled')->label('Custom ornamen'),
                Forms\Components\Toggle::make('custom_domain')->label('Custom domain'),
                Forms\Components\Toggle::make('remove_branding')->label('Hapus branding'),
            ])->columns(5),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->searchable(),
            Tables\Columns\TextColumn::make('price')->money('IDR'),
            Tables\Columns\TextColumn::make('max_invitations')->label('Undangan'),
            Tables\Columns\TextColumn::make('max_photos')->label('Foto'),
            Tables\Columns\IconColumn::make('video_enabled')->label('Video')->boolean(),
            Tables\Columns\IconColumn::make('co_host_enabled')->label('Turut mengundang')->boolean(),
            Tables\Columns\IconColumn::make('maps_enabled')->label('Maps')->boolean(),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => PlanResource\Pages\ListPlans::route('/'),
            'create' => PlanResource\Pages\CreatePlan::route('/create'),
            'edit'   => PlanResource\Pages\EditPlan::route('/{record}/edit'),
        ];
    }
}
