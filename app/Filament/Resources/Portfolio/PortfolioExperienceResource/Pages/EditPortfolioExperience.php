<?php

namespace App\Filament\Resources\Portfolio\PortfolioExperienceResource\Pages;

use App\Filament\Resources\Portfolio\PortfolioExperienceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPortfolioExperience extends EditRecord
{
    protected static string $resource = PortfolioExperienceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
