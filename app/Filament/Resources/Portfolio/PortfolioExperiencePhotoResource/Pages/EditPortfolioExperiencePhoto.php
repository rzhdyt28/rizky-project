<?php

namespace App\Filament\Resources\Portfolio\PortfolioExperiencePhotoResource\Pages;

use App\Filament\Resources\Portfolio\PortfolioExperiencePhotoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPortfolioExperiencePhoto extends EditRecord
{
    protected static string $resource = PortfolioExperiencePhotoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
