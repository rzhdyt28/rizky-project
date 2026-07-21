<?php

namespace App\Filament\Resources\Portfolio\PortfolioProfileResource\Pages;

use App\Filament\Resources\Portfolio\PortfolioProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPortfolioProfile extends EditRecord
{
    protected static string $resource = PortfolioProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
