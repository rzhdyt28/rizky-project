<?php

namespace App\Filament\Resources\Undangan\ThemeAssetResource\Pages;

use App\Filament\Resources\Undangan\ThemeAssetResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListThemeAssets extends ListRecords
{
    protected static string $resource = ThemeAssetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
