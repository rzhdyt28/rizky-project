<?php

namespace App\Filament\Resources\Portfolio\PortfolioSkillResource\Pages;

use App\Filament\Resources\Portfolio\PortfolioSkillResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPortfolioSkills extends ListRecords
{
    protected static string $resource = PortfolioSkillResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
