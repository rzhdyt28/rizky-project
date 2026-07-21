<?php

namespace App\Filament\Resources\Portfolio\PortfolioEducationResource\Pages;

use App\Filament\Resources\Portfolio\PortfolioEducationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPortfolioEducation extends EditRecord
{
    protected static string $resource = PortfolioEducationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
