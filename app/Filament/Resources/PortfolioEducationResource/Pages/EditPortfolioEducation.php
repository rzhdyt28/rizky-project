<?php

namespace App\Filament\Resources\PortfolioEducationResource\Pages;

use App\Filament\Resources\PortfolioEducationResource;
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
