<?php

namespace App\Filament\Resources\Portfolio\PortfolioSkillResource\Pages;

use App\Filament\Resources\Portfolio\PortfolioSkillResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPortfolioSkill extends EditRecord
{
    protected static string $resource = PortfolioSkillResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
