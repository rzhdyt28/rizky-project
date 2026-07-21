<?php

namespace App\Filament\Resources\Portfolio\PortfolioEducationResource\Pages;

use App\Filament\Resources\Portfolio\PortfolioEducationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPortfolioEducation extends ListRecords
{
    protected static string $resource = PortfolioEducationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
